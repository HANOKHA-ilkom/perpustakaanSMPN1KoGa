import sys
import json
import base64
import os

try:
    import cv2
    import numpy as np
except ImportError:
    print(json.dumps({"status": "error", "message": "OpenCV not installed"}))
    sys.exit(1)

def scan_image(image_path_or_data):
    try:
        if os.path.exists(image_path_or_data):
            img = cv2.imread(image_path_or_data)
        else:
            # Maybe base64
            if ',' in image_path_or_data:
                image_path_or_data = image_path_or_data.split(',')[1]
            nparr = np.frombuffer(base64.b64decode(image_path_or_data), np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"status": "error", "message": "Failed to load image"}

        detector = cv2.barcode.BarcodeDetector()

        # Try 0 deg
        res = detector.detectAndDecode(img)
        if res and res[0]:
            code = str(res[0]).strip()
            if code:
                return {"status": "success", "code": code, "angle": 0}

        # Try 90 deg
        img90 = cv2.rotate(img, cv2.ROTATE_90_CLOCKWISE)
        res90 = detector.detectAndDecode(img90)
        if res90 and res90[0]:
            code = str(res90[0]).strip()
            if code:
                return {"status": "success", "code": code, "angle": 90}

        # Try 180 deg
        img180 = cv2.rotate(img, cv2.ROTATE_180)
        res180 = detector.detectAndDecode(img180)
        if res180 and res180[0]:
            code = str(res180[0]).strip()
            if code:
                return {"status": "success", "code": code, "angle": 180}

        # Try 270 deg
        img270 = cv2.rotate(img, cv2.ROTATE_90_COUNTERCLOCKWISE)
        res270 = detector.detectAndDecode(img270)
        if res270 and res270[0]:
            code = str(res270[0]).strip()
            if code:
                return {"status": "success", "code": code, "angle": 270}

        # Also try QRCodeDetector
        qr_detector = cv2.QRCodeDetector()
        qr_res = qr_detector.detectAndDecode(img)
        if qr_res and qr_res[0]:
            return {"status": "success", "code": str(qr_res[0]).strip(), "angle": 0, "type": "QR"}

        return {"status": "not_found", "message": "No barcode detected"}
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "No input provided"}))
        sys.exit(1)
    result = scan_image(sys.argv[1])
    print(json.dumps(result))

