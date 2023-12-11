<?php

require_once("check.php");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        *{
            font-family: "Inter", sans-serif;
            color: #213644;
        }
        body{
            display: flex;
            flex-direction: column;
            min-height: 100%;
            height: 100vh;
        }
        
        h1{
            font-family: "Gotham", sans-serif;
            text-transform: uppercase;
            text-align: center;
            position: relative;
            width: fit-content;
            margin: auto;
            padding: 2%;
            
        }
        .Questions p{
            text-align: left;
            
            position: relative;
            margin: auto;
            padding: 2%;
            padding-left: 10%;
            border-radius: 10px;
        }
        .Questions{
            margin-top: 5%;
            width: fit-content;
            margin-left: 30%;
            margin-right: 30%;
            padding: 2%;
            background-color: #ffff;
            border-radius: 15px;
            height: 100vh;

        }

        footer{
            flex-shrink: 0;
        }
        .content, .collapsible{
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgb(0, 0, 0, 0.2);
            color: #213644;
        }


        
/*COLLAPSIBLE CONTENT FOR FAQ PAGE*/
.collapsible {
  background-color: #e6e6e6;
  color: black;
  cursor: pointer;
  width: 80%;
  margin-left: 10%;
  border: none;
  padding: 20px;
  text-align: left;
  outline: none;
  font-size: 12px;
  font-weight: bold;
  overflow: hidden;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
      /* Box shadow for the raised effect */
      transition: transform 0.2s, box-shadow 0.2s;
}


.content {
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
      /* Box shadow for the raised effect */
      transition: transform 0.2s, box-shadow 0.2s; 
  width: 80%;
  /*margin:auto;*/
  margin-left: 10%; 
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
  background-color: rgba(198, 171, 124, 0.1);
  
  border: none;
  font-size: 12px;
  padding-top: 1%;
  padding-bottom: 1%;
 
}




.active, .collapsible:hover {
    background-color: rgba(33, 54, 68, 0.85);
    color: #e6e6e6;

}

.collapsible:after {
    
  content: '\002B';
  color: rgb(0,0,0);
  font-weight: bold;
  float: right;

  
}

.active:after {
  content: "\2212";
  
  
}



/*FAQ END*/
        
    </style>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="index.css">
    <title>FAQs</title>
</head>
<body>
    <header>
        <nav id="navbar" >
            <ul>
                <!-- Nav bar  -->
    
                <!-- img on nav bar -->
                <li class="left">
                    <a href="index.php" id="homeBtn" rel="noopener noreferrer">
                        <img id="home_png" src="./images/home.png" height="35" width="85" alt="Off-Camp Review Icon">
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
                                <a href="propertyPage.php?accommodationid=' . $outcome['AccomodationID'] .'#rate" class="navBtn" id="reviewBtn">
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
                                <a href="agent_add_listing.php?agentid=' . $agent_id .'" class="navBtn" id="reviewBtn">
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
                    }
                    else{
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

    <!-- FAQ section -->
    <div class="Questions">
        <h1>Frequently Asked Questions</h1>
        <button class="collapsible">How do I search for off-campus housing that matches my specific preferences and needs?</button>
        <div class="content">
            <p>
                You can search for off-campus housing by using the "Search Properties" bar located at the top of your screen.
            </p>
        </div>
        <br>
        <button class="collapsible">Is there information about the neighborhood and its proximity to essential services like grocery stores, public transportation, and schools?</button>
        <div class="content">
            <p>
                Off-Campus Review makes an effort to ensure that all prospective tenants are made aware of 
                ammenities like water backup, WiFi availability and furnishment and proximity to places of interest
            </p>
        </div>
        <br>
        <button class="collapsible">What is the process for submitting a rental application through this platform?</button>
        <div class="content">
            <p> You can proceed to contact the agent to make a rental enquiry. 
            </p>
        </div>
        <br>
        <button class="collapsible">Can I see reviews or ratings from previous tenants who have lived in the same property?</button>
        <div class="content">
            <p>Yes, you can view and see the ratings of individuals who have stayed in the same property</p>
        </div>
        <br>
        <button class="collapsible">How do I contact property owners or managers to schedule a viewing or ask questions?</button>
        <div class="content">
            <p>
                Contact them via their details on the property page
            </p>
        </div>
    </div>
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

    <script>
    var coll = document.getElementsByClassName("collapsible");
    var i;

    for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        if (content.style.maxHeight){
        content.style.maxHeight = null;
        } else {
        content.style.maxHeight = content.scrollHeight + "px";
        } 
    });
    }
    
    </script>
    <script src="scroll.js"></script>
</body>
</html>