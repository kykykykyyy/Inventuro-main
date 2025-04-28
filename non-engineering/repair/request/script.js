document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
  });

  function showInfoModal(title, message) {
    document.querySelector('#infoModal .modal-title').textContent = title;
    document.querySelector('#infoModal .modal-body p').textContent = message;
    $('#infoModal').modal('show');
  }
  // Search functionality
  const searchBar = document.getElementById('search-bar');

  searchBar.addEventListener('input', function () {
    // Search functionality
    const searchBar = document.getElementById('search-bar');
    const historyTable = document.getElementById('historyTable');
    const historyRows = historyTable.querySelectorAll('tbody tr');

    const searchTerm = searchBar.value.toLowerCase();

    // Check if any of the cells contain the search term
    historyRows.forEach(row => {
        const dateCell = row.cells[1].textContent.toLowerCase(); // Date
        const requestNoCell = row.cells[2].textContent.toLowerCase(); // Repair Request No.
        const statusCell = row.cells[3].textContent.toLowerCase(); // Status
        const urgencyCell = row.cells[4].textContent.toLowerCase(); // Urgency

        // Check if any of the cells contain the search term
        if (dateCell.includes(searchTerm) || requestNoCell.includes(searchTerm) || statusCell.includes(searchTerm) || urgencyCell.includes(searchTerm)) {
            row.style.display = ''; // Show the row if it matches
        } else {
            row.style.display = 'none'; // Hide the row if it doesn't match
        }
    });
    
  });

  const mainContentLinks = {
    dashboard: document.getElementById("request-link"),
    announcement: document.getElementById("history-link")
  };

  const contentSections = {
    dashboard: document.getElementById("request-content"),
    announcement: document.getElementById("history-content")
  };

  function setActiveLink(linkId) {
    Object.values(mainContentLinks).forEach(link => link.classList.remove("active"));
    mainContentLinks[linkId].classList.add("active");
  }

  function showContent(sectionId) {
    Object.values(contentSections).forEach(section => section.classList.remove("active"));
    contentSections[sectionId].classList.add("active");
  }

  mainContentLinks.dashboard.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("dashboard");
    showContent("dashboard");
  });

  mainContentLinks.announcement.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("announcement");
    showContent("announcement");
  });

  // Load dashboard by default
  setActiveLink("dashboard");
  showContent("dashboard");
  
    const department = document.getElementById('department').value;

    // Function to fetch and populate machines based on department
    function fetchMachines(department, dropdownId) {
      fetch(`fetch_machines.php?department=${encodeURIComponent(department)}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(machines => {
          const machineDropdown = document.getElementById(dropdownId);
          machineDropdown.innerHTML = '<option value="" selected disabled>Select a machine</option>';

          machines.forEach(machine => {
            const option = document.createElement('option');
            option.value = machine.machine_id;
            option.textContent = `${machine.machine_name} - Serial No. ${machine.machine_serial_number}`;
            machineDropdown.appendChild(option);
          });
        })
        .catch(error => {
          console.error('Error fetching machines:', error);
        });
    }

    fetchMachines(department, "machine");

    function fetchAllMachines(department, dropdownId, selectedMachineId = null) {
      fetch(`fetch_all_machines.php?department=${encodeURIComponent(department)}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(machines => {
          const machineDropdown = document.getElementById(dropdownId);
          machineDropdown.innerHTML = '<option value="" selected disabled>Select a machine</option>';

          machines.forEach(machine => {
            const option = document.createElement('option');
            option.value = machine.machine_id;
            option.textContent = `${machine.machine_name} - Serial No. ${machine.machine_serial_number}`;
            machineDropdown.appendChild(option);
          });

          // Set the selected machine ID if provided
          if (selectedMachineId) {
            machineDropdown.value = selectedMachineId;
          }
        })
        .catch(error => {
          console.error('Error fetching machines:', error);
        });
    }

    const repairForm = document.getElementById('repairRequestForm');

    repairForm.addEventListener('submit', function (event) {

      event.preventDefault(); // Prevent the form from submitting traditionally

      // Capture form data
      const formData = new FormData(repairForm);

      // Capture form data
      const machineId = document.getElementById('machine').value;
      const remarks = document.getElementById('remarks').value;
      const requestedBy = document.getElementById('employee-id-text').textContent; // Get the employee ID from PHP session

      // Send the data to a PHP script for processing
      formData.append('machine_id', machineId);
      formData.append('details', remarks);
      formData.append('requested_by', requestedBy);

      // Log form data to the console
      console.log("Form data to be sent:");
      console.log("Machine ID:", document.getElementById('machine').value);
      console.log("Remarks:", document.getElementById('remarks').value);
      console.log("Requested By:", document.getElementById('employee-id-text').textContent); // logged from PHP session

      // Send the data to a PHP script for processing
      fetch('submit_repair_request.php', {
        method: 'POST',
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
          showInfoModal('Success', 'The repair request has been successfully submitted.');
          repairForm.reset(); // Reset the form after submission

          } else {
            showInfoModal('Error', 'There was an error submitting the repair request.');
          }
      })
      .catch(error => {
          console.error('Error:', error);
          showInfoModal('Error', 'There was an error submitting the repair request.');
      });
  });

  $(document).ready(function() {
    $('#historyTable').DataTable({
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
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });
  });

  // Handle "Select All" checkbox
  $('#selectAll').click(function() {
    var rows = $('#historyTable').DataTable().rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  // Open modal and populate it with repair request data when a row is clicked
  $('#historyTable tbody').on('click', 'tr', function () {
    const repairRequestId = $(this).data('repair-request-id'); // Capture repair request ID
    const dateRequested = $(this).data('date-requested');
    const machineId = $(this).data('machine-id');
    const status = $(this).data('status');
    const urgency = $(this).data('urgency');
    const requestedBy = $(this).data('requested-by');
    const details = $(this).data('details');
    const department = $('#department').val(); // This should be the relevant department for the request

    // Populate the modal fields
    $('#repairRequestIdLabel').text(repairRequestId);
    $('#modalDateRequested').text(dateRequested);
    $('#modalStatus').text(status);
    $('#modalUrgency').text(urgency);
    $('#modalRequestedBy').text(requestedBy);
    $('#modalDetails').val(details);

    // Populate the machine dropdown in the modal and set the selected machine
    if (department) {
      fetchAllMachines(department, 'machineName', machineId);
    }

    // Enable or disable fields based on the status
    const isEditable = status === 'Not Started';
    $('#modalDetails').prop('disabled', !isEditable);
    $('#saveRepairRequestBtn').toggle(isEditable);
    $('#deleteRepairRequestBtn').toggle(isEditable);

    // Show the offcanvas modal
    const modal = new bootstrap.Offcanvas(document.getElementById('repairRequestModal'));
    modal.show();
  });
  
  let actionType; // To store if the action is 'save' or 'delete'

  // Delete repair request functionality
  $('#deleteRepairRequestBtn').on('click', function() {
    actionType = 'delete';
    $('#confirmationMessage').text('Are you sure you want to delete this repair request?');
    $('#confirmationModal').modal('show'); // Show the confirmation modal after offcanvas is hidden
  });

  $('#saveRepairRequestBtn').on('click', function() {
    actionType = 'save';
    $('#confirmationMessage').text('Are you sure you want to save changes to this repair request?');
    $('#confirmationModal').modal('show'); // Show the confirmation modal after offcanvas is hidden
  });

// Handle confirmation modal 'Confirm' button click
$('#confirmActionBtn').on('click', function () {
  $('#confirmationModal').modal('hide'); // Close the confirmation modal

  if (actionType === 'delete') {
    // Perform delete action
    const repairRequestId = $('#repairRequestIdLabel').text();
    $.ajax({
      url: 'delete_repair_request.php',
      method: 'POST',
      data: { repair_request_id: repairRequestId },
      success: function(response) {
        window.location.reload();
        
      },
      error: function(xhr, status, error) {
        showInfoModal('Error', 'The repair request has not been deleted.');
      }
    });

  } else if (actionType === 'save') {
    // Perform save action
    const repairRequestId = $('#repairRequestIdLabel').text();
    const details = $('#modalDetails').val();

    if (!details) {
      showInfoModal('Error', 'Please enter problem details.');
      return;
    }

    $.ajax({
        url: 'update_repair_request.php', // Adjust to your actual PHP script path
        type: 'POST',
        data: {
            repair_request_id: repairRequestId,
            details: details
        },
        success: function(response) {
          window.location.reload();

        },
        error: function(xhr) {
          showInfoModal('Error', 'The repair request has not been saved.');
        }
    });
  }
});


});