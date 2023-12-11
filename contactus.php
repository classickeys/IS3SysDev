<?php

require_once("check.php");


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        * {
            font-family: "Inter", sans-serif;
        }

        body {
            margin: 0px;
            padding: 0%;
        }

        h1,
        h3 {
            font-family: "Gotham", sans-serif;
            text-transform: uppercase;
            color: #213644;

        }

        html,
        body {
            height: 100%;
        }

        h1,
        p {
            text-align: center;
        }

        /*CONTACT US FOR CSS*/


        .h1Top {
            margin-top: 5%;
        }



        .box1 {
            position: relative;
            text-align: center;
        }

        .eform {
            background-color: whitesmoke;
            padding: 20px 20px 20px;
            margin: auto;
            border-radius: 10px;
            width: 35%;
            height: fit-content;
            text-align: center;
            justify-content: center;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
            /* Box shadow for the raised effect */
            transition: transform 0.2s, box-shadow 0.2s;

        }

        .eform p {
            font-size: 14px;
            padding: 5%;
        }



        form.email {
            margin: 2% 2% 2% 2%;
            color: #213644;
            background: whitesmoke;
            display: flex;
            flex-direction: column;
            padding: 2% 2% 2% 2%;
            width: 90%;
            height: min-content;
            max-width: 90%;
            border-radius: 10px;
            border: solid 1px;
            border-color: silver;
        }

        form h3 {
            color: #213644;
            font-weight: 800;
            margin-bottom: 20px;
        }

        form.email input,
        form textarea {
            border: 0;
            margin: 10px 0;
            padding: 20px;
            outline: none;
            background-color: rgb(238, 237, 235);
            font-size: 12px;
        }

        form button {
            padding: 10px;
            background-color: #213644;
            color: #c6ab7c;
            font-size: 12px;
            border: 0;
            outline: none;
            cursor: pointer;
            width: 100px;
            margin: 20px auto 0;
            border-radius: 20px;
        }

        form button:hover {
            background-color: #c6ab7c;
            color: #213644;
        }

        /*CONTACT US END*/
    </style>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="index.css">

    <title>Contact Us</title>
</head>

<body>
    <header>
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
    </header>
    <br>
    <br>
    <div class="h1Top">
        <h1>Contact Us</h1>
    </div>


    <div class="eform">
        <p>So, you've taken a look at what we have to offer.
            But perhaps you still have questions that you want to hear from us.
            Please, get in touch with us! You can either call us on the number below,
            or fill out our email and send us an email and we will get back to you as soon as we can!
        </p>
        <form class="email" method="post">
            <h3>Get in Touch</h3>
            <input type="text" id="name" placeholder="Full name" required><br>
            <input type="email" id="email" placeholder="E-mail address" required><br>
            <input type="text" id="phone" placeholder="Phone number" required><br>
            <textarea id="message" rows="4" placeholder="How may we assist?"></textarea><br>
            <button type="submit" onclick=sendEmail()>Send</button><br>
        </form>
    </div>
    <br>
    <br>
    <footer>
        <p>
            The A Team &copy; 2023
        </p>
        <small>
            <a href="./index.php" target="_self">Home</a>
            <a href="aboutus.php">About Us</a>
            <a href="faqs.php">FAQs</a>
            <a href="privacy.php">Privacy</a>
            <a href="contactus.php">Contact Us</a>
            <a href="tenant.php" target="_self">I am a Tenant</a>
            <a href="agent.php" target="_self">I am an Agent</a>
        </small>
    </footer>
    <script src="scroll.js"> </script>
    <script src="https://smtpjs.com/v3/smtp.js"></script>
    <script type="text/javascript">
        function sendEmail() {
            Email.send({
                Host: ".gmail.com",
                Username: "kwmubayiwa@gmail.com",
                Password: "$ci3nc3RULEZ",
                To: "mubayiwakudzaishe@gmail.com",
                From: document.getElementById("email").value,
                Subject: "New Enquiry",
                Body: document.getElementById("message").value,
            })

            function popUp(message) {
                alert("Mail sent successfully.")
            }

        }
    </script>
</body>

</html>