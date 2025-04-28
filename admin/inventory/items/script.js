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

    const itemTable = $('#itemTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        processing: true,
        searching: true,
        order: [],
        columnDefs: [{ orderable: false, targets: [0] }]
    });

    // Disable image edit/remove buttons initially
    editImageBtn.disabled = true;
    removeImageBtn.disabled = true;

    // Disable input fields initially
    disableModalInputs();

    // Enable image buttons when the edit button is clicked
    editItemBtn.addEventListener('click', function () {
        if (!isEditing) {
            // Switch to edit mode
            editItemBtn.textContent = 'Save';
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
                editItemBtn.textContent = 'Edit'; // Reset button text
            }
            
            editImageBtn.disabled = true;
            removeImageBtn.disabled = true;

            // Extract data from the selected row
            const code = $(this).data('item-code');
            const name = $(this).data('item-name');
            const quantity = $(this).data('item-quantity');
            const sizePerUnit = $(this).data('item-size-per-unit');
            const unit = $(this).data('item-unit');
            const reorderPoint = $(this).data('item-reorder-point');
            const description = $(this).data('item-description');
            const createdBy = $(this).data('item-created-by-name') + " (" + $(this).data('item-created-by') + ")";
            const createdAt = $(this).data('item-created-at');
            const imgSrc = $(this).data('image');

            // Populate the modal with item data
            $('#modalItemNameText').text(name);  // Display the item name
            $('#modalItemCodeText').text(code); // Display the item code

            // Set the item  image or default icon
            $('#itemProfileImage').attr('src', imgSrc || '../../../images/gallery.png');

            modalItemName.value = name;
            modalSizePerUnit.value = sizePerUnit;
            modalUnit.value = unit;
            modalDescription.value = description;

            $('#modalQuantityText').text(quantity);
            $('#modalReorderPointText').text(reorderPoint);
            $('#modalCreatedByNameText').text(createdBy);
            $('#modalCreatedAtText').text(createdAt);

            fetchReorderPoint(code);
            fetchQuantityNeeded(code);

            $('#reorderPointModalInput').value = reorderPoint;

            // Disable inputs before showing the modal
            disableModalInputs();

            // Open the offcanvas modal
            const modal = new bootstrap.Offcanvas('#itemInfoModal');
            modal.show();
        }
        else {
            // Reset edit button and state if in editing mode
            if (isEditing) {
                isEditing = false; // Reset edit mode
                editItemBtn.textContent = 'Edit'; // Reset button text
            }

        }
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
                    $('#reorderPointText').text('Unavailable');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching reorder point:', error);
                $('#reorderPointText').text('Error fetching reorder point.');
            }
        });
    }

    function fetchQuantityNeeded(itemCode) {
        $.ajax({
            url: 'fetch_quantity_needed.php', // Adjust this to the correct path of your PHP script
            method: 'GET',
            dataType: 'json',
            data: { item_code: itemCode },
            success: function(response) {
                // Check if the response contains the total quantity
                if (response.total_quantity !== undefined) {
                    if (response.total_quantity === 0) {
                        $('#modalNeededQuantityText').text('0');
                    } else {
                        $('#modalNeededQuantityText').text(response.total_quantity); // Display the total quantity
                    }
                } else {
                    console.error('Total quantity not found in response:', response);
                    $('#modalNeededQuantityText').text('0');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching quantity needed:', error);
                $('#modalNeededQuantityText').text('Unavailable');
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

    // Open the Add User offcanvas modal
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const addItemModal = new bootstrap.Offcanvas(document.getElementById('addItemModal'));
        addItemModal.show();
    });

    // Save Item functionality
    document.getElementById('saveItemBtn').addEventListener('click', function() {
        // Collect input values
        const itemName = document.getElementById('addItemName').value;
        const quantity = document.getElementById('addQuantity').value;
        const sizePerUnit = document.getElementById('addSizePerUnit').value;
        const unit = document.getElementById('addUnit').value;
        const description = document.getElementById('addItemDescription').value;

        // Get the preview image element
        const imgElement = document.getElementById('previewItemImage');
        let imageData = getBase64Image(imgElement);

        const createdBy = loggedInEmployeeId;
        // Save the user to the database with the image data (if available)
        saveItemToDatabase(itemName, quantity, sizePerUnit, unit, description, imageData, createdBy);
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
        if (reorderPoint <= 1000 && reorderPoint >= 1) {
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