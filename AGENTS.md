# AGENTS.md — MegaMaps

## Project
- Main stack: PHP 8.3, SQLite, HTML, CSS and vanilla JavaScript.
- Main app: `frontend/index.html`.
- Backend: `backend/api.php` and `backend/guardar_foto.php`.
- Production deployment: Docker Compose with a persistent `/data` volume.
- Local start on Windows: `iniciar.bat`; Linux/macOS: `./iniciar.sh`.
- Required PHP extensions: `pdo_sqlite` and `fileinfo`.

## Data and photos
- `base de datos/megamaps.sqlite` and `base de datos/fotos_rutas_personalidas.sqlite` are separate seed databases with different roles.
- Do not delete or regenerate an existing database without preserving its user data.
- Keep bundled images in `frontend/assets/`; do not add another copy in the repository root.
- Production uploads are persisted in the Docker volume, not in the image.

## Working rules
- Keep changes scoped and preserve the public API and existing behavior.
- Validate PHP changes with `php -l` and verify the health endpoint when practical.
- Never commit `.env` values or production secrets.
