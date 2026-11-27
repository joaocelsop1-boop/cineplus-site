# CinePlus — site de vendas (PHP + Apache)
# Roda no Coolify (Oracle) via docker-compose.yml.
# O .htaccess do site precisa de AllowOverride All (mod_rewrite ligado).
FROM php:8.3-apache

# Extensões que o site usa: pdo_mysql (banco) e mbstring (mb_substr/mb_strlen).
RUN docker-php-ext-install pdo_mysql mbstring \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# O Coolify/Traefik roteia para a porta 80 do container (sem publicar
# porta no host — ver docker-compose.yml).
EXPOSE 80

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
