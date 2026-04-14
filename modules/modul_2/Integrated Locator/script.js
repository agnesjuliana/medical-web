// Data Dummy Lokasi Faskes (Sebagai contoh menggunakan daerah Jawa Timur / Surabaya)
// Nanti ini bisa di Fetch dari Backend MySQL Anda (SELECT * FROM modul_locator_faskes)
const faskesDatabase = [
    { id: 1, name: "RSIA Kendangsari", type: "RSIA", lat: -7.3188, lng: 112.7554, address: "Jl. Raya Kendangsari No.38" },
    { id: 2, name: "Puskesmas Keputih", type: "Puskesmas", lat: -7.2917, lng: 112.8021, address: "Jl. Keputih Tegal, Surabaya" },
    { id: 3, name: "Bidan Indah Mulyani", type: "Bidan", lat: -7.2831, lng: 112.7845, address: "Klampis Semalang, Sukolilo" },
    { id: 4, name: "RS Maternity Putri", type: "RSIA", lat: -7.2625, lng: 112.7483, address: "Pusat Kota Surabaya" },
    { id: 5, name: "Puskesmas Mulyorejo", type: "Puskesmas", lat: -7.2682, lng: 112.7816, address: "Jl. Mulyorejo Raya No. 201" },
    { id: 6, name: "Klinik Bidan Lestari", type: "Bidan", lat: -7.3000, lng: 112.7300, address: "Jl. Wonokromo Raya" }
];

let map;
let userMarker = null;
let currentFilter = "Semua";
let faskesMarkers = []; // Mempermudah hapus/render ulang marker
let userCurrentPos = { lat: -7.2504, lng: 112.7688 }; // Default (Center Surabaya) jika GPS mati
let isGpsActive = false;

// 1. Inisialisasi Peta Pertama Kali
function initMap() {
    map = L.map('map').setView([userCurrentPos.lat, userCurrentPos.lng], 13);
    
    // Style Map (menggunakan tile gratis dari OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Langsung tembak deteksi GPS
    detectUserGPS();
}

// 2. Deteksi GPS Otomatis (Navigator Geolocation API)
function detectUserGPS() {
    const gpsStatusDiv = document.getElementById('gpsStatus');
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userCurrentPos.lat = position.coords.latitude;
                userCurrentPos.lng = position.coords.longitude;
                isGpsActive = true;

                gpsStatusDiv.innerHTML = `<i class="fas fa-check-circle"></i> Lokasi Akurat`;
                gpsStatusDiv.style.color = "#2e7d32"; // Hijau

                // Update center peta ke lokasi pengguna
                map.setView([userCurrentPos.lat, userCurrentPos.lng], 14);

                // Tambah/Update Marker Pengguna Biru
                if(userMarker) map.removeLayer(userMarker);
                userMarker = L.marker([userCurrentPos.lat, userCurrentPos.lng]).addTo(map)
                              .bindPopup("<b>Lokasi Anda Saat Ini</b>").openPopup();

                renderListAndMap(); // Refresh tampilan karena jarak berubah
            },
            (error) => {
                gpsStatusDiv.innerHTML = `<i class="fas fa-exclamation-circle" style="color:red"></i> GPS Mati / Dilarang`;
                alert("GPS tidak diaktifkan. Jarak akan dilacak menggunakan standar pusat kota.");
                renderListAndMap(); // Re-render tanpa presisi GPS
            }
        );
    } else {
        alert("Browser Anda tidak mendung Geolocation API.");
        renderListAndMap();
    }
}

// Formula Rumus Jarak Matematika (Haversine Formula) -> Mengolah jarak Langitude Latitude jadi KiloMeter.
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius bumi dalam KM
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)*Math.sin(dLat/2) + 
              Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * 
              Math.sin(dLon/2)*Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; 
}

// 3. Render Daftar (KIRI) dan Marker Peta (KANAN) berdasar FIlter dan Jarak
function renderListAndMap() {
    const listDiv = document.getElementById('faskesList');
    listDiv.innerHTML = "";

    // Bersihkan semua Marker Merah lama di map
    faskesMarkers.forEach(cm => map.removeLayer(cm));
    faskesMarkers = [];

    // Proses Data: Hitung Jarak & Filter
    let processedData = faskesDatabase.map(faskes => {
        let distance = calculateDistance(userCurrentPos.lat, userCurrentPos.lng, faskes.lat, faskes.lng);
        return { ...faskes, distanceKm: distance };
    });

    // Menjalankan kondisi penyaringan tombol atas (Semua, Bidan, dsb)
    if(currentFilter !== "Semua") {
        processedData = processedData.filter(item => item.type === currentFilter);
    }

    // Urutkan (Sorting) dari yang terdekat (KM terkecil - ke - terbesar)
    processedData.sort((a, b) => a.distanceKm - b.distanceKm);

    if(processedData.length === 0){
        listDiv.innerHTML = `<p style="text-align:center; color:#aaa;">Tidak ada fasilitas kategori ini di area rekam kami.</p>`;
        return;
    }

    // Pembuatan Elemen untuk ditaruh di Layar
    processedData.forEach(item => {
        // UI Side List
        const card = document.createElement('div');
        card.className = "faskes-card";
        card.innerHTML = `
            <div class="fc-type">${item.type}</div>
            <div class="fc-title">${item.name}</div>
            <div class="fc-desc"><i class="fas fa-map-marker-alt"></i> ${item.address}</div>
            <div class="fc-distance"><i class="fas fa-location-arrow"></i> ${item.distanceKm.toFixed(1)} Km dari Anda</div>
        `;

        // Action Klik pada List -> Buka Pop Up Peta
        card.onclick = () => {
            map.flyTo([item.lat, item.lng], 16); // Efek Terbang di Peta
            markerInstance.openPopup();
        };
        listDiv.appendChild(card);

        // UI Marker Mapping
        const markerInstance = L.marker([item.lat, item.lng]).addTo(map)
                                .bindPopup(`<b>${item.name}</b><br>${item.distanceKm.toFixed(1)} Km dari Anda`);
        faskesMarkers.push(markerInstance);
    });
}

// 4. Tombol Filter Onclick Event 
function filterFaskes(category) {
    currentFilter = category;
    
    // Switch CSS class Active
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');

    renderListAndMap(); // re-render berdasarkan filter yg dirubah
}

// Auto Load setelah Document Loaded
window.onload = initMap;
