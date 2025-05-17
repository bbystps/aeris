
<!-- Table Script Start -->
<script>
$(document).ready(function() {
  // console.log("Loading Table Data");
  loadTableData2();
  
  // Event delegation for sort buttons
  $(document).on('click', '.sort-btn2', function() {
    const column = $(this).data('column');
    const isAscending = $(this).hasClass('asc');
    sortTable2(column, !isAscending);
    $(this).toggleClass('asc', !isAscending);
  });
});

function sortTable2(column, ascending) {
  const rows = $('#sensor-data2 tr').get();

  rows.sort((a, b) => {
    const cellA = $(a).children('td').eq(column).text();
    const cellB = $(b).children('td').eq(column).text();

    let comparison = 0;
    if ($.isNumeric(cellA) && $.isNumeric(cellB)) {
      comparison = parseFloat(cellA) - parseFloat(cellB);
    } else {
      comparison = cellA.localeCompare(cellB);
    }

    return ascending ? comparison : -comparison;
  });

  $.each(rows, function(index, row) {
    $('#sensor-data2').append(row);
  });

  paginationFunction2(); // Reapply pagination after sorting
}

// $(document).on('keyup', '#searchInputTable2', function() {
//   var value = $(this).val().toLowerCase();

//   if (value) {
//     $('#sensor-data2 tr').filter(function() {
//       $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
//     });
//   } else {
//     $('#sensor-data2 tr').show();
//     paginationFunction2();
//   }
// });

function loadTableData2() {
  console.log("TRYING TO LOAD TABLE2");
  let lamp_location2 = $('#location_ref').text();
  console.log("This is lamp location table 2: " + lamp_location2)
  $.ajax({
    url: "fetch_table_data2.php",
    method: "GET",
    dataType: "json",
    data: { lamp_location: lamp_location2 },
    success: function(data) {
      var rows = '';
      if (data.length === 0) {
        $('#sensor-data').html('<tr><td colspan="2">No data available</td></tr>');
      } else {
        $.each(data, function(index, value) {
          rows += '<tr>';
          rows += '<td>' + value.temperature + '</td>';
          rows += '<td>' + value.humidity + '</td>';
          rows += '<td>' + value.pm25 + '</td>';
          rows += '<td>' + value.pm10 + '</td>';
          rows += '<td>' + value.timestamp + '</td>';
          rows += '</tr>';
        });
        $('#sensor-data2').html(rows);
      }

      paginationFunction2();
    },
    error: function(err) {
      $('#sensor-data2').html('<tr><td colspan="2">Error fetching data</td></tr>');
      console.error("Fetch error:", err);
    }
  });
}


// function hideColumns() {
//   // Hide the first 3 columns (Sensor ID, Temp, RH)
//   $('.col-sensor-id, .col-temp, .col-sd').hide(); // Hide the header cells

//   $('#sensor-data tr').each(function() {
//     $(this).find('td').eq(0).hide(); // Hide Sensor ID column
//     $(this).find('td').eq(1).hide(); // Hide Temp column
//     $(this).find('td').eq(3).hide(); // Hide RH column
//   });
// }

function paginationFunction2() {
  const rowsPerPage = 5;
  const table = document.getElementById('sensorDatatable2').getElementsByTagName('tbody')[0];
  const rows = table.getElementsByTagName('tr');
  const pagination2 = document.getElementById('pagination2');
  const prevBtn = document.getElementById('prevBtn2');
  const nextBtn = document.getElementById('nextBtn2');
  const pageNumbers = document.getElementById('pageNumbers2');
  
  let currentPage = 1;
  let totalPages = Math.ceil(rows.length / rowsPerPage);

  function displayRows() {
      for (let i = 0; i < rows.length; i++) {
          rows[i].style.display = 'none';
      }
      const start = (currentPage - 1) * rowsPerPage;
      const end = start + rowsPerPage;
      for (let i = start; i < end && i < rows.length; i++) {
          rows[i].style.display = '';
      }
      updatePagination2();
  }

  function updatePagination2() {
      pageNumbers.innerHTML = `Page ${currentPage} of ${totalPages}`;
      prevBtn.disabled = currentPage === 1;
      nextBtn.disabled = currentPage === totalPages;
  }

  prevBtn.addEventListener('click', function () {
      if (currentPage > 1) {
          currentPage--;
          displayRows();
      }
  });

  nextBtn.addEventListener('click', function () {
      if (currentPage < totalPages) {
          currentPage++;
          displayRows();
      }
  });

  displayRows();
};
</script>
<!-- Table Script End -->
<script>
$(document).on('click', '.btn-export', function() {
  const lamp_location2 = $('#location_ref').text();

  $.ajax({
    url: "fetch_table_data2.php",
    method: "GET",
    dataType: "json",
    data: { lamp_location: lamp_location2 },
    success: function(data) {
      let csvContent = "data:text/csv;charset=utf-8,";

      // Define headers manually
      const headers = ["Temperature", "Humidity", "PM 2.5", "PM 10", "Timestamp"];
      csvContent += headers.join(",") + "\n";

      // Add data rows
      data.forEach(row => {
        csvContent += [
          row.temperature,
          row.humidity,
          row.pm25,
          row.pm10,
          row.timestamp
        ].join(",") + "\n";
      });

      // Trigger download
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "sensor_data_all.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    error: function(err) {
      alert("Failed to export data.");
      console.error("Export error:", err);
    }
  });
});
</script>
