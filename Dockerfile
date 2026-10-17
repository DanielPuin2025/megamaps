FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get purge -y --auto-remove libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --chown=www-data:www-data index.html api.php database.php guardar_foto.php manifest.json schema.sql script.js style.css subir_foto.html sw.js /var/www/html/
COPY --chown=www-data:www-data assets/ /var/www/html/assets/
COPY --chown=www-data:www-data assests/ /var/www/html/assests/
COPY docker/seed/ /opt/megamaps-seed/
COPY docker/apache-megamaps.conf /etc/apache2/conf-available/megamaps.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/megamaps.ini
COPY docker/entrypoint.sh /usr/local/bin/megamaps-entrypoint

RUN a2enconf megamaps \
    && chmod 755 /usr/local/bin/megamaps-entrypoint \
    && mkdir -p /data/fotos/salones \
    && chown -R www-data:www-data /data

ENV MEGAMAPS_DATA_DIR=/data \
    MEGAMAPS_PHOTOS_DIR=/data/fotos \
    MEGAMAPS_SEED_DIR=/opt/megamaps-seed

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r '$r=@file_get_contents("http://127.0.0.1/api.php?action=health"); exit($r !== false && strpos($r, "\"status\":\"ok\"") !== false ? 0 : 1);'

ENTRYPOINT ["/usr/local/bin/megamaps-entrypoint"]
CMD ["apache2-foreground"]
