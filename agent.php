<?php
    //secure.php: file that has session verification
    require_once("secure.php");

    // Check if the UserID is stored in the session
    if (isset($_SESSION["user_id"])) {
        // Retrieve the UserID from the session
        $user_id = $_SESSION["user_id"];

        require_once("config.php");
        
        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Failed to connect to our Server/Database. Please try again Later!")</script>');
        
        // Create and execute the SQL query to fetch admin data
        $agent_query = "SELECT AgentID FROM agent WHERE UserID = '$user_id'";
        $agent_result = mysqli_query($conn, $agent_query);

        // Check if the query was successful and if the user is an admin
        if ($agent_result && mysqli_num_rows($agent_result) > 0) {
            // Admin exists, fetch their AdminID
            $agent_data = mysqli_fetch_assoc($agent_result);
            $agent_id = $agent_data['AgentID'];
            $_SESSION["agent_id"] = $agent_id;
        } else {
            // User is not an admin, handle accordingly
            echo '<script type="text/javascript">alert("User is not an agent or an error occurred.");</script>';
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
        or die('<script type="text/javascript">alert("Failed to connect to our Server/Database. Please try again Later!")</script>');

    // Create and execute the SQL query to fetch agent data
    $agent_query = "SELECT * FROM agent WHERE AgentID = '$agent_id'";
    $agent_result = mysqli_query($conn, $agent_query);

    // Check if the query was successful and if the agent exists
    if ($agent_result && mysqli_num_rows($agent_result) > 0) {
        // Agent exists, fetch their data
        $agent_data = mysqli_fetch_assoc($agent_result);

        // Check if a profile picture URL is available
        if (!empty($agent_data['ProfilePicture'])) {
            $profilePictureURL = $agent_data['ProfilePicture'];
        } else {
            // Assign the default profile picture URL
            $profilePictureURL = 'profile_pictures/default_profile_pic.png';
        }

        // Store agent's data in session for later use
        $_SESSION['agent_data'] = $agent_data;


    } else {
        // Agent not found, handle accordingly
        echo'<script type="text/javascript">alert("Agent not found or an error occurred.");</script>';
    }
    // Function to check if the user is an agent
    function isAgent($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $agent_query = "SELECT AgentID FROM agent WHERE UserID = '$user_id'";
        $agent_result = mysqli_query($conn, $agent_query);

        return ($agent_result && mysqli_num_rows($agent_result) > 0);
    }

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

        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $agentProfilePic = mysqli_real_escape_string($conn, $_FILES['profile_pic']['name']);
            $agentProfilePic = 'AP' . substr(time(), -5) . $agentProfilePic;
            $destination = "profile_pictures/" . $agentProfilePic;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination); 

            // Update the agent's data in the database
            $update_query = "UPDATE agent SET 
                            Name = '$agent_name',
                            Surname = '$agent_surname',
                            Gender = '$agent_gender',
                            Date_Of_Birth = '$agent_dob',
                            Email = '$agent_email',
                            Contact_Number = '$agent_contact',
                            Agency = '$agent_agency',
                            ProfilePicture = '$destination'
                            WHERE AgentID = '$agent_id'";
        } else {
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

        }

        $result = mysqli_query($conn, $update_query);

        if ($result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile updated successfully!");</script>';
            header("Location: agent.php");
        } else {
            // Handle the update error
            echo '<script type="text/javascript">alert("Error: Failed to Update Profile.");</script>';
        }
    }

    //deleting the profile picture
    if(isset($_REQUEST['delProfPic'])){
        $del_query= "UPDATE agent
                    SET ProfilePicture = NULL
                    WHERE AgentID = '$agent_id'";

        $del_result = mysqli_query($conn, $del_query);
        if ($del_result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile Picture Deleted Successfully!");</script>';
            header("Location: agent.php");
        } else {
            // Handle the update error
            $errorMessage = "Error: " . mysqli_error($conn);
            echo '<script type="text/javascript">alert("Error: Failed to Delete Profile Picture.");</script>';
        }
    }

    //adding an accomodation
    if (isset($_REQUEST['add_listing'])) {
        $property_name = mysqli_real_escape_string($conn, $_POST["name"]);
        $check_listing_query = "SELECT * FROM accomodation WHERE Name = '$property_name'";
        $check_listing_result = mysqli_query($conn, $check_listing_query);
    
        if (mysqli_num_rows($check_listing_result) > 0) {
            // Agent with the same email already exists, display an error message
            echo('<script type="text/javascript">alert("A Property with this Name already exists!");</script>');
        } else {
    
            // Retrieve and sanitize form data
            $property_name = mysqli_real_escape_string($conn, $_POST["name"]);
            $property_address = mysqli_real_escape_string($conn, $_POST["address"]);
            $property_rent = mysqli_real_escape_string($conn, $_POST["rent"]);
            $property_deposit = mysqli_real_escape_string($conn, $_POST["deposit"]);
            $property_distance = mysqli_real_escape_string($conn, $_POST["distance"]);
            $property_beds = mysqli_real_escape_string($conn, $_POST["beds"]);
            $property_baths = mysqli_real_escape_string($conn, $_POST["baths"]);
            $property_type = mysqli_real_escape_string($conn, $_POST["type"]);
            $property_furnished = mysqli_real_escape_string($conn, $_POST["furnished"]);
            $property_nsfas = mysqli_real_escape_string($conn, $_POST["nsfas"]);
            $property_modern = mysqli_real_escape_string($conn, $_POST["modern"]);
            $property_water_backup = mysqli_real_escape_string($conn, $_POST["water_backup"]);
            $property_electricity_backup = mysqli_real_escape_string($conn, $_POST["electricity_backup"]);
            $property_wifi = mysqli_real_escape_string($conn, $_POST["wifi"]);
            $property_electricity = mysqli_real_escape_string($conn, $_POST["electricity"]);
            $property_water = mysqli_real_escape_string($conn, $_POST["water"]);
            $property_balcony = mysqli_real_escape_string($conn, $_POST["balcony"]);
            $property_parking = mysqli_real_escape_string($conn, $_POST["parking"]);
            $property_pets = mysqli_real_escape_string($conn, $_POST["pets"]);
            $property_smoking = mysqli_real_escape_string($conn, $_POST["smoking"]);
            $property_security = mysqli_real_escape_string($conn, $_POST["security"]);
            $property_description = mysqli_real_escape_string($conn, $_POST["description"]);
            $property_availability = mysqli_real_escape_string($conn, $_POST["available"]);
    
            $timestamp = time();
            $last_four_digits = substr($timestamp, -4); // Get the last four digits of the timestamp
            $first_letter = substr($property_name, 0, 1); // Get the first letter of the property name
            $accomodation_id = 'P' . $first_letter . $last_four_digits;
    
            // Insert accommodation details into the Accommodation table
            $insert_accommodation_query = "INSERT INTO accomodation (AccomodationID, Name, Address, Rent, Deposit, Distance_From_Campus, Bedrooms, Bathrooms, Type, Furnished, NSFAS_Accredited, Modern, Water_Backup, Electricity_Backup, WiFi, Electricity, Water, Balcony, Parking, Pets, Smoking, Security, Description, Availability) VALUES ('$accomodation_id', '$property_name', '$property_address', '$property_rent', '$property_deposit', '$property_distance', '$property_beds', '$property_baths', '$property_type', '$property_furnished', '$property_nsfas', '$property_modern', '$property_water_backup', '$property_electricity_backup','$property_wifi', '$property_electricity', '$property_water', '$property_balcony', '$property_parking', '$property_pets', '$property_smoking', '$property_security', '$property_description', '$property_availability')";
    
            $result = mysqli_query($conn, $insert_accommodation_query);
    
            if ($result) {
                $currentDate = date('Y-m-d');
                // Associate the accommodation with the agent in AgentAccommodation table
                $insert_agent_accommodation_query = "INSERT INTO agent_accomodation (AgentID, AccomodationID, From_Date) VALUES ('$agent_id', '$accomodation_id', '$currentDate')";
    
                $result2 = mysqli_query($conn, $insert_agent_accommodation_query);
    
                if ($result2) {
                    //Handle video uploads
                    // $target_video_dir = "propertyVideos/"; // Specify your upload directory for videos

                    // $video_name = mysqli_real_escape_string($conn, $_FILES["videos"]["name"]);

                    // $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                    // $video_id = 'V' . $unique_part;                        
                    // $target_video_file = $target_video_dir .  $unique_part . $video_name;
                    // //echo $target_video_file;

                    // // Upload video
                    // if (move_uploaded_file($_FILES["videos"]["tmp_name"], $target_video_file)) {
                    //     // Insert video path into the Videos table
                    //     $insert_video_query = "INSERT INTO videos (VideoID, AccomodationID, VideoPath) VALUES ('$video_id', '$accomodation_id', '$target_video_file')";
                        
                    //     $result5 = mysqli_query($conn, $insert_video_query) or die('<script type="text/javascript">alert("Error 10: FAILED TO MOVE INSERT VIDEO!")</script>');
                    // }
                    // else{
                    //     die('<script type="text/javascript">alert("FAILED TO MOVE UPLOADED VIDEO!")</script>');
                    // } 
                    

                    // Upload and save images to the Photographs table
                    $target_dir = "propertyImages/"; // Specify your upload directory
                    foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
                        $image_name = $_FILES["images"]["name"][$key];
                        
                        $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                        $photograph_id = 'I' . $unique_part;
                        $target_file = $target_dir . $unique_part . basename($image_name);
    
                        // Upload image
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            // Insert image path into the Photographs table
                        
                            $insert_photo_query = "INSERT INTO photographs (PhotographID, AccomodationID, Photo_Path) VALUES ('$photograph_id', '$accomodation_id', '$target_file')";

                            $result3 = mysqli_query($conn, $insert_photo_query) or die('<script type="text/javascript">alert("Error 11: FAILED TO INSERT PICTURES!")</script>');
                        }
                    }

                    //Handle main picture uploading
                    $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                    $main_photo_id = 'IM' . $unique_part;
    
                    $main_pic = $_FILES['main_image']['name'];
                    $destination = "propertyImages/" . $unique_part . $main_pic;
                    move_uploaded_file($_FILES['main_image']['tmp_name'], $destination);
    
                    $insert_main_photo_query = "INSERT INTO main_pictures (MainPhotoID, AccomodationID, PhotoPath) VALUES ('$main_photo_id', '$accomodation_id', '$destination')";
                    $result4 = mysqli_query($conn, $insert_main_photo_query) or die('<script type="text/javascript">alert("Error 12: FAILED TO INSERT PICTURES!")</script>');
    
                    // Redirect to a success page or show a success message
                    echo('<script type="text/javascript">alert("Property Added Successfully")</script>');
                } else {
                    // Handle error while associating accommodation with agent
                    echo('<script type="text/javascript">alert("Error 13: FAILED TO INSERT AGENT ACCOMODATION!")</script>');
                }
            } else {
                // Handle error while inserting accommodation details
                echo('<script type="text/javascript">alert("Error 14: FAILED TO INSERT ACCOMODATION!'  . mysqli_error($conn). '")</script>');
            }
        }
    }
    

    if (isset($_POST['add_tenant'])) {
        // Start a database transaction
        mysqli_begin_transaction($conn);
    
        $success = true; // Flag to track whether all operations were successful
        $error_message = '';
    
        try {
            // Check if the tenant with the same email already exists
            $tenantEmail = $_POST['tenant_email'];
            $check_tenant_query = "SELECT * FROM tenant WHERE Email = '$tenantEmail'";
            $check_tenant_result = mysqli_query($conn, $check_tenant_query);
    
            if (mysqli_num_rows($check_tenant_result) > 0) {
                // Tenant with the same email already exists, display an error message
                $success = false;
                throw new Exception("Tenant with this email already exists.");
            }
    
            // Create username of the format T23M1234...
            $username = 'T' . date('y') . substr($_POST['tenant_surname'], 0, 1) . substr(time(), -4);
            $password = generate_password();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'T';
    
            // Insert user details into the users table
            $insert_user_query = "INSERT INTO users (Password, Role, UserName) 
                                  VALUES ('$hashedPassword', '$role', '$username')";
            $user_result = mysqli_query($conn, $insert_user_query);
    
            if (!$user_result) {
                $success = false;
                throw new Exception("Failed to add user as tenant.");
            }
    
            // User added successfully, retrieve the generated UserID
            $userID = mysqli_insert_id($conn);
    
            // Now adding them as a tenant
            $tenantName = $_POST['tenant_name'];
            $tenantSurname = $_POST['tenant_surname'];
            $tenantGender = $_POST['tenant_gender'];
            $tenantDOB = $_POST['tenant_dob'];
            $tenantEmail = $_POST['tenant_email'];
            $tenantContact = $_POST['tenant_contact'];
            $tenantStatus = $_POST['tenant_status'];
    
            // Insert tenant details into the tenant table
            $insert_tenant_query = "INSERT INTO tenant (TenantID, Name, Surname, Email, Contact_Number, Status, Gender, Date_Of_Birth, UserID) 
                                    VALUES (CONCAT('T', $userID), '$tenantName', '$tenantSurname', '$tenantEmail',  '$tenantContact', '$tenantStatus', '$tenantGender', '$tenantDOB', '$userID')";
            $tenant_result = mysqli_query($conn, $insert_tenant_query);
    
            if (!$tenant_result) {
                $success = false;
                throw new Exception("Failed to add tenant details.");
            }
    
            // Link the tenant to their respective accommodation
            $accommodationName = mysqli_real_escape_string($conn, $_POST['accommodation_name']);
            $tenantID = "T" . $userID;
    
            // Find the AccommodationID based on the provided Accommodation Name
            $query = "SELECT AccomodationID, Bedrooms FROM accomodation WHERE Name = '$accommodationName'";
            $result = mysqli_query($conn, $query);
    
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $accommodationID = $row['AccomodationID'];
                $maxTenants = $row['Bedrooms'];
    
                // Count the number of existing tenants for the accommodation
                $countTenantsQuery = "SELECT COUNT(*) AS TenantCount FROM tenant_accomodation WHERE AccomodationID = '$accommodationID'";
                $countTenantsResult = mysqli_query($conn, $countTenantsQuery);
    
                if ($countTenantsResult && mysqli_num_rows($countTenantsResult) > 0) {
                    $tenantCountRow = mysqli_fetch_assoc($countTenantsResult);
                    $currentTenantCount = $tenantCountRow['TenantCount'];
    
                    if ($currentTenantCount < $maxTenants) {
                        // Insert the tenant-accommodation relationship
                        $insert_relationship_query = "INSERT INTO tenant_accomodation (TenantID, AccomodationID, From_Date) VALUES ('$tenantID', '$accommodationID', CURDATE())";
                        $relationship_result = mysqli_query($conn, $insert_relationship_query);
    
                        if (!$relationship_result) {
                            $success = false;
                            throw new Exception("Failed to link tenant to accommodation.");
                        }
                    } else {
                        // Handle the case where the maximum tenant limit is reached
                        $success = false;
                        throw new Exception("Maximum tenant limit reached for this accommodation.");
                    }
                } else {
                    // Handle the error in counting tenants
                    $success = false;
                    throw new Exception("Error counting tenants for this accommodation.");
                }
            } else {
                // Handle the case where the provided accommodation name doesn't exist
                $success = false;
                throw new Exception("Accommodation not found.");
            }
    
            // Commit the transaction only if all operations were successful
            if ($success) {
                mysqli_commit($conn);
                echo '<script type="text/javascript">alert("Tenant Added Successfully and Linked to Accommodation");</script>';
                echo('<script type="text/javascript">alert("Please Keep These Login Details somewhere SAFE! \n\n USERNAME:' . $username . ' \nPASSWORD:' . $password . '");</script>');
            } else {
                // Roll back the transaction if any operation failed
                mysqli_rollback($conn);
                echo '<script type="text/javascript">alert("An error occurred. Tenant was not added.");</script>';
            }
        } catch (Exception $e) {
            // If any query within the transaction fails, roll back the changes
            mysqli_rollback($conn);
    
            // Handle the error and display a specific error message
            echo '<script type="text/javascript">alert("Error: ' . $e->getMessage() . '");</script>';
        }
    } // add tenant
    
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

    // Check if the "Confirm" button is clicked
    if (isset($_POST["confirm_new_pass"])) {
        // Retrieve form data
        $oldPassword = mysqli_real_escape_string($conn, $_POST["old_pass"]);
        $newPassword = mysqli_real_escape_string($conn, $_POST["new_pass"]);
        $reenteredPassword = mysqli_real_escape_string($conn, $_POST["reenter"]);

        // Validate the input, e.g., check if the new password matches the reentered password
        if ($newPassword === $reenteredPassword) {
            // You should also perform additional validation, such as checking the strength of the new password
            
            // Check if the old password matches the agent's current password (you need to fetch the current password from your database)
            //$agentId = $_SESSION["agent_id"]; // Assuming you have an agent ID stored in the session
            $currentPassword = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Password FROM users WHERE UserID = '$user_id'")); // Replace with code to fetch the current password

            if (password_verify($oldPassword, $currentPassword['Password'])) {
                // Hash the new password before saving it to the database
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                
                // Update the agent's password in the database
                $updatePasswordQuery = "UPDATE users SET Password = '$hashedPassword' WHERE UserID = '$user_id'";
                
                // Execute the query to update the password
                $updatePasswordResult = mysqli_query($conn, $updatePasswordQuery);
                
                if ($updatePasswordResult) {
                    // Password updated successfully
                    echo '<script>alert("Password updated successfully.");</script>';

                    echo('<script type="text/javascript">alert("Please Keep This Password somewhere SAFE! \n\nNEW PASSWORD: ' . $newPassword . '");</script>');
                } else {
                    // Handle the database update error
                    echo '<script>alert("Error updating password: ' . mysqli_error($conn) . '");</script>';
                }
            } else {
                // Old password does not match the current password
                echo '<script>alert("Old password is incorrect.");</script>';
            }
        } else {
            // New password and reentered password do not match
            echo '<script>alert("New passwords do not match.");</script>';
        }
    }

    if(isset($_POST['deactivate_account'])){
        $user_id = intval(ltrim($agent_id, 'A'));
        $query = "UPDATE users SET Active = '0' WHERE UserID = '$user_id'";

        $deactivate_res = mysqli_query($conn, $query);

        if($deactivate_res){
            header("Location: logout.php");
        } else {
            echo '<script type="text/javascript">alert("Error 21: Failed to Deactivate Your Account!")</script>';
        }
    }

    // Close the database connection
    mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        /* Style the reports lists */
        .report-list {
            list-style: none;
            padding: 0;
            width: 90%;
        }
        .report-list li {
            padding: 3%;
            margin-right: 1%;

        }
        .report-list li:nth-child(odd) {
            width: 100%;
            background-color: #e3e3e3;
            border-radius: 5px;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="dashboards.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://kit.fontawesome.com/8320e0ead0.js" crossorigin="anonymous"></script>
    <script type="text/javascript">
        <?php
            require_once("config.php");
            $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                
            $tenants_query = "SELECT T.*, U.Active
                                FROM tenant T
                                JOIN users U ON T.UserID = U.UserID
                                JOIN tenant_accomodation TA ON T.TenantID = TA.TenantID
                                JOIN agent_accomodation AA ON TA.AccomodationID = AA.AccomodationID
                                WHERE AA.AgentID = '$agent_id'
                                ORDER BY T.Name;
                                ";

            $accomodation_query1 = "SELECT a.*
                                    FROM accomodation a
                                    JOIN agent_accomodation aa ON a.AccomodationID = aa.AccomodationID
                                    WHERE aa.AgentID = '$agent_id'
                                    ORDER BY Name";

            $accomodation_query = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";

            $topTen_Tenants = "SELECT * FROM tenant T, agents_tenant_rating ATR
                                WHERE T.TenantID = ATR.TenantID 
                                ORDER BY ATR.Rating_Value DESC
                                LIMIT 10";

            $occupancyRatesQuery = "SELECT YEAR(TA.From_Date) AS Year, 
                                    MONTH(TA.From_Date) AS Month, 
                                    COUNT(*) AS OccupiedAccommodations
                                    FROM tenant_accomodation TA
                                    JOIN agent_accomodation AA ON TA.AccomodationID = AA.AccomodationID
                                    WHERE AA.AgentID = '$agent_id'
                                    GROUP BY Year, Month
                                    ORDER BY Year, Month
                                    ";

            $accommodationRatingsQuery = "SELECT YEAR(r.Timestamp) AS Year, 
                                    MONTH(r.Timestamp) AS Month, 
                                    AVG(r.Final_Rating) AS AvgRating
                                    FROM tenants_accomodation_rating r
                                    GROUP BY Year, Month
                                    ORDER BY Year, Month";

            $tenants_result = mysqli_query($conn, $tenants_query) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF TENANTS!")</script>');

            $accomodation_result1 = mysqli_query($conn, $accomodation_query1) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');
            
            $accomodation_result = mysqli_query($conn, $accomodation_query) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            // $mostView_results = mysqli_query($conn, $mostView_query) or die('<script type="text/javascript">alert("FAILED TO FETCH THE MOST VIEWED!")</script>');

            $top10_tenants_res = mysqli_query($conn, $topTen_Tenants) or die('<script type="text/javascript">alert("FAILED TO FETCH THE TOP TEN TENANTS!")</script>');
            
            $occupancyRatesResult = mysqli_query($conn, $occupancyRatesQuery) or die('<script type="text/javascript">alert("FAILED TO FETCH THE AVERAGE OCCUPANCY RATES PER MONTH!")</script>');

            $accommodationRatingsResult = mysqli_query($conn, $accommodationRatingsQuery) or die('<script type="text/javascript">alert("FAILED TO FETCH THE AVERAGE ACCOMODATION RATINGS PER MONTH!")</script>');

        ?>

        document.addEventListener("DOMContentLoaded", function () {
            // This code will run after the HTML document is fully loaded

            // Define your tenantsData and accommodationsData arrays here
            const tenantsData = [
                // ['Name', 'Surname', 'Email', 'Contact', 'Status'],
                <?php
                    while($ten = mysqli_fetch_assoc($tenants_result)){
                        echo "['" . $ten['Name'] . "', '" . $ten['Surname'] ."'],"; //. "', '" . $ten['Email'] . "', '" . $ten['Contact_Number'] . "', '" . $ten['Active'] 
                    }
                ?>
            ];

            const accommodationsData = [
                // ['Name', 'Views'],
                <?php
                    while($acc = mysqli_fetch_assoc($accomodation_result1)){
                        echo "['" . $acc['Name'] ."'],"; 
                    }
                ?>
            ];

            // Add an event listener for the "showTenants" button
            const showTenantsButton = document.getElementById('showTenants');
            if (showTenantsButton) { // Check if the button exists
                showTenantsButton.addEventListener('click', () => {
                showList(tenantsData, 'My Current Tenants');
                });
            }

            // Add an event listener for the "showAccommodations" button
            const showAccommodationsButton = document.getElementById('showAccommodations');
            if (showAccommodationsButton) { // Check if the button exists
                showAccommodationsButton.addEventListener('click', () => {
                showList(accommodationsData, 'My Current Accomodations');
                });
            }

            function showList(dataArray, listTitle) {
                const reportContainer = document.getElementById('reportContainer');
                reportContainer.innerHTML = ''; // Clear the previous content

                const list = document.createElement('ul');
                list.className = 'report-list';

                // Create list items from the data
                for (let i = 0; i < dataArray.length; i++) {
                    const item = document.createElement('li');
                    item.textContent = (i + 1) + '. ' + dataArray[i].join(' '); // Add numbers to list items
                    list.appendChild(item);
                }

                // Create a paragraph element to display the count
                const countElement = document.createElement('p');
                countElement.textContent = 'Total Results found: ' + dataArray.length;

                const title = document.createElement('h2');
                title.textContent = listTitle;

                reportContainer.appendChild(title);
                reportContainer.appendChild(countElement); // Add the count element
                reportContainer.appendChild(list);
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            google.charts.load('current', { 'packages': ['corechart'] });

            // Sample data (replace with actual PHP data)
            const topTenants = [
                ["Tenant Name", 'Rating'],
                // Sample data (replace with actual PHP data)
                <?php
                if (mysqli_num_rows($top10_tenants_res) > 0) {
                    while ($top = mysqli_fetch_assoc($top10_tenants_res)) {
                        echo "['" . $top['Name'] . "', " . $top['Rating_Value'] . "],";
                    }
                } else {
                    echo "['N/A', " . 0 . "],";
                }
                ?>
            ];

            const topAccomodation = [
                ["Accommodation Name", 'Overall Rating'],
                <?php
                if (mysqli_num_rows($accomodation_result) > 0) {
                    while ($rate = mysqli_fetch_assoc($accomodation_result)) {
                        echo "['" . $rate['Name'] . "', " . $rate['Final_Rating'] . "],";
                    }
                } else {
                    echo "['N/A', " . 0 . "],";
                }
                ?>
            ];

            // Function to draw column chart
            function drawTopColumnChart(dataArray, chartTitle) {
                const data = google.visualization.arrayToDataTable(dataArray);

                const options = {
                    title: chartTitle,
                    width: 600,  // Adjust width as needed
                    height: 400, // Adjust height as needed
                    bars: 'vertical',
                    hAxis: {
                        title: 'Name',
                        slantedText: true,         // Set to true to enable slanted text
                        slantedTextAngle: 45       // Set the angle (45 degrees in this case)
                    },
                    vAxis: {
                        title: 'Rating',
                        minValue: 0,
                        maxValue: 5, // Set the maximum rating value
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.ColumnChart(document.getElementById('topReportContainer'));
                chart.draw(data, options);
            }

            // Event listeners for buttons
            document.getElementById('showTopTenants').addEventListener('click', () => {
                drawTopColumnChart(topTenants, 'Top 10 Tenants');
            });

            document.getElementById('showTopAccommodations').addEventListener('click', () => {
                drawTopColumnChart(topAccomodation, 'Top 10 Accommodations');
            });
        });    

        document.addEventListener("DOMContentLoaded", function () {
            google.charts.load('current', {'packages': ['corechart']});
            // Ensure Google Charts is loaded before drawing the chart
            google.charts.setOnLoadCallback(drawOccupancyRatesChart);
            google.charts.setOnLoadCallback(drawAccommodationRatingsChart);

            // Sample data (replace with actual PHP data)
            const occupancyRatesData = [
                ['Date', 'Leasing Rate'],
                <?php
                    // Loop through your database query result to populate data
                    while ($row = mysqli_fetch_assoc($occupancyRatesResult)) {
                        echo "['" . $row['Month'] . "', " . $row['OccupiedAccommodations'] . "],";
                    }
                ?>
            ];

            function drawOccupancyRatesChart() {
                const data = google.visualization.arrayToDataTable(occupancyRatesData);

                const options = {
                    title: 'Your Monthly Total Leasing Rate',
                    width: 600,
                    height: 400,
                    curveType: 'function',
                    legend: { position: 'bottom' },
                    hAxis: {
                        title: 'Date (Months)',
                    },
                    vAxis: {
                        title: 'Leasing Rate',
                        minValue: 0,
                        maxValue: 100, // Assuming occupancy rate is in percentage
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.LineChart(document.getElementById('rentalPricesChartContainer'));
                chart.draw(data, options);
            }

            function drawAccommodationRatingsChart() {
                // Your chart drawing code for accommodation ratings over time
                const data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', 'Average Rating');
                <?php
                    // Loop through your database query result to populate data
                    while ($row = mysqli_fetch_assoc($accommodationRatingsResult)) {
                        echo "data.addRow(['" . $row['Month'] . "', " . $row['AvgRating'] . "]);";
                    }
                ?>

                const options = {
                    title: 'Your Monthly Average Accommodation Ratings',
                    width: 600,
                    height: 400,
                    legend: { position: 'bottom' },
                    hAxis: {
                        title: 'Date (Months)',
                    },
                    vAxis: {
                        title: 'Average Rating',
                        minValue: 0,
                        maxValue: 5, // Assuming ratings are on a scale of 0-5
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.LineChart(document.getElementById('rentalPricesChartContainer'));
                chart.draw(data, options);
            }

            // Event listener for button to display the Occupancy Rates Over Time chart
            document.getElementById('showOccupancyRatesChart').addEventListener('click', () => {
                drawOccupancyRatesChart();
            });

            document.getElementById('showAccommodationRatingsChart').addEventListener('click', () => {
                drawAccommodationRatingsChart();
            });


        });

        <?php
            //close connection when done
            mysqli_close($conn);
        ?>

    </script>
</head>

<body>
    <nav id="navbar" >
        <ul >
            <!-- Nav bar  -->
            <!-- img on nav bar -->
            <li class="left">
                <a href="index.php" id="homeBtn" rel="noopener noreferrer">
                    <img id="home_png" src="./images/home.png" height="40" width="85" alt="Off-Camp Review Icon">
                </a>
            </li>

            <!-- all items on the right side of navbar -->
            <li class="right">

                <a href="#" class="navBtn" id="alertBtn" style="display: none;"  >
                    Alerts
                    <img id="alert_png" class="navImg" src="./images/icons/bell.png" alt="alert icon" height="18px" width="18px">
                </a>
                <?php
                    require_once("config.php");
                    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                
                    // Check if the user is logged in
                    if (isset($_SESSION["user_id"])) {
                        $user_id = $_SESSION["user_id"];


                        // Check if the user is an agent
                        if (isAgent($user_id)) {
                            // fetch agent's data from session 
                            $agent_data = $_SESSION['agent_data'];
                            $agent_id = $agent_data['AgentID'];

                            // Check if a profile picture URL is available
                            if (!empty($agent_data['ProfilePicture'])) {
                                $profilePictureURL = $agent_data['ProfilePicture'];
                            } else {
                                // Assign the default profile picture URL
                                $profilePictureURL = 'profile_pictures/default_profile_pic.png';
                            }

                            // Display their respective buttons 
                            echo '
                                <a href="agent_add_listing.php?agentid=' . $agent_id .'" class="navBtn" id="reviewBtn">
                                    List Property
                                    <img id="review_png" src="./images/icons/review.png" alt="alert icon" height="16px" width="16px">
                                </a>
                                <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')">
                                    Logout
                                    <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                                </a>
                            ';
                        }
                    }
                ?>
            </li>
        </ul>
    </nav>
                    

    <h1 id="header">My Dashboard</h1>

    <div class="grid-container">
        <div class="grid-item item1" alt="my_profile" style="border: none; margin-right: 5%;">
            
            <div class="profile-card">
                <h2>My Profile</h2>
                <!-- Profile Picture -->
                <?php
                    // Check if agent data is available in session
                    if (isset($_SESSION['agent_data'])) {
                        $agent_data = $_SESSION['agent_data'];

                        echo '<div id="profile-picture">
                                <img src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture" height="180px" width="200px" style="border-radius: 50%;"> 
                            </div>';
                        echo '<div class="profile-details">
                                <p>' . htmlspecialchars($agent_data['Name']) . ' ' . htmlspecialchars($agent_data['Surname']) . '</p>';
                        echo '  <p>' . htmlspecialchars($agent_data['Agency']) . '</p>
                            </div>';
                    } else {
                        // Handle the case where session data is missing
                        echo '<p>Agent data not found or an error occurred.</p>';
                    }
                ?>
                <button class="show_form" data-target="update_my_details"> Edit Profile</button>
                
                <button class="show_form" data-target="change_password">Change Password</button>
                
                <form action="agent.php" method="post">
                    <input type="submit" value="Deactivate Account"class="show_form" name="deactivate_account"
                        onclick="return confirm('Are you sure you want to deactivate your account <?php echo htmlspecialchars($agent_data['Name']); ?>? \n\nPlease note that doing so will remove you from our system, and as an agent for all accomodations you have listed - rendering them Unavailable, until they are cleaned out of the system.')">
                </form>


                <form action="agent.php" method="post" id="change_password" class="form_container">

                    <input type="password" name="old_pass" id="old_pass" required placeholder="Enter Old Password"> <br>

                    <input type="password" name="new_pass" id="new_pass" required placeholder="Enter New Password"> <br>

                    <input type="password" name="reenter" id="reenter" required placeholder="ReEnter New Password"> <br>

                    <input type="submit" value="Confirm" name="confirm_new_pass" id="confirm" onclick="return confirm('Are You Sure You Want To Change Your Password?')">
                </form>
                    

                <form action="agent.php" method="post"id="update_my_details" class="form_container" enctype="multipart/form-data">
                    
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>

                    <h3> Edit My Details </h3>

                    <label for="agent_name">Name: </label>
                    <input type="text" name="agent_name" id="agent_name" placeholder="eg. John" required value="<?php echo htmlspecialchars($agent_data['Name']); ?>"> <br>

                    <label for="agent_surname">Surname: </label>
                    <input type="text" name="agent_surname" id="agent_surname" placeholder="eg. Doe" required value="<?php echo htmlspecialchars($agent_data['Surname']); ?>"> <br>

                    <label for="agent_gender">Gender: </label>
                    <select name="agent_gender" id="agent_gender" required>
                        <option value="">---Choose Gender---</option>
                        <option value="M" <?php if ($agent_data['Gender'] === 'M') echo 'selected'; ?>>Male</option>
                        <option value="F" <?php if ($agent_data['Gender'] === 'F') echo 'selected'; ?>>Female</option>
                        <option value="O" <?php if ($agent_data['Gender'] === 'O') echo 'selected'; ?>>Other</option>
                    </select>

                    <label for="agent_dob">Date Of Birth: </label>
                    <input type="date" name="agent_dob" id="agent_dob" required value="<?php echo htmlspecialchars($agent_data['Date_Of_Birth']); ?>"> <br>

                    <label for="agent_email">Email: </label>
                    <input type="email" name="agent_email" id="agent_email" placeholder="eg. name@exapmle.com" required value="<?php echo htmlspecialchars($agent_data['Email']); ?>"> <br>

                    <label for="agent_contact">Mobile Contact: </label>
                    <input type="tel" name="agent_contact" id="agent_contact" placeholder="eg. 0123456789" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" value="<?php echo htmlspecialchars($agent_data['Contact_Number']); ?>"> <br>

                    <label for="agent_agency">Agency: </label>
                    <input type="text" name="agent_agency" id="agent_agency" placeholder="Name of Agency" required value="<?php echo htmlspecialchars($agent_data['Agency']); ?>"> <br>

                    <label for="profile_pic">Profile Picture: </label>
                    <input type="file" name="profile_pic" id="profile_pic" > <br>

                    <input type="submit" value="Submit Changes" name="submit_changes" class="submit" onclick="return confirm('Are you sure you want to Submit changes you have made to your profile?')"> <br>

                    <input type="submit" value="Remove Profile Picture" name="delProfPic" class="submit" onclick="return confirm('Are you sure you want to delete your current profile picture?')" > <br>

                </form>

            </div>
        </div>
        <div class="grid-item item2" alt="reports">
            <section class="controls">
                <div class="tab">
                    <button class="tablinks" onclick="openReport(event, 'Lists')" id="defaultOpen">Lists</button>
                    <button class="tablinks" onclick="openReport(event, 'General')">General</button>
                    <button class="tablinks" onclick="openReport(event, 'Top 10')">Top 10</button>
                    <button class="tablinks" onclick="openReport(event, 'Manage Listings')" >Manage Listings</button>
                    <button class="tablinks" onclick="openReport(event, 'Manage Tenants')">Manage Tenants</button>        
                </div>

                <div id="Lists" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>

                    <button class="show_form" id="showTenants">My Tenants</button>
                    <button class="show_form" id="showAccommodations">My Accommodations</button>

                    <div id="reportContainer">
                        <!-- Reports will be displayed here -->
                    </div>
                </div>
                <div id="General" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>

                    <button class="show_form" id="showOccupancyRatesChart">Show Lease Rates Over Time</button>

                    <button class="show_form" id="showAccommodationRatingsChart">Show Ratings Chart</button>

                    <div id="rentalPricesChartContainer" class="chart-container"></div>

                </div>

                <div id="Top 10" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times;</span>

                    <!-- Buttons to trigger reports -->
                    <button class="show_form" id="showTopTenants">Top Ten Tenants</button>
                    <button class="show_form" id="showTopAccommodations">Top Ten Accommodations</button>

                    <!-- HTML container for reports -->
                    <div id="topReportContainer"></div>
                </div>

                <div id="Manage Tenants" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h2>Manage Tenants</h2>
                    <div class="controls">
                        <button class="show_form" data-target="add_new_agent_form"> Add New Tenant </button>
                        <button class="link">
                            <a href="agent_view_update_tenants.php" id="view_my_listing">View & Rate My Tenants</a>
                        </button>
                    </div>

                    <form action="agent.php" method="post" id="add_new_agent_form" class="form_container" >
                    <h3> Enter New Tenant Details </h3>

                        <label for="tenant_name">Name: </label>
                        <input type="text" name="tenant_name" id="tenant_name" placeholder="eg. John" required> <br>

                        <label for="tenant_surname">Surname: </label>
                        <input type="text" name="tenant_surname" id="tenant_surname" placeholder="eg. Doe" required> <br>

                        <label for="tenant_gender">Gender: </label>
                        <select name="tenant_gender" id="tenant_gender" required>
                            <option value="">---Choose Gender---</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>

                        <label for="tenant_dob">Date Of Birth: </label>
                        <input type="date" name="tenant_dob" id="tenant_dob" required> <br>

                        <label for="tenant_email">Email: </label>
                        <input type="email" name="tenant_email" id="tenant_email" placeholder="eg. name@exapmle.com" required> <br>

                        <label for="tenant_contact">Mobile Contact: </label>
                        <input type="tel" name="tenant_contact" id="tenant_contact" placeholder="eg. 0123456789" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number"> <br>

                        <label for="tenant_status">Status: </label>
                        <select name="tenant_status" id="tenant_status" required>
                            <option value="">---Choose Status---</option>
                            <option value="Student">Student</option>
                            <option value="Employed">Employed</option>
                        </select>

                        <label for="accommodation_name">Accommodation Name: </label>
                        <input type="text" name="accommodation_name" id="accommodation_name" placeholder="eg. Name of Accomodation" required> <br>

                        <input type="submit" value="Add Tenant" name="add_tenant" class="submit">
                        
                    </form>
                </div>

                <div id="Manage Listings" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h2>Manage Listings</h2>
                    <div class="controls">
                        <button class="show_form" data-target="add_new_lsiting_form"> Add New Listing </button>
                        <button class="link">
                            <a href="agent_view_update_listings.php" id="view_my_listing">View & Update Listings</a>
                        </button>
                    </div>

                    <!--This form wont be displayed(css - display:none;) until the Add New Admin button is clicked, after which, this form will be revealed using javascript-->

                    <form action="agent.php" method="post" id="add_new_lsiting_form" enctype="multipart/form-data" class="form_container">
                        <h3>Add New Listing</h3>

                        <!-- Accommodation Details -->
                        <label for="name">Name:</label>
                        <input type="text" name="name" placeholder="eg. Hogwarts" required>

                        <label for="address">Address:</label>
                        <input type="text" name="address" placeholder="eg. 42 Privet Drive" required>

                        <label for="rent">Rent/Month:</label>
                        <input type="number" name="rent" required placeholder="eg. 4000">

                        <label for="deposit">Initial Deposit:</label>
                        <input type="number" name="deposit" required placeholder="eg. 4000">

                        <label for="distance">Distance From Campus:</label>
                        <input type="number" name="distance" placeholder="eg. 1 (for 1km)"required>

                        <label for="beds">Bedrooms:</label>
                        <input type="number" name="beds" placeholder="eg. 1 (for 1 bedroom)"eg. required min="0" max="20">

                        <label for="baths">Bathrooms:</label>
                        <input type="number" name="baths" placeholder="eg. 1 (for 1 bathroom)" required min="0" max="20">

                        <label for="type">Accommodation Type:</label>
                        <select name="type" id="type" required>
                            <option value="">---Choose Type---</option>
                            <option value="Single">Single</option>
                            <option value="Sharing">Sharing</option>
                            <option value="House">House</option>
                        </select>

                        <label for="furnished">Furnished Status:</label>
                        <select name="furnished" id="furnished" required>
                            <option value="">---Choose Furnish Level---</option>
                            <option value="No">Unfurnished</option>
                            <option value="Semi">Partly Furnished</option>
                            <option value="Yes">Fully Furnished</option>
                        </select>

                        <label for="description" style="margin-top: 5px;">Description:</label>
                        <textarea name="description" id="description" cols="30" rows="2" placeholder="Enter as Much Extra information as you need to Capture Your Future Tenants..."></textarea>


                        <label for="nsfas" >NSFAS Accredited:</label>
                        <br>
                        <label>
                            <input type="radio" name="nsfas" value="1">
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="nsfas" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="modern">Modern:</label>
                        <br>
                        <label>
                            <input type="radio" name="modern" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="modern" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="water_backup">Water Backup:</label>
                        <br>
                        <label>
                            <input type="radio" name="water_backup" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="water_backup" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="electricity_backup">Electricity Backup:</label>
                        <br>
                        <label>
                            <input type="radio" name="electricity_backup" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="electricity_backup" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="wifi">Comes With Wifi:</label>
                        <br>
                        <label>
                            <input type="radio" name="wifi" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="wifi" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="electricity">Prepaid Electricity:</label>
                        <br>
                        <label>
                            <input type="radio" name="electricity" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="electricity" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="water">Water Included in Rent?:</label>
                        <br>
                        <label>
                            <input type="radio" name="water" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="water" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="balcony">Has Balcony:</label>
                        <br>
                        <label>
                            <input type="radio" name="balcony" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="balcony" value="0">
                            <span></span> No
                        </label>
                        <br>
                        
                        <label for="parking">Parking Avaliable:</label>
                        <br>
                        <label>
                            <input type="radio" name="parking" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="parking" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="pets">Pets Allowed:</label>
                        <br>
                        <label>
                            <input type="radio" name="pets" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="pets" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="smoking">Smoking Allowed:</label>
                        <br>
                        <label>
                            <input type="radio" name="smoking" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="smoking" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="security">Is Secured?:</label>
                        <br>
                        <label>
                            <input type="radio" name="security" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="security" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <label for="available">Is Available?:</label>
                        <br>
                        <label>
                            <input type="radio" name="available" value="1" required>
                            <span></span> Yes
                        </label>
                        <label>
                            <input type="radio" name="available" value="0">
                            <span></span> No
                        </label>
                        <br>

                        <!-- Upload Images -->
                        <label for="images">Upload The Main Images (A Max of 18):</label>
                        <input type="file" name="images[]" accept="image/*" multiple required>


                        <label for="main_image">Upload The Main Image (Only 1)</label>
                        <input type="file" name="main_image" required>


                        <!-- <label for="videos">Upload A Video (Only 1):</label>
                        <input type="file" name="videos"> -->

                        <input type="submit" name="add_listing" class="submit" value="Add Accommodation">

                    </form>
                </div>

            </section>
        </div>
    </div>

<footer>
    <p>
        The A Team &copy; 2023
    </p>
    <small>
        <a href="index.php" target="_self">Home</a>
        <a href="aboutus.php">About Us</a>
        <a href="faqs.php">FAQs</a>
        <a href="privacy.php">Privacy</a>
        <a href="contactus.php">Contact Us</a>
        <a href="tenant.php" target="_self">I am a Tenant</a>
        <a href="agent.php" target="_self">I am an Agent</a>
    </small>
</footer>


    <script>
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

        function openReport(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();

    </script>
    <script src="admin_script.js"></script>

</body>
</html>
