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
    
    // Create and execute the SQL query to fetch tenant data
    $tenant_query = "SELECT TenantID FROM tenant WHERE UserID = '$user_id'";
    $tenant_result = mysqli_query($conn, $tenant_query);

    // Check if the query was successful and if the user is an tenant
    if ($tenant_result && mysqli_num_rows($tenant_result) > 0) {
        // Tenant exists, fetch their TenantID
        $tenant_data = mysqli_fetch_assoc($tenant_result);
        $tenant_id = $tenant_data['TenantID'];
        $_SESSION["tenant_id"] = $tenant_id;
    } else {
        // User is not an tenant, handle accordingly
        echo '<script type="text/javascript">alert("User is not an tenant or an error occurred.");</script>';
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
    $tenant_name = mysqli_real_escape_string($conn, $_POST['tenant_name']);
    $tenant_surname = mysqli_real_escape_string($conn, $_POST['tenant_surname']);
    $tenant_gender = mysqli_real_escape_string($conn, $_POST['tenant_gender']);
    $tenant_dob = mysqli_real_escape_string($conn, $_POST['tenant_dob']);
    $tenant_email = mysqli_real_escape_string($conn, $_POST['tenant_email']);
    $tenant_contact = mysqli_real_escape_string($conn, $_POST['tenant_contact']);
    $tenant_status = mysqli_real_escape_string($conn, $_POST['tenant_status']);

    // Update the tenant's data in the database
    $update_query = "UPDATE tenant SET 
                    Name = '$tenant_name',
                    Surname = '$tenant_surname',
                    Gender = '$tenant_gender',
                    Date_Of_Birth = '$tenant_dob',
                    Email = '$tenant_email',
                    Contact_Number = '$tenant_contact',
                    Status = '$tenant_status'
                    WHERE tenantID = '$tenant_id'";

    $result = mysqli_query($conn, $update_query);

    if ($result) {
        // Update successful
        // You can redirect the user or display a success message here
        //header("Location: tenant.php");
        echo '<script type="text/javascript">alert("Profile updated successfully!");</script>';

    } else {
        // Handle the update error
        $errorMessage = "Error: " . mysqli_error($conn);
        echo '<script type="text/javascript">alert("' . $errorMessage . '");</script>';
    }
}

// Fetch the tenant's current details from the session
$tenant_data = $_SESSION['tenant_data'];

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="tenant_styles.css">
</head>
<body>
    <main>
        <section>
            <form action="tenant_edit_profile.php" method="post" id="update_tenant_details" class="form_container">
                <h3> Edit My Details </h3>

                <label for="tenant_name">Name: </label>
                <input type="text" name="tenant_name" id="tenant_name" required value="<?php echo htmlspecialchars($tenant_data['Name']); ?>"> <br>

                <label for="tenant_surname">Surname: </label>
                <input type="text" name="tenant_surname" id="tenant_surname" required value="<?php echo htmlspecialchars($tenant_data['Surname']); ?>"> <br>

                <label for="tenant_gender">Gender: </label>
                <select name="tenant_gender" id="tenant_gender" required>
                    <option value="">---Choose Gender---</option>
                    <option value="M" <?php if ($tenant_data['Gender'] === 'M') echo 'selected'; ?>>Male</option>
                    <option value="F" <?php if ($tenant_data['Gender'] === 'F') echo 'selected'; ?>>Female</option>
                    <option value="O" <?php if ($tenant_data['Gender'] === 'O') echo 'selected'; ?>>Other</option>
                </select>

                <label for="tenant_dob">Date Of Birth: </label>
                <input type="date" name="tenant_dob" id="tenant_dob" required value="<?php echo htmlspecialchars($tenant_data['Date_Of_Birth']); ?>"> <br>

                <label for="tenant_email">Email: </label>
                <input type="email" name="tenant_email" id="tenant_email" required value="<?php echo htmlspecialchars($tenant_data['Email']); ?>"> <br>

                <label for="tenant_contact">Mobile Contact: </label>
                <input type="tel" name="tenant_contact" id="tenant_contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" value="<?php echo htmlspecialchars($tenant_data['Contact_Number']); ?>"> <br>

                <label for="tenant_status">Status: </label>
                <input type="text" name="tenant_status" id="tenant_status" required value="<?php echo htmlspecialchars($tenant_data['Status']); ?>"> <br>

                <input type="submit" value="Submit Changes" name="submit_changes" class="submit">
                <a href="tenant.php" id="profile_edit_back">Back</a>

            </form>
        </section>
    </main>

    <script src="tenant_script.js"></script>
</body>
</html>
