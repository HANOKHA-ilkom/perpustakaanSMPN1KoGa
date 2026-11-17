/**
 * PerpusScanner - Omni-Directional 1D & 2D Barcode Engine
 * Khusus Sistem Informasi Perpustakaan SMP Negeri 1 Kota Gajah
 *
 * Fitur Utama:
 * 1. Pencegahan Bug Histori / Memory Lingering:
 *    - Reset total buffer kanvas & memori frame setiap stop & start baru
 *    - AbortController untuk membatalkan request jaringan backend yang tertunda
 *    - Session ID acak untuk menolak respon frame lama
 *    - Warmup frame flush (membuang 8 frame pertama dari buffer DroidCam/Webcam)
 * 2. Prioritas & Deteksi Otomatis DroidCam (Kamera HP Android / PC Virtual Webcam)
 * 3. Omni-Directional 360° Scanning: Valid untuk barcode EAN-13 / ISBN horizontal, vertikal (non-portrait), miring, dan terbalik
 * 4. Multi-Tier Engine:
 *    - ZXing JavaScript dengan HTMLCanvasElementLuminanceSource + HybridBinarizer & GlobalHistogramBinarizer
 *    - Backend AI OpenCV Scanner Fallback (/admin/scan-image-api)
 * 5. Dukungan Unggah File Foto/Gambar Barcode, Drag-and-Drop, dan Clipboard Paste (Ctrl+V)
 * 6. Audio Beep sintetis Web Audio API (dua nada 880Hz & 1760Hz)
 * 7. Visual Scanning Laser & Kontrol Rotasi Video (0°, 90°, 180°, 270°)
 */

// 1. Audio Beep Feedback (Web Audio API Synthesizer)
let _sharedScannerAudioCtx = null;
function getScannerAudioContext() {
  try {
    if (!_sharedScannerAudioCtx) {
      const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
      if (AudioCtxClass) {
        _sharedScannerAudioCtx = new AudioCtxClass();
      }
    }
    if (_sharedScannerAudioCtx && _sharedScannerAudioCtx.state === 'suspended') {
      _sharedScannerAudioCtx.resume().catch(() => {});
    }
  } catch (e) {}
  return _sharedScannerAudioCtx;
}

// Auto-unlock Web Audio context saat pengguna berinteraksi pertama kali dengan halaman
if (typeof document !== 'undefined') {
  const unlockAudio = () => {
    try {
      const ctx = getScannerAudioContext();
      if (ctx && ctx.state === 'suspended') {
        ctx.resume();
      }
    } catch(e) {}
    ['click', 'touchstart', 'keydown'].forEach(evt => document.removeEventListener(evt, unlockAudio));
  };
  ['click', 'touchstart', 'keydown'].forEach(evt => document.addEventListener(evt, unlockAudio, { once: true, passive: true }));
}

function playScannerBeep() {
  try {
    const ctx = getScannerAudioContext();
    if (!ctx) return;

    const playTone = () => {
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      // Nada khas barcode POS Scanner (1200 Hz -> 1800 Hz, jernih & mantap)
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(1200, now);
      osc.frequency.setValueAtTime(1800, now + 0.04);

      gain.gain.setValueAtTime(0.45, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now);
      osc.stop(now + 0.15);
    };

    if (ctx.state === 'suspended') {
      ctx.resume().then(playTone).catch(playTone);
    } else {
      playTone();
    }
  } catch (e) {
    console.warn('Audio beep notice:', e);
  }
}

