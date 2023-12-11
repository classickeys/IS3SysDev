// Get references to the buttons and forms
const toggleFormButtons = document.querySelectorAll(".show_form");
const formContainers = document.querySelectorAll(".form_container");

// Add click event listeners to the buttons
toggleFormButtons.forEach((button) => {
    button.addEventListener("click", function () {
        // Get the target form ID
        const targetFormId = this.getAttribute("data-target");
        const targetForm = document.getElementById(targetFormId);

        // Toggle the form's visibility directly
        if (targetForm) {
            if (targetForm.style.display === "none" || targetForm.style.display === "") {
                targetForm.style.display = "block"; // Show the form
            } else {
                targetForm.style.display = "none"; // Hide the form
            }
        }
        // Hide other forms when showing a new one
        formContainers.forEach((form) => {
            if (form !== targetForm) {
                form.style.display = "none";
            }
        });
    });
});

