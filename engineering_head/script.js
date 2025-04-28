document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
    document.querySelector("#sidebar").classList.toggle("expand");
  });

  // Search functionality
  const searchBar = document.getElementById('search-bar');

  searchBar.addEventListener('input', function () {
    const searchTerm = searchBar.value.toLowerCase();
  
    // Search through recent requests
    const requests = document.querySelectorAll('#dashboard-content .list-group-item');
    requests.forEach(request => {
        const requestId = request.getAttribute('data-request-id').toLowerCase();
        const machine = request.getAttribute('data-machine').toLowerCase();
        const status = request.getAttribute('data-status').toLowerCase();
        const urgency = request.getAttribute('data-urgency').toLowerCase();
        const date = request.getAttribute('data-date').toLowerCase();

        // Check if any of the fields contain the search term
        if (requestId.includes(searchTerm) || machine.includes(searchTerm) || status.includes(searchTerm) || urgency.includes(searchTerm) || date.includes(searchTerm)) {
            request.style.display = ''; // Show matching requests
        } else {
            request.style.display = 'none'; // Hide non-matching requests
        }
    });

    // Search through announcements (if this is needed)
    const announcements = document.querySelectorAll('.timeline-item');
    announcements.forEach(announcement => {
        const date = announcement.querySelector('.timeline-date span').textContent.toLowerCase();
        const title = announcement.querySelector('h3').textContent.toLowerCase();
        const content = announcement.querySelector('p').textContent.toLowerCase();
  
        if (title.includes(searchTerm) || content.includes(searchTerm) || date.includes(searchTerm)) {
            announcement.style.display = ''; // Show matching items
        } else {
            announcement.style.display = 'none'; // Hide non-matching items
        }
    });
  });

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

  // Load dashboard by default
  setActiveLink("dashboard");
  showContent("dashboard");

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
                    <div class="icon"><img src="../images/megaphone.png" alt="Announcement Icon" class="icon-img"></div>
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
});