document.addEventListener('DOMContentLoaded', function () {
    const editUserBtn = document.getElementById('editUserBtn');
    const editImageBtn = document.getElementById('editImageBtn');
    const removeImageBtn = document.getElementById('removeImageBtn');

    const modalInputFirstName = document.getElementById('modalFirstName');
    const modalInputMiddleName = document.getElementById('modalMiddleName');
    const modalInputLastName = document.getElementById('modalLastName');

    const modalInputRole = document.getElementById('modalRole');
    const modalInputDepartment = document.getElementById('modalDepartment');

    let isEditing = false; // Track if the modal is in edit mode

    const userTable = $('#userTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        processing: true,
        searching: true,
        order: [],
        columnDefs: [{ orderable: false, targets: [0] }]
    });

    function showInfoModal(title, message) {
        document.querySelector('#infoModal .modal-title').textContent = title;
        document.querySelector('#infoModal .modal-body p').textContent = message;
    
        // Use Bootstrap's JavaScript API to show the modal
        const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
        infoModal.show();
    }

    // Disable image edit/remove buttons initially
    editImageBtn.disabled = true;
    removeImageBtn.disabled = true;

    // Disable input fields initially
    disableModalInputs();

    // Enable image buttons when the edit button is clicked
    editUserBtn.addEventListener('click', function () {
        if (!isEditing) {
            // Switch to edit mode
            editUserBtn.textContent = 'Save';
            editImageBtn.disabled = false;
            removeImageBtn.disabled = false;
            // Enable input fields for editing
            enableModalInputs();
            isEditing = true;
        } else {
            // Save changes and switch back to view mode
            const updatedUser = getModalInputValues();
            saveUserData(updatedUser); // Call save function
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
        var imageElement = document.getElementById('userProfileImage'); // Get the image element
        var imageData = ''; // Initialize imageData as an empty string

        // Check if the image element has a valid source
        if (imageElement && imageElement.src) {
            imageData = getBase64Image(imageElement); // Convert image to base64
        }

        return {
            first_name: modalInputFirstName.value,
            middle_name: modalInputMiddleName.value,
            last_name: modalInputLastName.value,
            role: modalInputRole.value,
            department: modalInputDepartment.value,
            employeeId: $('#modalEmployeeIdText').text(), // Assuming Employee ID is stored here

            // Add base64 encoded image
            image: imageData
        };
    }


    // Save updated data to the database
    function saveUserData(user) {
        $.ajax({
            url: 'update_user.php', // Adjust this to your endpoint
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

                editUserBtn.textContent = 'Edit'; // Change button back to "Edit"
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown); // Detailed logging
                console.error('Response text:', jqXHR.responseText); // Log response text

                showInfoModal('Error', `Failed to update user. Please try again. Error: ${textStatus}`);
            }
        });
    }

    // Function to enable input fields
    function enableModalInputs() {
        modalInputFirstName.disabled = false;
        modalInputMiddleName.disabled = false;
        modalInputLastName.disabled = false;
        modalInputRole.disabled = false;
        modalInputDepartment.disabled = false;
    }

    // Function to disable input fields
    function disableModalInputs() {
        modalInputFirstName.disabled = true;
        modalInputMiddleName.disabled = true;
        modalInputLastName.disabled = true;
        modalInputRole.disabled = true;
        modalInputDepartment.disabled = true;
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
                    showInfoModal('Error', 'Only JPEG, PNG, and JPG images are allowed');
                    return;
                }

                if (fileSize > maxFileSize) {
                    showInfoModal('Error', 'Maximum file size is 20 MB');
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
                        document.getElementById('userProfileImage').src = canvas.toDataURL('image/jpeg');

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
            $('#userProfileImage').attr('src', '../../images/person-circle.png'); // Put the default image
            $('#removeImageModal').modal('hide'); // Hide the modal
        });
    });

    // Select all checkboxes when the header checkbox is clicked
    $('#selectAll').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
    });

    // Open modal and populate it with user data when a row is clicked
    $('#userTable tbody').on('click', 'tr', function (event) {
        const checkbox = $(this).find('.row-checkbox');

        // Toggle checkbox state and row selection
        checkbox.prop('checked', !checkbox.prop('checked'));
        $(this).toggleClass('selected', checkbox.prop('checked'));

        // Check how many rows are selected
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount === 1) {
            // Reset edit button and state if in editing mode
            if (isEditing) {
                isEditing = false; // Reset edit mode
                editUserBtn.textContent = 'Edit'; // Reset button text
            }
            
            editImageBtn.disabled = true;
            removeImageBtn.disabled = true;

            // Extract data from the selected row
            const name = $(this).find('td:eq(1) span').text();

            const first_name = $(this).data('first-name');
            const middle_name = $(this).data('middle-name');
            const last_name = $(this).data('last-name');

            const role = $(this).find('td:eq(2)').text();
            const department = $(this).find('td:eq(3)').text();
            const employeeId = $(this).data('employee-id');
            const dateCreated = $(this).data('date-created');
            const imgSrc = $(this).data('image');

            // Populate the modal with user data
            $('#modalUserNameText').text(name);  // Display the user name
            $('#modalEmployeeIdText').text(`${employeeId}`);

            // Ensure input fields are populated and disabled
            modalInputFirstName.value = first_name;
            modalInputMiddleName.value = middle_name;
            modalInputLastName.value = last_name;

            modalInputRole.value = role;
            modalInputDepartment.value = department;

            // Show the date created as text
            $('#modalDateCreated').html(`<strong>Account Created:</strong><br>${dateCreated}`);

            // Set the user profile image or default icon
            $('#userProfileImage').attr('src', imgSrc || '../../images/person-circle.png');

            // Disable inputs before showing the modal
            disableModalInputs();

            // Open the offcanvas modal
            const modal = new bootstrap.Offcanvas('#userInfoModal');
            modal.show();
        }
        else {
            // Reset edit button and state if in editing mode
            if (isEditing) {
                isEditing = false; // Reset edit mode
                editUserBtn.textContent = 'Edit'; // Reset button text
            }

        }
    });

    // Prevent opening the modal if multiple rows are selected
    $('.row-checkbox').on('change', function () {
        const selectedCount = $('.row-checkbox:checked').length;

        if (selectedCount > 1) {
            const modal = bootstrap.Offcanvas.getInstance('#userInfoModal');
            if (modal) modal.hide();
        }
    });

    // Open the Add User offcanvas modal
    document.getElementById('addUserBtn').addEventListener('click', function() {
        const addUserModal = new bootstrap.Offcanvas(document.getElementById('addUserModal'));
        addUserModal.show();
    });

    // Save User functionality
    document.getElementById('saveUserBtn').addEventListener('click', function() {
        // Collect input values
        const employeeId = document.getElementById('addEmployeeId').value; // Get employee ID
        const firstName = document.getElementById('addFirstName').value;
        const middleName = document.getElementById('addMiddleName').value;
        const lastName = document.getElementById('addLastName').value;
        const role = document.getElementById('addRole').value;
        const department = document.getElementById('addDepartment').value;
        
        // Get the preview image element
        const imgElement = document.getElementById('previewUserImage');
        let imageData = getBase64Image(imgElement);

        // Save the user to the database with the image data (if available)
        saveUserToDatabase(employeeId, firstName, middleName, lastName, role, department, imageData);
    });

    // Function to save user to the database
    function saveUserToDatabase(employeeId, firstName, middleName, lastName, role, department, imageData) {
        $.ajax({
            url: 'add_user.php', // Adjust this to your endpoint
            method: 'POST',
            data: {
                employee_id: employeeId, // Include employee ID
                first_name: firstName,
                middle_name: middleName,
                last_name: lastName,
                role: role,
                department: department,
                image: imageData // Send the base64 image
            },
            success: function(response) {
                const parsedResponse = JSON.parse(response); // Parse the JSON response
                showInfoModal('Success', 'User added successfully! The password is: ' + parsedResponse.password);
                const addUserModal = new bootstrap.Offcanvas(document.getElementById('addUserModal'));
                addUserModal.hide();
            },
            error: function(xhr, status, error) {
                // Log the error response in the console
                const response = JSON.parse(xhr.responseText);
                console.error('Error:', response.errors || response.message);
                showInfoModal('Error', 'Please try again.');
            }
        });
    }

    document.getElementById('addUserImage').addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const allowedFileTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            const maxFileSize = 5 * 1024 * 1024; // 5 MB limit

            if (!allowedFileTypes.includes(file.type)) {
                showInfoModal('Error', 'Invalid file type. Only JPEG and PNG are allowed.');
                return;
            }

            if (file.size > maxFileSize) {
                showInfoModal('Error', 'File size exceeds the limit of 5 MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewUserImage').src = e.target.result; // Set the preview image source
            };
            reader.readAsDataURL(file); // Read the selected file as a data URL
        } else {
            document.getElementById('previewUserImage').src = '../../images/person-circle.png'; // Reset to default image if no file
        }
    });

    // Open confirmation modal for deletion
    document.getElementById('deleteUserBtn').addEventListener('click', function() {
        // Get user information
        const firstName = modalInputFirstName.value; // User's first name
        const lastName = modalInputLastName.value; // User's last name
        const employeeId = $('#modalEmployeeIdText').text(); // Get employee ID from the modal

        // Check if the user trying to delete is the logged-in admin
        if (employeeId === loggedInEmployeeId) {
            // Display error message in alert modal
            $('#alertModal').find('.modal-title').text('Error');
            $('#alertModal').find('.modal-text').text('You cannot delete yourself.');
            $('#alertModal').modal('show');
        } else {
            // Display the user's names in the confirmation modal
            document.getElementById('displayFirstName').textContent = firstName;
            document.getElementById('displayLastName').textContent = lastName;

            // Show the confirmation modal
            $('#confirmDeleteModal').modal('show');

            // Confirm delete action
            document.getElementById('confirmDeleteBtn').onclick = function() {
                const inputFirstName = document.getElementById('confirmFirstName').value;
                const inputLastName = document.getElementById('confirmLastName').value;

                // Check if input names match
                if (inputFirstName === firstName && inputLastName === lastName) {
                    $.ajax({
                        url: 'delete_user.php', // The PHP file that handles deletion
                        method: 'POST',
                        data: { employee_id: employeeId }, // Send employee ID for deletion
                        success: function(response) {
                            // Reload the page to reflect changes
                            window.location.reload();
                        },
                        error: function(xhr, status, error) {
                            showInfoModal('Error', 'Error deleting user. Please try again.');
                            console.error('Error:', xhr.responseText);
                        }
                    });
                    $('#confirmDeleteModal').modal('hide'); // Hide modal after confirming
                } else {
                    alert('The names do not match. Please try again.');
                }
            };
        }
    });

});
const hamBurger = document.querySelector(".toggle-btn");

hamBurger.addEventListener("click", function () {
document.querySelector("#sidebar").classList.toggle("expand");
});