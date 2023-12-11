
// search bar 

// Get a reference to the search button and search input
const searchButton = document.querySelector(".searchBtn");
const searchInput = document.querySelector(".searchInput");

// Function to toggle the search input on click
function toggleSearchInput(event) {
  // Prevent the anchor tag from navigating
  event.preventDefault();

  // Toggle visibility of the search button and input
  searchButton.style.display = "none";
  searchInput.style.display = "flex";
  searchInput.focus();

  // Remove the search input when clicking outside
  document.addEventListener("click", function (event) {
    if (event.target !== searchInput && event.target !== searchButton) {
      searchButton.style.display = "flex";
      searchInput.style.display = "none";
    }
  });
}

// Add a click event listener to the search button
searchButton.addEventListener("click", toggleSearchInput);