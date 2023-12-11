
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


    // Create and execute the SQL query to fetch admin data
    $admin_query = "SELECT * FROM administrator WHERE AdminID = '$admin_id'";
    $admin_result = mysqli_query($conn, $admin_query);

    // Check if the query was successful and if the admin exists
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
        // Admin exists, fetch their data
        $admin_data = mysqli_fetch_assoc($admin_result);

        // Check if a profile picture URL is available
        if (!empty($admin_data['ProfilePicture'])) {
            $profilePictureURL = $admin_data['ProfilePicture'];
        } else {
            // Assign the default profile picture URL otherwise
            $profilePictureURL = '/profile_pictures/default_profile_pic.png';
        }

        // Store admin's data in session for later use
        $_SESSION['admin_data'] = $admin_data;

    } else {
        // Admin not found, handle accordingly
        echo'<script type="text/javascript">alert("Admin not found or an error occurred.");</script>';
    }

    if(isset($_POST['add_agent'])){
        // Check if the agent with the same email already exists
        $agentEmail = $_POST['agent_email'];
        $check_agent_query = "SELECT * FROM agent WHERE Email = '$agentEmail'";
        $check_agent_result = mysqli_query($conn, $check_agent_query);

        if (mysqli_num_rows($check_agent_result) > 0) {
            // Agent with the same email already exists, display an error message
            echo('<script type="text/javascript">alert("Agent with this email already exists.");</script>');
        } else {
            // Continue with adding the agent, starting by first adding the agent as a new user

            //create username of the format A23M1234...
            $username = 'A' . date('y') . substr($_POST['agent_surname'], 0, 1) . substr(time(), -4);
            
            //create the agents password 
            $password = generate_password();
            $role = 'A';

            //insert agent as a new user into the users table 
            $insert_user_query = "INSERT INTO users (Password, Role, UserName) 
                                    VALUES (SHA1('$password'), '$role', '$username')";
                                    //using MySQL SHA1() function that applies an encryption algorithm to text
            $user_result = mysqli_query($conn, $insert_user_query);

            if ($user_result) {
                // User added successfully, retrieve the auto-generated(incremented) UserID. using respective function
                $userID = mysqli_insert_id($conn);

                //now adding them as an agent
                //first getting all their details from the form entered by the admin
                $agentName = $_POST['agent_name']; 
                $agentSurname = $_POST['agent_surname']; 
                $agentGender = $_POST['agent_gender'];
                $agentDOB = $_POST['agent_dob']; 
                $agentEmail = $_POST['agent_email']; 
                $agentContact = $_POST['agent_contact'];
                $agentAgency = $_POST['agent_agency'];
                $agentProfilePic = $_FILES['profile_pic']['name'];

                $agentProfilePic = 'AP' . substr(time(), -5) . $agentProfilePic;
                $destination = "profile_pictures/" . $agentProfilePic;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination);

                //Insert agent details into the agent table
                $insert_agent_query = "INSERT INTO agent (AgentID, Name, Surname, Email, Contact_Number, Agency, Gender, Date_Of_Birth, UserID, ProfilePicture) VALUES (CONCAT('A', $userID), '$agentName', '$agentSurname', '$agentEmail',  '$agentContact', '$agentAgency', '$agentGender', '$agentDOB', '$userID', '$agentProfilePic'";

                $agent_result = mysqli_query($conn, $insert_agent_query);

                if ($agent_result) {
                    // Agent added successfully
                    echo('<script type="text/javascript">alert("Agent Added Successfully");</script>');
                    //header("Location: admin.php");
                } else {
                    // Handle the error
                    $errorMessage = "Error 1(Failed to add agent as a new agent! ): " . mysqli_error($conn);

                    // Use JavaScript to display the error message as a pop-up for failed
                    echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
                }
            } else {
                // Handle the error
                $errorMessage = "Error 2(Failed to add agent as a new user! ): " . mysqli_error($conn);

                // Use JavaScript to display the error message as a pop-up
                echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
            }
        }
    }   //add_agent

    elseif(isset($_POST['add_admin'])){
        // Check if the admin with the same email already exists
        $adminEmail = $_POST['admin_email'];
        //fetch data to check
        $check_admin_query = "SELECT * FROM administrator WHERE Email = '$adminEmail'";
        $check_admin_result = mysqli_query($conn, $check_admin_query);

        if (mysqli_num_rows($check_admin_result) > 0) {
            // Admin with the same email already exists, display an error message
            echo('<script type="text/javascript">alert("Admin with this email already exists.");</script>');
        } else {
            // Continue with adding the admin, by first adding the admin as a new user

            //create username of the format S23M1234...
            $username = 'S' . date('y') . substr($_POST['admin_surname'], 0, 1) . substr(time(), -4);
            
            //generate random length 10 password for them
            $password = generate_password();
            $role = 'S';

            //query to insert the admin as a new user
            $insert_user_query = "INSERT INTO users (Password, Role, UserName) VALUES ('$password', '$role', '$username')";
            $user_result = mysqli_query($conn, $insert_user_query);

            if ($user_result) {
                // User added successfully, retrieve the generated UserID
                $userID = mysqli_insert_id($conn);

                //now adding them as an admin
                $adminName = $_POST['admin_name']; 
                $adminSurname = $_POST['admin_surname']; 
                $adminEmail = $_POST['admin_email']; 
                $adminProfilePic = $_FILES['profile_pic']['name'];

                $adminProfilePic = 'SP' . substr(time(), -5) . $adminProfilePic;
                $destination = "profile_pictures/" . $adminProfilePic;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination);


                // Insert admin details into the admin table
                $insert_admin_query = "INSERT INTO administrator (AdminID, Name, Surname, Email, UserID, ProfilePicture) VALUES (CONCAT('S', $userID), '$adminName', '$adminSurname', '$adminEmail', '$userID', '$adminProfilePic')";

                $admin_result = mysqli_query($conn, $insert_admin_query);

                if ($admin_result) {
                    // Admin added successfully
                    echo('<script type="text/javascript">alert("Admin Added Successfully");</script>');
                } else {
                    // Handle the error
                    $errorMessage = "Error 5(Failed to add admin as a new admin! ): " . mysqli_error($conn);

                    // Use JavaScript to display the error message as a pop-up
                    echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
                }
            } else {
                // Handle the error
                $errorMessage = "Error 6(Failed to add admin as a new user): " . mysqli_error($conn);

                // Use JavaScript to display the error message as a pop-up
                echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
            }
        }   
    }   //add admin

    function generate_password(){
        // Characters that can be included in the random string
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Length of the random string
        $length = 10;

        // Initialize the random string
        $random_password = '';

        // Generate a random string
        for ($i = 0; $i < $length; $i++) {
            //make the password by taking one random index from the string $characters, and appending it to the $random_password variable
            $random_password .= $characters[rand(0, strlen($characters) - 1)];
        }
        //return the new password
        return $random_password;
    }

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin's Portal</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="dash.css">
</head>
<body>
    <nav>
        <ul>
            <!-- Nav bar  -->

            <!-- img on nav bar -->
            <li class="left">
                <a href="index.php" id="homeBtn" rel="noopener noreferrer">
                    <img id="home_png" src="./images/home.png" height="25" width="60" alt="Off-Camp Review Icon">
                </a>
            </li>

            <!-- all items on navbar in the center -->
            <li class="center">
                <a class="searchBtn" href="#">
                    Search Properties...
                    <img id="search_icon" src="./images/icons/search.png" alt="search icon" height="20px" width="20px">
                </a>
            </li>

            <!-- all items on the right side of navbar -->

            <li class="right">
                <a href="#" class="navBtn" id="reviewBtn">
                    Review
                    <img id="review_png" src="./images/icons/review.png" alt="alert icon" height="16px" width="16px">
                </a>
                <a href="#" class="navBtn" id="alertBtn">
                    Alerts
                    <img id="alert_png" class="navImg" src="./images/icons/bell.png" alt="alert icon" height="18px" width="18px">
                </a>
                <a href="loginPage.php" class="navBtn" id="loginBtn">
                    Login
                    <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                </a>
            </li>
        </ul>
    </nav>
    <br>
    <br>
    <br>
    <br>

    <h1>Control Center</h1>

    <div class="grid-container">
        <div class="grid-item item 1" alt="my_profile">
            <h2>My Profile 1</h2>
            <div class="profile-card">
                <!-- Profile Picture -->
                <?php
                    // Check if agent data is available in session
                    if (isset($_SESSION['admin_data'])) {
                        //display the data as a profile card
                        $admin_data = $_SESSION['admin_data'];

                    echo '<div id="profile-picture">
                                <img src="' . ($profilePictureURL) . '" alt="Profile Picture"> 
                            </div>';
                    echo '<div class="profile-details">
                                <p>' . ($admin_data     ['Name']) . ' ' . ($admin_data['Surname']) . '</p>
                            </div>';
                        } else {
                            // Handle the case where session data is missing
                            echo '<p>Admin data not found or an error occurred.</p>';
                    }
                ?>
                <!--<a href="admin_edit_profile.php" class="link" target="update_my_details">Edit Profile</a>-->
                <button class="show_form" data-target="update_my_details"> Edit Profile</button>

                <!--Form for editing the profile of admin-->
                <form action="" method="post" id="update_my_details" class="form_container" enctype="multipart/form-data">
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
                <!-- Back button
                <a href="/sysDev/if0_35600039_Thea_team/index.php" id="profile_edit_back" style="background-color: #867455;
                color: #FFFFFF; padding: 8px 12px; cursor: pointer; border-radius: 5px; 
                text-decoration: none; text-align: center;">Back to Home</a> -->
            </div>
        </div>

        <div class="grid-item item2" alt="reports">
            <h2>Dashboard 2</h2>
            <section class="controls">
                <button class="show_form" data-target=""> Top 10 Tenants</button>
                <button class="show_form" data-target=""> Top 10 Agents</button>
                <button class="show_form" data-target=""> Top 10 Properties</button>
                <button class="show_form" data-target=""> All Time Top</button>
            </section>
        </div>

        <div class="grid-item item3" alt="manage_agent" >
            <h2>Manage Agent 3</h2>
            <div class="controls">
                <button class="show_form" data-target="add_new_agent_form"> Add New Agent </button>
                <button class="link">
                    <a href="admin_view_update_agents.php" id="view_my_listing">View & Update Agents</a>
                </button>
            </div>
            <!--This form wont be displayed(css - display:none;) until the Add New Agent button is clicked, after which, this form will be revealed using javascript-->
            <form action="admin.php" method="post" id="add_new_agent_form" class="form_container" enctype="multipart/form-data">
                <h3> Enter New Agent Details </h3>

                <label for="agent_name">Name: </label>
                <input type="text" name="agent_name" id="agent_name" required> <br>

                <label for="agent_surname">Surname: </label>
                <input type="text" name="agent_surname" id="agent_surname" required> <br>

                <label for="agent_gender">Gender: </label>
                <select name="agent_gender" id="agent_gender" required >
                    <option value="">---Choose Gender---</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>

                <label for="agent_dob">Date Of Birth: </label>
                <input type="date" name="agent_dob" id="agent_dob" required> <br>

                <label for="agent_email">Email: </label>
                <input type="email" name="agent_email" id="agent_email" required> <br>

                <label for="agent_contact">Mobile Contact: </label>
                <input type="tel" name="agent_contact" id="agent_contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number"> <br>

                <label for="agent_agency">Agency: </label>
                <input type="text" name="agent_agency" id="agent_agency" required> <br>

                <label for="profile_pic">Profile Picture: </label>
                <input type="file" name="profile_pic" id="profile_pic">

                <input type="submit" value="Add Agent" name="add_agent" class="submit">
            </form>
        </div>

        <div class="grid-item item4" alt="manage_admin">
            <h2>Manage Admin 4</h2>
            <section class="controls">
                <button class="show_form" data-target="add_new_admin_form"> Add New Admin </button>
                <button class="link">
                    <a href="admin_view_update_admins.php" id="view_my_listing">View & Update Admins</a>
                </button>
                
            </section>
            
            <!--This form wont be displayed(css - display:none;) until the Add New Admin button is clicked, after which, this form will be revealed using javascript-->
            <form action="admin.php" method="post" id="add_new_admin_form" class="form_container" enctype="multipart/form-data">
                <h3> Enter New Admin Details </h3>

                <label for="admin_name">Name: </label>
                <input type="text" name="admin_name" id="admin_name" required> <br>

                <label for="admin_surname">Surname: </label>
                <input type="text" name="admin_surname" id="admin_surname" required> <br>

                <label for="agent_email">Email: </label>
                <input type="email" name="admin_email" id="admin_email" required> <br>

                <label for="profile_pic">Profile Picture: </label>
                <input type="file" name="profile_pic" id="profile_pic">

                <input type="submit" value="Add Admin" name="add_admin" class="submit">
            </form>
        </div>
        <!-- <div class="grid-item item 5">5</div>
        <div class="grid-item item 6">6</div> -->
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
</body>
</html>