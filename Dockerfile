FROM dunglas/frankenphp

# Install ekstensi PHP yang dibutuhkan
RUN install-php-extensions mysqli pdo_mysql

# Gunakan HTTP saja di port 8080, tanpa HTTPS otomatis
ENV SERVER_NAME=":8080"

# Copy file project ke /app/public sesuai default document root FrankenPHP
COPY . /app/public/

WORKDIR /app

EXPOSE 8080
