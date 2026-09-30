FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan untuk MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite jika diperlukan
RUN a2enmod rewrite

# Copy seluruh file aplikasi ke web root Apache
COPY . /var/www/html/

# Atur izin direktori jika diperlukan
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
