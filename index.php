<!DOCTYPE html>
<html lang="sr">
<head>
  <meta charset="UTF-8">
  <title>Stanovi</title>
  <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.1/dist/bootstrap-table.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!-- Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">




</head>
<body>

  <div class="container mt-5">
    <h2 id="naslov"></h2>

    <!-- Dodati dugmad za prikaz ugovora i revalorizacija -->
    <div class="mb-3 d-flex gap-2">
      <button id="showUgovori" class="btn btn-dark"><i class="fa fa-file-contract me-1"></i> Prikaži Ugovore</button>
      <button id="showRevalorizacije" class="btn btn-dark"><i class="fa fa-percent me-1"></i> Prikaži Revalorizacije</button>
      <button id="showStavke" class="btn btn-dark"><i class="fa fa-list me-1"></i> Prikaži Stavke</button>
      <button id="createRevalorizacija" class="btn btn-success"><i class="fa fa-plus me-1"></i> Kreiraj Revalorizaciju</button>
    </div>

    <div id="toolbar"></div>

    <table id="mainTable" class="table table-bordered" data-toggle="table"></table>

</div>


  <!-- Modal for creating "revalorizacija" -->
  <div class="modal fade" id="revalorizacijaModal" tabindex="-1" aria-labelledby="revalorizacijaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="revalorizacijaModalLabel">Kreiraj Revalorizaciju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalContentRevalorizacija">
          <!-- Content from the AJAX request will be loaded here -->
        <form id="revalorizacijaForm">
    <div class="mb-3">
        <label for="iznos" class="form-label">Iznos</label>
        <input type="text" class="form-control" id="iznos" name="iznos" required>
    </div>
    <div class="mb-3">
        <label for="datum" class="form-label">Datum</label>
        <input type="date" class="form-control" id="datum" name="datum" required>
        <div id="datumError" style="color: red; display: none;">Dozvoljeni datumi su samo 1.1. i 30.6. bilo koje godine.</div>
    </div>
</form>
        </div>
        <div class="modal-footer">


          <button id="submitRevalorizacija" type="button" class="btn btn-dark">Kreiraj Revalorizaciju</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal for Revalorizacija Confirmation -->
<div class="modal fade" id="revalorizacijaConfirmModal" tabindex="-1" aria-labelledby="revalorizacijaConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="revalorizacijaConfirmModalLabel">Primena Revalorizacije</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="revalorizacijaConfirmForm" class="p-3">
          <div class="mb-3">
            <h5 class="text-center">Da li želite da odmah primenite revalorizaciju na sve ugovore?</h5>
          </div>
          <div class="d-flex justify-content-center">
            <button type="button" class="btn btn-success me-2" id="applyRevalorizacija">Da, hoću</button>
            <button type="button" class="btn btn-danger" id="cancelRevalorizacija">Ne</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.1/dist/bootstrap-table.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.1/dist/extensions/export/bootstrap-table-export.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin/tableExport.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>

function printUgovor(id) {
  const url = `print_ugovor.php?id_ugovora=${id}`;
  window.open(url, '_blank');
}




function loadTable(url, columns, useFilterControl = false, tabela) {
  $('#mainTable')
    .off('expand-row.bs.table') // uklanja prethodni handler
    .bootstrapTable('destroy')
    .bootstrapTable({
      url: url,
      method: 'GET',
      pagination: true,
      search: true,
      showExport: true,
      exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
      toolbar: '#toolbar',
      iconsPrefix: 'bi',
      uniqueId: 'id',
      detailView: true,
      detailFormatter: detailFormatter,
      filterControl: useFilterControl,
      columns: columns
    })
    .on('expand-row.bs.table', function (e, index, row, $detail) {
      if (tabela === 'ugovor') {
        loadRatesForUgovor(row.id, $detail);
      }
    });
}



// Helper: Default detailFormatter (required by Bootstrap Table)
function detailFormatter(index, row) {
  let html = [];
  $.each(row, function (key, value) {
    html.push('<p><b>' + key + ':</b> ' + value + '</p>');
  });
  return html.join('');
}

