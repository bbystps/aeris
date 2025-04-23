
<!-- Table Script Start -->
<script>
$(document).ready(function() {
  // console.log("Loading Table Data");
  loadTableData();
  
  // Event delegation for sort buttons
  $(document).on('click', '.sort-btn', function() {
    const column = $(this).data('column');
    const isAscending = $(this).hasClass('asc');
    sortTable(column, !isAscending);
    $(this).toggleClass('asc', !isAscending);
  });
});

function sortTable(column, ascending) {
  const rows = $('#sensor-data tr').get();

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
    $('#sensor-data').append(row);
  });

  paginationFunction(); // Reapply pagination after sorting
}

$(document).on('keyup', '#searchInputTable', function() {
  var value = $(this).val().toLowerCase();

  if (value) {
    $('#sensor-data tr').filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  } else {
    $('#sensor-data tr').show();
    paginationFunction();
  }
});

function loadTableData() {
  // console.log("TRYING TO LOAD TABLE");
  let lamp_location = $('#location_ref').text();
  $.ajax({
    url: "fetch_table_data.php",
    method: "GET",
    dataType: "json",
    data: { lamp_location: lamp_location },
    success: function(data) {
      var rows = '';
      if (data.length === 0) {
        $('#sensor-data').html('<tr><td colspan="2">No data available</td></tr>');
      } else {
        $.each(data, function(index, value) {
          rows += '<tr>';
          rows += '<td>' + value.timestamp + '</td>';
          rows += '<td>' + value.status + '</td>';
          rows += '</tr>';
        });
        $('#sensor-data').html(rows);
      }

      paginationFunction();
    },
    error: function(err) {
      $('#sensor-data').html('<tr><td colspan="2">Error fetching data</td></tr>');
      console.error("Fetch error:", err);
    }
  });
}


function hideColumns() {
  // Hide the first 3 columns (Sensor ID, Temp, RH)
  $('.col-sensor-id, .col-temp, .col-sd').hide(); // Hide the header cells

  $('#sensor-data tr').each(function() {
    $(this).find('td').eq(0).hide(); // Hide Sensor ID column
    $(this).find('td').eq(1).hide(); // Hide Temp column
    $(this).find('td').eq(3).hide(); // Hide RH column
  });
}

function paginationFunction() {
  const rowsPerPage = 20;
  const table = document.getElementById('sensorDatatable').getElementsByTagName('tbody')[0];
  const rows = table.getElementsByTagName('tr');
  const pagination = document.getElementById('pagination');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const pageNumbers = document.getElementById('pageNumbers');
  
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
      updatePagination();
  }

  function updatePagination() {
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