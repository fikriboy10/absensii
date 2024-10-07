<style>
    #map {
        height: 250px;
    }

</style>

<div id="map"></div>
<script>
    // Mendapatkan lokasi dari variabel $absensi
    var lokasi = "{{ $absensi->lokasi_in }}";
    var lok = lokasi.split(",");
    var latitude = lok[0];
    var longitude = lok[1];

    // Membuat peta dengan Leaflet
    var map = L.map('map').setView([latitude, longitude], 15);

    // Menambahkan lapisan peta dari OpenStreetMap
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // Menambahkan marker pada lokasi
    var marker = L.marker([latitude, longitude]).addTo(map);

    // Menambahkan lingkaran pada lokasi tertentu
    var circle = L.circle([-6.979469550074856, 107.67339922532808], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.5,
        radius: 500
    }).addTo(map);

    // Menambahkan popup pada lokasi
    var popup = L.popup()
        .setLatLng([latitude, longitude])
        .setContent("{{ $absensi->nama_lengkap }}")
        .openOn(map);
</script>
