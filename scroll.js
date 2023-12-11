//Hide navbar when we scroll

// Get a reference to the navbar element
const navbar = document.getElementById("navbar");

// Function to handle scroll event
function handleScroll() {
  // Check the current scroll position
  const currentScrollPos = window.scrollY;
  
  const scrollThreshold = 5;

  // Check if the user has scrolled down
  if (currentScrollPos > scrollThreshold) {
    // Scrolled down: Hide the navbar
    navbar.classList.add("hidden");
  } else {
    // Scrolled to the top: Show the navbar
    navbar.classList.remove("hidden");
  }
}

// Add a scroll event listener to the window
window.addEventListener("scroll", handleScroll);

// Initially, check the scroll position and hide the navbar if necessary
handleScroll();
