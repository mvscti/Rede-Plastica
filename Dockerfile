FROM php:8.4-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia os arquivos do projeto (opcional, se usar volumes pode omitir)
# COPY . /var/www/html

#CMD ["php-fpm"]
CMD ln -s /var/www/html/storage/public /var/www/html/public/files && php-fpm