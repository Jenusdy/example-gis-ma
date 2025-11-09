# Gunakan image PHP bawaan dengan built-in web server
FROM php:8.2-cli

# Salin semua file ke dalam container
WORKDIR /var/www/html
COPY . /var/www/html

# Jalankan PHP built-in web server di port 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]