function loadRatesForUgovor(ugovorId, $detail) {
  $.ajax({
    url: 'get.php',
    method: 'GET',
    data: { get: 'rate', ugovor_id: ugovorId },
    dataType: 'json',
    success: function(response) {
      if (!response || response.length === 0) {
        $detail.html('<div>No rate data available for this contract.</div>');
        return;
      }

      let currentPage = 1;
      const rowsPerPage = 10;
      const totalRows = response.length;
      const totalPages = Math.ceil(totalRows / rowsPerPage);

      function renderTablePage(page) {
        let start = (page - 1) * rowsPerPage;
        let end = start + rowsPerPage;
        let pageRows = response.slice(start, end);

        let html = `
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>Rata</th>
                <th>Iznos</th>
                <th>Revalorizovani iznos</th>
                <th>Datum</th>
                <th>Plaćeno</th>
                <th>Suma</th>
              </tr>
            </thead>
            <tbody>
        `;

        pageRows.forEach(rate => {
          html += `
            <tr>
              <td>${rate.redni_broj}</td>
              <td>${parseFloat(rate.cena).toFixed(2)}</td>
              <td>${parseFloat(rate.rev_cena).toFixed(2)}</td>
              <td>${rate.datum}</td>
              <td>${parseFloat(rate.placeno).toFixed(2)}</td>
              <td>${parseFloat(rate.ukupno_placeno).toFixed(2)}</td>
            </tr>
          `;
        });

        html += '</tbody></table>';

        // Add pagination controls
        html += `
<div class="pagination-controls d-flex justify-content-center align-items-center mt-3 gap-2">
  <button id="prevPage" class="btn btn-primary btn-sm" ${page === 1 ? 'disabled' : ''}>Previous</button>
  <span> Page ${page} of ${totalPages} </span>
  <button id="nextPage" class="btn btn-primary btn-sm" ${page === totalPages ? 'disabled' : ''}>Next</button>
</div>
        `;

        $detail.html(html);

        // Attach click handlers
        $detail.find('#prevPage').on('click', () => {
          if (currentPage > 1) {
            currentPage--;
            renderTablePage(currentPage);
          }
        });

        $detail.find('#nextPage').on('click', () => {
          if (currentPage < totalPages) {
            currentPage++;
            renderTablePage(currentPage);
          }
        });
      }

      renderTablePage(currentPage);
    },
    error: function() {
      $detail.html('<div>Error loading rate information.</div>');
    }
  });
}



// Specific functions for each menu item

function TableUgovor() {
  loadTable('get.php?get=ugovor', [
    { field: 'broj', title: 'Broj', sortable: true, align: 'center', filterControl: 'input' },
    { field: 'datum', title: 'Datum', sortable: true, align: 'center' },
    { field: 'Cena', title: 'Cena', sortable: true, align: 'center' },
    { field: 'Ucesce', title: 'Učešće', sortable: true, align: 'center' },
    { field: 'BrojRata', title: 'Broj Rata', sortable: true, align: 'center' },
    { field: 'Aktivan', title: 'Aktivan', sortable: true, align: 'center' },
    ], true, 'ugovor'); // Enable filterControl
}


function TableRevalorizacija() {
  loadTable('get.php?get=revalorizacija', [
    { field: 'id', title: 'ID', sortable: true, align: 'center' },
    { field: 'koef', title: 'Iznos Revalorizacije', sortable: true, align: 'center' },
    { field: 'datum', title: 'Datum', sortable: true, align: 'center' },
    { field: 'napomena', title: 'Napomena', align: 'center' },
    { field: 'aktivna', title: 'Aktivna', align: 'center' },
    { field: 'refresh', title: '', align: 'center' }
  ]);
}

function TableStavke() {
  loadTable('get.php?get=stavke', [
    { field: 'id', title: 'ID', sortable: true, align: 'center' },
    { field: 'id_ugovora', title: 'ID Ugovora', sortable: true, align: 'center' },
    { field: 'Dug', title: 'Dug', sortable: true, align: 'center' }
  ]);
}


