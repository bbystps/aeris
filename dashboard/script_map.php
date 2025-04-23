<script>
// Initialize the map globally
var map;
var currentMarker; // Variable to store the current marker

function initializeMap(sensorId) {
  $.ajax({
    url: `fetch_marker.php?sensorId=${sensorId}`,
    method: "GET",
    dataType: "json",
    success: function(data) {
      // console.log(data);
      const latitude = data[0].latitude;
      const longitude = data[0].longitude;

      if (!map) {
        map = L.map('map', {
          attributionControl: false // Disable the Leaflet attribution
        }).setView([latitude, longitude], 15);

        // Add the tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '' // Remove the attribution text
        }).addTo(map);
      }

      // Add or update the marker
      if (currentMarker) {
        map.removeLayer(currentMarker); // Remove the previous marker
      }

      currentMarker = L.marker([latitude, longitude]).addTo(map)
        // .bindPopup('Sensor Location').openPopup();
    },
    error: function(xhr, status, error) {
      console.error('Error fetching location:', error);
    }
  });
}

// Function to move to the marker based on sensorId
function goToMarker(sensorId) {
  // Fetch location data from the server based on sensorId
  $.ajax({
    url: `fetch_marker.php?sensorId=${sensorId}`,
    method: "GET",
    dataType: "json",
    success: function(data) {
      // console.log(data);
      const latitude = data[0].latitude;
      const longitude = data[0].longitude;

      // Ensure map is initialized
      if (!map) {
        console.error("Map not initialized");
        return;
      }

      // Move the map to the fetched coordinates
      map.setView([latitude, longitude], 15);

      // Add or update the marker
      if (currentMarker) {
        map.removeLayer(currentMarker); // Remove the previous marker
      }

      currentMarker = L.marker([latitude, longitude]).addTo(map)
        // .bindPopup('Sensor Location').openPopup();
    },
    error: function(xhr, status, error) {
      console.error('Error fetching location:', error);
    }
  });
}

</script>
