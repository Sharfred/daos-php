FROM php:8.2-apache

# Habilitar PDO, PDO MySQL y OPCache para rendimiento
RUN docker-php-ext-install pdo pdo_mysql opcache

# Habilitar el módulo rewrite de Apache (necesario para .htaccess)
RUN a2enmod rewrite

# Cambiar el DocumentRoot a /var/www/html (por defecto)
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configurar Apache para que escuche en el puerto que Railway le asigne (o 80 por defecto)
RUN sed -i 's/80/${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Copiar todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE ${PORT:-80}
