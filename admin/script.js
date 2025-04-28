document.addEventListener('DOMContentLoaded', function () {
    const hamBurger = document.querySelector(".toggle-btn");
    const addAnnouncementModal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));

    hamBurger.addEventListener("click", function () {
        document.querySelector("#sidebar").classList.toggle("expand");
    });
    
    function showInfoModal(title, message) {
        document.querySelector('#infoModal .modal-title').textContent = title;
        document.querySelector('#infoModal .modal-body p').textContent = message;
    
        // Use Bootstrap's JavaScript API to show the modal
        const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
        infoModal.show();
    }    
    
    const mainContentLinks = {
        dashboard: document.getElementById("dashboard-link"),
        announcement: document.getElementById("announcements-link")
    };

    const contentSections = {
        dashboard: document.getElementById("dashboard-content"),
        announcement: document.getElementById("announcement-content")
    };

    function setActiveLink(linkId) {
        Object.values(mainContentLinks).forEach(link => link.classList.remove("active"));
        mainContentLinks[linkId].classList.add("active");
    }

    function showContent(sectionId) {
        Object.values(contentSections).forEach(section => section.classList.remove("active"));
        contentSections[sectionId].classList.add("active");
    }

    mainContentLinks.dashboard.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveLink("dashboard");
        showContent("dashboard");
    });

    mainContentLinks.announcement.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveLink("announcement");
        showContent("announcement");
    });

      // Fetch announcements from fetch_announcements.php
  fetch('fetch_announcements.php')
  .then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return response.json(); // Parse JSON
  })
  .then(data => {
    // Check if the data is an array before attempting forEach
    if (Array.isArray(data)) {
      const timelineContainer = document.querySelector('.timeline');
      data.forEach(announcement => {
        const item = document.createElement('div');
        item.classList.add('timeline-item');

        // Format the date as "02 Aug 2024"
        const date = new Date(announcement.created_at);
        const formattedDate = date.toLocaleDateString('en-GB', {
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        });

        const formattedContent = announcement.content.replace(/\r\n|\n|\r/g, '<br>');

        item.innerHTML = `
            <div class="row">
              <div class="col-1">
                <div class="timeline-date"><span>${formattedDate}</span></div>
              </div>
              <div class="col-11">
                <div class="timeline-content">
                  <div class="icon-class"><img src="../images/megaphone.png" alt="Announcement Icon" class="icon-img"></div>
                  <div class="content-text">
                    <h3>${announcement.title}</h3>
                    <p>${formattedContent}</p>
                  </div>
                </div>
              </div>
            </div>
        `;
        timelineContainer.appendChild(item);
      });
    } else {
      console.error("Unexpected response format:", data);
    }
  })
  .catch(error => {
    console.error('Error fetching announcements:', error);
  });

    function fetchData() {
        fetch('fetch_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    console.error(data.message);
                } else {
                    // Populate data from fetch_data.php response
                    document.getElementById('items-on-hand-text').textContent = data.totalQuantity;
                    document.getElementById('this-month-count-text').textContent = data.thisMonthCount;
                    document.getElementById('low-stock-text').textContent = data.lowStockCount;
                    
                    // Calculate and display percentage change
                    const percentageChangeElement = document.getElementById('percentage-change');
                    const percentageChange = data.percentageChange;
                    const hasLastMonthCount = data.hasOwnProperty('lastMonthCount') && data.lastMonthCount > 0;
    
                    // Determine output based on availability of last month's data
                    if (hasLastMonthCount) {
                        // Display percentage change with up/down arrow as before
                        if (percentageChange > 0) {
                            percentageChangeElement.innerHTML = `<span class="up-arrow"> ${percentageChange.toFixed(1)}% this month</span>`;
                        } else if (percentageChange < 0) {
                            percentageChangeElement.innerHTML = `<span class="down-arrow"> ${Math.abs(percentageChange).toFixed(1)}% this month</span>`;
                        } else {
                            percentageChangeElement.textContent = 'No change this month';
                        }
                    } else {
                        // Display "New" if no previous data is available
                        percentageChangeElement.textContent = 'New this month';
                    }
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }
      
    fetch('fetch_progress_bar.php')
        .then(response => response.json())
        .then(data => {
            if (data.donePercentage !== undefined) {
                const progressCircle = document.querySelector('.radial-progress .progress');
                // const progressText = document.querySelector('.radial-progress .progress-text');
                const progressText = 45;
                
                // Calculate the stroke-dashoffset based on the percentage
                const radius = 54;
                const circumference = 2 * Math.PI * radius;
                const offset = circumference - (data.donePercentage / 100) * circumference;

                // Set the stroke-dasharray and stroke-dashoffset for animation
                progressCircle.style.strokeDasharray = `${circumference}`;
                progressCircle.style.strokeDashoffset = offset;

                // Update the text inside the circle with the percentage
                progressText.textContent = `${data.donePercentage}%`;
            } else {
                console.error('donePercentage not found in response data.');
            }
        })
        .catch(error => console.error('Error fetching data:', error));

    // Load data on page load
    fetchData();

    // Load dashboard by default
    setActiveLink("dashboard");
    showContent("dashboard");

    // Open modal when "Add announcement" button is clicked
    document.getElementById('addAnnouncementBtn').addEventListener('click', function () {
        addAnnouncementModal.show();
    });

    // Handle form submission
    document.getElementById('saveAnnouncementBtn').addEventListener('click', function () {
        const form = document.getElementById('announcementForm');
        const formData = new FormData(form);

        fetch('add_announcement.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addAnnouncementModal.hide();
                showInfoModal('Success', 'Announcement added successfully.');
                // Reload the page after 3 seconds (3000 milliseconds)
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showInfoModal('Error', 'Failed to add announcement');
            }
        })
        .catch(error => {
            showInfoModal('Error', 'An error occurred while adding the announcement');
        });
    });
    
})