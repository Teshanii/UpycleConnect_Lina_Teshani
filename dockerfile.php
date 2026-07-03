FROM php:8.2-apache
RUN apt-get update && apt-get install -y unzip
RUN docker-php-ext-install pdo pdo_mysql

# Reverse proxy : le navigateur appelle /api, Apache le transmet a l'API Go
# (via le reseau interne Docker). L'API n'est donc plus exposee publiquement.
RUN a2enmod proxy proxy_http
RUN printf 'ProxyPreserveHost On\nProxyPass /api http://api:8080/api\nProxyPassReverse /api http://api:8080/api\n' > /etc/apache2/conf-available/proxy-api.conf \
    && a2enconf proxy-api

# On augmente les limites d'upload pour accepter les grosses photos
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini