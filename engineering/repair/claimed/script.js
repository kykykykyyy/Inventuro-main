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
    removePlaceholderRow('#claimedRepairs');
    removePlaceholderRow('#completedRepairs');

    // Initialize DataTables
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
            emptyTable: "No claimed repairs available",
            search: "Search: "
        }
    });

    $('#completedRepairs').DataTable({
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
            emptyTable: "No completed repairs available", // Updated for completedRepairs
            search: "Search: "
        }
    });
  });

  $('#selectAllClaimed').on('click', function () {
    var rows = $('#claimedRepairs').rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  $('#claimedRepairs tbody').on('click', '#viewRepairRequest', function () {
    // Get the parent row of the clicked button
    const row = $(this).closest('tr');
  
    // Get data from the row
    const repairNo = row.data('repair-id');
    const machineId = row.data('machine-id');
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

    // Toggle visibility based on whether repair date is set
    if (repairDate || status === 'Done') {
      $('#proceedButton').hide();
      $('#unclaimButton').hide();
      $('#diagnosisDetails').show();

      // Fetch diagnosis details
      console.log(repairNo);
      console.log(machineId);

      
      // // Make an AJAX request to fetch additional information (diagnosis, parts, materials)
      //   $.ajax({
      //     url: 'getRepairDetails.php',  // Your PHP endpoint that will return the data
      //     method: 'POST',
      //     dataType: 'json',
      //     data: {
      //         repair_no: repairNo,
      //         machine_id: machineId// Pass the repair number to the server
      //     },
      //     success: function(response) {
      //         if (response.status === 'success') {
      //             // Populate diagnosis details in the modal
      //             $('#modalDiagnosisDate').text(response.data.diagnosis_date);
      //             $('#modalDiagnosisBy').text(response.data.diagnosis_by);

      //             // Populate machine parts list
      //             const partsList = response.data.parts.map(part => `
      //                 <p><strong>${part.item_name} (Qty: ${part.quantity})</strong></p>
      //             `).join('');
      //             $('#machinePartsList').html(partsList);

      //             // Populate material request list
      //             const materialsList = response.data.materials.map(material => `
      //                 <p><strong>${material.item_name} (Qty: ${material.quantity})</strong></p>
      //             `).join('');
      //             console.log(materialsList);
      //             $('#materialRequestList').html(materialsList);
      //         } else {
      //             // Handle error or no data
      //             console.error('Failed to fetch repair details');
      //         }
      //     },
      //     error: function(xhr, status, error) {
      //         console.error('Error fetching repair details:', error);
      //     }
      // });
    }
    else {
      $('#proceedButton').show();
      $('#unclaimButton').show();
      $('#diagnosisDetails').hide();
    }
  
    // Populate modal fields with the repair request data
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
    const repairRequestNo = row.data('repair-request-id');
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
            repair_request_no: repairRequestNo,
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
    const repairDate = row.data('repair-date');
    const requestedBy = row.data('requested-by');
    const requestedByName = row.data('requested-by-name');
    const details = row.data('details');

    if(repairDate || status === 'Done') {
      $('#proceedButton').hide();
      $('#unclaimButton').hide();
    }
    else {
      $('#proceedButton').show();
      $('#unclaimButton').show();
    }
  
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
    active: document.getElementById("active-link"),
    inactive: document.getElementById("inactive-link")
  };

  // Content sections
  const contentSections = {
    active: document.getElementById("active-repair-content"),
    diagnosis: document.getElementById("diagnosis-repair-content"),
    inactive: document.getElementById("inactive-repair-content")
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
  mainContentLinks.active.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("active");
    showContent("active");
  });

  mainContentLinks.inactive.addEventListener("click", function (e) {
    e.preventDefault();
    setActiveLink("inactive");
    showContent("inactive");
  });

  // Load request by default
  setActiveLink("active");
  showContent("active");

  $('#proceedButton').on('click', function () {
    repairRequestModal.hide();
    
    // Set the text of the target elements based on modal fields
    $('#repair_no').text($('#repairRequestIdLabel').text());
    $('#machine_name').text($('#modalMachineName').text());
    $('#machine_serial_number').text($('#modalMachineSerialNumber').text());
    
    // Get the updated machine serial number from the span after setting it
    const serialNumber = $('#machine_serial_number').text();

    // Fetch machine parts based on the updated serial number
    fetch("fetch_machine_parts.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `machine_serial_number=${encodeURIComponent(serialNumber)}`
    })
    .then(response => response.text())
    .then(data => {
        // Insert the data into the machineParts container
        $('#machineParts .row').html(data);
    })
    .catch(error => console.error('Error:', error));

    // Show the content section for "diagnosis"
    showContent("diagnosis");
  });

  // Use event delegation to ensure the click event works for dynamically added elements
  $(document).on('click', '.part-checkbox', function() {
    const partId = $(this).attr('id').split('-')[1]; // Extract machine_parts_id
    console.log('Part ID:', partId); // Log the part ID for debugging
    
    const dropdown = $('#issue-' + partId);
    const description = $('#description-' + partId);

    if ($(this).is(':checked')) {
        dropdown.prop('disabled', false);   // Enable dropdown
        description.prop('disabled', false); // Enable description

    } else {
        dropdown.prop('disabled', true);  // Disable dropdown
        description.prop('disabled', true); // Disable description
        
        // Reset the dropdown to "None" and clear the description text
        dropdown.val('none');   // Set dropdown to 'None'
        description.val('');    // Clear the description textarea
    }
  });
  
  let selectedParts = [];
  let machineSerialNumber = '';
  let machineName = '';
  let repairNo = '';
  let mainDescription = '';
  let selectedMaterials = [];
  
  // Function to collect machine parts data
  function collectMachinePartsData() {
      selectedParts = []; // Reset the array before collecting data
  
      // Loop through all the checkboxes with class 'part-checkbox'
      $('.part-checkbox').each(function() {
          // Check if the checkbox is checked
          if ($(this).is(':checked')) {
              const partId = $(this).attr('id').split('-')[1]; // Extract machine_parts_id
              const partName = $(this).attr('name');
              const reason = $('#issue-' + partId).val(); // Get selected issue from dropdown
              const description = $('#description-' + partId).val(); // Get description text
              selectedParts.push({
                  part_id: partId,
                  part_name: partName,
                  reason: reason,
                  description: description
              });
          }
      });
  
      // Collect the main description
      mainDescription = $('#mainDescription').val();
      machineSerialNumber = $('#machine_serial_number').text();
      machineName = $('#machine_name').text();
      repairNo = $('#repair_no').text();
  
      // Return the array if you need it elsewhere
      return selectedParts;
  }
  
  // Function to display the materials (items) in the table
  function displayMaterialRequestTable(materials) {
    const tableBody = $('#materialRequestTable tbody'); // Assuming you have a table with the id 'materialRequestTable'

    tableBody.empty();

    // Ensure the materials is an array (if it's not, we'll log an error)
    // Check if the materials is an array and if it's not empty
    if (Array.isArray(materials) && materials.length > 0) {
      // Loop through the materials data and create table rows
      materials.forEach(material => {
          // Set the max_count to 10 if it's null
          const maxCount = material.max_count === null ? 10 : material.max_count;

          // Check if the material is related to any of the selected machine parts
          const isSelected = selectedParts.some(part => part.part_id == material.machine_parts_id);

          // Create a row for each material
          const row = `
              <tr class="form-group">
                  <td>
                      <input type="checkbox" class="material-checkbox" data-item-id="${material.item_code}" ${isSelected ? 'checked' : ''}>
                  </td>
                  <td>${material.item_code}</td>
                  <td>${material.item_name}</td>
                  <td>
                      <input type="number" 
                          min="1" 
                          max="${maxCount}" 
                          class="form-control" 
                          id="quantity-${material.item_code}" 
                          placeholder="E.g. 1" 
                          ${isSelected ? '' : 'disabled'} 
                          ${isSelected ? 'required' : ''} 
                          value="${isSelected ? '1' : ''}">
                  </td>
              </tr>
          `;
          tableBody.append(row);
      });
    } else {
        // If materials array is empty or not an array, show a "No materials found" message
        const emptyRow = `
            <tr>
                <td colspan="4" class="text-center">No materials available.</td>
            </tr>
        `;
        tableBody.append(emptyRow);  // Add the empty row to the table body
    }

    // Add event listener to handle enabling/disabling quantity field based on checkbox
    $('#materialRequestTable').on('change', '.material-checkbox', function() {
        const checkbox = $(this);
        const quantityField = $('#quantity-' + checkbox.data('item-id'));

        // If checkbox is checked, enable the quantity field
        if (checkbox.is(':checked')) {
            quantityField.prop('disabled', false);
        } else {
            // If checkbox is unchecked, disable the quantity field
            quantityField.val('');
            quantityField.prop('disabled', true);
        }
    });
  }
  
  // After step 1: Collect machine parts and fetch materials
  $('#collectMachineParts').on('click', function() {

      const selectedPartsData = collectMachinePartsData();

      console.log('Selected parts:', selectedPartsData);
      console.log('Machine Serial Number:', machineSerialNumber);
      console.log('Repair No:', repairNo);

      if(selectedPartsData.length === 0) {
          showInfoModal('Important', 'No machine parts selected.');
          displayMaterialRequestTable([]);
      }
      else {
        // Send machineSerialNumber to PHP to fetch the related machine parts
          $.ajax({
            url: 'fetch_materials.php', // Change this to your actual PHP endpoint
            method: 'POST',
            data: {
                machine_serial_number: machineSerialNumber, // Pass machineSerialNumber to PHP
                selected_parts: selectedPartsData // Pass the selected parts data to PHP
            },
            success: function(response) {
                // Ensure the response is a valid JSON array before passing it to the table display function
                try {
                  console.log(response);
                  displayMaterialRequestTable(response); // Pass the parsed array to the table display function
                  console.log('Fetched materials:', response);
                } catch (error) {
                    console.error('Error parsing JSON response:', error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching materials:', error);
            }
        });

      }
  });  

  // After step 2: Collect materials and display review repair on next step
  $('#collectMaterialRequest').on('click', function() {

    selectedMaterials = [];
    $('#materialRequestTable tbody tr').each(function() {
        const checkbox = $(this).find('.material-checkbox');
        if (checkbox.is(':checked')) {
            const itemCode = checkbox.data('item-id');
            const quantity = $(this).find(`#quantity-${itemCode}`).val();
            const itemName = $(this).find('td:nth-child(3)').text(); // Get item name from table row
            
            selectedMaterials.push({
                item_code: itemCode,
                item_name: itemName,
                quantity: quantity
            });
        }
    });

    console.log("Selected Materials:", selectedParts);

    // Display the summary in Step 3
    $('#step-3').find('#repair_summary').html(`
      <h5 class="m-3 ml-0"><strong>Repair Summary</strong></h5>
      <div class="p-3 bg-white rounded">
          <p><strong>Repair No: </strong>${repairNo}</p>
          <p><strong>Machine: </strong>${machineName}</p>
          <p><strong>Serial No: </strong>${machineSerialNumber}</p>
          <p><strong>Description: </strong>${mainDescription}</p>
      </div>

      <div class="p-3 bg-light rounded">
        <p><strong>Machine Parts</strong></p>
        <ul>
            ${selectedParts.length === 0 ? 
            '<li>No machine parts affected</li>' : 
            selectedParts.map(part => `
                <li>${part.part_name}
                    <ul>
                        <li>Reason: ${part.reason}</li>
                        ${part.description === '' ? 
                        '<li>No reason description</li>' : 
                        `<li>${part.description}</li>`}
                    </ul>
                </li>`).join('')}
        </ul>
      </div>

      <div class="p-3 bg-white rounded">
        <p><strong>Requested Materials</strong></p>
        <ul>
          ${selectedMaterials.length === 0 ? 
          '<li>No materials requested</li>' : 
          selectedMaterials.map(material => `<li>${material.item_name} - Quantity: ${material.quantity}</li>`).join('')}
        </ul>
      </div>
      
    `);
  });


  $('#unclaimButton').on('click', function () {
    repairRequestModal.hide();
  });

  var navListItems = document.querySelectorAll('div.setup-panel div a');
  var allWells = document.querySelectorAll('.setup-content');
  var allNextBtn = document.querySelectorAll('.nextBtn');
  var allPrevBtn = document.querySelectorAll('.prevBtn');

  // Initially hide all the steps except the first one
  allWells.forEach(function(well) {
      well.style.display = 'none';  
  });

  // Activate first step
  document.querySelector('#step-1').style.display = 'block';
  navListItems[0].classList.add('btn-primary');

  // Handle navigation through steps
  navListItems.forEach(function(item) {
      item.addEventListener('click', function(e) {
          e.preventDefault();
          var targetId = this.getAttribute('href').substring(1);
          var targetStep = document.getElementById(targetId);

          if (!this.classList.contains('disabled')) {
              navListItems.forEach(function(nav) {
                  nav.classList.remove('btn-primary');
                  nav.classList.add('btn-default');
              });
              this.classList.add('btn-primary');
              allWells.forEach(function(well) {
                  well.style.display = 'none';
              });
              targetStep.style.display = 'block';
          }
      });
  });

  // Handle previous step
  allPrevBtn.forEach(function(btn) {
      btn.addEventListener('click', function() {
          var currentStep = this.closest(".setup-content");
          var currentStepId = currentStep.id;
          var prevStep = document.querySelector('div.setup-panel div a[href="#' + currentStepId + '"]')
              .parentNode.previousElementSibling.querySelector("a");
          prevStep.click();
      });
  });

  // Handle next step with validation for required fields only
  allNextBtn.forEach(function(btn) {
      btn.addEventListener('click', function() {
          var currentStep = this.closest(".setup-content");
          var currentStepId = currentStep.id;
          var nextStepWizard = document.querySelector('div.setup-panel div a[href="#' + currentStepId + '"]')
              .parentNode.nextElementSibling.querySelector("a");

          var isValid = true;
          var inputs = currentStep.querySelectorAll("input[type='text'], input[type='url'], textarea, select, input[type='number']");

          // Validate only required fields
          inputs.forEach(function(input) {
              if (input.hasAttribute('required')) { // Only validate required fields
                  if (!input.checkValidity()) {
                      isValid = false;
                      input.classList.add('is-invalid'); // Add red outline for invalid required inputs
                      input.closest(".form-group").classList.add("has-error"); // Optionally add error class to form group
                  } else {
                      input.classList.remove('is-invalid'); // Remove red outline for valid inputs
                      input.closest(".form-group").classList.remove("has-error"); // Remove error class from form group
                  }
              }
          });

          // If valid, move to the next step
          if (isValid) {
              currentStep.style.display = 'none'; // Hide current step
              var nextStep = document.getElementById(nextStepWizard.getAttribute('href').substring(1));
              nextStep.style.display = 'block'; // Show next step

              // Update the step wizard button state
              var currentNavItem = document.querySelector(`a[href='#${currentStepId}']`);
              var nextNavItem = currentNavItem.parentElement.nextElementSibling.querySelector("a");
              currentNavItem.classList.remove('btn-primary');
              currentNavItem.classList.add('btn-default');
              nextNavItem.classList.add('btn-primary');
              nextNavItem.classList.remove('btn-default');
          }
      });
  });

  // Handle submit button click in Step 3
  $('#submitRepairUpdate').on('click', function (e) {
    e.preventDefault(); // Prevent default form submission

    // Prepare the data to send
    const repairData = {
        repair_no: repairNo,
        machine_name: machineName,
        machine_serial_number: machineSerialNumber,
        main_description: mainDescription,
        selected_parts: selectedParts,
        selected_materials: selectedMaterials
    };

    console.log("Repair Data:", repairData);

    // Send the data to your PHP endpoint using AJAX
    $.ajax({
        url: 'submit_repair_update.php', // Replace with your actual PHP file
        method: 'POST',
        contentType: 'application/json', // Specify JSON format
        data: JSON.stringify(repairData), // Convert object to JSON string
        success: function (response) {
            console.log('Repair update submitted successfully:', response);
            showInfoModal('Success', 'Repair update submitted successfully!');
        },
        error: function (xhr, status, error) {
            console.error('Error submitting repair update:', error);
            showInfoModal('Error', 'An error occurred while submitting the repair update. Please try again.');
        }
    });
  });

});