FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get purge -y --auto-remove libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --chown=www-data:www-data frontend/ /var/www/html/frontend/
COPY --chown=www-data:www-data backend/api.php backend/guardar_foto.php /var/www/html/backend/
COPY docker/sw-migracion.js /var/www/html/sw.js
COPY ["base de datos/megamaps.sqlite", "base de datos/fotos_rutas_personalidas.sqlite", "/opt/megamaps-seed/"]
COPY docker/apache-megamaps.conf /etc/apache2/conf-available/megamaps.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/megamaps.ini
COPY docker/entrypoint.sh /usr/local/bin/megamaps-entrypoint
COPY docker/migrar-rutas.php /usr/local/share/megamaps-migrar-rutas.php

# "base de datos" y backend/fotos apuntan al volumen persistente /data (ver entrypoint).
RUN a2enconf megamaps \
    && chmod 755 /usr/local/bin/megamaps-entrypoint \
    && mkdir -p /data/fotos/salones \
    && chown -R www-data:www-data /data

ENV MEGAMAPS_DATA_DIR=/data \
    MEGAMAPS_SEED_DIR=/opt/megamaps-seed

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=5 \
    CMD php -r '$r=@file_get_contents("http://127.0.0.1/backend/api.php?action=health"); exit($r !== false && strpos($r, "\"status\":\"ok\"") !== false ? 0 : 1);'

ENTRYPOINT ["/usr/local/bin/megamaps-entrypoint"]
CMD ["apache2-foreground"]
