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

    body {
      margin: 0px;
      padding: 0%;
    }

    html,
    body {
      height: 100%;
    }

    h1,
    h2 {
      text-align: center;
      font-family: "Gotham", sans-serif;
      text-transform: uppercase;

    }

    p {
      font-size: 12px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    @media screen and (max-width: 768px){
      .grid{
        display: block;
      }
      .person{
        margin: 2%;
      }
    }

    .person {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
      /* Box shadow for the raised effect */
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .person img {
      width: 100%;
      aspect-ratio: 6/7;
      object-fit: cover;
      max-width: 100%;
      border-radius: 5px;
      /*border-radius: 50%;*/
    }

    @media screen and (max-width: 650px) {
      .column {
        width: 100%;
        display: block;
      }
    }
  </style>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
  <link rel="stylesheet" href="./index.css">
  <title>About Us</title>
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

          <a href="#" class="navBtn" id="alertBtn" style="display: none;">
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
                                <a href="propertyPage.php?accommodationid=' . $outcome['AccomodationID'] . '#rate" class="navBtn" id="reviewBtn">
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
  <!-- Add icon library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <div class="container">
    <h1>About Us</h1>
    <div class="grid">
      <div class="person">
        <img src="./profile_pictures/keys.jpg" alt="Person 1">
        <h2>Kelechi Nwachukwu</h2>
        <p>Project Manager/ Lead Developer</p>
        <p><i>The journey of a thousand miles begins with your first step.</i></p>
      </div>
      <div class="person">
        <img src="./profile_pictures/taku.jpg" alt="Person 2">
        <h2>Takunda Mazumbuze</h2>
        <p>Test Case Analyst</p>
        <p><i>Live, Learn, Love.</i></p>
      </div>
      <div class="person">
        <img src="./profile_pictures/denzel.jpg" alt="Person 3">
        <h2>Denzel Matapuri</h2>
        <p>Lead Back-End Developer</p>
        <p><i>Faithfully serving the season you are in, always produces results!!</i></p>
      </div>
      <div class="person">
        <img src="./profile_pictures/rinaee.jpg" alt="Person 4">
        <h2>Rinae Makhado</h2>
        <p>Design Co-ordinator</p>
        <p><i>I can do all things through Christ who strengthens me.</i></p>
      </div>
      <div class="person">
        <img src="./profile_pictures/kudz.jpg" alt="Person 5">
        <h2>Kudzaishe Mubayiwa</h2>
        <p>Deputy Project Manager/ Lead Front-End Developer</p>
        <p><i>Superbia in Prido - Pride in Battle.</i></p>
      </div>
    </div>
  </div>
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
      <a href="index.php" target="_self">I am a Tenant</a>
      <a href="index.php" target="_self">I am an Agent</a>
    </small>
  </footer>
  <script src="scroll.js"> </script>
</body>

</html>