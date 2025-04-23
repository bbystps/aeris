<script>
// Define chart and lastId as global variables
var lineChart;
var lastId = 0; // Keep track of the last fetched ID

// Function to fetch the latest data from the server
function fetchLatestData() {
  console.log("Fetching latest data...");
  const url = `fetch_history.php?last_id=${lastId}`;

  fetch(url)
    .then(response => response.json())
    .then(newData => {
      console.log('Fetched new data:', newData);
      if (newData.length > 0) {
        // Update lastId with the latest ID from the new data
        lastId = Math.max(...newData.map(d => d.id));
        console.log('Updated lastId:', lastId);

        // Append new data to the chart's existing data
        newData.forEach(dataPoint => {
          console.log('Adding dataPoint:', dataPoint);
          lineChart.data.push(dataPoint);
        });

        // Notify the chart about the new data
        lineChart.invalidateData();
        console.log('Chart updated with new data.');
      } else {
        console.log('No new data to add.');
      }
    })
    .catch(error => console.error('Error fetching latest data:', error));
}

// Function to initialize or update the chart
function updateChart() {
  
  // const activeSensor = document.querySelector('.card-sensor-id .dropdown-btn').textContent.trim();
  // const formattedSensor_id = activeSensor.toLowerCase().replace(/\s+/g, '_');

  let activeSensor = document.querySelector('.card-sensor-id .dropdown-btn').textContent.trim();
  let formattedSensor_id = activeSensor.split(': ')[1]; // Splits by ": " and takes the second part
  let start_date = document.getElementById('start_date').value;
  let end_date = document.getElementById('end_date').value;

  // console.log('Active Sensor:', activeSensor);
  // console.log('Formatted Sensor:', formattedSensor_id);
  // console.log('start_date:', start_date);
  // console.log('end_date:', end_date);

  // const url = `fetch_history.php`;
  let url = `fetch_history.php?sensor_id=${formattedSensor_id}&start_date=${start_date}&end_date=${end_date}`;

  fetch(url)
    .then(response => response.json())
    .then(data => {
      // console.log('Fetched initial data:', data);

      // Check if chart already exists
      if (lineChart) {
        console.log("Disposing existing chart...");
        lineChart.dispose();
        // lineChart = null; // Ensure chart is fully cleaned up
      }

      // Create the chart instance
      lineChart = am4core.create("history_chart", am4charts.XYChart);

      // Set the data for the chart
      lineChart.data = data;

      // Update lastId if data exists
      if (data.length > 0) {
        lastId = Math.max(...data.map(d => d.id));
        console.log('Initial lastId:', lastId);
      }

      // Create date axis
      var dateAxis = lineChart.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.grid.template.location = 0;
      dateAxis.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm:ss";
      dateAxis.renderer.labels.template.fontSize = 10;
      dateAxis.renderer.labels.template.fill = am4core.color("#FFFFFF");
      dateAxis.baseInterval = { timeUnit: "second", count: 1 };
      dateAxis.tooltipDateFormat = "yyyy-MM-dd HH:mm:ss";

      // Create value axis
      var valueAxis = lineChart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.labels.template.fontSize = 10;
      valueAxis.renderer.labels.template.fill = am4core.color("#FFFFFF");

      // Function to create a series for each data field
      var createSeries = function (field, name) {
        // Map field names to display names
        const nameMappings = {
          water_level: "Water Level",
          geophone: "Geophone",
          turbidity: "Turbidity"
        };

        if (nameMappings[field]) {
          name = nameMappings[field];
        }

        var series = lineChart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.dateX = "timestamp";
        series.name = name;
        series.tooltipText = "{name}: [bold]{valueY}[/]";
        series.strokeWidth = 2;
        series.strokeDasharray = "5,2";
        series.tensionX = 1;

        var bullet = series.bullets.push(new am4charts.CircleBullet());
        bullet.circle.radius = 2;
        bullet.circle.strokeWidth = 1;
        bullet.circle.fill = am4core.color("#fff");

        // Set series colors based on field
        const fieldColors = {
          water_level: "#2389da",
          geophone: "orange",
          turbidity: "green"
        };

        if (fieldColors[field]) {
          series.stroke = am4core.color(fieldColors[field]);
        }

        return series;
      };

      // Create series for all fields except "timestamp" and "id"
      for (var key in data[0]) {
        if (key !== "timestamp" && key !== "id") {
          createSeries(key, key);
        }
      }

      // Add legend
      lineChart.legend = new am4charts.Legend();
      lineChart.legend.fontSize = 12;
      lineChart.legend.labels.template.fill = am4core.color("#FFFFFF");

      // Make legend responsive
      if (window.matchMedia("(max-width: 767px)").matches) {
        lineChart.legend.itemContainers.template.layout = "vertical";
        lineChart.legend.itemContainers.template.columnCount = 1;
      }

      // Add cursor and export menu
      lineChart.cursor = new am4charts.XYCursor();
      lineChart.mouseWheelBehavior = "zoomX";
      // lineChart.exporting.menu = new am4core.ExportMenu();
    })
    .catch(error => console.error('Error fetching initial data:', error));
}

// Set an interval to fetch the latest data periodically
// setInterval(fetchLatestData, 10000); // Fetch every 5 seconds
</script>
