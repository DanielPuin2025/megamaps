#!/bin/sh
set -eu

DATA="${MEGAMAPS_DATA_DIR:-/data}"
SEED="${MEGAMAPS_SEED_DIR:-/opt/megamaps-seed}"
WEB=/var/www/html

mkdir -p "$DATA/fotos/salones"

# Bases iniciales: solo se copian si el volumen aun no las tiene.
for f in megamaps.sqlite fotos_rutas_personalidas.sqlite; do
    if [ ! -f "$DATA/$f" ] && [ -f "$SEED/$f" ]; then
        cp "$SEED/$f" "$DATA/$f"
    fi
done

php /usr/local/share/megamaps-migrar-rutas.php "$DATA/fotos_rutas_personalidas.sqlite" "$DATA/megamaps.sqlite" || true

chown -R www-data:www-data "$DATA"
chmod 0770 "$DATA"

# El codigo espera "base de datos/" junto a backend/ y frontend/, y backend/fotos/.
ln -sfn "$DATA" "$WEB/base de datos"
ln -sfn "$DATA/fotos" "$WEB/backend/fotos"

exec "$@"
