# WebGIS Peradilan Agama

A small WebGIS presentation for Peradilan Agama (Religious Court) data. The app serves GeoSpatial layers (GeoJSON / GeoPackage) and a simple web frontend (PHP + JavaScript) to visualise jurisdiction boundaries, court locations and related thematic layers.

## Features

- Interactive map with GeoJSON/GPKG layers stored in the `data/` folder.
- Several PHP endpoints/pages: `index.php`, `sebaran-perkara.php`, `cakupan-yurisdiksi.php`, `wbk-wbbm.php`.
- Static assets and icons in the `icon/` folder.
- Simple backend served by PHP (Dockerfile and `docker-compose.yml` are included).

## Repository structure

- `app.js` — main JavaScript for map interaction (edit to change map logic and layer handling).
- `index.php` — main entry page.
- `*.php` — per-page PHP views that load map/data variants.
- `data/` — GeoJSON, GeoPackage and other spatial data:
  - `peradilan_agama.geojson`, `peradilan_agama.gpkg`, `kabupaten.geojson`, `batas_kecamatan.geojson`, etc.
- `icon/` — icons used in the UI.
- `Dockerfile`, `docker-compose.yml` — containerised environment for quick local deployment.

## Requirements

- Docker and Docker Compose (recommended) OR
- PHP (7.4+) with a web server (Apache/Nginx) or PHP built-in server for local testing.
- Modern browser.

## Quick start (Docker)

1. From project root, build and run with Docker Compose:

```bash
docker-compose up --build
```

2. Open http://localhost:8080 (or the port configured in `docker-compose.yml`).

## Quick start (local PHP server)

From project root, you can run a simple PHP server for testing (if you have PHP installed):

```bash
php -S 127.0.0.1:8000
```

Then open http://127.0.0.1:8000 in your browser.

## Working with data

- Add or update GeoJSON files in `data/`. The front-end reads these files directly (or through PHP endpoints) — edit `app.js` or the PHP pages to change which layers are loaded.
- A GeoPackage (`peradilan_agama.gpkg`) is included for authoritative data; use QGIS or ogr2ogr to export/update GeoJSON if needed.

Example ogr2ogr export to GeoJSON (local environment):

```bash
ogr2ogr -f GeoJSON peradilan_agama.geojson peradilan_agama.gpkg
```

## Development notes

- Edit `app.js` to adjust map behaviour, layer styles and popups.
- Replace icons in `icon/` to change point symbology.
- Check PHP pages for server-side data loading/filters.

## Troubleshooting

- If layers do not appear, open browser DevTools and check the Network tab for 404s or invalid GeoJSON.
- Ensure Docker uses a port that is not already taken.

## License & Contact

- License: (add your preferred license)
- Author / Contact: (add your name and contact information)


