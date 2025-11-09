<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Peta Sebaran Perkara Perdata Agama</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #map {
            height: 100vh;
        }

        .legend {
            background: white;
            padding: 10px 12px;
            font-size: 14px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            line-height: 1.5;
        }

        .legend-title {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            color: #374151;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }

        .legend-color {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            border: 1px solid #999;
            border-radius: 3px;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">

    <!-- Kontainer Utama -->
    <div class="flex flex-row h-screen">
        <!-- Kolom Kiri: Peta (70%) -->
        <div class="relative w-[70%]">
            <!-- Dropdown Tahun -->
            <div class="absolute top-4 right-4 z-[1000] bg-white shadow-md rounded-lg p-3">
                <label class="block text-sm font-semibold mb-1 text-gray-700">Pilih Tahun</label>
                <select id="yearSelect" class="border border-gray-300 rounded p-2 text-sm">
                    <option value="2020" selected>2020</option>
                    <option value="2021">2021</option>
                    <option value="2022">2022</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
            </div>

            <!-- Peta -->
            <div id="map" class="w-full h-full"></div>
        </div>

        <!-- Kolom Kanan: Grafik Tren (30%) -->
        <div class="w-[30%] bg-white shadow-inner p-6 overflow-auto">
            <h2 id="chartTitle" class="text-lg font-semibold text-gray-700 mb-4 text-center">
                Tren Jumlah Perkara Perdata Agama (Nasional)
            </h2>
            <h2 id="chartTitle" class="text-lg font-semibold text-gray-700 mb-4 text-center">
                Pilih Wilayah pada Peta untuk Melihat Tren Jumlah Perkara
            </h2>
            <canvas id="trendChart" height="200"></canvas>

            <!-- Informasi tambahan -->
            <p class="text-xs text-gray-500 italic text-center mt-3">
                *Data yang ditampilkan merupakan data dummy untuk tujuan visualisasi*
            </p>
            <canvas id="trendChart" height="200"></canvas>
        </div>
    </div>

    <script>
        let map = L.map('map').setView([-2.5, 118], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 10,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let geojsonLayer;
        let dataKabupaten;
        let selectedYear = 2020;
        let chart;

        const colors = ['#fef0d9', '#fdcc8a', '#fc8d59', '#e34a33', '#b30000'];

        function getColor(d) {
            return d > 700 ? colors[4] :
                d > 500 ? colors[3] :
                    d > 300 ? colors[2] :
                        d > 100 ? colors[1] :
                            colors[0];
        }

        function style(feature) {
            const value = feature.properties[`jml_perkara_${selectedYear}`];
            return {
                fillColor: getColor(value),
                weight: 1,
                opacity: 1,
                color: 'white',
                fillOpacity: 0.8
            };
        }

        function highlightFeature(e) {
            const layer = e.target;
            layer.setStyle({
                weight: 2,
                color: '#666',
                fillOpacity: 1
            });
            layer.bringToFront();
        }

        function resetHighlight(e) {
            geojsonLayer.resetStyle(e.target);
        }

        function zoomToFeature(e) {
            const props = e.target.feature.properties;
            showChart(props);
        }

        function onEachFeature(feature, layer) {
            const props = feature.properties;
            layer.bindTooltip(`${props.nmkab} (${props.nmprov})<br><b>${props[`jml_perkara_${selectedYear}`]}</b> perkara`, {
                direction: 'top'
            });

            layer.on({
                mouseover: highlightFeature,
                mouseout: resetHighlight,
                click: zoomToFeature
            });
        }

        // Load GeoJSON
        fetch('data/kabupaten.geojson')
            .then(res => res.json())
            .then(json => {
                dataKabupaten = json;
                updateMap();
                showNationalTrend();
            });

        // Update peta saat tahun berubah
        document.getElementById('yearSelect').addEventListener('change', function () {
            selectedYear = this.value;
            updateMap();
        });

        function updateMap() {
            if (geojsonLayer) map.removeLayer(geojsonLayer);
            geojsonLayer = L.geoJSON(dataKabupaten, {
                style: style,
                onEachFeature: onEachFeature
            }).addTo(map);
            addLegend();
        }

        // Tambah legenda
        function addLegend() {
            if (map.legendControl) map.removeControl(map.legendControl);

            const legend = L.control({ position: 'bottomright' });
            legend.onAdd = function () {
                const div = L.DomUtil.create('div', 'legend');
                const grades = [0, 100, 300, 500, 700];

                div.innerHTML += '<span class="legend-title">Jumlah Perkara</span>';

                for (let i = 0; i < grades.length; i++) {
                    div.innerHTML += `
        <div class="legend-item">
          <div class="legend-color" style="background:${getColor(grades[i] + 1)}"></div>
          <span>${grades[i]}${grades[i + 1] ? '&ndash;' + grades[i + 1] : '+'}</span>
        </div>
      `;
                }

                return div;
            };

            legend.addTo(map);
            map.legendControl = legend;
        }

        // Chart.js
        function showChart(props) {
            const label = props.nmkab + ' (' + props.nmprov + ')';
            const data = [
                props.jml_perkara_2020, props.jml_perkara_2021,
                props.jml_perkara_2022, props.jml_perkara_2023,
                props.jml_perkara_2024, props.jml_perkara_2025
            ];
            const years = [2020, 2021, 2022, 2023, 2024, 2025];

            document.getElementById('chartTitle').innerText = `Tren Jumlah Perkara - ${label}`;
            updateChart(years, data, label);
        }

        function showNationalTrend() {
            const totals = { 2020: 0, 2021: 0, 2022: 0, 2023: 0, 2024: 0, 2025: 0 };
            dataKabupaten.features.forEach(f => {
                for (let year in totals) {
                    totals[year] += f.properties[`jml_perkara_${year}`];
                }
            });
            const years = Object.keys(totals);
            const data = Object.values(totals);
            document.getElementById('chartTitle').innerText = 'Tren Jumlah Perkara Perdata Agama (Nasional)';
            updateChart(years, data, 'Nasional');
        }

        function updateChart(labels, data, title) {
            const ctx = document.getElementById('trendChart');
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: title,
                        data,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Jumlah Perkara' } },
                        x: { title: { display: true, text: 'Tahun' } }
                    }
                }
            });
        }
    </script>
</body>

</html>