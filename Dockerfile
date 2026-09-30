FROM dunglas/frankenphp

# Install ekstensi mysqli dan pdo_mysql yang dibutuhkan PHP
RUN install-php-extensions mysqli pdo_mysql

# Copy seluruh file project ke direktori kerja container
COPY . /app
