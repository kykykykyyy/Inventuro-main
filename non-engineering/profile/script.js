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
    const searchTerm = searchBar.value.toLowerCase();
    const activityLogTable = document.getElementById('activityLogTable');
    const activityRows = activityLogTable.querySelectorAll('tbody tr');

    // Loop through each row in the table body
    activityRows.forEach(row => {
      const dateCell = row.cells[0].textContent.toLowerCase(); // Date
      const activityCell = row.cells[1].textContent.toLowerCase(); // Activity
      const ipCell = row.cells[2].textContent.toLowerCase(); // IP Address

      // Check if any of the cells contain the search term
      if (
        dateCell.includes(searchTerm) ||
        activityCell.includes(searchTerm) ||
        ipCell.includes(searchTerm)
      ) {
        row.style.display = ''; // Show the row if it matches
      } else {
        row.style.display = 'none'; // Hide the row if it doesn't match
      }
    });
  });

  const mainContentLinks = {
      settings: document.getElementById("settings-link"),
      activityLog: document.getElementById("activity-log-link")
  };

  const contentSections = {
      settings: document.getElementById("settings-content"),
      activityLog: document.getElementById("activity-log-content")
  };

  function setActiveLink(linkId) {
      Object.values(mainContentLinks).forEach(link => link.classList.remove("active"));
      mainContentLinks[linkId].classList.add("active");
  }

  function showContent(sectionId) {
      Object.values(contentSections).forEach(section => section.classList.remove("active"));
      contentSections[sectionId].classList.add("active");
  }

  mainContentLinks.settings.addEventListener("click", function (e) {
      e.preventDefault();
      setActiveLink("settings");
      showContent("settings");
  });

  mainContentLinks.activityLog.addEventListener("click", function (e) {
      e.preventDefault();
      setActiveLink("activityLog");
      showContent("activityLog");
  });

  // Load settings by default
  setActiveLink("settings");
  showContent("settings");

  // Fetch activity log from fetch_activity_log.php
  $(document).ready(function() {
    $('#activityLogTable').DataTable({
        dom: '<"d-flex align-items-center custom-toolbar"fB>tip',
        buttons: [
            {
                extend: 'copy',
                text: 'Copy'
            },
            {
                extend: 'collection',
                text: 'Download',
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
        info: true
    });
});
document.getElementById('updateProfileForm').addEventListener('submit', function(event) {
  event.preventDefault(); // Prevent default form submission

  const firstName = document.querySelector('[name="first_name"]').value.trim();
  const lastName = document.querySelector('[name="last_name"]').value.trim();
  const middleName = document.querySelector('[name="middle_name"]').value.trim();
  const currentPassword = document.getElementById('current-password').value.trim();
  const newPassword = document.getElementById('new-password').value.trim();
  const confirmPassword = document.getElementById('confirm-password').value.trim();

  // Basic validation
  if (!firstName || !lastName) {
    showInfoModal('Error', 'First and last names are required.');
    return;
  }
  
  if(currentPassword) {
    if(newPassword !== confirmPassword) {
        showInfoModal('Error', 'New password and confirm password do not match.');
        return;
    }
    else {
      if(newPassword.length < 8) {
        showInfoModal('Error', 'New password must be at least 8 characters long.');
        return;
      }
      if(!newPassword.match(/[a-z]/g)) {
        showInfoModal('Error', 'New Password must contain at least one lowercase letter.');
        return;
      }
      if(!newPassword.match(/[A-Z]/g)) {
        showInfoModal('Error', 'New Password must contain at least one uppercase letter.');
        return;
      }
      if(!newPassword.match(/[0-9]/g)) {
        showInfoModal('Error', 'New Password must contain at least one number.');
        return;
      }
    }
  }

  if(!currentPassword && (newPassword || confirmPassword)) {
    showInfoModal('Error', 'Please enter your current password.');
    return;
  }

  // Prepare form data
  const formData = new FormData();
  formData.append('first_name', firstName);
  formData.append('last_name', lastName);
  formData.append('middle_name', middleName);
  formData.append('current_password', currentPassword);
  formData.append('new_password', newPassword);
  formData.append('confirm_password', confirmPassword);

  // Handle profile image
  const profileImageInput = document.getElementById('profile-picture-input');
  if (profileImageInput.files.length > 0) {
      const file = profileImageInput.files[0];
      const reader = new FileReader();
      reader.onloadend = function() {
          const base64String = reader.result.split(',')[1]; // Get base64 string without header
          formData.append('image', base64String); // Append the base64 image
          submitForm(formData); // Now submit the form
      };
      reader.readAsDataURL(file);
  } else {
      submitForm(formData); // Submit without image if no file selected
  }
});

function submitForm(formData) {
  fetch('update_profile.php', {
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          window.location.reload();
      } else {
        showInfoModal('Error', 'There was an error updating the your profile.');
      }
  })
  .catch(error => {
      console.error("Error updating profile:", error);
  });
}

document.getElementById('toggle-password-visibility').addEventListener('change', function () {
  const passwordFields = [
      document.getElementById('current-password'),
      document.getElementById('new-password'),
      document.getElementById('confirm-password')
  ];
  
  passwordFields.forEach(field => {
      field.type = this.checked ? 'text' : 'password';
  });
});

});
