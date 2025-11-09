<?php
header("Access-Control-Allow-Origin: *");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Peta KDRT - Tooltip Hover</title>

  <!-- Leaflet -->
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    html, body, #map {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
    }

    .custom-tooltip {
      position: absolute;
      pointer-events: none;
      padding: 6px 10px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 4px;
      font-size: 13px;
      color: #222;
      box-shadow: 0 0 6px rgba(0, 0, 0, 0.3);
      display: none;
      z-index: 1000;
    }

    .legend {
      background: white;
      padding: 10px;
      line-height: 18px;
      color: #333;
      font-size: 12px;
      box-shadow: 0 0 6px rgba(0,0,0,0.2);
    }
    .legend i {
      width: 14px;
      height: 14px;
      float: left;
      margin-right: 6px;
      opacity: 0.8;
    }
  </style>
</head>
<body>
  <div id="map"></div>

  <!-- Tooltip manual -->
  <div id="tooltip" class="custom-tooltip"></div>

  <script>
    // Inisialisasi peta
    const map = L.map('map').setView([-2.5, 118], 5);

    // Tambahkan tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Tooltip DOM element
    const tooltip = document.getElementById('tooltip');

    // Fungsi warna berdasarkan nilai
    function getColor(kdrt) {
      return kdrt > 150 ? '#800026' :
             kdrt > 100 ? '#BD0026' :
             kdrt > 50  ? '#E31A1C' :
             kdrt > 20  ? '#FC4E2A' :
             kdrt > 10  ? '#FD8D3C' :
             kdrt > 0   ? '#FEB24C' :
                          '#FFEDA0';
    }

    // Fungsi radius proporsional
    function getRadius(kdrt) {
      return kdrt > 0 ? Math.sqrt(kdrt) * 2 : 3;
    }

    // Load GeoJSON
    fetch('data/kdrt.geojson')
      .then(res => res.json())
      .then(geojson => {
        const layer = L.geoJSON(geojson, {
          pointToLayer: (feature, latlng) => {
            const val = feature.properties.jml_kdrt;
            return L.circleMarker(latlng, {
              radius: getRadius(val),
              fillColor: getColor(val),
              color: '#555',
              weight: 1,
              opacity: 1,
              fillOpacity: 0.85
            });
          },
          onEachFeature: (feature, layer) => {
            const props = feature.properties;
            
            layer.on('mouseover', (e) => {
              tooltip.style.display = 'block';
              tooltip.innerHTML = `
                <b>${props.nama_satker}</b><br>
                Jumlah KDRT: <b>${props.jml_kdrt}</b>
              `;

              // Tambahkan efek hover marker
              e.target.setStyle({
                weight: 2,
                color: '#000',
                fillOpacity: 1
              });
            });

            layer.on('mousemove', (e) => {
              tooltip.style.left = (e.originalEvent.pageX + 12) + 'px';
              tooltip.style.top = (e.originalEvent.pageY - 10) + 'px';
            });

            layer.on('mouseout', (e) => {
              tooltip.style.display = 'none';
              e.target.setStyle({
                weight: 1,
                color: '#555',
                fillOpacity: 0.85
              });
            });
          }
        }).addTo(map);

        // Tambahkan legenda
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function () {
          const div = L.DomUtil.create('div', 'legend');
          const grades = [0, 10, 20, 50, 100, 150];
          div.innerHTML = '<b>Jumlah KDRT</b><br>';
          for (let i = 0; i < grades.length; i++) {
            div.innerHTML +=
              '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
              grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
          }
          return div;
        };
        legend.addTo(map);
      })
      .catch(err => console.error('Gagal memuat data:', err));
  </script>
</body>
</html>
