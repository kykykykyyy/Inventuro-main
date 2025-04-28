document.addEventListener('DOMContentLoaded', function () {
    const editItemBtn = document.getElementById('editItemBtn');
    const editImageBtn = document.getElementById('editImageBtn');
    const removeImageBtn = document.getElementById('removeImageBtn');

    const modalItemName = document.getElementById('modalItemName');
    const modalSizePerUnit = document.getElementById('modalSizePerUnit');
    const modalUnit = document.getElementById('modalUnit');
    const modalDescription = document.getElementById('modalDescription');

    const reorderPointModal = document.getElementById('reorderPointModal');

    const editItemActionBtn = document.getElementById('editItemActionBtn');
    const deleteItemActionBtn = document.getElementById('deleteItemActionBtn');

    let isEditing = false; // Track if the modal is in edit mode

    // const itemTable = $('#itemTable').DataTable({
    //     autoWidth: false,
    //     pageLength: 10,
    //     lengthMenu: [10, 25, 50, 100],
    //     processing: true,
    //     searching: true,
    //     order: [],
    //     columnDefs: [{ orderable: false, targets: [0] }]
    // });

    const calendarEl = document.getElementById('calendar');

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        editable: true,
        timeZone: 'local', // Use local timezone
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        dateClick: function(info) {
            openMaintenanceModal(info.dateStr);
        },
        eventClick: function(info) {
            console.log("Event clicked:", info.event);
            console.log("Machine IDs:", info.event.extendedProps.machine_ids);
            openEditModal(info.event);
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            $.ajax({
                url: 'fetch_maintenance_data.php', // PHP file that fetches events from the DB
                method: 'GET',
                success: function(data) {
                    const events = JSON.parse(data);
                    
                    // Log events to verify the structure
                    console.log("Parsed events:", events);

                    // Format events into the format FullCalendar expects
                    const formattedEvents = events.map(event => {
                        return {
                            title: event.title, // Make sure the title is correct
                            start: event.start, // Ensure the date format matches FullCalendar requirements
                            extendedProps: {
                                machine_parts_id: event.machine_parts_id
                            }
                        };
                    });

                    // Pass the formatted events to FullCalendar
                    successCallback(formattedEvents);
                },
                error: function(error) {
                    console.log('Error fetching events:', error);
                    failureCallback(error);
                }
            });
        }
    });

    // Render the calendar
    calendar.render();

    // // Fetch events from the database and render them on the calendar
    // fetch('fetch_events.php')
    // .then(response => {
    //     return response.text(); // Read the response as text
    // })
    // .then(data => {
    //     console.log('Raw response:', data); // Log the raw response
    //     // Try to parse the response as JSON
    //     const events = JSON.parse(data);
        
    //     // Map the fetched events to the format expected by FullCalendar
    //     const formattedEvents = events.map(event => ({
    //         id: event.machine_id, // Unique ID for the event
    //         title: event.machine_description, // Title/description of the event
    //         start: event.maintenance_scheduled_date, // Start date of the event
    //         extendedProps: {
    //             machine_ids: event.machine_ids // Add machine IDs to extendedProps
    //         }
    //     }));

    //     // Add formatted events to the calendar
    //     calendar.addEventSource(formattedEvents);
    // })
    // .catch(error => {
    //     console.error('Error fetching events:', error);
    // });

    // Function to open the maintenance modal for adding an event
    function openMaintenanceModal(dateStr) {
        const modal = new bootstrap.Offcanvas(document.getElementById('addItemModal'));
        modal.show();
        
        // Set modal title
        document.getElementById('addItemModalLabel').textContent = "Add Maintenance Schedule";

        // Format the date to YYYY-MM-DD HH:mm:ss
        const date = new Date(dateStr);
        const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}:00`;

        // Populate the maintenance date field
        document.getElementById('maintenanceDate').value = formattedDate;
        document.getElementById('maintenanceDescription').value = ''; // Clear previous value
        currentEventId = null; // Reset current event ID for adding
    }

    // Function to open the maintenance modal for editing an event
    function openEditModal(event) {
        const modal = new bootstrap.Offcanvas(document.getElementById('addItemModal'));
        modal.show();
    
        // Set modal title
        document.getElementById('addItemModalLabel').textContent = "Edit Maintenance Schedule";
    
        // Populate the modal fields with the event details
        const formattedDate = event.start.toISOString().slice(0, 16); // Format to 'YYYY-MM-DDTHH:MM'
        document.getElementById('maintenanceDate').value = formattedDate;
        document.getElementById('maintenanceDescription').value = event.title;
    
        // Store the current event ID for editing
        currentEventId = event.id; // Save the event ID to use when saving
    
        // Reset all checkboxes
        const checkboxes = document.querySelectorAll('#itemTable .row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false; // Uncheck all checkboxes initially
        });
    
        // Retrieve the machine IDs associated with this event
        const machineIds = event.extendedProps.machine_ids; // This should hold the machine IDs for the event
    
        // Check the corresponding checkboxes
        if (Array.isArray(machineIds)) {
            machineIds.forEach(machineId => {
                const checkbox = document.querySelector(`#itemTable .row-checkbox[data-machine-id="${machineId}"]`);
                if (checkbox) {
                    checkbox.checked = true; // Check the checkbox if it matches the machine ID
                    console.log(`Checkbox for Machine ID ${machineId} checked.`); // Log checkbox checking
                } else {
                    console.log(`Checkbox for Machine ID ${machineId} not found.`); // Log if not found
                }
            });
        }
    }
    
    // Save Maintenance Schedule
    document.getElementById('saveMaintenanceBtn').onclick = function() {
        const maintenanceDate = document.getElementById('maintenanceDate').value;
        const maintenanceDescription = document.getElementById('maintenanceDescription').value;

        // Collect checked machines
        const checkedMachines = [];
        document.querySelectorAll('#itemTable .row-checkbox:checked').forEach((checkbox) => {
            const row = checkbox.closest('tr');
            const machineId = row.getAttribute('data-machine-id');
            if (machineId) {
                checkedMachines.push(machineId);
            }
        });

        // Check if any machines were selected
        if (checkedMachines.length === 0) {
            alert('Please select at least one machine.');
            return; // Prevent further execution if no machines are selected
        }

        // Prepare data for the AJAX request
        const data = {
            maintenance_date: maintenanceDate,
            maintenance_description: maintenanceDescription,
            machine_ids: checkedMachines,
            maintenance_status: 'Scheduled', // Status is always "Scheduled"
            event_id: currentEventId // Include the event ID for updating
        };

        console.log('Data to send:', data); // Debug log

        // Send AJAX request to save the maintenance schedule
        fetch('save_maintenance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data) // Convert data object to JSON
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Maintenance schedule saved successfully!');
                closeModal();
                calendar.refetchEvents(); // Refresh events on the calendar
            } else {
                alert('Error saving maintenance schedule: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the maintenance schedule.');
        });
    };

    // Function to close the modal
    function closeModal() {
        const modal = bootstrap.Offcanvas.getInstance(document.getElementById('addItemModal'));
        modal.hide();
    }

    // Select all checkboxes
    document.getElementById('selectAll').onclick = function() {
        const checkboxes = document.querySelectorAll('#itemTable .row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    };

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
            reorder_point: $('#modalReorderPointText').text(),
            item_code: $('#modalItemCodeText').text(),
            // Add base64 encoded image
            image: imageData
        };
    }


    // Save updated data to the database
    function saveItemData(user) {
        $.ajax({
            url: 'update_item.php', // Adjust this to your endpoint
            method: 'POST',
            data: user,
            dataType: 'json', // Expect JSON from the server
            success: function (response) {
                console.log('Response received:', response); // Log server response

                editImageBtn.disabled = true;
                removeImageBtn.disabled = true;

                // Disable input fields for editing
                disableModalInputs();
                isEditing = false;

                editItemBtn.textContent = 'Edit'; // Change button back to "Edit"
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown); // Detailed logging
                console.error('Response text:', jqXHR.responseText); // Log response text

                alert(`Failed to update user. Please try again. Error: ${textStatus}`);
            }
        });
    }

    // Function to enable input fields
    function enableModalInputs() {
        modalItemName.disabled = false;
        modalSizePerUnit.disabled = false;
        modalUnit.disabled = false;
        modalDescription.disabled = false;
    }

    // Function to disable input fields
    function disableModalInputs() {
        modalItemName.disabled = true;
        modalSizePerUnit.disabled = true;
        modalUnit.disabled = true;
        modalDescription.disabled = true;
    }

    // Edit Image button functionality
    editImageBtn.addEventListener('click', function () {
        uploadImageInput.click();

        const allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        const maxFileSize = 20 * 1024 * 1024; // 20 MB in bytes

        uploadImageInput.addEventListener('change', function () {
            const selectedFile = this.files[0];

            if (selectedFile) {
                const fileType = selectedFile.type;
                const fileSize = selectedFile.size;

                if (!allowedFileTypes.includes(fileType)) {
                    alert('Only JPEG, PNG, and JPG images are allowed');
                    return;
                }

                if (fileSize > maxFileSize) {
                    alert('Maximum file size is 20 MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const image = new Image();
                    image.src = e.target.result;
                    image.onload = function () {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = image.naturalWidth; // Use naturalWidth for original dimensions
                        canvas.height = image.naturalHeight; // Use naturalHeight for original dimensions
                        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

                        // Set the image source with the canvas result (base64 encoded image)
                        document.getElementById('itemProfileImage').src = canvas.toDataURL('image/jpeg');

                    };
                };
                reader.readAsDataURL(selectedFile);
            }
        });
    });

    // Remove Image button functionality
    removeImageBtn.addEventListener('click', function () {
        $('#removeImageModal').modal('show');

        // Handle the confirm removal action
        $('#confirmRemoveImageBtn').on('click', function () {
            $('#itemProfileImage').attr('src', "../../../images/gallery.png"); // Put the default image
            $('#removeImageModal').modal('hide'); // Hide the modal
        });
    });

    // Select all checkboxes when the header checkbox is clicked
    $('#selectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
    });

    function fetchReorderPoint(itemCode) {
        $.ajax({
            url: 'fetch_fa_reorder_point.php', // Adjust this to the correct path of your PHP script
            method: 'GET',
            dataType: 'json',
            data: { item_code: itemCode },
            success: function(response) {
                // Check if the response contains the reorder point
                if (response.reorder_point !== undefined) {
                    $('#reorderPointText').text(response.reorder_point); // Update the text with the reorder point
                } else {
                    console.error('Reorder point not found in response:', response);
                    $('#reorderPointText').text('[Reorder Point]');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching reorder point:', error);
                $('#reorderPointText').text('Error fetching reorder point.');
            }
        });
    }

    // Prevent opening the modal if multiple rows are selected
    $('.row-checkbox').on('change', function () {
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount > 1) {
            const modal = bootstrap.Offcanvas.getInstance('#itemInfoModal');
            if (modal) modal.hide();
        }
    });

    // Function to save item to the database
    function saveItemToDatabase(itemName, quantity, sizePerUnit, unit, description, imageData, createdBy) {
        $.ajax({
            url: 'add_item.php', // Adjust this to your endpoint
            method: 'POST',
            dataType: 'json',
            data: {
                item_name: itemName,
                item_quantity: quantity,
                size_per_unit: sizePerUnit,
                unit: unit,
                description: description,
                image: imageData,// Send the base64 image
                created_by: createdBy
            },
            success: function(response) {
                console.log(response)

                const addItemModal = new bootstrap.Offcanvas(document.getElementById('addItemModal'));
                addItemModal.hide();

                window.location.reload();
            },
            error: function(xhr, status, error) {
                // Log the raw response to help debug server-side errors
                console.error('Raw response:', xhr.responseText);

                // Check if the response is JSON or not
                let response;
                try {
                    // Attempt to parse the response as JSON
                    response = JSON.parse(xhr.responseText);
                    console.error('Error:', response.errors || response.message);
                    alert('Error adding user. Check console for details.');
                } catch (e) {
                    // If parsing fails, it means the response is not JSON (likely HTML error page)
                    console.error('Failed to parse JSON. Raw response:', xhr.responseText);
                    alert('Server returned an invalid response. Check console for details.');
                }
            }
        });
    }

    // Open confirmation modal for deletion
    document.getElementById('deleteItemBtn').addEventListener('click', function() {
        // Get item information
        const item_name = $('#modalItemNameText').text();
        const item_code = $('#modalItemCodeText').text();

        document.getElementById('displayItemName').textContent = item_name;

        // Show the confirmation modal
        $('#confirmDeleteModal').modal('show');
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            const inputItemName = document.getElementById('confirmItemName').value;
            // Check if item names match
            if (inputItemName === item_name) {
                $.ajax({
                    url: 'delete_item.php', // The PHP file that handles deletion
                    method: 'POST',
                    data: { item_code: item_code }, // Send employee ID for deletion
                    success: function(response) {
                        // Reload the page to reflect changes
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting user. Please try again.');
                        console.error('Error:', xhr.responseText);
                    }
                });
                $('#confirmDeleteModal').modal('hide'); // Hide modal after confirming
            } else {
                alert('The names do not match. Please try again.');
            }
        };

    });

    $('#editReorderPointBtn').on('click', function(event) {
        event.preventDefault();  // Prevent default anchor behavior

        $('#reorderPointModal').modal('show');  // Show the modal

        $('#reorderPointModal').on('shown.bs.modal', function() {
            $(this).find('input').focus();  // Focus on an input field inside the modal (optional)
        });
    });

    $('#editQuantityBtn').on('click', function(event) {
        event.preventDefault();  // Prevent default anchor behavior
        window.location.href = '../adjustments/index.php?openModal=true';
    });

    $('#updateReorderPointBtn').on('click', function(event) {
        event.preventDefault();  // Prevent the default form submission behavior

        var reorderPoint =$('#reorderPointModalInput').val();  // Get reorder point as a number
        var itemCode = $('#modalItemCodeText').text();  // Get the item ID

        // Validate reorderPoint
        if (reorderPoint <= 100 && reorderPoint >= 1) {
            // Proceed with AJAX request if validation passes
            $.ajax({
                url: 'update_reorder_point.php',  // The PHP file that handles updating the reorder point
                method: 'POST',
                data: { 
                    item_code: itemCode, 
                    reorder_point: reorderPoint
                },
                success: function(response) {
                    // Reload the page to reflect changes
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error updating reorder point. Please try again.');
                    console.error('Error:', xhr.responseText);
                }
            });

            // Hide the modal only after the AJAX call is triggered
            $('#reorderPointModal').modal('hide');
        }
        else {
            alert('Please enter a valid reorder point between 1 and 100.');
            return;  // Stop further execution if validation fails
        }
    });


    const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
        document.querySelector("#sidebar").classList.toggle("expand");
    });
});