// 2. Class PerpusScanner
class PerpusScanner {
  constructor(options) {
    this.containerId    = options.containerId;
    this.selectCameraId  = options.selectCameraId;
    this.statusId       = options.statusId;
    this.onScanSuccess  = options.onScanSuccess;
    this.onError        = options.onError;
    this.baseApiUrl     = options.baseApiUrl || '/';

    this.container      = document.getElementById(this.containerId);
    this.selectCamera   = document.getElementById(this.selectCameraId);
    this.statusEl       = document.getElementById(this.statusId);

    this.videoStream    = null;
    this.videoElement   = null;
    this.isScanning     = false;
    this.scanInterval   = null;
    this.currentRotation = 0; // 0, 90, 180, 270 deg

    // Session ID unik untuk mencegah histori kamera / respon lama
    this.sessionId      = 0;
    this.abortController = null;

    // Warmup frame counter (buang frame awal agar tidak membaca sisa buffer DroidCam)
    this.warmupFrames   = 0;

    // Offscreen Canvas untuk multi-angle frame rotation (dimulai dalam kondisi bersih)
    this.canvas0        = document.createElement('canvas'); // 0 deg
    this.canvas90       = document.createElement('canvas'); // 90 deg
    this.resetCanvases();

    // Timestamp server check throttle
    this.lastServerCheck = 0;
    this.serverCheckPending = false;

    // ZXing Reader instance
    this.zxingReader    = null;

    // Native BarcodeDetector instance
    this.barcodeDetector = null;
    this.hasBarcodeDetector = false;

    this.initEngines();
    this.initGlobalPasteHandler();
  }

  // Bersihkan total buffer kanvas agar histori gambar lama 100% terhapus
  resetCanvases() {
    try {
      this.canvas0.width = 1;
      this.canvas0.height = 1;
      const ctx0 = this.canvas0.getContext('2d');
      if (ctx0) ctx0.clearRect(0, 0, 1, 1);

      this.canvas90.width = 1;
      this.canvas90.height = 1;
      const ctx90 = this.canvas90.getContext('2d');
      if (ctx90) ctx90.clearRect(0, 0, 1, 1);
    } catch (e) {}
  }

  // Inisialisasi Engine Pemindai
  initEngines() {
    if ('BarcodeDetector' in window) {
      try {
        BarcodeDetector.getSupportedFormats().then(supported => {
          const wanted = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf', 'qr_code'];
          const formats = wanted.filter(f => supported.includes(f));
          if (formats.length > 0) {
            this.barcodeDetector = new BarcodeDetector({ formats: formats });
            this.hasBarcodeDetector = true;
          }
        }).catch(() => {});
      } catch (err) {}
    }

    this.setupZxingReader();
  }

  setupZxingReader() {
    if (window.ZXing && window.ZXing.MultiFormatReader) {
      try {
        const hints = new Map();
        if (window.ZXing.DecodeHintType) {
          hints.set(window.ZXing.DecodeHintType.TRY_HARDER, true);
          if (window.ZXing.BarcodeFormat) {
            hints.set(window.ZXing.DecodeHintType.POSSIBLE_FORMATS, [
              window.ZXing.BarcodeFormat.EAN_13,
              window.ZXing.BarcodeFormat.EAN_8,
              window.ZXing.BarcodeFormat.CODE_128,
              window.ZXing.BarcodeFormat.CODE_39,
              window.ZXing.BarcodeFormat.UPC_A,
              window.ZXing.BarcodeFormat.UPC_E,
              window.ZXing.BarcodeFormat.ITF,
              window.ZXing.BarcodeFormat.QR_CODE
            ]);
          }
        }
        this.zxingReader = new window.ZXing.MultiFormatReader();
        this.zxingReader.setHints(hints);
      } catch (err) {
        console.warn('[PerpusScanner] ZXing setup error:', err);
      }
    }
  }

  // Ambil daftar kamera yang tersedia dengan nama lengkap
  async enumerateCameras() {
    try {
      const testStream = await navigator.mediaDevices.getUserMedia({ video: true });
      testStream.getTracks().forEach(t => t.stop());
    } catch (e) {
      console.warn('[PerpusScanner] Izin kamera notice:', e);
    }

    try {
      const devices = await navigator.mediaDevices.enumerateDevices();
      return devices.filter(d => d.kind === 'videoinput');
    } catch (e) {
      console.error('[PerpusScanner] enumerateDevices error:', e);
      return [];
    }
  }

