<?php

require_once("check.php");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        * {
            font-family: "Inter", sans-serif;
            color: #213644;
        }

        .policy {
            background-color: #ffffff;
            position: relative;
            width: fit-content;
            margin: auto;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            /* Box shadow for the raised effect */
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 5%;
            padding: 2%;
            text-align: center;
            padding-bottom: 5%;
            margin-bottom: 10%;
        }

        h1,
        h3 {
            font-family: "Gotham", sans-serif;
            text-transform: uppercase;


        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="index.css">
    <style>
        .policy{
            width: 50%;
            margin: auto;
            border-radius: 10px;
            margin-bottom: 2%;
        }

        .policy img{
            border-radius:10px;
        }

        #pElement{
            text-align: center;
            vertical-align: middle;
            margin-left: 25%;
            width: 50%;
        }


        #hideButton {
            background-color: #213644;
    color: #e3e3e3;
    border: none;
    padding: 8px 16px;
    cursor: pointer;
    border-radius: 10px;
    margin-top: 1%;
    font-size: 12px;
        }

        #hideButton:hover {
            background-color: rgba(227, 227, 227, 0.601); 
    color: #213644;
        }
    </style>
    <title>Privacy</title>
</head>

<body>
    <nav id="navbar">
        <ul>
            <!-- Nav bar  -->
            <!-- img on nav bar -->
            <li class="left">
                <a href="index.php" id="homeBtn" rel="noopener noreferrer">
                    <img id="home_png" src="./images/home.png" height="35" width="85" alt="Off-Camp Review Icon">
                </a>
            </li>

            <li class="right">

                <?php
                require_once("config.php");
                $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

                // Check if the user is logged in
                if (isset($_SESSION["user_id"])) {
                    $user_id = $_SESSION["user_id"];

                    // Check if the user is a tenant
                    if (isTenant($user_id)) {
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
                                <a href="propertyPage.php?accommodationid=' . $outcome['AccomodationID'] . '" class="navBtn" id="reviewBtn">
                                    Rate & Review
                                    <img id="review_png" src="./images/icons/review.png" alt="alert icon" height="16px" width="16px">
                                </a>
                                <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')">
                                    Logout
                                    <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                                </a>
                                <a href="tenant.php" class="navBtn" id="loginBtn">
                                    Dashboard 
                                    <img id="login_png" class="navImg" src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture" height="18px" width="18px" style="border-radius: 50%; margin-left: 2%">
                                </a>
                            ';
                    }

                    // Check if the user is an agent
                    elseif (isAgent($user_id)) {
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
                                <a href="agent_add_listing.php?agentid=' . $agent_id . '" class="navBtn" id="reviewBtn">
                                    List Property
                                    <img id="review_png" src="./images/icons/review.png" alt="alert icon" height="16px" width="16px">
                                </a>
                                <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')">
                                    Logout
                                    <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                                </a>
                                <a href="agent.php" class="navBtn" id="loginBtn">
                                    Dashboard
                                    <img id="login_png" class="navImg" src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture" height="18px" width="18px"  style="border-radius: 50%; margin-left: 2%">
                                </a>
                            ';
                    }
                    // Check if the user is an admin
                    elseif (isAdmin($user_id)) {
                        // fetch admin's data from session 
                        $admin_data = $_SESSION['admin_data'];

                        // Check if a profile picture URL is available
                        if (!empty($admin_data['ProfilePicture'])) {
                            $profilePictureURL = $admin_data['ProfilePicture'];
                        } else {
                            // Assign the default profile picture URL
                            $profilePictureURL = 'profile_pictures/default_profile_pic.png';
                        }

                        // Display their profile button 
                        echo '

                                <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')" >
                                    Logout
                                    <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                                </a>
                                <a href="admin.php" class="navBtn" id="loginBtn">
                                    Dashboard
                                    <img id="login_png" class="navImg" src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture" height="18px" width="18px" style="border-radius: 50%; margin-left: 2%">
                                </a>
                            ';
                    }
                } else {
                    // Display the "Login" button if they are not logged in
                    echo '
                            <a href="loginPage.php" class="navBtn" id="loginBtn">
                                Login
                                <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                            </a>
                        ';
                }
                ?>
            </li>
        </ul>
    </nav>
    <br><br><br><br><br>
    <div class="policy">
        <h1>Privacy</h1>
        <p id="pElement">Terms and Conditions</p>
        <img id="imageElement" src="./images/meme.jpg" alt="TsnCs"> <br>
        <button id="hideButton" >Your Privacy Matters To Us</button>
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
    <script src="scroll.js"> </script>
    <script>
        const hideButton = document.getElementById('hideButton');
        const imageElement = document.getElementById('imageElement');
        const pElement = document.getElementById('pElement');
        // Add a click event listener to the button
        hideButton.addEventListener('click', function() {
            // Hide the element by setting its display property to 'none'
            imageElement.style.display = 'none';

            // Replace the content of the paragraph element

            let f = '<h3>Privacy Page for Off-campus Review </h3><br> At Off-campus Review, we are committed to safeguarding your privacy and ensuring the security of your personal information. <br>This Privacy Policy outlines how we collect, use, disclose, and protect your information when you use our services or interact with our website.<br> By using our services, you consent to the practices described in this Privacy Policy.<br>';
            let h = '<p><br><h3>Information We Collect</h3><br> We collect various types of information when you interact with Off-campus Review, including but not limited to:<br> Personal Information: This includes your name, contact information, and address.<br> Cookies and Usage Data</p>';
            let a = '<p>How We Use Your Information<br>We use your personal information for the following purposes:<br>';
            let b = 'Customer Support: To respond to your inquiries, requests, and provide assistance.<br><br>';
            let c = 'Analytics: To improve our website, services, and customer experience.<br>Compliance: To meet legal, regulatory, and contractual obligations.<br>';
            let d = '<p><br><h3>Information Sharing</h3><br>We may share your information with trusted third parties in the following circumstances:<br>Service Providers: We may disclose your information to third-party service providers who assist us in delivering our services.<br>Legal Obligations: To comply with legal requirements, protect our rights, and ensure safety and security. <p>';
            let e = '<p>Changes to this Privacy Policy<br>Off-campus Review reserves the right to update this Privacy Policy periodically. Any changes will be posted on our website with the updated effective date.<br><br><h3>Contact Us</h3><br>If you have any questions or concerns regarding this Privacy Policy or your personal information, please <a href="contactus.php"> contact us</a>. </p>';
            let g = '<p>By using Off-campus Review\'s services, you acknowledge that you have read and understood this Privacy Policy and agree to its terms and conditions.</p>'
            pElement.innerHTML = '' + f + h + a + b + c + d + e + g;

            hideButton.style.display = 'none';

        });
    </script>
</body>

</html>