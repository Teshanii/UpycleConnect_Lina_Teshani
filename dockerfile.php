FROM php:8.2-apache
RUN apt-get update && apt-get install -y unzip
RUN docker-php-ext-install pdo pdo_mysql

# On augmente les limites d'upload pour accepter les grosses photos
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini