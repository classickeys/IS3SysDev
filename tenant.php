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
        $tenant_query = "SELECT * FROM tenant WHERE UserID = '$user_id'";
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

    } else {
        // Agent not found, handle accordingly
        echo '<p>Tenant not found or an error occurred.</p>';
    }

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

        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $tenantProfilePic = mysqli_real_escape_string($conn, $_FILES['profile_pic']['name']);
            $tenantProfilePic = 'TP' . substr(time(), -5) . $tenantProfilePic;
            $destination = "profile_pictures/" . $tenantProfilePic;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination); 

            // Update the tenant's data in the database
            $update_query = "UPDATE tenant SET 
                        Name = '$tenant_name',
                        Surname = '$tenant_surname',
                        Gender = '$tenant_gender',
                        Date_Of_Birth = '$tenant_dob',
                        Email = '$tenant_email',
                        Contact_Number = '$tenant_contact',
                        Status = '$tenant_status',
                        ProfilePicture = '$destination'
                        WHERE TenantID = '$tenant_id'";
    
        } else {
            // Update the tenant's data in the database
            $update_query = "UPDATE tenant SET 
                        Name = '$tenant_name',
                        Surname = '$tenant_surname',
                        Gender = '$tenant_gender',
                        Date_Of_Birth = '$tenant_dob',
                        Email = '$tenant_email',
                        Contact_Number = '$tenant_contact',
                        Status = '$tenant_status'
                        WHERE TenantID = '$tenant_id'";
    
        }

        $result = mysqli_query($conn, $update_query);
    
        if ($result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile updated successfully!");</script>';
            header("Location: tenant.php");
        } else {
            // Handle the update error
            echo '<script type="text/javascript">alert("Error: Failed to Update Profile.");</script>';
        }
    }

    //deleting the profile picture
    if(isset($_REQUEST['delProfPic'])){
        $del_query= "UPDATE tenant
                    SET ProfilePicture = NULL
                    WHERE TenantID = '$tenant_id'";

        $del_result = mysqli_query($conn, $del_query);
        if ($del_result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile Picture Deleted Successfully!");</script>';
            header("Location: tenant.php");
        } else {
            // Handle the update error
            echo '<script type="text/javascript">alert("Error: Failed to Delete Profile Picture.");</script>';
        }
    }

    // Create and execute the SQL query to fetch the tenant's agent data, rating, and review
    $query = "SELECT A.*, TAR.Rating_Value AS AgentRating, TAR.Review AS AgentReview
            FROM tenant T
            LEFT JOIN tenants_agent_rating TAR ON T.TenantID = TAR.TenantID
            LEFT JOIN tenant_accomodation TA ON T.TenantID = TA.TenantID
            LEFT JOIN accomodation AC ON TA.AccomodationID = AC.AccomodationID
            LEFT JOIN agent_accomodation AA ON AC.AccomodationID = AA.AccomodationID
            LEFT JOIN agent A ON AA.AgentID = A.AgentID
            WHERE T.TenantID = '$tenant_id'";
    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch agent's data, rating, and review
        $agent_data = mysqli_fetch_assoc($result);
        $agent_rating = $agent_data['AgentRating'];
        $agent_review = $agent_data['AgentReview'];

        // Check if a profile picture URL is available
        if (!empty($agent_data['ProfilePicture'])) {
            $profilePictureURLagent = $agent_data['ProfilePicture'];
        } else {
            // Assign the default profile picture URL
            $profilePictureURLagent = 'profile_pictures/default_profile_pic.png';
        }
         // Handle rating and review updates
    
    } else {
        // Agent not found or no rating/review available, handle accordingly
        $agent_data = null;
        $agent_rating = null;
        $agent_review = null;
        echo mysqli_error($conn);
    }

    if (isset($_POST["submit_rating_review"])) {
        // Handle rating and review submission here
        $agent_id = mysqli_real_escape_string($conn, $_POST["agent_id"]);

        $tenant_rating = mysqli_real_escape_string($conn, $_POST["tenant_rating"]);

        $tenant_review = mysqli_real_escape_string($conn, $_POST["tenant_review"]);


        // Check if the tenant has already rated and reviewed the agent
        if ($agent_rating !== null && $agent_review !== null) {
            // Update the agent_tenant_rating table with the new rating and review
            $update_rating_review_query = "UPDATE tenants_agent_rating
                                        SET Rating_Value = '$tenant_rating', Review = '$tenant_review'
                                        WHERE TenantID = '$tenant_id' AND AgentID = '$agent_id'";
        } else {
            // Insert a new rating and review
            $update_rating_review_query = "INSERT INTO tenants_agent_rating (TenantID, AgentID, Rating_Value, Review)
                                        VALUES ('$tenant_id', '$agent_id', '$tenant_rating', '$tenant_review')";
        }

        $update_rating_review_result = mysqli_query($conn, $update_rating_review_query);

        if ($update_rating_review_result) {
            // Rating and review updated successfully
            $agent_rating = $tenant_rating;
            $agent_review = $tenant_review;
            header("Location: tenant.php");
        } else {
            // Handle the error
            echo "Error updating rating and review: " . mysqli_error($conn);
        }
    }

    if(isset($_REQUEST['delete_rating_review'])){
        $agent_id = mysqli_real_escape_string($conn, $_POST["agent_id"]);

        $delete_query= "DELETE FROM tenants_agent_rating
                        WHERE AgentID = '$agent_id'
                        AND TenantID = '$tenant_id'";

        $delete_result = mysqli_query($conn, $delete_query);
        if ($delete_result) {
            // Update successful
            echo '<script type="text/javascript">alert("Rating & Review Deleted Successfully!");</script>';
            header("Location: tenant.php");
        } else {
            // Handle the update error
            echo '<script type="text/javascript">alert("Failed to Delete Rating & Review!");</script>';
        }
    }

    // Check if the "Confirm" button is clicked
    if (isset($_POST["confirm_new_pass"])) {
        // Retrieve form data
        $oldPassword = mysqli_real_escape_string($conn, $_POST["old_pass"]);
        $newPassword = mysqli_real_escape_string($conn, $_POST["new_pass"]);
        $reenteredPassword = mysqli_real_escape_string($conn, $_POST["reenter"]);
        
        // Validate the input, e.g., check if the new password matches the reentered password
        if ($newPassword === $reenteredPassword) {
            // Check if the old password matches the agent's current password 
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
    
    
    // if(isset($_POST['deactivate_account'])){
    //     $user_id = intval(ltrim($tenant_id, 'T'));
    //     $query = "UPDATE users SET Active = '0' WHERE UserID = '$user_id'";

    //     $deactivate_res = mysqli_query($conn, $query);

    //     if($deactivate_res){
    //         header("Location: logout.php");
    //     } else {
    //         echo '<script type="text/javascript">alert("Error 21: Failed to Deactivate Your Account!")</script>';
    //     }
    // }
    

    // Close the database connection

    function displayStars($rating)
    {
        $maxStars = 5; // Maximum number of stars
        $roundedRating = round($rating); // Round the rating to the nearest whole number

        // Output the star icons based on the rounded rating
        $starsHtml = '';
        for ($i = 1; $i <= $maxStars; $i++) {
            if ($i <= $roundedRating) {
                $starsHtml .= '<span class="nyeredzi">★</span>';
            } else {
                $starsHtml .= '<span class="nyeredzi">☆</span>';
            }
        }

        return $starsHtml;
    }
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tenant_data['Name']; ?>'s Portal</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="tenantDashboards.css">

    <style>
.rating {
  display: flex;
  flex-direction: row-reverse;
  justify-content: center;
  }

  /*hides the radio buttons*/
  .rating > input{ display:none;}

  /*style the empty stars, sets position:relative as base for pseudo-elements*/
  .rating > label{
  position: relative;
  width: 1.1em;
  font-size: 25px;
  color: #c6ab7c;
  cursor: pointer;
  }

  .nyeredzi{
    position: relative;
    width: 1.1em;
    font-size: 25px;
    color: #c6ab7c;
    cursor: pointer;

  }

  /* sets filled star pseudo-elements */
  .rating > label::before{ 
  content: "\2605";
  position: absolute;
  opacity: 0;
  }
  /*overlays a filled start character to the hovered element and all previous siblings*/
  .rating > label:hover:before,
  .rating > label:hover ~ label:before {
  opacity: 1 !important;
  }

  /*overlays a filled start character on the selected element and all previous siblings*/
  .rating > input:checked ~ label:before{
  opacity:1;
  }

  /*when an element is selected and pointer re-enters the rating container, selected rate and siblings get semi transparent, as reminder of current selection*/
  .rating:hover > input:checked ~ label:before{ opacity: 0.4; }

    </style>
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

                        // fetch agent's data from session 
                        $tenant_data = $_SESSION['tenant_data'];
                        $tenant_id = $tenant_data['TenantID'];

                        // Check if a profile picture URL is available
                        if (!empty($tenant_data['ProfilePicture'])) {
                            $profilePictureURL = $tenant_data['ProfilePicture'];
                        } else {
                            // Assign the default profile picture URL
                            $profilePictureURL = 'profile_pictures/default_profile_pic.png';
                        }
                        
                        //query to get tenants respective accomodationid 
                        $myquery = "SELECT AccomodationID FROM tenant_accomodation WHERE TenantID = '$tenant_id'";
                        $myresult = mysqli_query($conn, $myquery);
                        $outcome = mysqli_fetch_assoc($myresult);
                        
                        // Display their respective buttons 
                        echo '
                            <a href="propertyPage.php?accommodationid=' . $outcome["AccomodationID"] . '#rate" class="navBtn" id="reviewBtn">
                                Rate & Review
                            <img id="review_png" src="./images/icons/review.png" alt="alert icon" height="16px" width="16px">
                            </a>
                            <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')">
                                Logout
                                <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                            </a>
                        ';
                    }
                ?>
            </li>
        </ul>
    </nav>

    <div class="grid-container">
        <div class="grid-item item1" alt="my_profile" >
        
            <div class="profile-card">
            <h2>My Profile</h2>
                <!-- Profile Picture -->
                <?php

                        require_once("config.php");

                        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

                        $query = "SELECT ta.AccomodationID, a.Name
                                    FROM tenant_accomodation AS ta JOIN accomodation a 
                                    WHERE ta.AccomodationID = a.AccomodationID
                                    AND ta.TenantID = '$tenant_id'";

                        $result = mysqli_query($conn, $query);

                        if($result && mysqli_num_rows($result) > 0){
                            $row = mysqli_fetch_assoc($result);
                        }

                    // Check if agent data is available in session
                    if (isset($_SESSION['tenant_data'])) {
                        $tenant_data = $_SESSION['tenant_data'];

                        echo '<div id="profile-picture">
                                <img src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture"  height="200px" width="200px" style="border-radius: 10px;"> 
                            </div>';
                        echo '<div class="profile-details">
                                <p>' . htmlspecialchars($tenant_data['Name']) . ' ' . htmlspecialchars($tenant_data['Surname']) . '</p>';
                        echo '  <p>' . htmlspecialchars($row['Name']) . '</p>
                            </div>';
                    } else {
                        // Handle the case where session data is missing
                        echo '<p>Tenant data not found or an error occurred.</p>';
                    }
                ?>

                <button class="show_form" data-target="update_tenant_details">Edit Profile</button>

                <button class="show_form" data-target="change_password">Change Password</button>

                <!-- <form action="tenant.php" method="post">
                    <input type="submit" value="Deactivate Account"class="show_form" name="deactivate_account"
                        onclick="return confirm('Are you sure you want to deactivate your account <?php echo htmlspecialchars($tenant_data['Name']); ?>? \n\nPlease note that doing so will remove you from our system, and as a tenant for your current accomodation.')">
                </form> -->

                <form action="tenant.php" method="post" id="change_password" class="form_container">

                    <input type="password" name="old_pass" id="old_pass" required placeholder="Enter Old Password"> <br>

                    <input type="password" name="new_pass" id="new_pass" required placeholder="Enter New Password"> <br>

                    <input type="password" name="reenter" id="reenter" required placeholder="ReEnter New Password"> <br>

                    <input type="submit" value="Confirm" name="confirm_new_pass" id="confirm" onclick="return confirm('Are You Sure You Want To Change Your Password?')">
                </form>                
                <button class="show_form" data-target="view_rate_agent" class="link">View & Rate My Agent</button>
                
                <button class="link">
                    <a href=" <?php echo 'propertyPage.php?accommodationid=' . $row['AccomodationID'] ?>" id="view_rate_property" class="link">View & Rate My Property</a>
                </button>

                <form action="tenant.php" method="post" id="update_tenant_details" class="form_container" enctype="multipart/form-data">
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

                    <label for="profile_pic">Profile Picture: </label>
                    <input type="file" name="profile_pic" id="profile_pic" > <br>
                    
                    <input type="submit" value="Submit Changes" name="submit_changes" class="submit" onclick="return confirm('Are you sure you want to Submit changes you have made to your profile?')"> <br>

                    <input type="submit" value="Remove Profile Picture" name="delProfPic" class="submit" onclick="return confirm('Are you sure you want to delete your current profile picture?')"> <br>
                </form>
                
                <div id="my_agent ">
                <!-- Display input fields for adding/updating rating and review -->
                <form action="tenant.php" method="post" id="view_rate_agent" class="form_container" style="padding: 0;">
                    <?php 
                        // Create and execute the SQL query to fetch agent data
                        $a_query = "SELECT A.*, AVG(TAR.Rating_Value) AS AVGRating
                                    FROM agent A 
                                    INNER JOIN tenants_agent_rating TAR ON A.AgentID = TAR.AgentID
                                    ";

                        $a_result = mysqli_query($conn, $a_query);

                        //$ratingsres = mysqli_query($conn, $ratings);
                        if($a_result && mysqli_num_rows($a_result) > 0){
                            $rating = mysqli_fetch_assoc($a_result);
                            $stars = displayStars($rating['AVGRating']);
                        } else {
                            $stars = displayStars(0);
                        }

                    ?>
                    <?php if ($agent_data) { ?>
                        <div class="profile-card">
                            <h3>My Agent</h3>
                            <!-- Agent's Profile Picture and Details -->
                            <div id="profile-picture">
                                <img src="<?php echo htmlspecialchars($profilePictureURLagent); ?>" alt="Agent's Profile Picture"  height="180px" width="200px" style="border-radius: 20%;">
                            </div>
                            <div class="profile-details">
                                <p><?php echo htmlspecialchars($agent_data['Name'] . ' ' . $agent_data['Surname']); ?></p>
                                <p><?php echo htmlspecialchars($agent_data['Agency']); ?></p>
                                <p><?php echo $stars; ?></p>
                            </div>
                        </div>
                        <div class="profile-card">
                            <?php if ($agent_rating !== null && $agent_review !== null) { ?>
                                <p>Current Rating: <?php echo htmlspecialchars($agent_rating); ?></p>
                                <p>Current Review: <?php if($agent_review != NULL){echo htmlspecialchars($agent_review);}else{echo "<i>Leave A Review</i>";} ?></p>
                            <?php } else { ?>
                                <p>You Haven't Rated <?php echo $agent_data['Name'];?></p>
                            <?php } ?>
                            <!-- Agent's Rating and Review -->
                            <button type="button" class="show_form" data-target="update_rating">Update Rating & Review</button>
                    
                        </div>

                    <?php } else { ?>
                        <p>Agent data not found or an error occurred!</p>
                    <?php } ?>
                </form>

                <form action="" method="post" id="update_rating" class="form_container">
                    <input type="hidden" name="agent_id" value="<?php echo htmlspecialchars($agent_data['AgentID']); ?>">

                    <label for="tenant_rating" class="main_label">Rating</label>
                    <div class="rating">
                        <input type="radio" name="tenant_rating" value="5" id="m5" class="nyeredzi"><label for="m5">☆</label>
                        <input type="radio" name="tenant_rating" value="4" id="m4" class="nyeredzi"><label for="m4">☆</label>
                        <input type="radio" name="tenant_rating" value="3" id="m3" class="nyeredzi"><label for="m3">☆</label>
                        <input type="radio" name="tenant_rating" value="2" id="m2" class="nyeredzi"><label for="m2">☆</label>
                        <input type="radio" name="tenant_rating" value="1" id="m1" class="nyeredzi"><label for="m1">☆</label>
                    </div><br>

                    <label for="tenant_review">Review</label>
                    <textarea name="tenant_review" id="tenant_review" placeholder="Leave an Honest Rating..."><?php echo htmlspecialchars($agent_review); ?></textarea>

                    <input type="submit" name="submit_rating_review" value="Submit Rating & Review" onclick="return confirm('Are you sure you want to submit new rating and/or review?')">

                    <input type="submit" name="delete_rating_review" value="Delete Rating & Review" onclick="return confirm('Are you sure you want to Delete your current rating and/or review?')">
                </form>
                </div>
            </div>
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
    
    <script src="admin_script.js"></script>
    <script src="scroll.js"></script>
</body>
</html>