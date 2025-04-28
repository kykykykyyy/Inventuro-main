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

                showInfoModal('Error', 'There was an error saving the machine.');
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

    function showInfoModal(title, message) {
        document.querySelector('#infoModal .modal-title').textContent = title;
        document.querySelector('#infoModal .modal-body p').textContent = message;
        $('#infoModal').modal('show');
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
                    showInfoModal('Invalid File Type', 'Please upload a JPEG, PNG, or JPG image.');
                    return;
                }

                if (fileSize > maxFileSize) {
                    showInfoModal('File Size', 'File size exceeds the limit of 25 MB.');
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
    });

    // Prevent opening the modal if multiple rows are selected
    $('.row-checkbox').on('change', function () {
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount > 1) {
            const modal = bootstrap.Offcanvas.getInstance('#machineInfoModal');
            if (modal) modal.hide();
        }
    });

    // Switch to the add machine view
    document.getElementById('addNewMachineBtn').addEventListener('click', function() {
        // const addMachineModal = new bootstrap.Offcanvas(document.getElementById('addMachineModal'));
        // addMachineModal.show();
        const defaultView = document.querySelector('.default-view');
        const addMachineView = document.querySelector('.add-machine-view');

        // Toggle visibility
        defaultView.style.display = 'none';
        addMachineView.style.display = 'block';

    });

    document.getElementById('closeAddMachineBtn').addEventListener('click', function() {
        const defaultView = document.querySelector('.default-view');
        const addMachineView = document.querySelector('.add-machine-view');

        // Toggle visibility
        defaultView.style.display = 'block';
        addMachineView.style.display = 'none';

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
    
    // function saveMachineToDatabase(machineName, type, model, manufacturer, manufacturedYear, maintenanceInterval, description, departmentID, imageData, createdBy, createdAt) {
    //     $.ajax({
    //         url: 'add_machine.php',
    //         method: 'POST',
    //         dataType: 'json',
    //         data: {
    //             machine_name: machineName,
    //             machine_type: type,
    //             machine_model: model,
    //             machine_manufacturer: manufacturer,
    //             machine_year_of_manufacture: manufacturedYear,
    //             machine_maintenance_interval_days: maintenanceInterval,
    //             machine_description: description,
    //             department_id: departmentID, // Pass department ID
    //             machine_created_by: createdBy,
    //             machine_created_at: createdAt,
    //             image: imageData
    //         },
    //         success: function(response) {
    //             console.log(response);
    
    //             const addMachineModal = new bootstrap.Offcanvas(document.getElementById('addMachineModal'));
    //             addMachineModal.hide();
    
    //             window.location.reload();
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Raw response:', xhr.responseText);
    
    //             let response;
    //             try {
    //                 response = JSON.parse(xhr.responseText);
    //                 console.error('Error:', response.errors || response.message);
    //                 alert('Error adding machine. Check console for details.');

    //             } catch (e) {
    //                 console.error('Failed to parse JSON. Raw response:', xhr.responseText);
    //                 alert('Server returned an invalid response. Check console for details.');
    //             }
    //         }
    //     });
    // }    
    
    document.getElementById('addItemImage').addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const maxFileSize = 5 * 1024 * 1024; // 5 MB limit

            if (!allowedFileTypes.includes(file.type)) {
                showInfoModal('Invalid File Type', 'Please upload a JPEG, PNG, or JPG image.');
                return;
            }

            if (file.size > maxFileSize) {
                showInfoModal('File Size', 'File size exceeds the limit of 5 MB.');
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
                        showInfoModal('Error', 'There was an error deleting the machine.');
                        console.error('Error:', xhr.responseText);
                    }
                });
                $('#confirmDeleteModal').modal('hide'); // Hide modal after confirming
            } else {
                showInfoModal('Error', 'The names do not match. Please try again.');
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

    function validateStep(step) {
        let isValid = true; // Assume valid until a validation fails
    
        // Define required fields for Step 1
        if (step === 1) {
            const fields = [
                { id: 'machineName', name: 'Machine Name' },
                { id: 'serialNumber', name: 'Serial Number' },
                { id: 'machineDescription', name: 'Machine Description' },
                { id: 'department', name: 'Department' },
                { id: 'manufacturer', name: 'Manufacturer' },
                { id: 'manufacturedDate', name: 'Manufactured Date' }
            ];
        
            let isValid = true; // Initialize validation flag
        
            // Loop through the fields and validate them
            fields.forEach(field => {
                const input = document.getElementById(field.id);
                if (!input.value.trim()) {
                    // Add 'is-invalid' class for invalid fields
                    input.classList.add('is-invalid');
                    isValid = false; // Mark as invalid
                } else {
                    // Remove 'is-invalid' class for valid fields
                    input.classList.remove('is-invalid');
        
                    // Additional validation for the serialNumber field
                    if (field.id === 'serialNumber') {
                        const serialNumber = input.value.trim();
                        if (serialNumber.length !== 12 && serialNumber.length !== 16) {
                            input.classList.add('is-invalid');
                            isValid = false; // Mark as invalid if length condition is not met
                            input.focus();
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    }
        
                    // Additional validation for the manufacturer field
                    if (field.id === 'manufacturer') {
                        const manufacturer = input.value.trim();
                        const newManufacturerInput = document.getElementById('newManufacturer');
                        // Access the select element
                        const manufacturerSelect = document.getElementById('manufacturer');
                        // Get the selected option's text (name)
                        const selectedOptionName = manufacturerSelect.options[manufacturerSelect.selectedIndex].textContent;

                        if (manufacturer === 'other') {
                            if (!newManufacturerInput || newManufacturerInput.value.trim() === '') {
                                newManufacturerInput?.classList.add('is-invalid');
                                isValid = false; // Mark as invalid if input is empty
                                newManufacturerInput?.focus();
                            } else {
                                newManufacturerInput.classList.remove('is-invalid');
                                document.getElementById('providerName').value = newManufacturerInput.value;
                            }
                        } else {
                            if (newManufacturerInput) {
                                newManufacturerInput.classList.add('d-none');
                                newManufacturerInput.classList.remove('is-invalid');
                                newManufacturerInput.value = ''; // Clear the input
                            }
                            document.getElementById('providerName').value = selectedOptionName;
                        }
                    }
                }
            });
        
            return isValid; // Return overall validation result
        }

        // Validation for Step 2
        if (step === 2) {
            const selectedCards = document.querySelectorAll('.card input[type="checkbox"]:checked');

            selectedCards.forEach(cardCheckbox => {
                const card = cardCheckbox.closest('.card');
                const maintenanceField = card.querySelector('input[name="maintenanceInterval"]');
                const replacementField = card.querySelector('input[name="replacementLifespan"]');
                const quantityField = card.querySelector('input[name="quantity"]');
                const otherFields = card.querySelectorAll('.part-details input, .part-details select, .part-details textarea');

                // General validation for all fields
                otherFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                // Additional validation for quantity
                if (quantityField && quantityField.value < 1) {
                    quantityField.classList.add('is-invalid');
                    isValid = false;
                } else if (quantityField) {
                    quantityField.classList.remove('is-invalid');
                }

                // Additional validation for maintenance interval
                if (maintenanceField && maintenanceField.value < 24) {
                    maintenanceField.classList.add('is-invalid');
                    isValid = false;
                } else if (maintenanceField) {
                    maintenanceField.classList.remove('is-invalid');
                }

                // Additional validation for replacement lifespan
                if (replacementField && replacementField.value < 24) {
                    replacementField.classList.add('is-invalid');
                    isValid = false;
                } else if (replacementField) {
                    replacementField.classList.remove('is-invalid');
                }
            });

            // Ensure at least one card is selected
            if (selectedCards.length === 0) {
                showInfoModal('Error', 'Please select at least one part.');
                isValid = false;
            }
        }

        // Validation for Step 3
        if (step === 3) {
            let warrantyToggle = document.getElementById('warrantyToggle')?.checked;
            let isValid = true; // Initialize validation flag
            let focusSet = false; // To set focus only once
        
            if (warrantyToggle) {
                const fields = [
                    { id: 'providerName', name: 'Provider Name' },
                    { id: 'coverageType', name: 'Coverage Type' },
                    { id: 'startDate', name: 'Start Date' },
                    { id: 'expirationDate', name: 'Expiration Date' },
                    { id: 'termsConditions', name: 'Terms and Conditions' },
                    { id: 'warrantyDocument', name: 'Warranty Document' },
                    { id: 'contactName', name: 'Contact Name' },
                    { id: 'contactNumber', name: 'Contact Number' },
                    { id: 'contactEmail', name: 'Contact Email' }
                ];
        
                // Helper function for setting invalid state
                const markInvalid = (input) => {
                    if (!input) return;
                    input.classList.add('is-invalid');
                    if (!focusSet) {
                        input.focus();
                        focusSet = true;
                    }
                    isValid = false;
                };
        
                fields.forEach(field => {
                    const input = document.getElementById(field.id);
                    if (!input?.value.trim()) {
                        markInvalid(input);
                    } else {
                        input.classList.remove('is-invalid');
        
                        if (field.id === 'coverageType') {
                            const coverageType = input.value.trim();
                            if (coverageType === 'otherServices') {
                                const otherServiceTitle = document.getElementById('otherServiceTitle');
                                if (!otherServiceTitle?.value.trim()) {
                                    markInvalid(otherServiceTitle);
                                } else {
                                    otherServiceTitle.classList.remove('is-invalid');
                                }
                            } else if (coverageType === 'specificParts') {
                                const specificPartsList = document.getElementById('specificPartsList');
                                const checkedInputs = specificPartsList?.querySelectorAll('input[type="checkbox"]:checked');
                                if (!checkedInputs || checkedInputs.length === 0) {
                                    markInvalid(input);
                                }
                            }
                        }
                    }
                });
            }
        
            return isValid; // Optionally return the validation result
        }        

        return isValid; // Return whether the step is valid
    }
    
    function nextStep(step) {
        // Validate the current step before proceeding
        const currentStep = step - 1; // Assuming you're coming from the previous step
        if (!validateStep(currentStep)) {
            return; // Stop if validation fails
        }
    
        // Hide all step contents
        document.querySelectorAll('.step').forEach(stepDiv => stepDiv.classList.add('d-none'));
        // Show the selected step content
        document.getElementById(`step${step}`).classList.remove('d-none');
    
        // Reset all buttons to secondary
        document.querySelectorAll('.steps button').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        });
    
        // Highlight the current step's button
        document.getElementById(`step${step}-button`).classList.add('btn-primary');
        document.getElementById(`step${step}-button`).classList.remove('btn-secondary');
    }    
    
    function prevStep(step) {
        // Hide all step contents
        document.querySelectorAll('.step').forEach(stepDiv => stepDiv.classList.add('d-none'));
        // Show the selected step content
        document.getElementById(`step${step}`).classList.remove('d-none');
    
        // Reset all buttons to secondary
        document.querySelectorAll('.steps button').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
        });
    
        // Highlight the current step's button
        document.getElementById(`step${step}-button`).classList.add('btn-primary');
        document.getElementById(`step${step}-button`).classList.remove('btn-secondary');
    }
    
    window.nextStep = nextStep;
    window.prevStep = prevStep;

    const manufacturedDateInput = document.getElementById('manufacturedDate');
    const startDateInput = document.getElementById('startDate');
    const expirationDateInput = document.getElementById('expirationDate');

    // Set current date as initial value and maximum for manufacturedDate
    const today = new Date(); // Today's date as a Date object
    const formattedToday = today.toISOString().split('T')[0]; // Format YYYY-MM-DD
    const tomorrow = new Date(today); // Clone today's date
    tomorrow.setDate(today.getDate() + 1); // Add one day to calculate tomorrow

    // Set default and limits for manufacturedDate
    manufacturedDateInput.value = formattedToday;
    manufacturedDateInput.max = formattedToday;

    // Set default and limits for startDate
    startDateInput.min = formattedToday;
    startDateInput.value = formattedToday;
    startDateInput.max = formattedToday; // Start date cannot be in the future

    // Set minimum for expirationDate
    expirationDateInput.min = tomorrow.toISOString().split('T')[0]; // At least tomorrow

    // Update startDate limits when manufacturedDate changes
    manufacturedDateInput.addEventListener('change', () => {
        const selectedManufacturedDate = new Date(manufacturedDateInput.value);

        // Ensure manufacturedDate cannot set startDate earlier than itself
        startDateInput.min = manufacturedDateInput.value;
        if (new Date(startDateInput.value) < selectedManufacturedDate) {
            startDateInput.value = manufacturedDateInput.value;
        }
    });

    // Update expirationDate limits when startDate changes
    startDateInput.addEventListener('change', () => {
        const selectedStartDate = new Date(startDateInput.value);

        // Calculate the day after the selected start date
        const nextDay = new Date(selectedStartDate);
        nextDay.setDate(selectedStartDate.getDate() + 1);

        // Set expirationDate minimum to the next day
        expirationDateInput.min = nextDay.toISOString().split('T')[0];

        // Clear expiration date if it is invalid
        if (expirationDateInput.value && new Date(expirationDateInput.value) < nextDay) {
            expirationDateInput.value = ""; // Clear invalid expiration date
        }
    });

    // Validate expirationDate when it changes
    expirationDateInput.addEventListener('change', () => {
        const startDate = new Date(startDateInput.value);
        const expirationDate = new Date(expirationDateInput.value);

        // Ensure expirationDate is at least one day after startDate
        const validMinDate = new Date(startDate);
        validMinDate.setDate(validMinDate.getDate() + 1);

        if (expirationDate < validMinDate) {
            showInfoModal('Invalid Expiration Date', 'Expiration date should be at least one day after start date.');
            expirationDateInput.value = ""; // Clear invalid expiration date
        }
    });

    function toggleManufacturerInput() {
        const manufacturerSelect = document.getElementById('manufacturer');
        const newManufacturerInput = document.getElementById('newManufacturer');
    
        // Show input field if "Other" is selected
        if (manufacturerSelect.value === 'other') {
            newManufacturerInput.classList.remove('d-none');
            newManufacturerInput.required = true; // Make it required
        } else {
            newManufacturerInput.classList.add('d-none');
            newManufacturerInput.value = ''; // Clear the input
            newManufacturerInput.required = false; // Remove the required attribute
        }
    }
    
    const manufacturerSelect = document.getElementById('manufacturer');
    manufacturerSelect.addEventListener('change', toggleManufacturerInput);

    function fetchMachineParts() {
        const templateId = document.getElementById('template').value;

        if (!templateId) return;
    
        fetch(`fetch_machine_parts.php?template_id=${templateId}`)
            .then(response => response.json())
            .then(data => {
                const partsList = document.getElementById('machinePartsList');
                partsList.innerHTML = ''; // Clear existing parts
    
                if (data.length === 0) {
                    partsList.innerHTML = '<p>No parts found for this template.</p>';
                } else {
                    data.forEach(part => {
                        const card = document.createElement('div');
                        card.classList.add('card', 'mb-3', 'shadow-sm');
    
                        card.innerHTML = `
                            <div class="card-header d-flex align-items-center">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input me-2" 
                                    id="togglePart_${part.machine_type_parts_id}" 
                                    checked
                                >
                                <h5 class="card-title mb-0">${part.machine_type_parts_name}</h5>
                            </div>
                            <div class="card-body part-details">
                                <p class="card-text">${part.machine_type_parts_description}</p>
                                <div class="mb-3">
                                    <label class="form-label">Quantity:</label>
                                    <input type="number" name="quantity" class="form-control" value="${part.machine_type_parts_quantity || 1}" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Maintenance Interval (operating hours):
                                    <span 
                                        class="text-muted" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="right" 
                                        title="How many hours before the part needs to be maintained/cleaned?">
                                        <i class="bi bi-question-circle"></i>
                                    </span>
                                    </label>
                                    <input type="number" name="maintenanceInterval" class="form-control" value="${part.machine_type_parts_maintenance_interval || 100}" min="24">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Replacement Lifespan (operating hours):
                                    <span 
                                        class="text-muted" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="right" 
                                        title="How many hours before the part needs to be replaced?">
                                        <i class="bi bi-question-circle"></i>
                                    </span>
                                    </label>
                                    <input type="number" name="replacementLifespan" class="form-control" value="${part.machine_type_parts_replacement_lifespan || 1000}" min="24">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Criticality Level:</label>
                                    <select class="form-select">
                                        <option value="Low" ${part.machine_type_parts_criticality_level === 'Low' ? 'selected' : ''}>Low</option>
                                        <option value="Medium" ${part.machine_type_parts_criticality_level === 'Medium' ? 'selected' : ''}>Medium</option>
                                        <option value="High" ${part.machine_type_parts_criticality_level === 'High' ? 'selected' : ''}>High</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Maintenance Instructions:</label>
                                    <textarea class="form-control">${part.machine_type_parts_instructions || 'No specified instructions'}</textarea>
                                </div>
                            </div>
                        `;
    
                        partsList.appendChild(card);
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
                        // Add event listener to handle toggling of part details
                        const toggleCheckbox = card.querySelector('.form-check-input');
                        const partDetails = card.querySelector('.part-details');
                        toggleCheckbox.addEventListener('change', () => {
                            partDetails.style.display = toggleCheckbox.checked ? 'block' : 'none';
                        });
                    });
                }
            })
            .catch(error => {
                const partsList = document.getElementById('machinePartsList');
                partsList.innerHTML = `<p class="text-danger">Error fetching machine parts: ${error.message}</p>`;
            });
        document.getElementById('nextStepButton3').disabled = false;
        document.getElementById('addPartButton').classList.remove('d-none');
    }               

    document.getElementById('template').addEventListener('change', fetchMachineParts);
    
    document.getElementById('addPartButton').addEventListener('click', () => {
        // Show the form for adding a new part
        document.getElementById('addPartForm').classList.remove('d-none');
    });
    
    document.getElementById('cancelPartButton').addEventListener('click', () => {
        // Hide the form and clear inputs
        document.getElementById('addPartForm').classList.add('d-none');
        clearNewPartForm();
    });
    
    function validateNewPartForm() {
        let isValid = true; // Assume valid until a validation fails
    
        // Get input fields
        const nameField = document.getElementById('newPartName');
        const descriptionField = document.getElementById('newPartDescription');
        const quantityField = document.getElementById('newPartQuantity');
        const maintenanceIntervalField = document.getElementById('newPartMaintenanceInterval');
        const replacementLifespanField = document.getElementById('newPartReplacementLifespan');
        const criticalityLevelField = document.getElementById('newPartCriticalityLevel');
        const instructionsField = document.getElementById('newPartInstructions');
    
        // Validate fields and apply 'is-invalid' class if needed
        if (!nameField.value.trim()) {
            nameField.classList.add('is-invalid');
            isValid = false;
        } else {
            nameField.classList.remove('is-invalid');
        }
    
        if (!descriptionField.value.trim()) {
            descriptionField.classList.add('is-invalid');
            isValid = false;
        } else {
            descriptionField.classList.remove('is-invalid');
        }
    
        if (!quantityField.value || quantityField.value < 1) {
            quantityField.classList.add('is-invalid');
            isValid = false;
        } else {
            quantityField.classList.remove('is-invalid');
        }
    
        if (!maintenanceIntervalField.value || maintenanceIntervalField.value < 24) {
            maintenanceIntervalField.classList.add('is-invalid');
            isValid = false;
        } else {
            maintenanceIntervalField.classList.remove('is-invalid');
        }
    
        if (!replacementLifespanField.value || replacementLifespanField.value < 24) {
            replacementLifespanField.classList.add('is-invalid');
            isValid = false;
        } else {
            replacementLifespanField.classList.remove('is-invalid');
        }
    
        if (!criticalityLevelField.value.trim()) {
            criticalityLevelField.classList.add('is-invalid');
            isValid = false;
        } else {
            criticalityLevelField.classList.remove('is-invalid');
        }
    
        if (!instructionsField.value.trim()) {
            instructionsField.classList.add('is-invalid');
            isValid = false;
        } else {
            instructionsField.classList.remove('is-invalid');
        }
    
        return isValid; // Return overall validity
    }

    document.getElementById('savePartButton').addEventListener('click', () => {

        if (validateNewPartForm()) {
        // Proceed to save the part
        console.log('Form is valid. Proceeding to save the new part.');
        } else {
            console.log('Form is invalid. Please correct the highlighted fields.');
            return;
        }

        // Get input fields
        const nameField = document.getElementById('newPartName');
        const descriptionField = document.getElementById('newPartDescription');
        const quantityField = document.getElementById('newPartQuantity');
        const maintenanceIntervalField = document.getElementById('newPartMaintenanceInterval');
        const replacementLifespanField = document.getElementById('newPartReplacementLifespan');
        const criticalityLevelField = document.getElementById('newPartCriticalityLevel');
        const instructionsField = document.getElementById('newPartInstructions');

        const name = nameField.value.trim();
        const description = descriptionField.value.trim();
        const quantity = quantityField.value;
        const maintenanceInterval = maintenanceIntervalField.value;
        const replacementLifespan = replacementLifespanField.value;
        const criticalityLevel = criticalityLevelField.value;
        const instructions = instructionsField.value.trim();
        
        // Create a new card for the part
        const card = document.createElement('div');
        card.classList.add('card', 'mb-3', 'shadow-sm');
    
        card.innerHTML = `
            <div class="card-header d-flex align-items-center">
                <input 
                    type="checkbox" 
                    class="form-check-input me-2" 
                    checked
                >
                <h5 class="card-title mb-0">${name}</h5>
            </div>
            <div class="card-body part-details">
                <p class="card-text">${description}</p>
                <div class="mb-3">
                    <label class="form-label">Quantity:</label>
                    <input type="number" name="quantity" class="form-control" value="${quantity}" min="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maintenance Interval (operating hours):
                    <span 
                        class="text-muted" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="right" 
                        title="How many hours before the part needs to be maintained/cleaned?">
                        <i class="bi bi-question-circle"></i>
                    </span>
                    </label>
                    <input type="number" name="maintenanceInterval" class="form-control" value="${maintenanceInterval}" min="24">
                </div>
                <div class="mb-3">
                    <label class="form-label">Replacement Lifespan (operating hours):
                    <span 
                        class="text-muted" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="right" 
                        title="How many hours before the part needs to be replaced?">
                        <i class="bi bi-question-circle"></i>
                    </span>
                    </label>
                    <input type="number" name="replacementLifespan" class="form-control" value="${replacementLifespan}" min="24">
                </div>
                <div class="mb-3">
                    <label class="form-label">Criticality Level:</label>
                    <select class="form-select">
                        <option value="Low" ${criticalityLevel === 'Low' ? 'selected' : ''}>Low</option>
                        <option value="Medium" ${criticalityLevel === 'Medium' ? 'selected' : ''}>Medium</option>
                        <option value="High" ${criticalityLevel === 'High' ? 'selected' : ''}>High</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Maintenance Instructions:</label>
                    <textarea class="form-control">${instructions}</textarea>
                </div>
            </div>
        `;
    
        // Add the new card to the parts list
        document.getElementById('machinePartsList').appendChild(card);

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Add functionality to the checkbox
        const toggleCheckbox = card.querySelector('.form-check-input');
        const partDetails = card.querySelector('.part-details');
        toggleCheckbox.addEventListener('change', () => {
            partDetails.style.display = toggleCheckbox.checked ? 'block' : 'none';
        });

        // Hide the form and clear inputs
        document.getElementById('addPartForm').classList.add('d-none');
        clearNewPartForm();
    });
    
    // Utility function to clear the form inputs
    function clearNewPartForm() {
        document.getElementById('newPartName').value = '';
        document.getElementById('newPartDescription').value = '';
        document.getElementById('newPartQuantity').value = '1';
        document.getElementById('newPartMaintenanceInterval').value = '100';
        document.getElementById('newPartReplacementLifespan').value = '1000';
        document.getElementById('newPartCriticalityLevel').value = 'Low';
        document.getElementById('newPartInstructions').value = '';
    }

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Toggle Warranty Details Section
    function toggleWarrantyDetails() {
        const warrantyDetails = document.getElementById('warrantyDetails');
        const warrantyToggle = document.getElementById('warrantyToggle');
        warrantyDetails.style.display = warrantyToggle.checked ? 'block' : 'none';
        notifyWarranty.style.display = warrantyToggle.checked ? 'block' : 'none';
    }

    // Handle Covered Parts Toggle
    function togglePartWarranty(checkbox) {
        const partCovered = checkbox.checked;
        if (partCovered) {
            console.log("This part is covered under warranty.");
            // Link part to warranty record in the system (implement backend logic)
        } else {
            console.log("This part is not covered under warranty.");
            // Handle exclusion logic
        }
    }

    const warrantySelect = document.getElementById('warrantyToggle');
    warrantySelect.addEventListener('change', toggleWarrantyDetails);

    const warrantyDocumentInput = document.getElementById('warrantyDocument');

    warrantyDocumentInput.addEventListener('change', () => {
        const file = warrantyDocumentInput.files[0]; // Get the selected file

        if (file) {
            const maxSizeInBytes = 25 * 1024 * 1024; // 25MB in bytes

            // Check file type
            const allowedExtensions = ['pdf', 'png', 'jpg'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                showInfoModal('Invalid File Type', 'Please upload a PDF, PNG, or JPG file.');
                warrantyDocumentInput.value = ''; // Clear the input
                return;
            }

            // Check file size
            if (file.size > maxSizeInBytes) {
                showInfoModal('File Size Limit Exceeded', 'Please select a file smaller than 25MB.');
                warrantyDocumentInput.value = ''; // Clear the input
                return;
            }
        }
    });

    const clearFileButton = document.getElementById('clearFile');

    // Clear the file input when the button is clicked
    clearFileButton.addEventListener('click', () => {
        warrantyDocumentInput.value = ''; // Reset the file input
    });

    function handleCoverageTypeChange() {
        const coverageType = document.getElementById('coverageType').value;
        const specificPartsContainer = document.getElementById('specificPartsContainer');
        const otherServicesContainer = document.getElementById('otherServicesContainer');
    
        if (coverageType === 'specificParts') {
            specificPartsContainer.classList.remove('d-none');
            otherServicesContainer.classList.add('d-none');
            fetchSpecificPartsFromFrontend(); // Fetch from machinePartsList in the DOM
        } else if (coverageType === 'otherServices') {
            specificPartsContainer.classList.add('d-none');
            otherServicesContainer.classList.remove('d-none');
        } else {
            specificPartsContainer.classList.add('d-none');
            otherServicesContainer.classList.add('d-none');
        }
    }    
    
    function fetchSpecificPartsFromFrontend() {
        const specificPartsList = document.getElementById('specificPartsList');
        const machinePartsList = document.getElementById('machinePartsList');
    
        specificPartsList.innerHTML = ''; // Clear the existing list
    
        // Iterate through all cards in machinePartsList
        const parts = machinePartsList.querySelectorAll('.card');
        parts.forEach(partCard => {
            // Get the part name and ID from the card
            const checkbox = partCard.querySelector('.form-check-input');
            const title = partCard.querySelector('.card-title');
    
            if (checkbox && checkbox.checked && title) {
                const partId = checkbox.id.replace('togglePart_', ''); // Unique ID for the part
                const partName = title.textContent.trim(); // Part name
    
                // Create a checkbox for specificPartsList
                const checkboxWrapper = document.createElement('div');
                checkboxWrapper.classList.add('form-check');
    
                checkboxWrapper.innerHTML = `
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="specificPart_${partId}" 
                        value="${partId}"
                    >
                    <label class="form-check-label" for="specificPart_${partId}">
                        ${partName}
                    </label>
                `;
    
                specificPartsList.appendChild(checkboxWrapper);
            }
        });
    
        // If no parts are found, show a message
        if (specificPartsList.children.length === 0) {
            specificPartsList.innerHTML = '<p>No parts available.</p>';
        }
    }    
    
    document.getElementById('coverageType').addEventListener('change', handleCoverageTypeChange);    

    function fetchFinalValues() {
        const partsList = document.getElementById('machinePartsList');
        const selectedParts = [];
    
        // Get warranty information
        const warrantyToggle = document.getElementById('warrantyToggle')?.checked;
        let coverageType = null;
        let warrantyCoveredPartsIds = new Set();
    
        if (warrantyToggle) {
            coverageType = document.getElementById('coverageType')?.value;
    
            if (coverageType === 'specificParts') {
                const specificPartsList = document.getElementById('specificPartsList');
                if (specificPartsList) {
                    const checkedWarrantyCheckboxes = specificPartsList.querySelectorAll('input[type="checkbox"]:checked');
                    checkedWarrantyCheckboxes.forEach(checkbox => {
                        const partId = checkbox.value;
                        warrantyCoveredPartsIds.add(partId);
                    });
                }
            }
        }
    
        // Iterate over each part card
        const parts = partsList.querySelectorAll('.card');
        parts.forEach(card => {
            const checkbox = card.querySelector('.form-check-input');
            if (checkbox && checkbox.checked) {
                const partId = checkbox.id.replace('togglePart_', '');
                const part = {
                    id: partId,
                    name: card.querySelector('.card-title')?.textContent.trim(),
                    description: card.querySelector('.card-text')?.textContent.trim(),
                    quantity: card.querySelector('input[name="quantity"]')?.value || '1',
                    maintenanceInterval: card.querySelector('input[name="maintenanceInterval"]')?.value || '0',
                    replacementLifespan: card.querySelector('input[name="replacementLifespan"]')?.value || '0',
                    criticalityLevel: card.querySelector('select')?.value || 'Unknown',
                    instructions: card.querySelector('textarea')?.value.trim() || '',
                    warrantyCovered: "False", // Default value
                };
    
                // Determine if this part is covered by warranty
                if (warrantyToggle) {
                    if (coverageType === 'fullMachine') {
                        part.warrantyCovered = "True";
                    } else if (coverageType === 'specificParts') {
                        if (warrantyCoveredPartsIds.has(partId)) {
                            part.warrantyCovered = "True";
                        }
                    }
                    // For 'otherServices' or unhandled cases, warrantyCovered remains "False"
                }
    
                selectedParts.push(part);
            }
        });
    
        return selectedParts;
    }

    // Handle the machine image to Base64 conversion
    let machineImageBase64 = null; // Store the Base64 string globally

    document.getElementById('machineImage').addEventListener('change', (event) => {
        const file = event.target.files[0]; // Get the selected file
        if (file) {
            const reader = new FileReader();
            reader.onload = () => {
                machineImageBase64 = reader.result.split(',')[1]; // Strip metadata prefix
            };
            reader.onerror = () => {
                console.error("Error reading file.");
            };
            reader.readAsDataURL(file); // Convert file to Base64
        } else {
            machineImageBase64 = null; // Reset if no file is selected
        }
    });

    function assignFinalValues() {
        // Step 1 values
        const machineName = document.getElementById('machineName').value.trim();
        const serialNumber = document.getElementById('serialNumber').value.trim();
        const machineDescription = document.getElementById('machineDescription').value.trim();
        const department = document.getElementById('department').value;
        let manufacturer = document.getElementById('manufacturer').value.trim();
    
        const departmentMapping = {
            1: "Tractorco",
            2: "Warehouse",
            4: "Logistics",
            7: "Pre-production",
            8: "Manufacturing",
            9: "Flavoring",
            10: "Packaging",
            11: "IT"
        };
        const manufacturerMapping = {
            1: "Tractorco",
            2: "Zhengzhou Xifu Machinery",
            3: "Food Machinery Industrial Corporation",
            4: "Multico Prime Power Inc",
            5: "JSS Machinery Corporation"
        };
    
        const departmentName = departmentMapping[department] || "Unknown Department";
        const manufacturerName = manufacturer === 'other'
            ? document.getElementById('newManufacturer').value.trim()
            : manufacturerMapping[manufacturer];
    
        const manufacturedDate = document.getElementById('manufacturedDate').value.trim();
        const machineImage = machineImageBase64;
        const machineType = document.getElementById('template').value;
    
        // Step 3 values
        const warrantyToggle = document.getElementById('warrantyToggle')?.checked;
        let warranty = null;
        if (warrantyToggle) {
            warranty = {
                providerName: document.getElementById('providerName').value.trim(),
                coverageType: document.getElementById('coverageType').value.trim(),
                startDate: document.getElementById('startDate').value.trim(),
                expirationDate: document.getElementById('expirationDate').value.trim(),
                termsConditions: document.getElementById('termsConditions').value.trim(),
                contactPerson: document.getElementById('contactName').value.trim(),
                contactNumber: document.getElementById('contactNumber').value.trim(),
                contactEmail: document.getElementById('contactEmail').value.trim(),
                otherServices: document.getElementById('otherServiceTitle').value.trim() || null
            };

            // Populate warranty table
            document.getElementById('warrantyProviderNameText').textContent = warranty.providerName || '[Warranty Provider Name]';
            document.getElementById('warrantyCoverageTypeText').textContent = warranty.coverageType || '[Warranty Coverage Type]';
            document.getElementById('warrantyStartDateText').textContent = warranty.startDate || '[Warranty Start Date]';
            document.getElementById('warrantyExpirationDateText').textContent = warranty.expirationDate || '[Warranty Expiration Date]';
            document.getElementById('warrantyTermsAndConditionsText').textContent = warranty.termsConditions || '[Warranty Terms and Conditions]';
            document.getElementById('warrantyContactPersonText').textContent = warranty.contactPerson || '[Warranty Contact Person]';
            document.getElementById('warrantyContactNumberText').textContent = warranty.contactNumber || '[Warranty Contact Number]';
            document.getElementById('warrantyContactEmailText').textContent = warranty.contactEmail || '[Warranty Contact Email]';
            
            // Show the warranty table
            document.getElementById('warrantyTable').classList.remove('d-none');
        } else {
            // Hide the warranty table
            document.getElementById('warrantyTable').classList.add('d-none');
        }

        // Assign values to the step 4 now
        machineNameText.textContent = machineName;
        machineSerialNumberText.textContent = serialNumber;
        machineDescriptionText.textContent = machineDescription;
        machineDepartmentText.textContent = departmentName;
        machineManufacturerText.textContent = manufacturerName;
        machineManufacturedDateText.textContent = manufacturedDate;

        // Populate the machine parts table
        const machinePartsTableBody = document.querySelector('#machinePartsTable tbody');
        machinePartsTableBody.innerHTML = ''; // Clear existing rows

        const selectedParts = fetchFinalValues(); // Fetch selected parts
        selectedParts.forEach(part => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${part.name}</td>
                <td>${part.description}</td>
                <td>${part.quantity}</td>
                <td>${part.maintenanceInterval}</td>
                <td>${part.replacementLifespan}</td>
                <td>${part.criticalityLevel}</td>
                <td>${part.instructions}</td>
                <td>${part.warrantyCovered}</td>
            `;
            machinePartsTableBody.appendChild(row);
        });

        return {
            machineName,
            machineType,
            serialNumber,
            machineDescription,
            departmentName,
            manufacturerName,
            manufacturedDate,
            machineImage,
            warrantyEnabled: warrantyToggle,
            warranty
        };
    }    

    document.getElementById('nextStepButton4').addEventListener('click', assignFinalValues);

    document.getElementById('submitRepairUpdate').addEventListener('click', (event) => {
        event.preventDefault();
    
        // Validate inputs (reuse your validation logic)
        let isValid = true;
    
        if (!isValid) {
            console.log("Validation failed. Correct the errors.");
            return;
        }
    
        // Collect parts data
        const selectedParts = fetchFinalValues();
    
        // Collect machine and warranty data
        const machineAndWarrantyData = assignFinalValues();
    
        // Collect notification data
        const notificationData = {
            email: document.getElementById('notificationEmail').value.trim(),
            notifyDays: document.getElementById('maintenanceNotifyDays').value.trim(),
            notifyWeeks: document.getElementById('warrantyNotifyWeeks').value.trim()
        };
    
        // Get file inputs
        const machineManual = document.getElementById('machineManual').files[0] || null;
        const warrantyDocument = document.getElementById('warrantyDocument').files[0] || null;
    
        // Build the FormData object
        const formData = new FormData();
    
        // Add JSON data
        formData.append('data', JSON.stringify({
            ...machineAndWarrantyData,
            selectedParts,
            notifications: notificationData,
            created_by: loggedInEmployeeId
        }));
    
        // Add files to FormData
        if (machineManual) {
            formData.append('machineManual', machineManual);
        }
    
        if (warrantyDocument) {
            formData.append('warrantyDocument', warrantyDocument);
        }
    
        // Send the payload to the backend
        fetch('add_new_machine.php', {
            method: 'POST',
            body: formData // FormData automatically sets the correct Content-Type
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Data submitted successfully!');
    
                    // Email the recipient that they will receive a notification
                    const recipientEmail = document.getElementById('notificationEmail').value.trim();
                    const machineName = document.getElementById('machineName').value.trim();
                    const notifyDays = document.getElementById('maintenanceNotifyDays').value.trim();
                    const notifyWeeks = document.getElementById('warrantyNotifyWeeks').value.trim() || 0;
    
                    if (recipientEmail) {
                        $.ajax({
                            url: 'send_email.php', // The PHP file that handles sending emails
                            method: 'POST',
                            data: {
                                recipientEmail: recipientEmail,
                                machineName: machineName,
                                notifyDays: notifyDays,
                                notifyWeeks: notifyWeeks
                            }, // Send recipient email, machine name, and notify days
                            success: function (response) {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    console.log('Email sent successfully:', result.message);
                                    showInfoModal('Success', 'Machine is added successfully.');
                                    window.location.reload();
                                } else {
                                    console.error('Failed to send email:', result.error);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error sending email:', error);
                            }
                        });
                    } else {
                        console.error('Recipient email is required.');
                    }
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });    

});