$(document).ready(function () {
  // Handle "Kreiraj Ugovor" button click
  $('#createUgovor').click(function () {
    $('#ugovorModal').modal('show'); // Show the modal
    $('#modalContentUgovor').html('<p>Loading...</p>'); // Show loading message
    $.ajax({
      url: 'form.php',
      method: 'GET',
      data: { form: 'ugovor' },
      success: function (response) {
        $('#modalContentUgovor').html(response); // Load the form into the modal
        $('#id_komitenta').select2({
          placeholder: 'Izaberi stranku',
          allowClear: true,
          dropdownParent: $('#ugovorModal') // Ensure dropdown works inside modal
        });
      },
      error: function () {
        $('#modalContentUgovor').html('<p class="text-danger">Greška prilikom učitavanja forme.</p>');
      }
    });
  });


  $('#createRevalorizacija').click(function () {
  $('#revalorizacijaModal').modal('show');

});

// Form submission
$(document).on('click', '#submitRevalorizacija', function () {
  const iznos = $('#iznos').val();
  const datum = $('#datum').val();
  const napomena = $('#napomena').val();

  if (!iznos || !datum) {
    alert("Molimo unesite iznos i datum.");
    return;
  }

  $.ajax({
    url: 'revalorizacija/create.php',
    method: 'POST',
    data: {
      create: 'revalorizacija',
      iznos: iznos,
      datum: datum,
      napomena: napomena
    },
    success: function (res) {
      alert(res);
      try {
        const result = JSON.parse(res);

        if (result.success && result.insert_id) {
          $('#revalorizacijaModal').data('id', result.insert_id);
          $('#revalorizacijaModal').modal('hide');
          $('#revalorizacijaConfirmModal').modal('show');
        } else {
          alert('Neuspešno kreiranje revalorizacije.');
        }
      } catch (e) {
        alert('Greška pri obradi odgovora.');
        console.error(e, res.message);
      }
    },
    error: function () {
      alert('Greška prilikom slanja podataka.');
    }
  });
});

// Confirmation: Apply revalorizacija
$(document).on('click', '#applyRevalorizacija', function () {
  const id_revalorizacija = $('#revalorizacijaModal').data('id');
  if (!id_revalorizacija) {
    alert("ID revalorizacije nije pronađen.");
    return;
  }
  alert("ID revalorizacije: " + id_revalorizacija); // Debugging line

  $.ajax({
    url: 'revalorizacija/apply_revalorizacija.php',
    method: 'POST',
    data: { id_revalorizacija: id_revalorizacija },
    success: function (response) {
      const result = JSON.parse(response);
      if (result.success) {
        alert('Revalorizacija je uspešno primenjena.');
        $('#revalorizacijaConfirmModal').modal('hide');
        $('#revalorizacijaModal').modal('hide');
      } else {
        alert('Greška: ' + (result.message || 'Nepoznata greška.'));
      }
    },
    error: function () {
      alert('Greška prilikom slanja revalorizacije.');
    }
  });
});

// Cancel confirmation
$(document).on('click', '#cancelRevalorizacija', function () {
  $('#revalorizacijaConfirmModal').modal('hide');
});


// Make refreshRevalorizacija globally accessible
window.refreshRevalorizacija = function (idRevalorizacija) {
    if (!idRevalorizacija) {
        alert('Invalid revalorizacija ID.');
        return;
    }

    $.ajax({
        url: 'revalorizacija/apply_revalorizacija.php',
        method: 'POST',
        data: { id_revalorizacija: idRevalorizacija },
        success: function (response) {
            const result = JSON.parse(response);
            if (result.success) {
                alert('Revalorizacija successfully applied for ID: ' + idRevalorizacija);
            } else {
                alert('Error: ' + (result.error || 'Failed to apply revalorizacija.'));
            }
        },
        error: function () {
            alert('An error occurred while applying revalorizacija.');
        }
    });
};

  // Dugmad za prikaz ugovora i revalorizacija
  $('#showUgovori').on('click', function () {
    $('#naslov').text('Ugovori');
    TableUgovor();
  });

  $('#showRevalorizacije').on('click', function () {
    $('#naslov').text('Revalorizacije');
    TableRevalorizacija();
  });

  $('#showStavke').on('click', function () {
    $('#naslov').text('Stavke');
    // Load default stavke table or handle as needed
    TableStavke();
  });

  $('#showStavke').on('click', function () {
    $('#naslov').text('Stavke za ugovor ');
    TableStavke();

});

});
  </script>

  <!-- Add custom CSS -->
<style>
  .btn-dark {
    background-color: orange !important;
    border-color: orange !important;
    color: white !important;
  }
  .btn-dark:hover {
    background-color: darkorange !important;
    border-color: darkorange !important;
  }
</style>
</body>
</html>
