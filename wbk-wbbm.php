<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Peta Capaian WBK & WBBM - Fullscreen</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }
    #map {
      height: 100%;
      width: 100%;
    }
    .marker-wbk {
      width: 18px; height: 18px; border-radius: 50%; display:block; border:3px solid white;
      background: #2ecc71; box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }
    .marker-wbbm {
      width: 20px; height: 20px; display:block; transform: rotate(15deg);
      background: linear-gradient(180deg,#9b59b6,#6b2a9b);
      clip-path: polygon(50% 0%,61% 35%,98% 35%,68% 57%,79% 91%,50% 70%,21% 91%,32% 57%,2% 35%,39% 35%);
      border:3px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }

      #yearSelector {
    position: absolute;
    z-index: 9999 !important; /* pastikan di atas semua elemen Leaflet */
    pointer-events: auto; /* supaya bisa diklik */
  }

  /* Pastikan peta tidak menimpa overlay */
  .leaflet-container {
    z-index: 1 !important;
  }
  </style>
</head>
<body class="h-screen w-screen overflow-hidden flex">

  <!-- Kiri: Peta -->
  <div class="flex-1 relative">
    <div id="map"></div>
  </div>

  <!-- Kanan: Panel Statistik -->
  <aside class="w-96 bg-white border-l border-gray-200 p-4 overflow-y-auto">
    <h2 class="text-lg font-semibold">Ringkasan Statistik</h2>
    <p class="text-sm text-gray-600 mb-2">Jumlah WBK & WBBM per provinsi</p>

    <div class="w-full h-56 mb-4">
      <canvas id="barChart" class="w-full h-full"></canvas>
    </div>

    <hr class="my-3" />
    <h3 class="text-sm font-medium mb-2">Detail Satuan Kerja</h3>
    <div id="detailList" class="text-sm text-gray-700 space-y-2">
      <!-- populated dynamically -->
    </div>
  </aside>

<script>
  const map = L.map('map', { minZoom: 4 }).setView([-2.5, 118], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  const markerLayer = L.layerGroup().addTo(map);

  function createIcon(predikat){
    return predikat === 'WBK'
      ? L.divIcon({ html:'<span class="marker-wbk"></span>', iconSize:[18,18], iconAnchor:[9,9] })
      : L.divIcon({ html:'<span class="marker-wbbm"></span>', iconSize:[22,22], iconAnchor:[11,11] });
  }

  function renderMarkers(geojsonData, filterYear='all'){
    markerLayer.clearLayers();
    const list = [];
    geojsonData.features.forEach(f=>{
      const p = f.properties, c = f.geometry.coordinates;
      if(filterYear!=='all' && String(p.tahun)!==String(filterYear)) return;
      const marker = L.marker([c[1], c[0]], {icon:createIcon(p.predikat)})
        .bindTooltip(
          `<div>
            <b>${p.nama}</b><br>
            ${p.provinsi} — ${p.predikat}<br>
            <i>${p.inovasi}</i>
          </div>`,
          {direction:'top', offset:[0,-5]}
        );
      marker.addTo(markerLayer);
      list.push({provinsi:p.provinsi, predikat:p.predikat});
    });
    return list;
  }

  let barChart;
  function updateChart(list){
    const counts = {};
    list.forEach(i=>{
      counts[i.provinsi] ??= {WBK:0, WBBM:0};
      counts[i.provinsi][i.predikat]++;
    });
    const labels = Object.keys(counts);
    const dataWBK = labels.map(l=>counts[l].WBK);
    const dataWBBM = labels.map(l=>counts[l].WBBM);
    const ctx = document.getElementById('barChart').getContext('2d');
    if(barChart) barChart.destroy();
    barChart = new Chart(ctx,{
      type:'bar',
      data:{labels,datasets:[
        {label:'WBK',data:dataWBK,backgroundColor:'#2ecc71'},
        {label:'WBBM',data:dataWBBM,backgroundColor:'#9b59b6'}
      ]},
      options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}
    });

    const listDiv = document.getElementById('detailList');
    listDiv.innerHTML = '';
    labels.forEach(l=>{
      const e = document.createElement('div');
      e.innerHTML = `<strong>${l}</strong> — WBK: ${counts[l].WBK} • WBBM: ${counts[l].WBBM}`;
      listDiv.appendChild(e);
    });
  }

  // Ambil data GeoJSON eksternal
  fetch('data/wbk-wbbm.geojson')
    .then(res => res.json())
    .then(geojsonData => {
      const dataList = renderMarkers(geojsonData, 'all');
      updateChart(dataList);
      if(markerLayer.getLayers().length) map.fitBounds(markerLayer.getBounds().pad(0.2));

      const yearFilter = document.getElementById('yearFilter');
      if(yearFilter){
        yearFilter.addEventListener('change', e=>{
          const yr = e.target.value;
          const list = renderMarkers(geojsonData, yr);
          updateChart(list);
          if(markerLayer.getLayers().length) map.fitBounds(markerLayer.getBounds().pad(0.2));
        });
      }
    })
    .catch(err => console.error('Gagal memuat data GeoJSON:', err));
</script>



  </body>
</html>
