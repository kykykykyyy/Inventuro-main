document.addEventListener('DOMContentLoaded', function () {
    const editMachineBtn = document.getElementById('editMachineBtn');
    const editImageBtn = document.getElementById('editImageBtn');
    const removeImageBtn = document.getElementById('removeImageBtn');

    const modalMachineName = document.getElementById('modalMachineName');
    const modalDepartmentName = document.getElementById('modalDepartmentName');
    const modalDescription = document.getElementById('modalDescription');

    const machineIntervalModal = document.getElementById('machineIntervalModal');

    const editItemActionBtn = document.getElementById('editItemActionBtn');
    const deleteItemActionBtn = document.getElementById('deleteItemActionBtn');

    let isEditing = false; // Track if the modal is in edit mode

    const itemTable = $('#itemTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        processing: true,
        searching: true,
        order: [],
        columnDefs: [{ orderable: false, targets: [0] }]
    });

    var calendarEl = document.getElementById('calendar');
        
    // Disable image edit/remove buttons initially
    editImageBtn.disabled = true;
    removeImageBtn.disabled = true;

    // Disable input fields initially
    disableModalInputs();

    // Enable image buttons when the edit button is clicked
    editMachineBtn.addEventListener('click', function () {
        if (!isEditing) {
            // Switch to edit mode
            editMachineBtn.textContent = 'Save';
            editImageBtn.disabled = false;
            removeImageBtn.disabled = false;
            // Enable input fields for editing
            enableModalInputs();
            isEditing = true;
        } else {
            // Save changes and switch back to view mode
            const updatedItem = getModalInputValues();
            saveItemData(updatedItem); // Call save function
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
            machine_id: $('#modalMachineCodeText').text(),
            machine_name: modalMachineName.value,
            department_id: modalDepartmentName.value,
            description: modalDescription.value,
            machine_maintenance_interval_days: $('#modalMaintenanceScheduleText').text(),
            // Add base64 encoded image
            image: imageData
        };
    }

    // Save updated data to the database
    function saveItemData(user) {
        $.ajax({
            url: 'update_machine.php', // Adjust this to your endpoint
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

                editMachineBtn.textContent = 'Edit'; // Change button back to "Edit"
                window.location.reload();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown); // Detailed logging
                console.error('Response text:', jqXHR.responseText); // Log response text

                alert(`Failed to update machine. Please try again. Error: ${textStatus}`);
            }
        });
    }

    // Function to enable input fields
    function enableModalInputs() {
        modalMachineName.disabled = false;
        modalDepartmentName.disabled = false;
        modalDescription.disabled = false;
    }

    // Function to disable input fields
    function disableModalInputs() {
        modalMachineName.disabled = true;
        modalDepartmentName.disabled = true;
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

    // Open modal and populate it with user data when a row is clicked
    $('#itemTable tbody').on('click', 'tr', function (event) {
        const checkbox = $(this).find('.row-checkbox');

        // Toggle checkbox state and row selection
        checkbox.prop('checked', !checkbox.prop('checked'));
        $(this).toggleClass('selected', checkbox.prop('checked'));

        // Check how many rows are selected
        const selectedCount = $('.row-checkbox:checked').length;

        // Enable edit button if only one row is selected
        if (selectedCount === 1) {
            // Reset edit button and state if in editing mode
            if (isEditing) {
                isEditing = false; // Reset edit mode
                editMachineBtn.textContent = 'Edit'; // Reset button text
            }
            
            editImageBtn.disabled = true;
            removeImageBtn.disabled = true;

            // Extract data from the selected row
            const code = $(this).data('machine-id');
            const name = $(this).data('machine-name');
            const departmentId = $(this).data('department-id');
            const description = $(this).data('machine-description');
            const createdBy = $(this).data('machine-created-by');
            const createdAt = $(this).data('machine-created-at');
            const warrantyStatus = $(this).data('warranty-status');
            const imgSrc = $(this).data('image');

            // Populate the modal with item data
            $('#modalMachineNameText').text(name);  // Display the item name
            $('#modalMachineCodeText').text(code); // Display the item code

            // Set the item  image or default icon
            $('#itemProfileImage').attr('src', imgSrc || '../../../images/gallery.png');

            modalMachineName.value = name;
            modalDepartmentName.value = departmentId;
            modalDescription.value = description;

            $('#modalCreatedByNameText').text(createdBy);
            $('#modalCreatedAtText').text(createdAt);

            // Disable inputs before showing the modal
            disableModalInputs();

            if (warrantyStatus !== 'No warranty') {
                // Populate the warranty details in the modal
                $('#companyName').text($(this).data('warranty-company-name'));
                $('#startDate').text($(this).data('warranty-start-date'));
                $('#endDate').text($(this).data('warranty-end-date'));
                $('#details').text($(this).data('warranty-coverage-details'));
                $('#contactNumber').text($(this).data('warranty-contact-info'));

                underWarrantySection.classList.toggle('d-none', false);
                
            } else {
                underWarrantySection.classList.toggle('d-none', true);
            }

            fetchMachineParts(code);

            // Open the offcanvas modal
            const modal = new bootstrap.Offcanvas('#machineInfoModal');
            modal.show();
        }
        else {
            // Reset edit button and state if in editing mode
            if (isEditing) {
                isEditing = false; // Reset edit mode
                editMachineBtn.textContent = 'Edit'; // Reset button text
            }

        }
    });

    // Function to fetch machine parts based on machine ID
    function fetchMachineParts(machineId) {
        $.ajax({
            url: 'fetch_parts.php', // Path to your PHP script
            method: 'GET',
            data: {
                machine_id: machineId // Pass the machine_id as a parameter
            },
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    // Handle any error messages from the server
                    alert(response.message);
                } else {
                    // If parts are found, update the HTML
                    let partDetails = '';
                    response.forEach(function(part) {
                        partDate = `<p>${part.replacement_date} : ${part.replacement_hours} of maintenance hours</p>`;
                        partDetails += `<p>${part.machine_parts_name} : Remaining ${part.current_operating_hours} hours</p>`;
                    });

                    // Inject the part details into the modal
                    $('#modalMaintenanceDateText').html(partDate);
                    $('#modalMaintenancePartText').html(partDetails);
                }
            },
            error: function(xhr, status, error) {
                // Handle any AJAX errors
                alert('Error: ' + error);
            }
        });
    }

    document.getElementById('underWarrantySection').addEventListener('click', function () {
        $('#warrantyModal').modal('show');
    })

    // Prevent opening the modal if multiple rows are selected
    $('.row-checkbox').on('change', function () {
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount > 1) {
            const modal = bootstrap.Offcanvas.getInstance('#machineInfoModal');
            if (modal) modal.hide();
        }
    });

    // Open the Add User offcanvas modal
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const addMachineModal = new bootstrap.Offcanvas(document.getElementById('addMachineModal'));
        addMachineModal.show();
    });
    
    document.getElementById('saveItemBtn').addEventListener('click', function() {
        // Collect input values
        const machineName = document.getElementById('addMachineName').value;
        const type = document.getElementById('addType').value;
        const model = document.getElementById('addModel').value;
        const manufacturer = document.getElementById('addManufacturer').value;
        const manufacturedYear = document.getElementById('addManufactureYear').value;
        const maintenanceInterval = document.getElementById('addMaintenanceInterval').value;
        const description = document.getElementById('addMachineDescription').value || '';
        const departmentID = document.getElementById('addDepartmentID').value; // Get department ID
    
        const imgElement = document.getElementById('previewItemImage');
        let imageData = getBase64Image(imgElement);
    
        const createdBy = loggedInEmployeeId;
        
        // Get current timestamp in format YYYY-MM-DD HH:MM:SS
        const now = new Date();
        const createdAt = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ` +
                          `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
    
        // Pass data to AJAX function
        saveMachineToDatabase(machineName, type, model, manufacturer, manufacturedYear, maintenanceInterval, description, departmentID, imageData, createdBy, createdAt);
    });
    
    function saveMachineToDatabase(machineName, type, model, manufacturer, manufacturedYear, maintenanceInterval, description, departmentID, imageData, createdBy, createdAt) {
        $.ajax({
            url: 'add_machine.php',
            method: 'POST',
            dataType: 'json',
            data: {
                machine_name: machineName,
                machine_type: type,
                machine_model: model,
                machine_manufacturer: manufacturer,
                machine_year_of_manufacture: manufacturedYear,
                machine_maintenance_interval_days: maintenanceInterval,
                machine_description: description,
                department_id: departmentID, // Pass department ID
                machine_created_by: createdBy,
                machine_created_at: createdAt,
                image: imageData
            },
            success: function(response) {
                console.log(response);
    
                const addMachineModal = new bootstrap.Offcanvas(document.getElementById('addMachineModal'));
                addMachineModal.hide();
    
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Raw response:', xhr.responseText);
    
                let response;
                try {
                    response = JSON.parse(xhr.responseText);
                    console.error('Error:', response.errors || response.message);
                    alert('Error adding machine. Check console for details.');
                } catch (e) {
                    console.error('Failed to parse JSON. Raw response:', xhr.responseText);
                    alert('Server returned an invalid response. Check console for details.');
                }
            }
        });
    }    
    
    document.getElementById('addItemImage').addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const maxFileSize = 5 * 1024 * 1024; // 5 MB limit

            if (!allowedFileTypes.includes(file.type)) {
                alert('Invalid file type. Only JPEG and PNG are allowed.');
                return;
            }

            if (file.size > maxFileSize) {
                alert('File size exceeds the limit of 5 MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewItemImage').src = e.target.result; // Set the preview image source
            };
            reader.readAsDataURL(file); // Read the selected file as a data URL
        } else {
            document.getElementById('previewItemImage').src = '../../images/person-circle.png'; // Reset to default image if no file
        }
    });

    // Open confirmation modal for deletion
    document.getElementById('deleteMachineBtn').addEventListener('click', function() {
        // Get machine information
        const machineName = $('#modalMachineNameText').text();
        const machineId = $('#modalMachineCodeText').text();

        // Display machine name in the confirmation modal
        document.getElementById('displayMachineName').textContent = machineName;

        // Show the confirmation modal
        $('#confirmDeleteModal').modal('show');
        
        // Set up confirmation button to handle deletion
        document.getElementById('confirmDeleteBtn').onclick = function() {
            const inputMachineName = document.getElementById('confirmMachineName').value.trim();

            // Check if machine names match
            if (inputMachineName === machineName) {
                $.ajax({
                    url: 'delete_machine.php', // The PHP file that handles deletion
                    method: 'POST',
                    data: { machine_id: machineId }, // Send machine ID for deletion
                    success: function(response) {
                        // Reload the page to reflect changes
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting machine. Please try again.');
                        console.error('Error:', xhr.responseText);
                    }
                });
                $('#confirmDeleteModal').modal('hide'); // Hide modal after confirming
            } else {
                alert('Machine name does not match. Please try again.');
            }
        };
    });


    $('#editMaintenanceScheduleBtn').on('click', function(event) {
        event.preventDefault();  // Prevent default anchor behavior

        $('#machineIntervalModal').modal('show');  // Show the modal

        $('#machineIntervalModal').on('shown.bs.modal', function() {
            $(this).find('input').focus();  // Focus on an input field inside the modal (optional)
        });
    });

    $('#editQuantityBtn').on('click', function(event) {
        event.preventDefault();  // Prevent default anchor behavior
        window.location.href = '../adjustments/index.php?openModal=true';
    });

    const hamBurger = document.querySelector(".toggle-btn");
        hamBurger.addEventListener("click", function () {
        document.querySelector("#sidebar").classList.toggle("expand");
    });
});