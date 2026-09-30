FROM dunglas/frankenphp

# Install ekstensi PHP yang dibutuhkan
RUN install-php-extensions mysqli pdo_mysql

# Matikan HTTPS otomatis via environment variable bawaan FrankenPHP
ENV SERVER_NAME=":8080"
ENV FRANKENPHP_CONFIG="/app/Caddyfile"

# Copy seluruh file project ke /app
COPY . /app

WORKDIR /app

EXPOSE 8080
