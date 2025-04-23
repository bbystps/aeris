<!DOCTYPE html>
<html lang="en">
  
<?php 
  // session_start();
  // if($_SESSION['loggedin'] !== true){
  //   header("Location: ../login");
  //   exit();
  // }
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AERIS</title>
  
  <link rel="stylesheet" href="../css/montserrat.css">
  <link rel="stylesheet" href="../css/icon.css">
  <link rel="stylesheet" href="../css/element.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="toastr/toastr.min.css">
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<script src="mqtt/mqttws31.js"></script>
<?php include("mqtt/cards_mqtt.php"); ?>

<body onload="client.connect(options);">
<!-- <body> -->
      
  <div class="container">

    <div id="nav-hidden">
      <div class="container-space-between">
        <div class="toggle-button">&#9776;</div>
        <div class="title-nav-hidden"><span class="highlight">AERIS</span></div>
        <div class="user-button">
        </div>
      </div>
    </div>
    
    <div id="nav">
      <div class="nav-container">
        <div class="nav-lists" onclick="gotoDashboard();">Dashboard</div>
      </div>
    </div>

    <!-- sidebar start -->
    <div id="sidebar">
      <?php include("../includes/sidebar.php"); ?>
    </div>
    <!-- sidebar end -->

    <div id="content">

      <div class="top-container">
        <div class="card">
          <div class="card-value">
            <span class="value-number" id="temperature">--</span>
            <span class="card-unit">°C</span>
          </div>
          <div class="card-label">Temperature</div>
        </div>
        <div class="card">
          <div class="card-value">
            <span class="value-number" id="humidity">--</span>
            <span class="card-unit">%</span>
          </div>
          <div class="card-label">Humidity</div>
        </div>
        <div class="card">
          <div class="card-value">
            <span class="value-number" id="pm2.5">--</span>
            <span class="card-unit">µg/m³</span>
          </div>
          <div class="card-label">PM 2.5</div>
        </div>
        <div class="card">
          <div class="card-value">
            <span class="value-number" id="pm10">--</span>
            <span class="card-unit">µg/m³</span>
          </div>
          <div class="card-label">PM 10</div>
        </div>
      </div>

      <div class="bottom-container">
        <div class="bottom-left">

          <div class="table-header">
            <div class="table-title">
              <div class="table-label" id="table_label">LAMP</div>
              <!-- <div class="table-location" id="table_location">National Capital Region</div> -->
              <div hidden id="location_ref"></div>
            </div>
            <div class="table-search">
              <label class="searchLabelWrap-content1">
                <span class="visually-hidden">Search</span>
                <svg viewBox="0 0 512 512" aria-hidden="true" class="icon" width="20">
                  <path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z" />
                </svg>
                <input type="search" placeholder="Search . . ." class="searchInput" id="searchInputTable" name="searchInputTable">
              </label>
            </div>
          </div>
          <div class="table-container">
            <table id="sensorDatatable" class="table-content">
              <thead>
                <tr>
                  <th>Timestamp <button class="sort-btn" data-column="0"><span class="sort-icon">&#9650;</span><span class="sort-icon">&#9660;</span></button></th>
                  <th>Status <button class="sort-btn" data-column="1"><span class="sort-icon">&#9650;</span><span class="sort-icon">&#9660;</span></button></th>
                </tr>
              </thead>

              <tbody id="sensor-data">
              </tbody>
            </table>
            <div id="pagination">
                <button id="prevBtn">Prev</button>
                <span id="pageNumbers">1</span>
                <button id="nextBtn">Next</button>
            </div>
          </div>

        </div>

        <div class="bottom-right">
          <div class="card-traffic">
            <div class="traffic-number" id="footTraffic">--</div>
            <div class="traffic-label">Foot Traffic</div>
          </div>
          <div class="card-map">
            <div id="map"></div>
          </div>
        </div>
      </div>

    </div>

  </div>

</body>

<script src="../js/jquery.min.js"></script>
<script src="toastr/toastr.min.js"></script>

<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<?php include("script_table.php"); ?>
<?php include("script_map.php"); ?>

<!-- Fetch Sidebar Site Locations -->
<script>
  $(document).ready(function(){
    $.ajax({
      url: 'fetch_site_locations.php', // Path to your PHP script
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        // Clear the existing content
        $('.site-lists').empty();

        // Loop through the response and append to the site-lists div
        response.forEach(function(location, index) {
          const isActive = index === 0 ? ' active' : ''; // Make the first item active
          $('.site-lists').append(
            '<p><i class="icon-location"></i><span class="site-location' + isActive + '" data-area="' + location.name + '">' + location.name + '</span></p>'
          );
        });
        
        // ✅ Set the label and ref based on the first item (onload)
        if (response.length > 0) {
          const firstArea = response[0].name;
          const formattedFirstArea = firstArea.toLowerCase().replace(/\s+/g, '');
          $('#table_label').text(firstArea);
          $('#location_ref').text(formattedFirstArea);
          // console.log('Selected area1:', formattedFirstArea);
          
          displaySensorDatas(formattedFirstArea);
          displayFootTraffic(formattedFirstArea);
          initializeMap(firstArea);
        }

        // ✅ Handle click events
        $('.site-lists').on('click', '.site-location', function() {
          $('.site-location').removeClass('active');
          
          $(this).addClass('active');
          
          const selectedArea = $(this).data('area');
          const formattedArea = selectedArea.toLowerCase().replace(/\s+/g, '');
          // console.log('Selected area2:', formattedArea);
          // Update table label and location reference
          $('#table_label').text(selectedArea); // Show the name, e.g., "Lamp 1"
          $('#location_ref').text(formattedArea); // Hidden value, e.g., "lamp1"

          displaySensorDatas(formattedArea);
          displayFootTraffic(formattedArea);
          goToMarker(selectedArea);
          loadTableData();
        });
        
        loadTableData();
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
      }
    });
  });
</script>

<script>
  function displaySensorDatas(sensorId) {
    $.ajax({
      url: 'fetch_sensor_data.php', // Path to the PHP script fetching sensor IDs
      method: 'POST',
      data: { sensorId: sensorId },
      dataType: 'json',
      success: function(sensorData) {
        // console.log('Parsed Sensor Data:', sensorData);

        sensorData.forEach(function(data) {
          document.getElementById('temperature').innerHTML = data.temperature;
          document.getElementById('humidity').innerHTML = data.humidity;
          document.getElementById('pm2.5').innerHTML = data.pm25;
          document.getElementById('pm10').innerHTML = data.pm10;
        });
      }
    });
  }
</script>

<script>
  function displayFootTraffic(sensorId) {
    $.ajax({
      url: 'fetch_foot_traffic.php', // Path to the PHP script fetching sensor IDs
      method: 'POST',
      data: { sensorId: sensorId },
      dataType: 'json',
      success: function(sensorData) {
        document.getElementById('footTraffic').innerHTML = sensorData.total_today;
      }
    });
  }
</script>

<!-- Sidebar toggle button open close -->
<script>
  document.querySelector('.toggle-button').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.add("sidebar-visible");
    sidebar.style.display = 'block';
  });
  document.querySelector('.close-sidebar').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.remove("sidebar-visible");
    sidebar.style.display = 'none';
  });
  window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 550) {
      // Automatically hide the sidebar on larger screens
      sidebar.classList.remove("sidebar-visible");
      sidebar.style.display = 'block';
    } else if (!sidebar.classList.contains("sidebar-visible")) {
      sidebar.style.display = 'none';
    }
  });
</script>

</html>
