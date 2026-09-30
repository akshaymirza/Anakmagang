FROM dunglas/frankenphp

# Install ekstensi PHP yang dibutuhkan
RUN install-php-extensions mysqli pdo_mysql

# Arahkan document root ke /app karena PHP files ada di root project (bukan /app/public)
ENV DOCUMENT_ROOT=/app

# Gunakan HTTP saja di port 8080, tanpa HTTPS otomatis
ENV SERVER_NAME=":8080"

# Copy seluruh file project ke /app
COPY . /app

WORKDIR /app

EXPOSE 8080
