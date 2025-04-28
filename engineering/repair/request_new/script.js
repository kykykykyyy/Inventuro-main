document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
  });

  var repairRequestModal = new bootstrap.Offcanvas(document.getElementById('repairRequestModal'));
  // var completedRepairModal = new bootstrap.Offcanvas(document.getElementById(''));
  function showInfoModal(title, message) {
    document.querySelector('#infoModal .modal-title').textContent = title;
    document.querySelector('#infoModal .modal-body p').textContent = message;
    
    // Show the modal
    const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
    infoModal.show(); // Show the modal

    if (title === 'Request Details') {
      const requestDetails = document.getElementById('request-details');
      requestDetails.style.display = 'block';
    }
  }

  function showConfirmationModal(title, body, confirmCallback) {
    // Set the modal title and body text dynamically
    $('#confirmationModalLabel').text(title);
    $('#confirmationModalBody').text(body);
    
    // Set up the "Yes" button to call the callback function when clicked
    $('#confirmButton').off('click').on('click', function () {
      // Call the provided callback function (if any)
      if (typeof confirmCallback === 'function') {
        confirmCallback();
      }
      
      // Close the modal after confirmation
      $('#confirmationModal').modal('hide');
    });
    
    // Show the modal
    $('#confirmationModal').modal('show');
  }

  $(document).ready(function () {
    // Function to remove the placeholder row if it exists
    function removePlaceholderRow(tableId) {
        const noDataRow = $(`${tableId} tbody tr[data-dt-no-data]`);
        if (noDataRow.length) {
            noDataRow.remove();
        }
    }

    // Remove placeholder rows for the tables
    removePlaceholderRow('#unclaimedRepairs');
    removePlaceholderRow('#claimedRepairs');
    removePlaceholderRow('#warrantyRepairs');

    // Initialize DataTables
    $('#unclaimedRepairs').DataTable({
        dom: '<"row"<"col-md-6"f><"col-md-6 text-end"B>>tip',
        buttons: [
            {
                extend: 'copy',
                text: 'Copy'
            },
            {
                extend: 'collection',
                text: 'Download',
                className: 'btn',
                buttons: [
                    { extend: 'csv', text: 'CSV' },
                    { extend: 'excel', text: 'Excel' },
                    { extend: 'pdf', text: 'PDF' }
                ]
            },
            {
                extend: 'print',
                text: 'Print'
            }
        ],
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        language: {
            emptyTable: "No unclaimed repairs available",
            search: "Search: "
        }
    });

    $('#claimedRepairs').DataTable({
        dom: '<"row"<"col-md-6"f><"col-md-6 text-end"B>>tip',
        buttons: [
            {
                extend: 'copy',
                text: 'Copy'
            },
            {
                extend: 'collection',
                text: 'Download',
                className: 'btn',
                buttons: [
                    { extend: 'csv', text: 'CSV' },
                    { extend: 'excel', text: 'Excel' },
                    { extend: 'pdf', text: 'PDF' }
                ]
            },
            {
                extend: 'print',
                text: 'Print'
            }
        ],
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        language: {
            emptyTable: "No claimed repairs available", // Updated for completedRepairs
            search: "Search: "
        }
    });

    $('#warrantyRepairs').DataTable({
      dom: '<"row"<"col-md-6"f><"col-md-6 text-end"B>>tip',
      buttons: [
          {
              extend: 'copy',
              text: 'Copy'
          },
          {
              extend: 'collection',
              text: 'Download',
              className: 'btn',
              buttons: [
                  { extend: 'csv', text: 'CSV' },
                  { extend: 'excel', text: 'Excel' },
                  { extend: 'pdf', text: 'PDF' }
              ]
          },
          {
              extend: 'print',
              text: 'Print'
          }
      ],
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      language: {
          emptyTable: "No warranty repairs available", // Updated for completedRepairs
          search: "Search: "
      }
  });
  });

  $('#selectAllClaimed').on('click', function () {
    var rows = $('#claimedRepairs').rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  $('#unclaimedRepairs tbody').on('click', '#viewRepairRequest', function () {
    // Get the parent row of the clicked button
    const row = $(this).closest('tr');
  
    // Get data from the row
    const repairNo = row.data('repair-request-id');
    const dateRequested = row.data('date-requested');
    const machineName = row.data('machine-name');
    const machineSerialNumber = row.data('machine-serial-number');
    const department = row.data('department');
    const urgency = row.data('urgency');
    const status = row.data('status');
    const repairDate = row.data('repair-date');
    const requestedBy = row.data('requested-by');
    const requestedByName = row.data('requested-by-name');
    const details = row.data('details');

    if(repairDate){
      $('#proceedButton').hide();
      $('#unclaimButton').hide();
    }
    else {
      $('#proceedButton').show();
      $('#unclaimButton').show();
    }

    console.log("Repair Request ID: ", repairNo);
  
    // Populate modal fields
    $('#repairRequestIdLabel').text(repairNo);
    $('#modalDateRequested').text(dateRequested);
    $('#modalMachineName').text(machineName);
    $('#modalMachineSerialNumber').text(machineSerialNumber);
    $('#modalDepartment').text(department);
    $('#modalUrgency').text(urgency);
    $('#modalStatus').text(status);
    $('#modalRequestedBy').text(requestedByName + " (" + requestedBy + ")");
    $('#modalDetails').text(details);
  
    // Show the offcanvas modal
    repairRequestModal.show();
    
  });
  
  $('#claimedRepairs tbody').on('click', '#completeRepairButton', function () {
    // Get the parent row of the clicked button
    const row = $(this).closest('tr');
  
    // Get data from the row
    const repairNo = row.data('repair-id');
    const repairedBy = row.data('repaired-by');

    showConfirmationModal(
      'Completion of the Repair',  // Title of the modal
      'Are you sure you want to claim this repair as completed?',  // Body of the modal
      function () { // The callback function for the "Yes" button
        // Send an AJAX request to complete the repair
        $.ajax({
          url: 'complete_repair.php', // PHP script to handle the update
          type: 'POST',
          data: {
            repair_no: repairNo,
            repaired_by: repairedBy
          },
          success: function (response) {
            const result = JSON.parse(response);
            if (result.success) {
              showInfoModal('Success', 'Repair is marked as complete!');
              row.remove(); // Remove the row from the table
            } else {
              alert('Error: ' + result.message);
            }
          },
          error: function () {
            showInfoModal('Error', 'An error occurred while processing the request.');
          }
        });
      }
    );
  });

  $('#claimedRepairs tbody').on('click', '#unclaimRepairButton', function () {
    // Get the parent row of the clicked button
    const row = $(this).closest('tr');
  
    // Get data from the row
    const repairNo = row.data('repair-id');
    const repairedBy = row.data('repaired-by');

    // Unclaim the repair request
  });

  $('#completedRepairs tbody').on('click', '#viewCompletedRepair', function () {
    // Get the parent row of the clicked button
    const row = $(this).closest('tr');
  
    // Get data from the row
    const repairNo = row.data('repair-id');
    const dateRequested = row.data('date-requested');
    const machineName = row.data('machine-name');
    const machineSerialNumber = row.data('machine-serial-number');
    const department = row.data('department');
    const urgency = row.data('urgency');
    const status = row.data('status');
    const requestedBy = row.data('requested-by');
    const requestedByName = row.data('requested-by-name');
    const details = row.data('details');
  
    // Populate modal fields
    $('#repairRequestIdLabel').text(repairNo);
    $('#modalDateRequested').text(dateRequested);
    $('#modalMachineName').text(machineName);
    $('#modalMachineSerialNumber').text(machineSerialNumber);
    $('#modalDepartment').text(department);
    $('#modalUrgency').text(urgency);
    $('#modalStatus').text(status);
    $('#modalRequestedBy').text(requestedByName + " (" + requestedBy + ")");
    $('#modalDetails').text(details);
  
    // Show the offcanvas modal
    repairRequestModal.show();
  });

  // Main navigation links
  const mainContentLinks = {
    unclaimed: document.getElementById("unclaimed-link"),
    claimed: document.getElementById("claimed-link"),
    warranty: document.getElementById("under-warranty-link")
  };

  // Content sections
  const contentSections = {
    unclaimed: document.getElementById("unclaimed-content"),
    claimed: document.getElementById("claimed-content"),
    warranty: document.getElementById("under-warranty-content")
  };

  function setActiveLink(linkId) {
    Object.values(mainContentLinks).forEach(link => {
      if (link) link.classList.remove("active");
    });
    if (mainContentLinks[linkId]) {
      mainContentLinks[linkId].classList.add("active");
    }
  }

  function showContent(sectionId) {
    Object.values(contentSections).forEach(section => {
      if (section) section.classList.remove("active");
    });
    if (contentSections[sectionId]) {
      contentSections[sectionId].classList.add("active");
    }
  }

  // Event listeners for main content links
  mainContentLinks.unclaimed.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("unclaimed");
    showContent("unclaimed");
  });

  mainContentLinks.claimed.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("claimed");
    showContent("claimed");
  });

  mainContentLinks.warranty.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("warranty");
    showContent("warranty");
  });

  // Load request by default
  setActiveLink("unclaimed");
  showContent("unclaimed");

  document.getElementById('claimButton').addEventListener('click', function () {
    const repairRequestId = document.getElementById('repairRequestIdLabel').textContent;

      fetch('claim_repair_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            repair_request_id: repairRequestId, // Ensure this value is valid
        }),
    })
    .then((response) => {
        if (!response.ok) {
            return response.json().then((error) => {
                console.error('Error Response:', error);
                throw new Error(error.message || 'Failed to claim repair.');
            });
        }
        return response.json();
    })
    .then((data) => {
        console.log('Success:', data);
        showInfoModal('Success', data.message);
        location.reload();
    })
    .catch((error) => {
        console.error('Fetch Error:', error.message);
        showInfoModal('Error', error.message);
    });

  });

});