  // Render & Mulai Pemindaian Kamera (Session Baru)
  async start() {
    this.stop();

    // Buat session ID baru dan reset memori
    this.sessionId = Date.now() + Math.random();
    this.resetCanvases();

    if (!this.container) return;

    if (this.statusEl) {
      this.statusEl.innerHTML = '<span class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Mendeteksi kamera DroidCam / Webcam...</span>';
    }

    const cameras = await this.enumerateCameras();
    if (!cameras || cameras.length === 0) {
      if (this.statusEl) {
        this.statusEl.innerHTML = `
          <div class="alert alert-warning py-2 mb-0 text-left text-xs">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada kamera terdeteksi.</strong>
            <div>Anda tetap dapat <strong>mengunggah file foto barcode</strong> atau mengetikkan nomor ISBN secara manual.</div>
          </div>
        `;
      }
      this.setupViewportUI(false);
      return;
    }

    let selectedDeviceId = cameras[0].deviceId;
    let isDroidCamFound = false;

    if (this.selectCamera) {
      this.selectCamera.innerHTML = '';
      cameras.forEach((cam, idx) => {
        const opt = document.createElement('option');
        opt.value = cam.deviceId;
        const lbl = cam.label || ('Kamera ' + (idx + 1));
        const isDroid = lbl.toLowerCase().includes('droidcam') || lbl.toLowerCase().includes('droid') || lbl.toLowerCase().includes('wireless');

        if (isDroid) {
          opt.text = '📱 ' + lbl + ' (DroidCam HP Rekomendasi ⭐)';
          if (!isDroidCamFound) {
            selectedDeviceId = cam.deviceId;
            isDroidCamFound = true;
          }
        } else {
          opt.text = '📷 ' + lbl;
        }

        this.selectCamera.appendChild(opt);
      });

      this.selectCamera.value = selectedDeviceId;

      this.selectCamera.onchange = () => {
        this.startWithDeviceId(this.selectCamera.value);
      };
    }

    await this.startWithDeviceId(selectedDeviceId, isDroidCamFound);
  }

  // Mulai stream dengan Device ID tertentu
  async startWithDeviceId(deviceId, isDroidCam = false) {
    this.stopStreamOnly();

    // Reset kanvas dan memori
    this.sessionId = Date.now() + Math.random();
    this.resetCanvases();
    this.warmupFrames = 8; // Buang 8 frame pertama agar tidak membaca histori gambar lama

    this.setupViewportUI(isDroidCam);

    const constraints = {
      video: {
        deviceId: deviceId ? { exact: deviceId } : undefined,
        width: { ideal: 1280, min: 640 },
        height: { ideal: 720, min: 480 },
        frameRate: { ideal: 30 }
      }
    };

    try {
      this.videoStream = await navigator.mediaDevices.getUserMedia(constraints);
      this.videoElement.srcObject = this.videoStream;
      await this.videoElement.play();

      this.isScanning = true;

      const track = this.videoStream.getVideoTracks()[0];
      const settings = track ? track.getSettings() : {};
      const resText = (settings.width && settings.height) ? `(${settings.width}x${settings.height})` : '';

      if (this.statusEl) {
        this.statusEl.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-success font-weight-bold">
              <i class="fas fa-video mr-1"></i> Kamera Aktif ${resText} ${isDroidCam ? '<span class="badge badge-success ml-1">DroidCam HP</span>' : ''}
            </span>
            <span class="badge badge-light border text-muted">
              <i class="fas fa-crosshairs mr-1"></i> Multi-Angle 360°
            </span>
          </div>
          <small class="text-muted d-block mt-1">
            Arahkan barcode sampul belakang buku. Barcode dapat dalam posisi <strong>tidur (horizontal)</strong> atau <strong>tegak (vertikal/portrait)</strong>.
          </small>
        `;
      }

      this.startScanLoop();

    } catch (err) {
      console.error('[PerpusScanner] Gagal mengakses kamera:', err);
      if (this.statusEl) {
        this.statusEl.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle mr-1"></i> Gagal membuka video kamera: ${err.message || err}. Anda dapat menggunakan tombol <strong>Unggah Foto Barcode</strong> di bawah.</span>`;
      }
    }
  }

