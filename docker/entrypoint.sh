#!/bin/sh
set -eu

mkdir -p "${MEGAMAPS_DATA_DIR:-/data}" "${MEGAMAPS_PHOTOS_DIR:-/data/fotos}/salones"
chown -R www-data:www-data "${MEGAMAPS_DATA_DIR:-/data}"
chmod 0770 "${MEGAMAPS_DATA_DIR:-/data}"

exec "$@"
