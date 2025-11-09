<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Peta Yurisdiksi Pengadilan Agama</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        .legend {
            background: white;
            line-height: 1.4em;
            padding: 8px;
            border-radius: 6px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
            font-size: 0.9rem;
            max-height: 250px;
            overflow-y: auto;
        }

        .dropdown {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 1000;
            background: white;
            border-radius: 8px;
            padding: 6px 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>

<body class="bg-gray-50">
    <div id="map"></div>

    <!-- Dropdown daftar Pengadilan Agama -->
    <div class="dropdown">
        <label for="pengadilanSelect" class="text-sm font-semibold block mb-1">Pilih Pengadilan:</label>
        <select id="pengadilanSelect" class="border border-gray-300 rounded-md px-2 py-1 text-sm w-56">
            <option value="">-- Pilih Pengadilan Agama --</option>
        </select>
    </div>

    <script>
        // Inisialisasi peta
        const map = L.map('map').setView([-6.9, 107.6], 8);

        // Basemap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
        }).addTo(map);

        // Fungsi warna acak
        function getRandomColor() {
            return '#' + Math.floor(Math.random() * 16777215).toString(16);
        }

        const satkerColors = {};

        // ========================
        // Layer Batas Yurisdiksi
        // ========================
        fetch('data/batas_kecamatan.geojson')
            .then(res => res.json())
            .then(data => {
                const batasLayer = L.geoJSON(data, {
                    style: feature => {
                        const namaSatker = feature.properties.nama_satker;
                        if (!satkerColors[namaSatker]) {
                            satkerColors[namaSatker] = getRandomColor();
                        }
                        return {
                            color: "white",        // warna outline
                            weight: 0.5,           // ketebalan outline (semakin besar makin tebal)
                            fillColor: satkerColors[namaSatker],
                            fillOpacity: 0.6
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        layer.bindTooltip(
                            `<strong>Kecamatan ${feature.properties.nmkec}</strong>`,
                            { direction: "auto" }
                        );
                    }
                }).addTo(map);

                map.fitBounds(batasLayer.getBounds());

                // Legend
                const legend = L.control({ position: 'bottomright' });
                legend.onAdd = function () {
                    const div = L.DomUtil.create('div', 'legend');
                    div.innerHTML = '<strong>Yurisdiksi Pengadilan Agama</strong><br>';
                    for (const [nama, color] of Object.entries(satkerColors)) {
                        div.innerHTML += `
              <div class="flex items-center mb-1">
                <span style="background:${color}; width:14px; height:14px; display:inline-block; margin-right:6px; border:1px solid #555;"></span>
                ${nama}
              </div>`;
                    }
                    return div;
                };
                legend.addTo(map);
            });

        // ========================
        // Layer Titik Pengadilan Agama
        // ========================
        const courtIcon = L.icon({
            iconUrl: 'icon/court.png',
            iconSize: [28, 28], // sesuaikan ukuran
            iconAnchor: [14, 28],
            popupAnchor: [0, -28]
        });

        const markers = {}; // Simpan marker berdasarkan nama_satker

        fetch('data/peradilan_agama.geojson')
            .then(res => res.json())
            .then(data => {
                const layer = L.geoJSON(data, {
                    pointToLayer: (feature, latlng) => {
                        const marker = L.marker(latlng, { icon: courtIcon });
                        const namaSatker = feature.properties.nama_satker;
                        markers[namaSatker] = marker;
                        return marker;
                    },
                    onEachFeature: (feature, layer) => {
                        const props = feature.properties;
                        layer.bindTooltip(`<strong>${props.nama_satker}</strong>`);
                        layer.bindPopup(
                            `<strong>${props.nama_satker}</strong><br>`
                        );
                    }
                }).addTo(map);

                // Isi dropdown
                const select = document.getElementById('pengadilanSelect');
                Object.keys(markers)
                    .sort()
                    .forEach(nama => {
                        const option = document.createElement('option');
                        option.value = nama;
                        option.textContent = nama;
                        select.appendChild(option);
                    });

                // Zoom ke lokasi ketika dipilih
                select.addEventListener('change', (e) => {
                    const nama = e.target.value;
                    if (nama && markers[nama]) {
                        const marker = markers[nama];
                        map.setView(marker.getLatLng(), 12, { animate: true });
                        marker.openPopup();
                    }
                });
            });

        fetch("data/batas_kabupaten.geojson")
            .then(response => response.json())
            .then(batasKabupaten => {
                L.geoJSON(batasKabupaten, {
                    style: {
                        color: "red",       // outline warna merah
                        weight: 3,          // ketebalan garis
                        fillOpacity: 0      // transparan (tidak ada isi warna)
                    }
                }).addTo(map);
            });
    </script>
</body>

</html>