  // Setup DOM & UI Pemindai dalam container
  setupViewportUI(isDroidCam) {
    this.container.innerHTML = '';
    this.container.style.position = 'relative';
    this.container.style.width = '100%';
    this.container.style.minHeight = '320px';
    this.container.style.height = '360px';
    this.container.style.background = '#0d1117';
    this.container.style.borderRadius = '8px';
    this.container.style.overflow = 'hidden';
    this.container.style.boxShadow = 'inset 0 0 15px rgba(0,0,0,0.8)';
    this.container.style.border = '2px solid #28a745';

    // Elemen Video
    this.videoElement = document.createElement('video');
    this.videoElement.setAttribute('autoplay', '');
    this.videoElement.setAttribute('playsinline', '');
    this.videoElement.setAttribute('muted', '');
    this.videoElement.style.width = '100%';
    this.videoElement.style.height = '100%';
    this.videoElement.style.objectFit = 'cover';
    this.videoElement.style.display = 'block';
    this.videoElement.style.transition = 'transform 0.25s ease';
    this.videoElement.style.transform = `rotate(${this.currentRotation}deg)`;
    this.container.appendChild(this.videoElement);

    // Target Box & Laser Scanline Overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'absolute';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.pointerEvents = 'none';
    overlay.style.display = 'flex';
    overlay.style.justifyContent = 'center';
    overlay.style.alignItems = 'center';

    overlay.innerHTML = `
      <div style="position: relative; width: 82%; height: 75%; max-width: 400px; max-height: 270px; border: 2px dashed rgba(40, 167, 69, 0.85); border-radius: 12px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);">
        <!-- 4 Corner Brackets -->
        <div style="position: absolute; top: -3px; left: -3px; width: 24px; height: 24px; border-top: 4px solid #28a745; border-left: 4px solid #28a745; border-top-left-radius: 8px;"></div>
        <div style="position: absolute; top: -3px; right: -3px; width: 24px; height: 24px; border-top: 4px solid #28a745; border-right: 4px solid #28a745; border-top-right-radius: 8px;"></div>
        <div style="position: absolute; bottom: -3px; left: -3px; width: 24px; height: 24px; border-bottom: 4px solid #28a745; border-left: 4px solid #28a745; border-bottom-left-radius: 8px;"></div>
        <div style="position: absolute; bottom: -3px; right: -3px; width: 24px; height: 24px; border-bottom: 4px solid #28a745; border-right: 4px solid #28a745; border-bottom-right-radius: 8px;"></div>
        
        <!-- Laser Sweep Line -->
        <div class="perpus-scanner-laser"></div>

        <!-- Crosshair center guide -->
        <div style="position: absolute; top: 50%; left: 50%; width: 16px; height: 16px; transform: translate(-50%, -50%); border-top: 2px solid rgba(255,255,255,0.7); border-left: 2px solid rgba(255,255,255,0.7);"></div>
      </div>
    `;
    this.container.appendChild(overlay);

    // CSS Animasi Laser
    if (!document.getElementById('perpus-scanner-css')) {
      const style = document.createElement('style');
      style.id = 'perpus-scanner-css';
      style.innerHTML = `
        @keyframes perpusLaserAnim {
          0% { top: 6%; opacity: 0.9; }
          50% { top: 92%; opacity: 1; }
          100% { top: 6%; opacity: 0.9; }
        }
        .perpus-scanner-laser {
          position: absolute;
          left: 4%;
          width: 92%;
          height: 3px;
          background: #28a745;
          box-shadow: 0 0 10px #28a745, 0 0 18px #20c997;
          border-radius: 2px;
          animation: perpusLaserAnim 2s infinite ease-in-out;
        }
      `;
      document.head.appendChild(style);
    }

    // Tombol Floating di Atas Video
    const toolbar = document.createElement('div');
    toolbar.style.position = 'absolute';
    toolbar.style.top = '10px';
    toolbar.style.right = '10px';
    toolbar.style.zIndex = '10';
    toolbar.style.display = 'flex';
    toolbar.style.gap = '6px';

    // Tombol Putar Video 90°
    const btnRotate = document.createElement('button');
    btnRotate.type = 'button';
    btnRotate.className = 'btn btn-dark btn-xs font-weight-bold shadow-sm';
    btnRotate.style.background = 'rgba(0,0,0,0.7)';
    btnRotate.style.border = '1px solid rgba(255,255,255,0.3)';
    btnRotate.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Putar (' + this.currentRotation + '°)';
    btnRotate.onclick = (e) => {
      e.preventDefault();
      this.rotateVideo();
    };
    toolbar.appendChild(btnRotate);
    this.btnRotate = btnRotate;

    // Tombol Segarkan Kamera
    const btnRefresh = document.createElement('button');
    btnRefresh.type = 'button';
    btnRefresh.className = 'btn btn-dark btn-xs font-weight-bold shadow-sm';
    btnRefresh.style.background = 'rgba(0,0,0,0.7)';
    btnRefresh.style.border = '1px solid rgba(255,255,255,0.3)';
    btnRefresh.innerHTML = '<i class="fas fa-redo"></i>';
    btnRefresh.title = 'Segarkan Perangkat Kamera';
    btnRefresh.onclick = (e) => {
      e.preventDefault();
      this.start();
    };
    toolbar.appendChild(btnRefresh);

    this.container.appendChild(toolbar);

    // Setup Fitur Upload Foto
    this.setupUploadAndDropUI();
  }

