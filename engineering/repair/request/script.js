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

  // Event listener for row clicks
  $('#historyTable tbody').on('click', 'tr', function () {
    // Get data from the clicked row
    const repairRequestId = $(this).data('repair-request-id');
    const dateRequested = $(this).data('date-requested');
    const machineName = $(this).data('machine-name');
    const department = $(this).data('department');
    const urgency = $(this).data('urgency');
    const status = $(this).data('status');
    const requestedBy = $(this).data('requested-by');
    const details = $(this).data('details');
    const warrantyStatus = $(this).data('warranty-status');

    // Populate modal fields
    $('#repairRequestIdLabel').text(repairRequestId);
    $('#modalDateRequested').text(dateRequested);
    $('#modalMachineName').text(machineName);
    $('#modalDepartment').text(department);
    $('#modalUrgency').text(urgency);
    $('#modalStatus').text(status);
    $('#modalRequestedBy').text(requestedBy);
    $('#modalDetails').text(details);

    // Conditionally display the Request Material button based on status
    if (status === 'Not Started') {
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
    const urgency = $(this).data('urgency');
    const details = $(this).data('details');

    // Populate modal fields
    $('#modalMaterialRequestId').text(materialRequestId);
    $('#materialRequestDate').text(dateRequested);
    $('#materialRequestStatus').text(status);
    $('#materialRequestMachine').text(machineName);
    $('#materialRequestDepartment').text(department);
    $('#materialRequestUrgency').text(urgency);
    $('#materialRequestDetails').text(details);

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
                                <th>Current Quantity</th>
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
                          <td style="padding-left: 120px;">${item.item_quantity}</td>
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

  // Offcanvas modal button event for "Request Material"
  $('#requestMaterialBtn').on('click', function () {
    const offcanvasModal = bootstrap.Offcanvas.getInstance(document.getElementById('repairRequestModal'));
    if (offcanvasModal) offcanvasModal.hide();

    const repairRequestId = $('#repairRequestIdLabel').text();
    showInfoModal('Request Material', `You have selected repair request id: ${repairRequestId}. Please proceed to request material.`);
    $('#modalRepairRequestId').text(repairRequestId);

    showContent("requestMaterials");
  });

  // Cart functionality
  let cart = []; // Initialize the cart array

  document.querySelectorAll('.add-to-cart-btn').forEach(button => {
      button.addEventListener('click', function() {
          const row = this.closest('tr'); // Get the closest table row
          const itemId = row.dataset.itemCode; // Retrieve item ID from data attribute
          const itemName = row.dataset.itemName; // Retrieve item name from data attribute
          const itemQuantity = parseInt(row.dataset.itemQuantity); // Retrieve item quantity from data attribute
  
          // Toggle the 'selected' class on the row
          const isSelected = row.classList.toggle('selected');
  
          // Get the checkbox within the current row
          const checkbox = row.querySelector('.row-checkbox');
  
          // Check or uncheck the checkbox based on the row's selection state
          checkbox.checked = isSelected;
  
          if (isSelected) {
              // If the row was not previously selected, add the item to the cart
              const existingItem = cart.find(item => item.id === itemId);
              if (existingItem) {
                  // If the item already exists in the cart, increment the quantity
                  existingItem.quantity += 1; // Change this if you want to control the quantity further
              } else {
                  // Add the item to the cart if it's not already there
                  cart.push({
                      id: itemId,
                      name: itemName,
                      quantity: 1, // Default quantity
                      available: itemQuantity
                  });
              }
          } else {
              // If the row is unselected, remove the item from the cart
              cart = cart.filter(item => item.id !== itemId); // Remove the item from the cart
          }
  
          console.log(cart); // Log the cart for debugging purposes
      });
  });  

  function openCartModal() {
    const cartModalBody = document.getElementById('cartModalBody');
    cartModalBody.innerHTML = ''; // Clear previous cart items

    cart.forEach(item => {
        const row = document.createElement('tr');

        // Create cells for item code, item name, available quantity, requested quantity, and note
        const itemCodeCell = document.createElement('td');
        itemCodeCell.textContent = item.id; // Item code
        row.appendChild(itemCodeCell);

        const itemNameCell = document.createElement('td');
        itemNameCell.textContent = item.name; // Item name
        row.appendChild(itemNameCell);

        const availableQtyCell = document.createElement('td');
        availableQtyCell.textContent = item.available; // Available quantity from cart
        row.appendChild(availableQtyCell);

        const requestedQtyCell = document.createElement('td');
        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.value = item.quantity || 1; // Default to 1 if quantity is not set
        qtyInput.min = 1; // Minimum quantity
        qtyInput.max = item.available; // Max quantity based on availability
        qtyInput.classList.add('form-control');
        qtyInput.setAttribute('data-item-id', item.id); // Set data-item-id attribute
        requestedQtyCell.appendChild(qtyInput);
        row.appendChild(requestedQtyCell);

        // Create a note cell for out of stock items
        const noteCell = document.createElement('td');
        if (item.available === 0) {
            noteCell.innerHTML = '<span class="text-danger">Not guaranteed, and ordering may take a while</span>';
        } else {
            noteCell.textContent = ''; // No note for items in stock
        }
        row.appendChild(noteCell);

        // Append the row to the cart modal body
        cartModalBody.appendChild(row);
    });

    // Show the modal
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
    cartModal.show();
  }

// Attach the function to the complete button
document.getElementById('completeRequestBtn').addEventListener('click', openCartModal);

document.getElementById('completeOrderBtn').addEventListener('click', function() {
  // Get the repair request ID from the modal
  const repairRequestId = document.getElementById('modalRepairRequestId').textContent;
  const requestedById = document.getElementById('employee-id-text').textContent;

  // Prepare the order data
  const orderItems = cart.map(item => {
      const qtyInput = document.querySelector(`input[data-item-id="${item.id}"]`); // Use data-item-id
      if (qtyInput) {
          return {
              id: item.id,
              name: item.name,
              quantity: parseInt(qtyInput.value), // Get the quantity input
              available: item.available
          };
      } else {
          console.error(`Quantity input not found for item: ${item.id}`);
          return null; // Return null if input is not found
      }
  }).filter(item => item !== null); // Filter out any null values

  // Check if orderItems is empty
  if (orderItems.length === 0) {
      showInfoModal('Error', 'Please select at least one item in the cart before completing the order.');
      return;
  }

  // Validate quantities
  for (const item of orderItems) {
    if (item.quantity < 1) {
        showInfoModal('Error', `The quantity for item "${item.name}" cannot be less than 1.`);
        return;
    }
    if (item.quantity > item.available) {
        showInfoModal('Error', `The quantity for item "${item.name}" exceeds available stock.`);
        return;
    }
  }
  // Send the order data to your server
  $.ajax({
      url: 'process_order.php', // Adjust this to your server-side processing script
      method: 'POST',
      dataType: 'json',
      contentType: 'application/json', // Specify content type
      data: JSON.stringify({
          items: orderItems,
          repair_request_id: repairRequestId, // Include the repair request ID
          requested_by: requestedById
      }),
      success: function(response) {
        if (response.success) {
            showInfoModal('Success', response.message); // Show success message
            cart = []; // Clear the cart
            $('#cartModal').modal('hide'); // Hide the modal
        } else {
            // Handle errors returned from the server
            showInfoModal('Error', response.message); // Show the error message
            console.error('Error:', response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', xhr.responseText); // Log full response
        showInfoModal('Error', 'An error occurred while processing your order. Please try again.');
      }    
  });
});

});