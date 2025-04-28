document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
  });

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

  const dataTable = $('#historyTable').DataTable({
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
      { orderable: false, targets: 0 } // Disable ordering on the first column (checkbox)
    ],
    language: {
      search: "Search: " // Customizing the search label
    }
  });

  // Handle "Select All" checkbox
  $('#selectAll').on('click', function () {
    var rows = dataTable.rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  $('#itemTable').DataTable({
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
      { orderable: false, targets: 0 } // Disable ordering on the first column (checkbox)
    ],
    language: {
      search: "Search: " // Customizing the search label
    }
  });

  // Handle "Select All" checkbox
  $('#selectAllCart').on('click', function () {
    var rows = dataTable.rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  $('#requestMaterialBtn').on('click', function () {
    const maintenanceId = $('#repairRequestIdLabel').text().trim(); // Get maintenance_id from button data attribute
    console.log(maintenanceId);
    
    fetch('claim_repair_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            maintenance_id: maintenanceId // Send the maintenance_id to the server
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Handle the response
        if (data.message === 'Success') {
            showInfoModal('Successful', 'Maintenance task claimed successfully!');
        } else {
          showInfoModal('Successful', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
  });

  // Event listener for row clicks
  $('#historyTable tbody').on('click', '#view-details-btn', function () {

    // Get the parent row of the clicked button
    const row = $(this).closest('tr');

    // Get data from the clicked row
    const maintenanceId = row.data('maintenance-id');
    const dateRequested = row.data('date-requested');
    const machineName = row.data('machine-name');
    const department = row.data('department');
    const status = row.data('status');
    const warrantyStatus = row.data('warranty-status');

    // Populate modal fields
    $('#repairRequestIdLabel').text(maintenanceId);
    $('#modalDateRequested').text(dateRequested);
    $('#modalMachineName').text(machineName);
    $('#modalDepartment').text(department);
    $('#modalStatus').text(status);

    // Conditionally display the Request Material button based on status
    if (status === 'Scheduled') {
      $('#requestMaterialBtn').show();
    } else {
      $('#requestMaterialBtn').hide();
    }

    if(warrantyStatus === 'Active') {
      const warrantyElement = document.getElementById('modalWarranty');

      warrantyElement.textContent = warrantyStatus; // Set the warranty status

      // Make the <p> element visible
      const warrantyStatusElement = warrantyElement.parentElement; // Get the parent <p> element
      warrantyStatusElement.style.display = 'block'; // Change display style to block
      $('#requestMaterialBtn').hide();
    } else {
      const warrantyElement = document.getElementById('modalWarranty');
      const warrantyStatusElement = warrantyElement.parentElement;
      warrantyStatusElement.style.display = 'none';
    }

    // Show the offcanvas modal
    const modal = new bootstrap.Offcanvas(document.getElementById('repairRequestModal'));
    modal.show();
  });

  $('#materialRequestTable').DataTable({
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
      { orderable: false, targets: 0 } // Disable ordering on the first column (checkbox)
    ],
    language: {
      search: "Search: " // Customizing the search label
    }
  });

  // Event listener for row clicks
  $('#materialRequestTable tbody').on('click', 'tr', function () {
    const materialRequestId = $(this).data('material-request-id');
    const dateRequested = $(this).data('date-requested');
    const status = $(this).data('status');
    const machineName = $(this).data('machine-name');
    const department = $(this).data('department');

    // Populate modal fields
    $('#modalMaterialRequestId').text(materialRequestId);
    $('#materialRequestDate').text(dateRequested);
    $('#materialRequestStatus').text(status);
    $('#materialRequestMachine').text(machineName);
    $('#materialRequestDepartment').text(department);

    // Fetch items associated with the material request
    fetchMaterialRequestItems(materialRequestId);

    // Conditionally display the Save and Delete button, and disable/enable inputs based on status
    if(status === 'Not Started') {
      $('#deleteMaterialRequestBtn').show();
      $('#saveMaterialRequestBtn').show();
    } else {
      $('#deleteMaterialRequestBtn').hide();
      $('#saveMaterialRequestBtn').hide();
    }
    // Show the offcanvas modal
    const modal = new bootstrap.Offcanvas(document.getElementById('materialRequestModal'));
    modal.show();
  });

  // Function to fetch material request items
  function fetchMaterialRequestItems(materialRequestId) {
    $.ajax({
        url: 'get_material_request_items.php', // Your server-side script to get items
        method: 'POST',
        dataType: 'json',
        data: {
            material_request_id: materialRequestId // Send the ID to the server
        },
        success: function (response) {
            if (response.success) {
                // Clear existing items
                $('#modalItemList').empty();
                
                // Create the table header
                const tableHeader = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity Needed</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                $('#modalItemList').append(tableHeader);
                
                // Populate the modal with material request items
                response.items.forEach(item => {
                  // Determine if the input should be enabled or disabled based on the item's status
                  const isDisabled = item.status !== 'Not Started';
              
                  $('#modalItemList tbody').append(`
                      <tr>
                          <td class="text-start" style="padding-left: 30px;">${item.item_name}</td>
                          <td>
                              <input type="number" 
                                     data-item-id="${item.item_id}" 
                                     value="${item.quantity}" 
                                     min="1" 
                                     class="form-control quantity-input" 
                                     style="margin-left: 60px; width: 100px;" 
                                     ${isDisabled ? 'disabled' : ''}>
                          </td>
                      </tr>
                  `);
                });

                // Close the table
                $('#modalItemList').append(`</tbody></table>`);
            } else {
                // Handle case where no items are found
                $('#modalItemList').html('<p>No items found for this request.</p>');
            }
        },
        error: function () {
            $('#modalItemList').html('<p>Error fetching items. Please try again.</p>');
        }
    });
  }

  $('#historyTable tbody').on('click', '#done-btn', function () {
    const maintenanceId = $('#repairRequestIdLabel').text().trim() ?? ''; // Get maintenance_id from button data attribute
    const handledBy = "202830-ENG";

    console.log(maintenanceId);

    // Send maintenance_id and handled_by to server
    fetch('complete_repair_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            maintenance_id: maintenanceId,  // Send the maintenance_id to the server
            handled_by: handledBy,          // Send the handled_by value to the server
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Handle the response
        if (data.message === 'Success') {
            showInfoModal('Successful', 'Maintenance task completed successfully!');
        } else {
            showInfoModal('Error', data.message);
        }
    })
    .catch(error => {
        console.log('Error:', error);
        showInfoModal('Error', 'An error occurred while completing the task.');
    });
});

// Event listener for save button
$('#saveMaterialRequestBtn').click(function() {
  const updatedItems = [];
  let isValid = true;

  // Gather the updated quantities
  $('#modalItemList .quantity-input').each(function() {
      const itemId = $(this).data('item-id');
      const newQuantity = parseInt($(this).val());
      
      // Assuming you have a way to get the current quantity for validation
      const currentQuantity = parseInt($(this).closest('tr').find('td:last-child').text()); // Get the quantity from the last column

      // Validate quantity
      if (newQuantity < 1) {
          isValid = false;
          showInfoModal('Error', 'Quantity cannot be less than 1.');
          return false; // Break the loop
      }
      if (newQuantity > currentQuantity) {
          isValid = false;
          showInfoModal('Error', `Quantity cannot exceed the current quantity of ${currentQuantity}.`);
          return false; // Break the loop
      }

      // If valid, push to updatedItems array
      updatedItems.push({ item_id: itemId, quantity: newQuantity });
  });

  if (!isValid) {
      return; // If not valid, stop execution
  }

  // Make AJAX call to update material_request_items
  $.ajax({
      url: 'update_material_request_items.php', // Your server-side script
      method: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify({
          items: updatedItems
      }),
      success: function(response) {
          if (response.success) {
              showInfoModal('Success', 'Material request updated successfully!');
          } else {
              showInfoModal('Error', response.message);
          }
      },
      error: function(xhr, status, error) {
          showInfoModal('Error', 'An error occurred while updating material request items.');
          console.error('Error:', error);
      }
  });
});


$('#deleteMaterialRequestBtn').click(function() {
  const materialRequestId = $('#modalMaterialRequestId').text(); // Get the Material Request ID from the modal

  // Confirm deletion
  if (confirm('Are you sure you want to delete this material request? This action cannot be undone.')) {
      // Send AJAX request to delete the material request
      $.ajax({
          url: 'delete_material_request.php', // Your server-side script
          method: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify({
              material_request_id: materialRequestId
          }),
          success: function(response) {
              if (response.success) {
                  showInfoModal('Success', 'Material request deleted successfully!');
                  // Optionally, refresh the table or close the modal
              } else {
                  showInfoModal('Error', response.message);
              }
          },
          error: function(xhr, status, error) {
              showInfoModal('Error', 'An error occurred while deleting the material request.');
              console.error('Error:', error);
          }
      });
  }
});
  // Custom Search Functionality for Table and Timeline
  const searchBar = document.getElementById('search-bar');
  searchBar.addEventListener('input', function () {
    const searchTerm = searchBar.value.toLowerCase();

    // Filter DataTable
    dataTable.search(searchTerm).draw();

    // Filter Timeline Items
    const announcements = document.querySelectorAll('.timeline-item');
    announcements.forEach(announcement => {
      const date = announcement.querySelector('.timeline-date span').textContent.toLowerCase();
      const title = announcement.querySelector('h3').textContent.toLowerCase();
      const content = announcement.querySelector('p').textContent.toLowerCase();

      // Check if any part matches the search term
      if (title.includes(searchTerm) || content.includes(searchTerm) || date.includes(searchTerm)) {
        announcement.style.display = ''; // Show matching items
      } else {
        announcement.style.display = 'none'; // Hide non-matching items
      }
    });
  });

  // Main navigation links
  const mainContentLinks = {
    request: document.getElementById("request-link"),
    materials: document.getElementById("materials-link")
  };

  // Content sections
  const contentSections = {
    request: document.getElementById("request-content"),
    materials: document.getElementById("materials-content"),
    requestMaterials: document.getElementById("materials-request-content") // Ensure this exists in HTML
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
  mainContentLinks.request.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("request");
    showContent("request");
  });

  mainContentLinks.materials.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("materials");
    showContent("materials");
  });

  // Load request by default
  setActiveLink("request");
  showContent("request");

});