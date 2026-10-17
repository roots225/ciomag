# Étape 1 : Build des assets frontend avec Node.js
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* .npmrc* ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

# Étape 2 : Image PHP-FPM pour Laravel
FROM php:8.4-fpm-alpine

# Installation des dépendances système et des extensions PHP requises (MySQL, Redis, GD, Zip, Intl, etc.)
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS linux-headers

# Récupération de Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . .

# Copie des assets compilés depuis l'étape Node (utile pour une image autonome/production)
COPY --from=node-builder /app/public/build ./public/build

# Ajustement des permissions pour Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
