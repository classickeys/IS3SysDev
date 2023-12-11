<?php

//secure.php: file that has session verification
require_once("secure.php");

// Fetch the agent's current details from the session
$agent_data = $_SESSION['agent_data'];

// //get the agents id from the previous page using the url
// $agent_id = $_REQUEST['agentid'];

require_once("config.php");
$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

if(isset($_POST['add_tenant'])){
    //first adding the tenant as a new user

    //create username of the format T23M1234...
    $username = 'T' . date('y') . substr($_POST['tenant_surname'], 0, 1) . substr(time(), -4);
    
    $password = generate_password();
    $role = 'T';

    $insert_user_query = "INSERT INTO users (Password, Role, UserName) VALUES ('$password', '$role', '$username')";
    $user_result = mysqli_query($conn, $insert_user_query);

    if ($user_result) {
        // User added successfully, retrieve the generated UserID
        $userID = mysqli_insert_id($conn);

        //now adding them as an tenant
        $tenantName = $_POST['tenant_name']; 
        $tenantSurname = $_POST['tenant_surname']; 
        $tenantGender = $_POST['tenant_gender'];
        $tenantDOB = $_POST['tenant_dob']; 
        $tenantEmail = $_POST['tenant_email']; 
        $tenantContact = $_POST['tenant_contact'];
        $tenantStatus = $_POST['tenant_status'];

        // Insert tenant details into the tenants table
        $insert_tenant_query = "INSERT INTO tenant (TenantID, Name, Surname, Email, Contact_Number, Status, Gender, Date_Of_Birth, UserID) VALUES (CONCAT('T', $userID), '$tenantName', '$tenantSurname', '$tenantEmail',  '$tenantContact', '$tenantStatus', '$tenantGender', '$tenantDOB', '$userID')";

        $tenant_result = mysqli_query($conn, $insert_tenant_query);

        if ($tenant_result) {

            // Link the tenant to their respective accommodation
            $accommodationName = mysqli_real_escape_string($conn, $_POST['accommodation_name']);
            $tenantID = "T" . $userID;

            // Find the AccommodationID based on the provided Accommodation Name
            $query = "SELECT AccomodationID FROM accomodation WHERE Name = '$accommodationName'";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $accommodationID = $row['AccomodationID'];

                // Insert the tenant-accommodation relationship
                $insert_relationship_query = "INSERT INTO tenant_accomodation (TenantID, AccomodationID, From_Date) VALUES ('$tenantID', '$accommodationID', CURDATE())";
                $relationship_result = mysqli_query($conn, $insert_relationship_query);

                if ($relationship_result) {
                    echo '<script type="text/javascript">alert("Tenant Added Successfully and Linked to Accommodation");</script>';
                } else {
                    // Handle the error
                    $errorMessage = "Error 5: " . mysqli_error($conn);
                    die('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
                }
            } else {
                // Handle the case where the provided accommodation name doesn't exist
                die('<script type="text/javascript">alert("Accommodation Name not found");</script>');
            }
        } else {
            // Handle the error
            $errorMessage = "Error 3: " . mysqli_error($conn);

            // Use JavaScript to display the error message as a pop-up
            die('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
        }
        //emails sending
        // if ($relationship_result) {
        //     // Send an email to the tenant with username and password
        //     sendEmail($tenantEmail, $username, $password);
        
        //     echo '<script type="text/javascript">alert("Tenant Added Successfully and Linked to Accommodation");</script>';
        // } else {
        //     // Handle the error
        //     $errorMessage = "Error 5: " . mysqli_error($conn);
        //     die('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
        // }

    } else {
        // Handle the error
        $errorMessage = "Error 4: " . mysqli_error($conn);

        // Use JavaScript to display the error message as a pop-up
        die('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
    }
}   //add tenant

function generate_password(){
    // Characters that can be included in the random string
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // Length of the random string
    $length = 10;

    // Initialize the random string
    $random_password = '';

    // Generate a random string
    for ($i = 0; $i < $length; $i++) {
        $random_password .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_password;
}

// function sendEmail($recipient, $username, $password) {
//     // Characters that can be included in the random string
//     $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

//     // Length of the random string
//     $length = 10;

//     // Initialize the random string
//     $random_password = '';

//     // Generate a random string
//     for ($i = 0; $i < $length; $i++) {
//         $random_password .= $characters[rand(0, strlen($characters) - 1)];
//     }

//     $to = $recipient;
//     $subject = "Your Account Details";
//     $message = "Hello,\n\n";
//     $message .= "Your username: " . $username . "\n";
//     $message .= "Your password: " . $password . "\n";
//     $message .= "Please keep this information secure.\n\n";
//     $message .= "Thank you!";

//     $headers = "From: denzeltadiwah@gmail.com"; // Replace with your email address

//     // Send the email
//     if (mail($to, $subject, $message, $headers)) {
//         echo '<script type="text/javascript">alert("Email sent successfully.");</script>';
//     } else {
//         echo '<script type="text/javascript">alert("Error sending email.");</script>';
//     }
// }

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tenant</title>
    <link rel="stylesheet" href="agent_styles.css">
</head>
<body>
    <main>
        <section>
        <form action="agent_add_tenant.php" method="post" id="add_new_tenant_form" class="form_container">
                <h3> Enter New Tenant Details </h3>

                <label for="tenant_name">Name: </label>
                <input type="text" name="tenant_name" id="tenant_name" required> <br>

                <label for="tenant_surname">Surname: </label>
                <input type="text" name="tenant_surname" id="tenant_surname" required> <br>

                <label for="tenant_gender">Gender: </label>
                <select name="tenant_gender" id="tenant_gender" required>
                    <option value="">---Choose Gender---</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="F">Other</option>
                </select>

                <label for="tenant_dob">Date Of Birth: </label>
                <input type="date" name="tenant_dob" id="tenant_dob" required> <br>

                <label for="tenant_email">Email: </label>
                <input type="email" name="tenant_email" id="tenant_email" required> <br>

                <label for="tenant_contact">Mobile Contact: </label>
                <input type="tel" name="tenant_contact" id="tenant_contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number"> <br>

                <label for="tenant_status">Status: </label>
                <select name="tenant_status" id="tenant_status" required>
                    <option value="">---Choose Status---</option>
                    <option value="Student">Student</option>
                    <option value="Employed">Employed</option>
                </select>

                <label for="accommodation_name">Accommodation Name: </label>
                <input type="text" name="accommodation_name" id="accommodation_name" required> <br>

                <input type="submit" value="Add Tenant" name="add_tenant" class="submit">
                <a href="agent.php" id="add_listing_back">Back</a>
            </form>
        </section>
    </main>
</body>
</html>