  // Setup Fitur Upload Foto & Drag-and-Drop
  setupUploadAndDropUI() {
    const uploadContainerId = 'perpusUploadContainer_' + this.containerId;
    let uploadDiv = document.getElementById(uploadContainerId);
    if (!uploadDiv) {
      uploadDiv = document.createElement('div');
      uploadDiv.id = uploadContainerId;
      uploadDiv.className = 'mt-2 d-flex justify-content-between align-items-center flex-wrap';

      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      fileInput.style.display = 'none';
      fileInput.id = 'perpusFileInput_' + this.containerId;
      fileInput.onchange = (e) => {
        if (e.target.files && e.target.files[0]) {
          const file = e.target.files[0];
          // Reset value input file agar tidak tersimpan sebagai histori
          fileInput.value = '';
          this.scanImageFile(file);
        }
      };

      const btnUpload = document.createElement('button');
      btnUpload.type = 'button';
      btnUpload.className = 'btn btn-outline-primary btn-xs font-weight-bold shadow-xs';
      btnUpload.innerHTML = '<i class="fas fa-image mr-1"></i> Ambil/Unggah Foto Barcode';
      btnUpload.onclick = (e) => {
        e.preventDefault();
        fileInput.click();
      };

      const hintText = document.createElement('small');
      hintText.className = 'text-muted';
      hintText.innerHTML = '<i class="fas fa-paste mr-1"></i> Bisa Paste foto (Ctrl+V) atau Drag & Drop';

      uploadDiv.appendChild(fileInput);
      uploadDiv.appendChild(btnUpload);
      uploadDiv.appendChild(hintText);

      this.container.parentNode.insertBefore(uploadDiv, this.container.nextSibling);

      this.container.ondragover = (e) => {
        e.preventDefault();
        this.container.style.border = '2px dashed #007bff';
      };
      this.container.ondragleave = () => {
        this.container.style.border = '2px solid #28a745';
      };
      this.container.ondrop = (e) => {
        e.preventDefault();
        this.container.style.border = '2px solid #28a745';
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          this.scanImageFile(e.dataTransfer.files[0]);
        }
      };
    }
  }

  // Support Paste Foto dari Clipboard (Ctrl + V)
  initGlobalPasteHandler() {
    window.addEventListener('paste', (e) => {
      if (!this.isScanning) return;
      const items = (e.clipboardData || e.originalEvent.clipboardData).items;
      for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
          const blob = items[i].getAsFile();
          if (blob) {
            this.scanImageFile(blob);
            break;
          }
        }
      }
    });
  }

  // Putar Video Kamera (0° -> 90° -> 180° -> 270°)
  rotateVideo() {
    this.currentRotation = (this.currentRotation + 90) % 360;
    if (this.videoElement) {
      this.videoElement.style.transform = `rotate(${this.currentRotation}deg)`;
      if (this.currentRotation === 90 || this.currentRotation === 270) {
        this.videoElement.style.transformOrigin = 'center center';
        this.videoElement.style.scale = '1.35';
      } else {
        this.videoElement.style.scale = '1.0';
      }
    }
    if (this.btnRotate) {
      this.btnRotate.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Putar (' + this.currentRotation + '°)';
    }
  }

  // Loop Pemindaian Frame
  startScanLoop() {
    if (this.scanInterval) clearInterval(this.scanInterval);

    this.scanInterval = setInterval(() => {
      this.processVideoFrame();
    }, 80);
  }

  // Helper untuk decode canvas menggunakan ZXing
  decodeCanvasWithZxing(canvas) {
    if (!window.ZXing) return null;
    if (!this.zxingReader) {
      this.setupZxingReader();
      if (!this.zxingReader) return null;
    }

    try {
      if (!window.ZXing.HTMLCanvasElementLuminanceSource || !window.ZXing.BinaryBitmap) return null;

      const source = new window.ZXing.HTMLCanvasElementLuminanceSource(canvas);

      // Pass 1: HybridBinarizer
      if (window.ZXing.HybridBinarizer) {
        try {
          const bitmap = new window.ZXing.BinaryBitmap(new window.ZXing.HybridBinarizer(source));
          const res = this.zxingReader.decode(bitmap);
          if (res && res.getText()) return res.getText();
        } catch (e1) {}
      }

      // Pass 2: GlobalHistogramBinarizer
      if (window.ZXing.GlobalHistogramBinarizer) {
        try {
          const bitmap2 = new window.ZXing.BinaryBitmap(new window.ZXing.GlobalHistogramBinarizer(source));
          const res2 = this.zxingReader.decode(bitmap2);
          if (res2 && res2.getText()) return res2.getText();
        } catch (e2) {}
      }
    } catch (e) {}

    return null;
  }

  // Proses Frame Video (Omni-Directional Multi-Angle)
  async processVideoFrame() {
    if (!this.isScanning || !this.videoElement || this.videoElement.readyState < 2) return;

    const video = this.videoElement;
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    if (!vw || !vh) return;

    // Flush warmup frames agar tidak membaca histori gambar dari buffer driver kamera
    if (this.warmupFrames > 0) {
      this.warmupFrames--;
      this.canvas0.width = vw;
      this.canvas0.height = vh;
      const ctxFlush = this.canvas0.getContext('2d');
      ctxFlush.drawImage(video, 0, 0, vw, vh);
      return;
    }

    const currentSession = this.sessionId;

    // =========================================================================
    // TIER 1: Native BarcodeDetector (Jika didukung OS)
    // =========================================================================
    if (this.hasBarcodeDetector && this.barcodeDetector) {
      try {
        const barcodes = await this.barcodeDetector.detect(video);
        if (barcodes && barcodes.length > 0 && this.isScanning && currentSession === this.sessionId) {
          const raw = barcodes[0].rawValue;
          if (raw && raw.trim().length >= 3) {
            this.handleScanHit(raw.trim(), barcodes[0].format, currentSession);
            return;
          }
        }
      } catch (err) {}
    }

    // =========================================================================
    // TIER 2: ZXing Canvas Multi-Angle (0° Horizontal & 90° Vertikal)
    // =========================================================================
    // Pass A: Sudut 0° (Normal Horizontal)
    this.canvas0.width = vw;
    this.canvas0.height = vh;
    const ctx0 = this.canvas0.getContext('2d', { willReadFrequently: true });
    ctx0.drawImage(video, 0, 0, vw, vh);

    const hit0 = this.decodeCanvasWithZxing(this.canvas0);
    if (hit0 && this.isScanning && currentSession === this.sessionId) {
      this.handleScanHit(hit0, 'ZXing-Horizontal', currentSession);
      return;
    }

    // Pass B: Sudut 90° (Rotasi Vertikal - KUNCI BARCODE TIDAK PORTRAIT)
    this.canvas90.width = vh;
    this.canvas90.height = vw;
    const ctx90 = this.canvas90.getContext('2d', { willReadFrequently: true });
    ctx90.save();
    ctx90.translate(vh / 2, vw / 2);
    ctx90.rotate(Math.PI / 2);
    ctx90.drawImage(video, -vw / 2, -vh / 2);
    ctx90.restore();

    const hit90 = this.decodeCanvasWithZxing(this.canvas90);
    if (hit90 && this.isScanning && currentSession === this.sessionId) {
      this.handleScanHit(hit90, 'ZXing-Vertikal', currentSession);
      return;
    }

    // =========================================================================
    // TIER 3: Backend OpenCV Hardware-Accelerated Fallback (/admin/scan-image-api)
    // =========================================================================
    const now = Date.now();
    if (now - this.lastServerCheck > 1200 && !this.serverCheckPending) {
      this.lastServerCheck = now;
      this.serverCheckPending = true;

      const dataUrl = this.canvas0.toDataURL('image/jpeg', 0.75);
      this.sendFrameToServer(dataUrl, currentSession).finally(() => {
        this.serverCheckPending = false;
      });
    }
  }

  // Kirim snapshot frame kamera ke Server Backend OpenCV
  async sendFrameToServer(dataUrl, currentSession) {
    if (!this.isScanning || currentSession !== this.sessionId) return;
    try {
      this.abortController = new AbortController();
      const resp = await fetch(this.baseApiUrl + 'admin/scan-image-api', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'image_base64=' + encodeURIComponent(dataUrl),
        signal: this.abortController.signal
      });
      const data = await resp.json();
      if (data && data.status === 'success' && data.code && this.isScanning && currentSession === this.sessionId) {
        this.handleScanHit(data.code, 'OpenCV-Engine (Angle ' + (data.angle || 0) + '°)', currentSession);
      }
    } catch (e) {}
  }

  // Pemindaian File Gambar / Foto Barcode yang Diunggah
  scanImageFile(file) {
    if (!file) return;

    // Reset kanvas sebelum memproses file baru
    this.resetCanvases();
    const currentSession = this.sessionId;

    if (this.statusEl) {
      this.statusEl.innerHTML = '<span class="text-primary font-weight-bold"><i class="fas fa-spinner fa-spin mr-1"></i> Memproses foto: ' + file.name + '...</span>';
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = img.width;
        tempCanvas.height = img.height;
        const ctx = tempCanvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        // 1. Coba decode client-side ZXing (0 deg)
        let hit = this.decodeCanvasWithZxing(tempCanvas);
        if (hit && currentSession === this.sessionId) {
          this.handleScanHit(hit, 'File-ZXing-0', currentSession);
          return;
        }

        // 2. Coba decode client-side ZXing (90 deg vertikal)
        const rotCanvas = document.createElement('canvas');
        rotCanvas.width = img.height;
        rotCanvas.height = img.width;
        const rCtx = rotCanvas.getContext('2d');
        rCtx.translate(rotCanvas.width / 2, rotCanvas.height / 2);
        rCtx.rotate(Math.PI / 2);
        rCtx.drawImage(img, -img.width / 2, -img.height / 2);

        hit = this.decodeCanvasWithZxing(rotCanvas);
        if (hit && currentSession === this.sessionId) {
          this.handleScanHit(hit, 'File-ZXing-90', currentSession);
          return;
        }

        // 3. Backend OpenCV Fallback
        const formData = new FormData();
        formData.append('image', file);

        fetch(this.baseApiUrl + 'admin/scan-image-api', {
          method: 'POST',
          body: formData
        })
        .then(r => r.json())
        .then(res => {
          if (res && res.status === 'success' && res.code && currentSession === this.sessionId) {
            this.handleScanHit(res.code, 'File-OpenCV (Angle ' + (res.angle || 0) + '°)', currentSession);
          } else {
            if (this.statusEl) {
              this.statusEl.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0 text-xs"><i class="fas fa-times-circle mr-1"></i> Barcode tidak terdeteksi pada gambar. Pastikan gambar jelas dan tidak terlalu blur.</div>';
            }
          }
        })
        .catch(err => {
          if (this.statusEl) {
            this.statusEl.innerHTML = '<div class="alert alert-danger py-1 px-2 mb-0 text-xs"><i class="fas fa-times-circle mr-1"></i> Gagal memproses gambar: ' + err + '</div>';
          }
        });
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  // Barcode Berhasil Ditemukan
  handleScanHit(code, format, currentSession) {
    if (!this.isScanning) return;
    if (currentSession && currentSession !== this.sessionId) return;
    this.isScanning = false;

    // Bersihkan buffer kanvas seketika agar tidak tersimpan di histori
    this.resetCanvases();

    // 1. Bunyikan Audio Beep Sukses
    playScannerBeep();

    // 2. Efek Visual Sukses (Border Hijau Cerah)
    if (this.container) {
      this.container.style.border = '4px solid #28a745';
      this.container.style.boxShadow = '0 0 25px #28a745';
    }

    // 3. Update Teks Status
    if (this.statusEl) {
      this.statusEl.innerHTML = `
        <div class="alert alert-success py-2 px-3 mb-0 text-center font-weight-bold shadow-sm">
          <i class="fas fa-check-circle mr-2"></i> Berhasil Memindai Barcode: <code>${code}</code>
          <div class="text-xs text-dark mt-1 font-weight-normal">Format: <strong>${format || 'EAN-13/ISBN'}</strong> &bull; Memproses data ke sistem...</div>
        </div>
      `;
    }

    // 4. Hentikan Kamera
    this.stopStreamOnly();

    // 5. Eksekusi Callback Pengguna
    setTimeout(() => {
      if (typeof this.onScanSuccess === 'function') {
        this.onScanSuccess(code, format);
      }
    }, 200);
  }

  // Hentikan Stream Video Saja & Bersihkan Buffer
  stopStreamOnly() {
    this.sessionId = 0; // Invalidasi session lama
    if (this.abortController) {
      try { this.abortController.abort(); } catch (e) {}
      this.abortController = null;
    }
    if (this.scanInterval) {
      clearInterval(this.scanInterval);
      this.scanInterval = null;
    }
    this.isScanning = false;
    this.resetCanvases();

    if (this.videoStream) {
      this.videoStream.getTracks().forEach(t => t.stop());
      this.videoStream = null;
    }
    if (this.videoElement) {
      this.videoElement.srcObject = null;
    }
  }

  // Hentikan Total & Bersihkan Viewport
  stop() {
    this.stopStreamOnly();
    if (this.container) {
      this.container.innerHTML = '';
      this.container.style.border = '2px solid #6c757d';
      this.container.style.boxShadow = 'none';
      this.container.style.background = '#000';
    }
  }
}

// Ekspor ke window global
window.PerpusScanner = PerpusScanner;
window.playScannerBeep = playScannerBeep;
