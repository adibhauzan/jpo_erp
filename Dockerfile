# Gunakan image PHP versi terbaru
FROM php:latest

# Set working directory di dalam container
WORKDIR /var/www/html

# Instal dependensi yang diperlukan
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clone kode sumber Laravel
RUN git clone https://github.com/adibhauzan/jpo_erp.git .

# Install dependensi PHP menggunakan Composer
RUN composer install --no-dev

# Buat script entrypoint untuk menjalankan migrasi dan menjalankan server
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
