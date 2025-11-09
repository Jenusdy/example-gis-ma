<?php
header("Access-Control-Allow-Origin: *");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Peta Tren Jumlah Perkara - Leaflet TimeDimension</title>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <!-- iso8601-js-period (WAJIB untuk TimeDimension) -->
  <script src="https://cdn.jsdelivr.net/npm/iso8601-js-period@0.2.1/iso8601.min.js"></script>

  <!-- Leaflet.TimeDimension -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-timedimension@1.1.0/dist/leaflet.timedimension.control.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/leaflet-timedimension@1.1.0/dist/leaflet.timedimension.min.js"></script>

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
<div id="tooltip" class="custom-tooltip"></div>

<script>
  // Inisialisasi peta
  const map = L.map('map', {
    center: [-2.5, 118],
    zoom: 5,
    timeDimension: true,
    timeDimensionControl: true,
    timeDimensionOptions: {
      timeInterval: "2018-01-01/2025-01-01",
      period: "P1Y"
    },
    timeDimensionControlOptions: {
      autoPlay: true,
      loopButton: true,
      minSpeed: 1,
      maxSpeed: 5,
      speedStep: 1,
      timeSliderDragUpdate: true
    }
  });

  // Basemap
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Tooltip DOM element
  const tooltip = document.getElementById('tooltip');

  // Fungsi warna (sama seperti versi KDRT)
  function getColor(val) {
    return val > 150 ? '#800026' :
           val > 100 ? '#BD0026' :
           val > 50  ? '#E31A1C' :
           val > 20  ? '#FC4E2A' :
           val > 10  ? '#FD8D3C' :
           val > 0   ? '#FEB24C' :
                        '#FFEDA0';
  }

  // Fungsi radius proporsional (sama seperti versi KDRT)
  function getRadius(val) {
    return val > 0 ? Math.sqrt(val) * 2 : 3;
  }

  // Contoh data tren jumlah perkara per tahun
  const trendData = {
    "type": "FeatureCollection",
    "features": [
      {
        "type": "Feature",
        "properties": {
          "nama": "Aceh",
          "data_tren": [
            {"tahun": "2018-01-01", "jumlah_perkara": 40},
            {"tahun": "2019-01-01", "jumlah_perkara": 70},
            {"tahun": "2020-01-01", "jumlah_perkara": 95},
            {"tahun": "2021-01-01", "jumlah_perkara": 110},
            {"tahun": "2022-01-01", "jumlah_perkara": 150},
            {"tahun": "2023-01-01", "jumlah_perkara": 180},
            {"tahun": "2024-01-01", "jumlah_perkara": 210}
          ]
        },
        "geometry": { "type": "Point", "coordinates": [95.3222, 5.55] }
      },
      {
        "type": "Feature",
        "properties": {
          "nama": "Jawa Barat",
          "data_tren": [
            {"tahun": "2018-01-01", "jumlah_perkara": 120},
            {"tahun": "2019-01-01", "jumlah_perkara": 170},
            {"tahun": "2020-01-01", "jumlah_perkara": 240},
            {"tahun": "2021-01-01", "jumlah_perkara": 310},
            {"tahun": "2022-01-01", "jumlah_perkara": 370},
            {"tahun": "2023-01-01", "jumlah_perkara": 420},
            {"tahun": "2024-01-01", "jumlah_perkara": 480}
          ]
        },
        "geometry": { "type": "Point", "coordinates": [107.6098, -6.9147] }
      }
    ]
  };

  // Konversi ke fitur berdasarkan tahun
  const timeFeatures = [];
  trendData.features.forEach(f => {
    f.properties.data_tren.forEach(d => {
      const newFeature = JSON.parse(JSON.stringify(f));
      newFeature.properties.jumlah_perkara = d.jumlah_perkara;
      newFeature.properties.time = d.tahun;
      timeFeatures.push(newFeature);
    });
  });

  const trendGeoJSON = { "type": "FeatureCollection", "features": timeFeatures };

  // Layer dasar (circleMarker + hover tooltip)
  const baseLayer = L.geoJson(trendGeoJSON, {
    pointToLayer: (feature, latlng) => {
      const val = feature.properties.jumlah_perkara;
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

      layer.on('mouseover', e => {
        tooltip.style.display = 'block';
        tooltip.innerHTML = `
          <b>${props.nama}</b><br>
          Tahun: ${props.time.split('-')[0]}<br>
          Jumlah Perkara: <b>${props.jumlah_perkara}</b>
        `;
        e.target.setStyle({ weight: 2, color: '#000', fillOpacity: 1 });
      });

      layer.on('mousemove', e => {
        tooltip.style.left = (e.originalEvent.pageX + 12) + 'px';
        tooltip.style.top = (e.originalEvent.pageY - 10) + 'px';
      });

      layer.on('mouseout', e => {
        tooltip.style.display = 'none';
        e.target.setStyle({ weight: 1, color: '#555', fillOpacity: 0.85 });
      });
    }
  });

  // TimeDimension layer
  const timeDimensionLayer = L.timeDimension.layer.geoJson(baseLayer, {
    updateTimeDimension: true,
    addlastPoint: false,
    duration: "P1Y",
  });

  timeDimensionLayer.addTo(map);

  // Tambahkan legenda
  const legend = L.control({ position: 'bottomright' });
  legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'legend');
    const grades = [0, 10, 20, 50, 100, 150];
    div.innerHTML = '<b>Jumlah Perkara</b><br>';
    for (let i = 0; i < grades.length; i++) {
      div.innerHTML +=
        '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
        grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
    }
    return div;
  };
  legend.addTo(map);
</script>

</body>
</html>
