FROM php:8.2-apache

# Habilitar PDO, PDO MySQL y OPCache para rendimiento
RUN docker-php-ext-install pdo pdo_mysql opcache

# Habilitar el módulo rewrite de Apache (necesario para .htaccess)
RUN a2enmod rewrite

# Copiar todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
