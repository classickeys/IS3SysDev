<?php
    
    // Include your config.php for database connection
    require_once("config.php");
    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["search_submit"])) {
            // Handle search functionality here
            $searchTerm = mysqli_real_escape_string($conn, $_POST["search"]);
            header("Location: searchProperties.php?search_term=" . $searchTerm);
        } 
    }

    // Check if the user is logged in and is a tenant or agent
    session_start();

    // Function to check if the user is a tenant
    function isTenant($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $tenant_query = "SELECT TenantID FROM tenant WHERE UserID = '$user_id'";
        $tenant_result = mysqli_query($conn, $tenant_query);
        
        return ($tenant_result && mysqli_num_rows($tenant_result) > 0);
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

    // Function to check if the user is an admin
    function isAdmin($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $admin_query = "SELECT AdminID FROM administrator WHERE UserID = '$user_id'";
        $admin_result = mysqli_query($conn, $admin_query);

        
        return ($admin_result && mysqli_num_rows($admin_result) > 0);

    }

    

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
    <title>Off-Camp Review</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="index.css">  
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

            <!-- all items on navbar in the center -->
            <li class="center">
                <div class="searchContainer">
                    <a class="searchBtn" href="#">
                        Search Properties...
                        <img id="search_icon" src="./images/icons/search.png" alt="search icon" height="20px" width="20px">
                    </a>
                    <form action="" method="post">
                        <input type="text" name="search" class="searchInput">
                        <input type="submit" name="search_submit" id="search_submit" value="hide">
                    </form>
                </div>
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

    <!-- Featuring Section  -->

    <section class="featuring">
        <?php
        // db details
        require_once("config.php");

        // connection
        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE) or die("Failed to connect to Database");
        // imgQuery
        // total of entries
        $nb_query = "SELECT * FROM if0_35600039_Thea_team.main_pictures";

        $nb_result = mysqli_query($conn, $nb_query) or die("database query has failed");
        $nb = 0;
        while ($row = mysqli_fetch_array($nb_result)) {
            $nb++;
        }
        // random number within those entries
        $rand_nb = rand(0, $nb - 1);
        //echo "<h1 style = \" postion: absolute; margin-top: 600px; \">". $nb ."</h1>";


        // query images
        $img_query = "SELECT * FROM if0_35600039_Thea_team.main_pictures, if0_35600039_Thea_team.accomodation WHERE if0_35600039_Thea_team.accomodation.AccomodationID = if0_35600039_Thea_team.main_pictures.AccomodationID  ";
        $img_result = mysqli_query($conn, $img_query) or die("database query has failed");
        $count = 0;
        $accomID;

        while ($row = mysqli_fetch_array($img_result)) {
            if ($count == $rand_nb) {

                $path = $row['PhotoPath'];
                echo "<a id=\"featureLink\" href=\"propertyPage.php?accommodationid=". $row['AccomodationID'] ."\">";
                echo "<img id=\"featureImg\" src= \"$path\" alt=\" Image of house\">";
                echo "<div class=\"opacity\"></div> </a>";
                //echo "<\a>";
                $accomID = $row['AccomodationID'];
                break;
            }
            $count++;
        }
        //display detail
        echo "<h1 class = \"featuringTitle\" >" . strtoupper($row['Name']) . "</h1>" ;
        echo "<p class = \"featuringP\" >" . $row['Description'] . "</p>";
        ?>
    </section>

    <!-- Filter Section  -->
    <section class="filter_container">
        <section class="filter">
            <div class="carousel-container">
                <div class="carousel">

                    <a href="filterProperties.php">
                        <div class="carousel-item" id="carousel-filter">
                            Filter
                            <img src="./images/icons/filterBar/filter.png" alt="filter icon" height="30px" width="30px">
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Houses">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/house.png" alt="house icon" height="40px" width="40px">
                            <p>House</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Bachelor%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/bachelor.png" alt="bachelor icon" height="40px" width="40px">
                            <p>Bachelor</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Apartment%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/apartment.png" alt="apartment" height="40px" width="40px">
                            <p>Apartment</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Cottage%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/cottage.png" alt="waterbackup" height="40px" width="40px">
                            <p>Cottage</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Modern%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/modern.png" alt="waterbackup" height="40px" width="40px">
                            <p>Modern</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Furnished%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/furnished.png" alt="waterbackup" height="40px" width="40px">
                            <p>Furnished</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Non-Furnished%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/non_furnished.png" alt="waterbackup" height="40px" width="40px">
                            <p>Non-Furnished</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Wifi%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/wifi.png" alt="waterbackup" height="40px" width="40px">
                            <p>Wifi</p>
                        </div>
                    </a>

                    <a href="filterProperties.php?type=Water Backup%Properties">
                        <div class="carousel-item">
                            <img src="./images/icons/filterBar/water_backup.png" alt="waterbackup" height="40px" width="40px">
                            <p>Water Backup</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    </section>

    <!-- trending this week section  -->
    <section class="trending-section">
        <header>
            <h2>Demo Agent:  Login: A24A7239 Password: H3DuraWuin</h2>
            <h2>Trending Properties</h2> 
            <a href="trendingProperties.php" class="view-all-button">View All</a>
        </header>
        <section class="filter">
            <div class="carousel-container">
                <div class="carousel">
                    <?php
                        require_once("config.php");

                        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE) or die("Failed to connect to the database!");

                        $trending_query = "SELECT * FROM if0_35600039_Thea_team.main_pictures, if0_35600039_Thea_team.accomodation WHERE if0_35600039_Thea_team.accomodation.AccomodationID = if0_35600039_Thea_team.main_pictures.AccomodationID ORDER BY ViewCount DESC";
                        $trending_result = mysqli_query($conn, $trending_query) or die(mysqli_error($conn));
                        
                        $count = 1;
                        if(empty($trending_result)){
                            echo "<div class=\"all-item\">";
                            echo "<h2>There Are No Trending Properties.</h2>";
                            echo "</div>";
                        }
                        else{
                            while ($trending_properties = mysqli_fetch_assoc($trending_result)) {

                                $ratings = "SELECT Final_Rating FROM tenants_accomodation_rating WHERE AccomodationID = '" .  $trending_properties['AccomodationID'] . "'";
                                
                                $ratingsres = mysqli_query($conn, $ratings);
                               

                                if($ratingsres && mysqli_num_rows($ratingsres)){
                                    $rating = mysqli_fetch_assoc($ratingsres);
                                    $stars = displayStars($rating['Final_Rating']);
                                } else {
                                   $stars = displayStars(0);
                                }
                                
                                echo '<a href="propertyPage.php?accommodationid=' . $trending_properties['AccomodationID'] . '">';
                                echo '<div class="carousel-item">';
                                echo '<div class="unavailable-banner">';
                                echo '<img src="' . $trending_properties['PhotoPath'] . '" alt="House Image">';
                                //echo '<div class="overlay"></div>';
                                echo '<div class="unavailable-overlay" id="unavailableOverlay">';
                                echo '<p class="unavailable-text">UNAVAILABLE</p>';
                                echo '</div>';
                                echo '</div>';
                                echo '<p>' . $trending_properties['Name'] . '</p>';
                                echo '<p>' . $stars . '</p>';
                                echo '</div>';
                                echo '</a>';
                            }
                        }
                        mysqli_close($conn);
                    ?>
                </div>
            </div>
        </section>
    </section>

    <!-- NewArrivals Section -->

    <section id="NewArrivals">
        <header>
            <h2>New Arrivals</h2>
            <a href="newArrivals.php" class="view-all-button">View All</a>
        </header>
        <?php
            require_once("config.php");

            $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE) or die("Failed to connect to the database!");
            
            $new = "SELECT V.VideoPath, A.AccomodationID, A.Name, A.Description, AA.From_Date
                    FROM videos V
                    INNER JOIN accomodation A ON V.AccomodationID = A.AccomodationID 
                    INNER JOIN agent_accomodation AA ON A.AccomodationID = AA.AccomodationID
                    ORDER BY AA.From_Date DESC 
                    LIMIT 1";
                
            $new_result = mysqli_query($conn, $new) or die(mysqli_error($conn));

            // Check if there are results before trying to fetch data
            if ($new_result && mysqli_num_rows($new_result) > 0) {
                $new_data = mysqli_fetch_assoc($new_result);

                $acid = $new_data['AccomodationID'];

                $vid_rate = "SELECT AVG(TAR.Final_Rating) AS AVGRating
                                FROM tenants_accomodation_rating TAR
                                WHERE TAR.AccomodationID = '$acid'";

                $vid_res = mysqli_query($conn, $vid_rate) or die(mysqli_error($conn));
                $vid_data = mysqli_fetch_assoc($vid_res);

            } else {
                // Handle the case where no data is found, e.g., show a default message or error handling
                $new_data = array(
                    'VideoPath' => 'default_video.mp4', // Provide a default video path
                    'Name' => 'No New Arrivals Found',
                    'Final_Rating' => 'N/A',
                    'Description' => 'No new arrivals are available at the moment.',
                    'AVGRating' => 'Not Yet Rated!'
                );
            }
        ?>

        <div class="content-container">
            <div class="video-container">
                <video controls autoplay muted loop>
                    <source src="<?php echo $new_data['VideoPath'];?>">
                    Your Browser Does Not Support The Video Tag
                </video>
            </div>
            <div class="info-container">
                <div class="inner-info-container">
                    <h1><?php echo $new_data['Name'];?></h1>
                    <p class="rating"><?php if($vid_data['AVGRating'] == NULL) {echo 'Not Yet Rated!'; } else { echo $vid_data['AVGRating']; };?></p> 
                    <button type="button">Clean</button>
                    <button type="button">Secure</button>
                    <p><?php echo $new_data['Description'];?></p>

                    <!-- <p><?php echo $new_data['Description'];?></p> -->
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>
            The A-Team &copy; 2023
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
    <script src="search.js"> </script>
    <script>
        <?php
            require_once("config.php");

            $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE) or die("Failed to connect to the database!");

            $avail = "SELECT Availability FROM accomodation WHERE AccomodationID = '" .  $trending_properties['AccomodationID'] . "'"; //Kudzi edit for availibility
            $availibility = mysqli_query($conn, $avail);    //Kudzi edit for availibility
            $row = mysqli_fetch_assoc($availibility);
            mysqli_close($conn);
        ?>
        const conditionMet = $row['Availability'];
        const unavailableOverlay = document.getElementById('unavailableOverlay');

        if(conditionMet = 0){
            unavailableOverlay.style.display = 'block'; //display the "UNAVAILABLE" banner
        }else if(conditionMet = 1){
            unavailableOverlay.style.display = 'none';  //hide the "UNAVAILABLE" banner
        }
    </script>
</body>

</html>