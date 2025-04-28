document.addEventListener('DOMContentLoaded', function () {
  const hamBurger = document.querySelector(".toggle-btn");

  hamBurger.addEventListener("click", function () {
    const sidebar = document.querySelector("#sidebar");
    const submenu = document.querySelector("#machines");

    sidebar.classList.toggle("expand");

    // Check if the parent link has the "active" class
    const parentLink = document.querySelector(".sidebar-item .has-dropdown.active");
    
    if (parentLink && submenu) {
      submenu.classList.add("show"); // Adds "show" class to open the submenu
    } else {
      submenu.classList.remove("show"); // Removes "show" if the active class is not there
    }
  });

});