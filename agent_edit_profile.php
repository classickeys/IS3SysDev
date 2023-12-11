<?php

//secure.php: file that has session verification
require_once("secure.php");

// Fetch the agent's current details from the session
$agent_data = $_SESSION['agent_data'];

//get the agents id from the previous page using the url
$agent_id = $_REQUEST['agentid'];

require_once("config.php");

$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

// Check if the form is submitted
if (isset($_POST['submit_changes'])) {
    // Retrieve the updated data from the form
    $agent_name = mysqli_real_escape_string($conn, $_POST['agent_name']);
    $agent_surname = mysqli_real_escape_string($conn, $_POST['agent_surname']);
    $agent_gender = mysqli_real_escape_string($conn, $_POST['agent_gender']);
    $agent_dob = mysqli_real_escape_string($conn, $_POST['agent_dob']);
    $agent_email = mysqli_real_escape_string($conn, $_POST['agent_email']);
    $agent_contact = mysqli_real_escape_string($conn, $_POST['agent_contact']);
    $agent_agency = mysqli_real_escape_string($conn, $_POST['agent_agency']);

    // Update the agent's data in the database
    $update_query = "UPDATE agent SET 
                    Name = '$agent_name',
                    Surname = '$agent_surname',
                    Gender = '$agent_gender',
                    Date_Of_Birth = '$agent_dob',
                    Email = '$agent_email',
                    Contact_Number = '$agent_contact',
                    Agency = '$agent_agency'
                    WHERE AgentID = '$agent_id'";

    $result = mysqli_query($conn, $update_query);

    if ($result) {
        // Update successful
        // You can redirect the user or display a success message here
        // Example: header("Location: profile.php");
        echo '<script type="text/javascript">alert("Profile updated successfully!");</script>';
    } else {
        // Handle the update error
        $errorMessage = "Error: " . mysqli_error($conn);
        echo '<script type="text/javascript">alert("' . $errorMessage . '");</script>';
    }
}

// Close the database connection
mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="admin_edit_profile.css">
</head>
<body>
    <main>
        <section>
            
            <form action="agent_edit_profile.php" method="post" id="update_agent_details" class="form_container">
                <h3> Edit My Details </h3>

                <label for="agent_name">Name: </label>
                <input type="text" name="agent_name" id="agent_name" required value="<?php echo htmlspecialchars($agent_data['Name']); ?>"> <br>

                <label for="agent_surname">Surname: </label>
                <input type="text" name="agent_surname" id="agent_surname" required value="<?php echo htmlspecialchars($agent_data['Surname']); ?>"> <br>

                <label for="agent_gender">Gender: </label>
                <select name="agent_gender" id="agent_gender" required>
                    <option value="">---Choose Gender---</option>
                    <option value="M" <?php if ($agent_data['Gender'] === 'M') echo 'selected'; ?>>Male</option>
                    <option value="F" <?php if ($agent_data['Gender'] === 'F') echo 'selected'; ?>>Female</option>
                </select>

                <label for="agent_dob">Date Of Birth: </label>
                <input type="date" name="agent_dob" id="agent_dob" required value="<?php echo htmlspecialchars($agent_data['Date_Of_Birth']); ?>"> <br>

                <label for="agent_email">Email: </label>
                <input type="email" name="agent_email" id="agent_email" required value="<?php echo htmlspecialchars($agent_data['Email']); ?>"> <br>

                <label for="agent_contact">Mobile Contact: </label>
                <input type="tel" name="agent_contact" id="agent_contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" value="<?php echo htmlspecialchars($agent_data['Contact_Number']); ?>"> <br>

                <label for="agent_agency">Agency: </label>
                <input type="text" name="agent_agency" id="agent_agency" required value="<?php echo htmlspecialchars($agent_data['Agency']); ?>"> <br>

                <input type="submit" value="Submit Changes" name="submit_changes" class="submit">

            </form>
        </section>
    </main>

    <script src="agent_script.js"></script>
</body>
</html>
