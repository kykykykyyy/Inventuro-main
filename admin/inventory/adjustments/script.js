document.addEventListener('DOMContentLoaded', function () {
    const adjustmentTable = $('#adjustmentTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        processing: true,
        searching: true,
        order: [],
        columnDefs: [{ orderable: false, targets: [0] }]
    });

    $(document).ready(function() {
        // Check for the query parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModal') === 'true') {
            $('#addAdjustmentBtn').click(); // Simulate a click on the button to open the modal
        }
    });

    flatpickr("#addDate", {
        dateFormat: "d M Y", // Format to display
        defaultDate: "today",// Sets today's date as default
        onChange: function(selectedDates, dateStr, instance) {
            // Format the date to YYYY-MM-DD when selected
            const date = new Date(dateStr);
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const dd = String(date.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;
    
            // Store the formatted date in a hidden input or variable if needed
            document.getElementById('hiddenDateInput').value = formattedDate;
        }
    });

    $('#addReason').select2({
        placeholder: 'Select a reason',
        allowClear: true,
        width: '100%',
        templateResult: function(data) {
            // Custom formatting for options
            if (!data.id) {
                return data.text; // Display the default option
            }
            // Check if the option has an icon
            var $result = $('<span>' + data.text + '</span>');
            if (data.element.dataset.icon) {
                $result.prepend('<i class="' + data.element.dataset.icon + '"></i> '); // Add the icon
            }
            return $result;
        }
    });

    function getBase64Image(img) {
        var canvas = document.createElement("canvas");
        var ctx = canvas.getContext("2d");

        // Ensure canvas matches image dimensions
        canvas.width = img.naturalWidth;  // Use naturalWidth to get the actual image dimensions
        canvas.height = img.naturalHeight; // Use naturalHeight

        // Draw the image onto the canvas
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        // Convert canvas to base64 (data URL)
        var dataURL = canvas.toDataURL("image/jpeg"); // Use 'image/jpeg' for JPEG format, or adjust for other formats
        return dataURL.replace(/^data:image\/(png|jpg|jpeg);base64,/, ""); // Strip the base64 header
    }

    function getModalInputValues() {
        var imageElement = document.getElementById('itemProfileImage'); // Get the image element
        var imageData = ''; // Initialize imageData as an empty string

        // Check if the image element has a valid source
        if (imageElement && imageElement.src) {
            imageData = getBase64Image(imageElement); // Convert image to base64
        }

        return {
            item_name: modalItemName.value,
            size_per_unit: modalSizePerUnit.value,
            unit: modalUnit.value,
            description: modalDescription.value,
            adjustment_id: $('#modalAdjustmentIDText').text(),
            // Add base64 encoded image
            image: imageData
        };
    }

    // Select all checkboxes when the header checkbox is clicked
    $('#selectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
    });

    // Open modal and populate it with adjustment data when a row is clicked
    $('#adjustmentTable tbody').on('click', 'tr', function (event) {
        const checkbox = $(this).find('.row-checkbox');

        // Toggle checkbox state and row selection
        checkbox.prop('checked', !checkbox.prop('checked'));
        $(this).toggleClass('selected', checkbox.prop('checked'));
        
        // Check how many rows are selected
        const selectedCount = $('.row-checkbox:checked').length;

        // Enable edit button if only one row is selected
        if (selectedCount === 1) {

            const viewCheck = document.getElementById('viewCheck');
            viewCheck.checked = true;
            viewCheck.dispatchEvent(new Event('change'));

            // Extract data from the selected row
            const adjustment_id = $(this).data('adjustment-id');
            const entry_date = $(this).data('entry-date');
            const reason = $(this).data('reason');
            const created_by = $(this).data('created-by-name');
            const description = $(this).data('description');
            const reference_number = $(this).data('reference-number');

            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            };

            const formattedDate = new Date(entry_date).toLocaleDateString('en-US', options).replace(',', '');
            
            $('#modalAdjustmentIDText').text(adjustment_id); // Display the adjustment ID
            $('#modalEntryDateText').text(formattedDate); // Display the entry date
            $('#modalReasonText').text(reason); // Display the reason
            $('#modalCreatedByText').text(created_by); // Display the created by
            $('#modalDescriptionText').text(description); // Display the description
            $('#modalReferenceNumberText').text(reference_number); // Display the reference number
            
            fetchItemsForAdjustment(adjustment_id);

            const adjustmentId = document.getElementById('modalAdjustmentIDText').textContent;
            const employeeId = document.getElementById('loggedInEmployeeId').value;
            const employeeName = document.getElementById('loggedInName').value;

            // Embed the PDF in an iframe within #pdfView
            const pdfUrl = `generate_pdf.php?adjustment_id=${adjustmentId}&employee_id=${encodeURIComponent(employeeId)}&employee_name=${encodeURIComponent(employeeName)}`;
            document.getElementById('pdfView').innerHTML = `<iframe src="${pdfUrl}" width="100%" height="600px" style="border: none;"></iframe>`;
            
            // Open the offcanvas modal
            const modal = new bootstrap.Offcanvas('#adjustmentInfoModal');
            modal.show();
        }
    });

    // Prevent opening the modal if multiple rows are selected
    $('.row-checkbox').on('change', function () {
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount > 1) {
            const modal = bootstrap.Offcanvas.getInstance('#adjustmentInfoModal');
            if (modal) modal.hide();
        }
    });

    // Save Reason functionality
    document.getElementById('saveNewReasonBtn').addEventListener('click', function() {
        // Collect input values
        const reason_name = document.getElementById('reasonName').value;

        if(reason_name == '') {
            alert('Please enter a reason name.');
            return;
        }
        // Save the reason to the database
        saveReasonToDatabase(reason_name);
    });

    // Function to save reason to the database
    function saveReasonToDatabase(reason_name) {
        $.ajax({
            url: 'add_reason.php', // Adjust this to your endpoint
            method: 'POST',
            dataType: 'json',
            data: {
                reason_name: reason_name
            },
            success: function(response) {
                console.log(response);

                const manageReasonModal = new bootstrap.Offcanvas(document.getElementById('manageReasonModal'));
                manageReasonModal.hide();

                window.location.reload();
            },
            error: function(xhr, status, error) {
                alert('Error deleating the reason. Please try again.');
                console.error('Error:', error);
            }
        });
    }

    // Open the Add Adjustment offcanvas modal
    document.getElementById('addAdjustmentBtn').addEventListener('click', function() {
        const addAdjustmentModal = new bootstrap.Offcanvas(document.getElementById('addAdjustmentModal'));
        addAdjustmentModal.show();
    });

    // Save Adjustment functionality
    document.getElementById('saveItemBtn').addEventListener('click', function() {

        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const dd = String(now.getDate()).padStart(2, '0');

        // Capture values from the modal inputs
        const referenceNumber = $('#addReferenceNumber').val().trim() || "";
        const entryDate = document.getElementById('hiddenDateInput').value || yyyy + '-' + mm + '-' + dd; // Get the formatted date
        const reasonId = $('#addReason').val();
        const description = $('#addDescription').val().trim() || ""; // Default to empty string if null
        const createdBy = loggedInEmployeeId.value;

        // Validate input
        if (reasonId === "manage" || reasonId === "") {
            alert("Please select a valid reason");
            return;
        }
        // Get the current time
        const hours = String(now.getHours()).padStart(2, '0'); // Get hours and pad with leading zero if needed
        const minutes = String(now.getMinutes()).padStart(2, '0'); // Get minutes and pad with leading zero if needed
        const seconds = String(now.getSeconds()).padStart(2, '0'); // Get seconds and pad with leading zero if needed

        // Combine entryDate with current time to form a complete timestamp
        const timestamp = `${entryDate} ${hours}:${minutes}:${seconds}`; // Format: YYYY-MM-DD HH:MM:SS

        // Collect item data from the table
        const items = [];
        $('#addAdjustmentTableBody tr').each(function() {
            const itemCode = $(this).find('.item-code').text();
            const itemName = $(this).find('.item-name').val();
            const currentQuantity = parseInt($(this).find('.quantity-available').text(), 10) || 0;
            const newQuantity = parseInt($(this).find('.new-quantity').val(), 10) || 0;
            const quantityAdjusted = parseInt($(this).find('.quantity-adjusted').val(), 10) || 0;

            // Only add items with valid data
            if (itemCode && itemName && newQuantity != currentQuantity && quantityAdjusted != 0 && currentQuantity >= 0) {
                items.push({
                    item_id: itemCode,
                    item_name: itemName,
                    previous_quantity: currentQuantity,
                    new_quantity: newQuantity,
                    quantity_adjusted: quantityAdjusted
                });
            }
        });

        // Check if there are valid items to save
        if (items.length === 0) {
            alert("Please add at least one item to adjust.");
            return;
        }

        // Save the adjustment to the database
        saveAdjustmentToDatabase(reasonId, referenceNumber, timestamp, description, createdBy, items);

    });

    // Function to save adjustment to the database
    function saveAdjustmentToDatabase(reasonId, referenceNumber, timestamp, description, createdBy, items) {
        $.ajax({
            url: 'add_adjustment.php', // Adjust this to your endpoint
            method: 'POST',
            dataType: 'json',
            data: {
                reason_id: reasonId,
                reference_number: referenceNumber,
                entry_date: timestamp,
                description: description,
                created_by: createdBy,
                items: items // Send the items array as part of the AJAX request
            },
            success: function(response) {
                console.log(response);

                const addAdjustmentModal = new bootstrap.Offcanvas(document.getElementById('addAdjustmentModal'));
                addAdjustmentModal.hide();

                // Refresh the page or update the UI as needed
                window.location.reload();
            },
            error: function(xhr, status, error) {
                alert('Error adding the adjustment. Please try again.');
                console.error('Error:', error);
            }
        });
    }

    // Open confirmation modal for deletion
    document.getElementById('deleteAdjustmentBtn').addEventListener('click', function() {
        // Get item information
        const adjustment_id = $('#modalAdjustmentIDText').text();

        // Show the confirmation modal
        $('#confirmDeleteModal').modal('show');
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            $.ajax({
                url: 'delete_adjustment.php', // The PHP file that handles deletion
                method: 'POST',
                data: { adjustment_id: adjustment_id }, // Send employee ID for deletion
                success: function(response) {
                    // Reload the page to reflect changes
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error deleting adjustment. Please try again.');
                    console.error('Error:', xhr.responseText);
                }
            });
            $('#confirmDeleteModal').modal('hide'); // Hide modal after confirming
        };

    });

    document.getElementById('viewCheck').addEventListener('change', function() {
        const isChecked = this.checked;
        document.getElementById('pdfView').classList.toggle('d-none', !isChecked);
        document.getElementById('normalView').classList.toggle('d-none', isChecked);
    })

    function fetchItemsForAdjustment(adjustment_id) {
        if (!adjustment_id || adjustment_id === "[Adjustment ID]") {
            console.error("Invalid adjustment ID:", adjustment_id);
            return;
        }
    
        $.ajax({
            url: 'fetch_item_adjustments.php', 
            type: 'GET',
            dataType: 'json',  
            data: { adjustment_id: adjustment_id },
            success: function (data) {
                if (!data || (Array.isArray(data) && data.length === 0)) {
                    alert('No items found for this adjustment.');
                    return;
                }

                // Clear existing table rows
                const tableBody = document.querySelector('#itemAdjustmentTable tbody');
                tableBody.innerHTML = '';

                // Populate table rows
                data.forEach(item => {
                    const row = `
                        <tr>
                            <td>
                                <img class="text-start align-middle" src="data:image/jpeg;base64,${item.image}" alt="Item Image" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                <span class="align-middle">${item.item_name}</span>
                            </td>
                            <td class="text-center align-middle">${item.item_quantity}</td>
                            <td class="text-center align-middle">${item.quantity_adjusted}</td>
                            <td class="text-center align-middle">${item.previous_quantity}</td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            },
            error: function (xhr, status, error) {
                console.error('AJAX request failed:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                alert('An error occurred while fetching data.');
            }
        });
    }

    document.getElementById('downloadFileBtn').addEventListener('click', function() {
        const adjustmentId = document.getElementById('modalAdjustmentIDText').textContent;
        const employeeId = document.getElementById('loggedInEmployeeId').value;
        const employeeName = document.getElementById('loggedInName').value;
    
        // Add employee ID and name as query parameters
        fetch(`generate_pdf.php?adjustment_id=${adjustmentId}&employee_id=${encodeURIComponent(employeeId)}&employee_name=${encodeURIComponent(employeeName)}`, {
            method: 'GET',
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `Inventory_Adjustment_${adjustmentId}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        })
        .catch(error => console.error('Error downloading PDF:', error));
    });

    // Event listener for managing reasons
    $('#addReason').on('change', function() {
        const selectedValue = $(this).val();
        
        if(selectedValue === "") {
            alert("Please select a reason");
            return;
        }
        if (selectedValue === "manage") {
            $('#addReason').val('');
            $('#manageReasonModal').modal('show');
        }
    });

    let selectedItems = []; // Array to track selected items

    function populateDatalist() {
        $.ajax({
            url: 'fetch_items.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const datalist = $('#itemList');
                datalist.empty(); // Clear existing items
                
                // Populate datalist with fetched items, excluding selected items
                data.forEach(item => {
                    if (!selectedItems.includes(item.item_name)) { // Only add unselected items
                        datalist.append(`<option value="${item.item_name}" data-id="${item.item_code}" data-quantity="${item.item_quantity}" data-picture="${item.image}">`);
                    }
                });

                if (data.every(item => selectedItems.includes(item.item_name))) {
                    $('#addRowBtn').prop('disabled', true); // Disable the add row button
                } else {
                    $('#addRowBtn').prop('disabled', false); // Enable the button if there are items
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching items:', textStatus, errorThrown);
            }
        });
    }

    // Handle item selection from the datalist
    $(document).on('input', '.item-name', function() {
        const itemName = $(this).val();
        const selectedItem = $('#itemList option[value="' + itemName + '"]');

        if (selectedItem.length) {
            // Check if the item is already selected
            if (selectedItems.includes(itemName)) {
                alert("This item has already been added.");
                $(this).val(''); // Clear the input
                return;
            }

            // Get item details
            const row = $(this).closest('tr');
            const itemId = selectedItem.data('id');
            const itemQuantity = selectedItem.data('quantity');
            const itemImage = selectedItem.data('picture');

            // Update the row with the selected item's details
            row.find('.item-code').text(itemId);
            row.find('.quantity-available').text(itemQuantity); // Set current quantity
            row.find('img').attr('src', itemImage); // Update the image
            row.find('.quantity-adjusted, .new-quantity').prop('disabled', false); // Enable inputs
            
            // Add item name to the selectedItems array
            selectedItems.push(itemName);
            populateDatalist(); // Refresh the datalist to remove the selected item
        } else {
            $(this).closest('tr').find('.quantity-available').text('-'); // Reset if not found
        }
    });

    // Add a new row
    $('#addRowBtn').click(function() {
        const newRow = $('.template-row').first().clone();
        newRow.find('input').val(''); // Clear input fields in the new row
        newRow.find('.quantity-available').text('-'); // Reset available quantity
        newRow.find('.item-code').text('#');
        newRow.find('img').attr('src', '../../../images/gallery.png'); // Reset image
        newRow.find('.quantity-adjusted, .new-quantity').prop('disabled', true); // Enable inputs
        $('#addAdjustmentTableBody').append(newRow);
    });

    // Remove a row and update the selectedItems array
    $(document).on('click', '.remove-row', function() {
        const totalRows = $('#addAdjustmentTableBody tr').length; // Get the total number of rows
        
        if (totalRows > 1) { // Check if there is more than one row
            const row = $(this).closest('tr');
            const itemName = row.find('.item-name').val();

            // Remove the item from selectedItems array
            selectedItems = selectedItems.filter(item => item !== itemName);

            $(this).closest('tr').remove(); // Remove the row
            populateDatalist(); // Refresh the datalist to add the removed item back

        } else {
            alert("You cannot remove the last row."); // Alert the user if they try to remove the last row
        }
    });

    populateDatalist();

    // Event listener for the Add New Reason button
    document.getElementById('addReasonBtn').addEventListener('click', function() {
        const newReasonSection = document.getElementById('newReasonSection');
        const addReasonSection = document.getElementById('addReasonSection');

        addReasonSection.classList.add('d-none'); // Hide the section
        newReasonSection.classList.toggle('d-none'); // Show the section
    });

    // Event listener for the Cancel button
    document.getElementById('cancelNewReasonBtn').addEventListener('click', function() {
        const newReasonSection = document.getElementById('newReasonSection');
        const addReasonSection = document.getElementById('addReasonSection');
        const newReasonNameInput = document.getElementById('reasonName');

        newReasonNameInput.value = ''; // Clear the input
        addReasonSection.classList.remove('d-none'); // Show the section
        newReasonSection.classList.add('d-none'); // Hide the section
    });

    // Event listener for the delete reason icon
    $(document).on('click', '.delete-icon', function() {
        const row = $(this).closest('.reason-row');
        const reasonId = row.data('reason-id');
        const isUsed = row.data('used') === 'true';

        if (isUsed) {
            alert("This reason cannot be deleted because it has been used in an adjustment.");
        } else {
            // Confirm deletion
            if (confirm("Are you sure you want to delete this reason?")) {
                // Proceed with AJAX request to delete the reason
                console.log(reasonId);
                $.ajax({
                    url: 'delete_reason.php', // Adjust this to your deletion script
                    method: 'POST',
                    data: { reason_id: reasonId },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleating the reason. Please try again.');
                        console.error('Error:', error);
                    }
                });
            }
        }
    });

    // When focus leaves the quantity-adjusted (QA) input
    $(document).on('blur', '.quantity-adjusted', function() {
        const row = $(this).closest('tr'); // Get the current row
        const currentQuantity = parseInt(row.find('.quantity-available').text(), 10) || 0; // Get current quantity (CQ)
        const qaValue = parseInt($(this).val(), 10) || 0; // Get quantity adjusted value (QA)

        if (currentQuantity === 0) {
            alert('Select an item first.');
            row.find('.item-name').focus(); // Focus on the item selection input in the current row
            $(this).val(''); // Clear the QA input
            return;
        }

        if (qaValue === 0) {
            alert('Quantity Adjusted (QA) cannot be 0.');
            $(this).val(''); // Clear the QA input
            $(this).focus(); // Focus on QA input
            return;
        }

        // Update the new quantity (NQ) based on current quantity and QA
        row.find('.new-quantity').val(currentQuantity + qaValue);
    });

    // When focus leaves the new-quantity (NQ) input
    $(document).on('blur', '.new-quantity', function() {
        const row = $(this).closest('tr'); // Get the current row
        const currentQuantity = parseInt(row.find('.quantity-available').text(), 10) || 0; // Get current quantity (CQ)
        const nqValue = parseInt($(this).val(), 10) || 0; // Get new quantity value (NQ)

        if (currentQuantity === 0) {
            alert('Select an item first.');
            row.find('.item-name').focus(); // Focus on the item selection input in the current row
            $(this).val(''); // Clear the NQ input
            return;
        }

        if (nqValue === currentQuantity) {
            alert('New Quantity (NQ) should not be equal to the Current Quantity (CQ).');
            $(this).val(''); // Clear the NQ input
            $(this).focus(); // Focus on NQ input
            return;
        }

        // Update the quantity adjusted (QA) based on NQ and current quantity
        row.find('.quantity-adjusted').val(nqValue - currentQuantity);
    });

    const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
        document.querySelector("#sidebar").classList.toggle("expand");
    });
});