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

        // Create and execute the SQL query to fetch agent data
        $tenant_query = "SELECT * FROM tenant WHERE TenantID = '$tenant_id'";
        $tenant_result = mysqli_query($conn, $tenant_query);

        // Check if the query was successful and if the agent exists
        if ($tenant_result && mysqli_num_rows($tenant_result) > 0) {
            // Agent exists, fetch their data
            $tenant_data = mysqli_fetch_assoc($tenant_result);

            // Check if a profile picture URL is available
            if (!empty($tenant_data['ProfilePicture'])) {
                $profilePictureURL = $tenant_data['ProfilePicture'];
            } else {
                // Assign the default profile picture URL
                $profilePictureURL = 'profile_pictures/default_profile_pic.png';
            }

            // Store agent's data in session for later use
            $_SESSION['tenant_data'] = $tenant_data;

            // Close the database connection
            mysqli_close($conn);
        } else {
            // Agent not found, handle accordingly
            echo '<p>Tenant not found or an error occurred.</p>';
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant's Portal</title>
    <link rel="stylesheet" href="tenant_styles.css">
</head>
<body>
    <div class="grid-container">
        <div class="grid-item item1" alt="my_profile" style="border: none; margin-right: 5%;">
            <h2>My Profile</h2>
            <div class="profile-card">
                <!-- Profile Picture -->
                <?php
                    // Check if agent data is available in session
                    if (isset($_SESSION['tenant_data'])) {
                        $tenant_data = $_SESSION['tenant_data'];

                        echo '<div id="profile-picture">
                                <img src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture"> 
                            </div>';
                        echo '<div class="profile-details">
                                <p>' . htmlspecialchars($tenant_data['Name']) . ' ' . htmlspecialchars($tenant_data['Surname']) . '</p>';
                        echo '  <p>' . htmlspecialchars($tenant_data['Email']) . '</p>
                            </div>';
                    } else {
                        // Handle the case where session data is missing
                        echo '<p>Tenant data not found or an error occurred.</p>';
                    }
                ?>
                <button class="show_form" data-target="update_my_details"> Edit Profile</button>
                <!-- <a href="tenant_edit_profile.php" class="link">Edit Profile</a> -->
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
                    <a href="tenant_edit_profile.php" id="clearForm">Clear</a>
                </form>
            </div>
        </div>

        <div class="grid-item item2" id="my_property" >
            <h2>My Property</h2>
            <section class="controls">
                
                <?php 
                    require_once("config.php");

                    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                
                    $query = "SELECT ta.AccomodationID
                                FROM tenant_accomodation AS ta
                                WHERE ta.TenantID = '$tenant_id'";
                    $result = mysqli_query($conn, $query);

                    if($result && mysqli_num_rows($result) > 0){
                        $row = mysqli_fetch_assoc($result);
                    }
                ?>
                <a href=" <?php echo 'propertyPage.php?accommodationid=' . $row['AccomodationID'] ?>" id="view_rate_property" class="link">View & Rate My Property</a>
            </section>
        </section>

        <section id="my_agent">
            <h2>My Agent</h2>
            <section class="controls">
                <a href="tenant_view_rate_agent.php" id="view_rate_agent" class="link">View & Rate My Agent</a>
            </section>
        </section>
    </div>
</body>
</html>