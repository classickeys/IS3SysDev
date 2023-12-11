<?php

//secure.php: file that has session verification
require_once("secure.php");

// Check if the UserID is stored in the session
if (isset($_SESSION["user_id"])) {
    // Retrieve the UserID from the session
    $user_id = $_SESSION["user_id"];

    require_once("config.php");
    
    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
    
    // Create and execute the SQL query to fetch admin data
    $admin_query = "SELECT AdminID FROM administrator WHERE UserID = '$user_id'";
    $admin_result = mysqli_query($conn, $admin_query);

    // Check if the query was successful and if the user is an admin
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
        // Admin exists, fetch their AdminID
        $admin_data = mysqli_fetch_assoc($admin_result);
        $admin_id = $admin_data['AdminID'];
    } else {
        // User is not an admin, handle accordingly
        echo '<script type="text/javascript">alert("User is not an admin or an error occurred.");</script>';
        header("Location: loginPage.php");
        exit();
    }

    mysqli_close($conn);

} else {
    // Handle the case where UserID is not found in the session
    echo '<script type="text/javascript">alert("UserID not found in the session.");</script>';
    // You may choose to redirect the user to the login page here
    header("Location: loginPage.php");
    exit();
}

require_once("config.php");

$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

// Check if the form is submitted
if (isset($_POST['submit_changes'])) {
    // Retrieve the updated data from the form
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_surname = mysqli_real_escape_string($conn, $_POST['admin_surname']);
    $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    $adminProfilePic = mysqli_real_escape_string($conn, $_FILES['profile_pic']['name']);

    $adminProfilePic = 'SP' . substr(time(), -5) . $adminProfilePic;
    $destination = "profile_pictures/" . $adminProfilePic;
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination);

    // Update the agent's data in the database
    $update_query = "UPDATE administrator SET 
                    Name = '$admin_name',
                    Surname = '$admin_surname',
                    Email = '$admin_email',
                    ProfilePicture = '$destination'
                    WHERE AdminID = '$admin_id'";

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

// Fetch the agent's current details from the session
$admin_data = $_SESSION['admin_data'];

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
            <form action="admin_edit_profile.php" method="post" id="update_my_details" class="form_container" enctype="multipart/form-data">
                <h3> Edit My Details </h3>

                <label for="admin_name">Name: </label>
                <input type="text" name="admin_name" id="admin_name" required value="<?php echo ($admin_data['Name']); ?>"> <br>

                <label for="admin_surname">Surname: </label>
                <input type="text" name="admin_surname" id="admin_surname" required value="<?php echo ($admin_data['Surname']); ?>"> <br>

                <label for="admin_email">Email: </label>
                <input type="email" name="admin_email" id="admin_email" required value="<?php echo ($admin_data['Email']); ?>"> <br>

                <label for="profile_pic">Profile Picture: </label>
                <input type="file" name="profile_pic" id="profile_pic">

                <input type="submit" value="Submit Changes" name="submit_changes" class="submit">
                <a href="admin.php" id="profile_edit_back">Back</a>
                <!--<a href="./admin_edit_profile.php" id="clearForm">Clear</a>-->
            </form>
        </section>
    </main>

    <script src="agent_script.js"></script>
</body>
</html>
