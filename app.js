// Inisialisasi peta
const map = L.map('map').setView([-2.5, 118], 5);

// Tambahkan basemap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Fungsi warna berdasarkan nilai jml_kdrt
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

// Buat elemen tooltip manual di DOM
const tooltip = L.DomUtil.create('div', 'custom-tooltip');
tooltip.style.position = 'absolute';
tooltip.style.pointerEvents = 'none';
tooltip.style.padding = '6px 10px';
tooltip.style.background = 'rgba(255,255,255,0.9)';
tooltip.style.borderRadius = '4px';
tooltip.style.fontSize = '12px';
tooltip.style.boxShadow = '0 0 6px rgba(0,0,0,0.3)';
tooltip.style.display = 'none';
document.body.appendChild(tooltip);

// Muat data GeoJSON
fetch('data/kdrt.geojson')
  .then(res => res.json())
  .then(data => {
    const geojsonLayer = L.geoJSON(data, {
      pointToLayer: (feature, latlng) => {
        return L.circleMarker(latlng, {
          radius: getRadius(feature.properties.jml_kdrt),
          fillColor: getColor(feature.properties.jml_kdrt),
          color: "#555",
          weight: 1,
          opacity: 1,
          fillOpacity: 0.8
        });
      },
      onEachFeature: (feature, layer) => {
        const props = feature.properties;

        // Event hover
        layer.on('mouseover', (e) => {
          tooltip.style.display = 'block';
          tooltip.innerHTML = `
            <b>${props.nama_satker}</b><br>
            Jumlah KDRT: ${props.jml_kdrt}
          `;
        });

        // Update posisi tooltip mengikuti kursor
        layer.on('mousemove', (e) => {
          tooltip.style.left = (e.originalEvent.pageX + 10) + 'px';
          tooltip.style.top = (e.originalEvent.pageY - 10) + 'px';
        });

        // Sembunyikan tooltip saat mouse keluar
        layer.on('mouseout', () => {
          tooltip.style.display = 'none';
        });
      }
    }).addTo(map);

    // Tambah legenda
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
  .catch(err => console.error("Gagal memuat GeoJSON:", err));