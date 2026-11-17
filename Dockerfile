FROM php:8.3-apache

# Install ekstensi PDO MySQL untuk koneksi database cloud (TiDB / Aiven)
RUN docker-php-ext-install pdo pdo_mysql

# Aktifkan mod_rewrite Apache untuk URL routing index.php
RUN a2enmod rewrite

# Izinkan .htaccess mengatur konfigurasi direktori
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Salin seluruh isi proyek ke dalam container
COPY . .

# Pastikan direktori cover_buku bisa ditulis (upload gambar)
RUN mkdir -p /var/www/html/cover_buku && chmod -R 755 /var/www/html/cover_buku

# Vercel menyuntikkan $PORT secara dinamis — Apache harus mendengarkan di port tersebut
# Default fallback ke 80 jika $PORT tidak tersedia (lokal)
CMD ["/bin/sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/*:80/*:${PORT:-80}/g\" /etc/apache2/sites-enabled/000-default.conf && apache2-foreground"]
