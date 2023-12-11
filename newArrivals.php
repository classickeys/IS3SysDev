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
    <title>New Arrivals</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="./filterProperties.css">
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

    <?php
    if (isset($_GET['type'])) {
        $typ = $_GET['type'];
        $type = str_replace('%', ' ', $typ);
    } else {
        $type = 'All Properties';
        
    }

    ?>
    <!-- Filter Section  -->
<!-- Filter Section HTML -->
<section class="filter_container">
    <section class="filter">
        <div class="carousel-container">
            <div class="carousel">
                <a href="newArrivals.php">
                    <div class="carousel-item" id="carousel-filter">
                        Filter
                        <img src="./images/icons/filterBar/filter.png" alt="filter icon" height="30px" width="30px">
                    </div>
                </a>

                <a href="newArrivals.php?type=Houses">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/house.png" alt="house icon" height="40px" width="40px">
                        <p>House</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Bachelor%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/bachelor.png" alt="bachelor icon" height="40px" width="40px">
                        <p>Bachelor</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Apartment%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/apartment.png" alt="apartment" height="40px" width="40px">
                        <p>Apartment</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Cottage%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/cottage.png" alt="waterbackup" height="40px" width="40px">
                        <p>Cottage</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Modern%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/modern.png" alt="waterbackup" height="40px" width="40px">
                        <p>Modern</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Furnished%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/furnished.png" alt="waterbackup" height="40px" width="40px">
                        <p>Furnished</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Non-Furnished%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/non_furnished.png" alt="waterbackup" height="40px" width="40px">
                        <p>Non-Furnished</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Wifi%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/wifi.png" alt="waterbackup" height="40px" width="40px">
                        <p>Wifi</p>
                    </div>
                </a>

                <a href="newArrivals.php?type=Water Backup%Properties">
                    <div class="carousel-item">
                        <img src="./images/icons/filterBar/water_backup.png" alt="waterbackup" height="40px" width="40px">
                        <p>Water Backup</p>
                    </div>
                </a>


            </div>
        </div>
    </section>
</section>

    <section class="all-properties">
        <header>
            <h2><?php echo "New Arrivals -" . strtoupper($type); ?></h2>
        </header>
        <div class="row-container">
            <div class="image-container">
                <?php

                require_once("config.php"); //db details

                //connection 
                $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE) or die("Failed to connect to the database");

                //query
                // $query_img = "SELECT * FROM thea_team.main_pictures, thea_team.accomodation WHERE thea_team.accomodation.AccomodationID = thea_team.main_pictures.AccomodationID";
                // $query_img = "SELECT a.*, p.PhotoPath 
                //                 FROM accomodation AS a
                //                 INNER JOIN thea_team.main_pictures AS p 
                //                 ON a.AccomodationID = p.AccomodationID
                //                 WHERE (a.Name LIKE '%$searchTerm%' OR a.Description LIKE '%$searchTerm%' OR a.Address LIKE '%$searchTerm%')";

                $query_img = "SELECT V.PhotoPath, A.AccomodationID, A.Name, A.Description, AA.From_Date
                FROM thea_team.main_pictures V
                INNER JOIN accomodation A ON V.AccomodationID = A.AccomodationID 
                INNER JOIN agent_accomodation AA ON A.AccomodationID = AA.AccomodationID
                ";

                if ($type != 'All Properties') {
                    $words = explode(" ", $type);
                    switch ($words[0]) {
                        case "Houses":
                            $query_img = $query_img ." AND (Type = 'House')";
                            break;
                        case "Bachelor":
                            $query_img = $query_img ." AND (Bedrooms = 1 AND Type = 'Single')";
                            break;
                        case "Apartment":
                            $query_img = $query_img ." AND (Bedrooms < 4 AND (Type = 'Single' OR Type = 'Sharing'))"  ;
                            break;
                        case "Cottage":
                            $query_img = $query_img ." AND (Bedrooms = 1 AND Type = 'House')";
                            break;
                        case "Modern":
                            $query_img = $query_img ." AND (Modern = 1)";
                            break;
                        case "Furnished":
                            $query_img = $query_img ." AND (Furnished = 'Yes') ";
                            break;
                        case "Non-Furnished":
                            $query_img = $query_img ." AND (Furnished = 'No') ";
                            break;
                        case "Wifi":
                            $query_img = $query_img ." AND (Wifi = 1)";
                            break;
                        default:
                        $query_img = $query_img ." AND (Water_Backup = 1)";
                        break;

                    }
                }

                //query result
                $query_img .= "ORDER BY AA.From_Date DESC "; //AccomodationID or Name ASC"
                
                $result = mysqli_query($conn, $query_img) or die("The query couldn't be executed");

                $count = 1;
                $row = mysqli_fetch_array($result);
                if(empty($row)){
                    echo "<div class=\"all-item\">";
                    echo "<h2> This Search produced no results. </h2>";
                    echo "</div>";
                }

                while ($row ) {

                    $acc_id = $row['AccomodationID'];

                    // Create and execute the SQL query to fetch agent data
                    $a_query = "SELECT AVG(TAR.Final_Rating) AS AVGRating
                                FROM tenants_accomodation_rating TAR
                                WHERE TAR.AccomodationID = '$acc_id'
                                ";

                    $a_result = mysqli_query($conn, $a_query);

                    //$ratingsres = mysqli_query($conn, $ratings);

                    if($a_result && mysqli_num_rows($a_result) > 0){
                        $rating = mysqli_fetch_assoc($a_result);
                        $stars = displayStars($rating['AVGRating']);
                    } else {
                        $stars = displayStars(0);
                    }

                    echo "<div class=\"all-item\">";
                    echo "<a href=\"propertyPage.php?accommodationid=". $row['AccomodationID'] ."\">";
                    echo "<img src=\"" . $row['PhotoPath'] . "\" alt=\"" . $row['Name'] . "\">";
                    echo "</a>" ;
                    echo "<div class=\"overlay\"></div>";
                    echo "<p class=\"all-title\">" . $row['Name'] . "</p>";
                    echo "<p class=\"all-title\">" . $stars . "</p>";
                    echo "</div>";
                    $count++;
                    
                    if ($count % 6 == 0 && $row = mysqli_fetch_array($result)) {
                        echo "</div>";
                        echo "<div class=\"image-container\">";
                        echo "<div class=\"all-item\">";
                        echo "<a href=\"propertyPage.php?accommodationid=". $row['AccomodationID'] ."\">";
                        echo "<img src=\"" . $row['PhotoPath'] . "\" alt=\"" . $row['Name'] . "\">";
                        echo "</a>" ;
                        echo "<div class=\"overlay\"></div>";
                        echo "<p class=\"all-title\">" . $row['Name'] . "</p>";
                        echo "<p class=\"all-title\">" . $stars . "</p>";
                        echo "</div>";
                        $count = 2;
                    }
                    $row = mysqli_fetch_array($result); 
                }

                ?>


            </div>
        </div>
    </section>



    <footer>
        <p>
            The A Team &copy; 2023
        </p>
        <small>
            <a href="index.php" target="_self">Home</a>
            <a href="aboutus.html">About Us</a>
            <a href="faqs.html">FAQs</a>
            <a href="#">Privacy</a>
            <a href="contactus.html">Contact Us</a>
            <a href="tenant.php" target="_self">I am a Tenant</a>
            <a href="agent.php" target="_self">I am an Agent</a>
        </small>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Get the current search term
            const searchTerm = "<?php echo $searchTerm; ?>";

            // Get references to the filter links
            const filterAllLink = document.getElementById("filter-all");
            const filterHousesLink = document.getElementById("filter-houses");
            const filterBachelorLink = document.getElementById("filter-bachelor");
            const filterApartmentLink = document.getElementById("filter-apartment");
            const filterCottageLink = document.getElementById("filter-cottage");
            const filterModernLink = document.getElementById("filter-modern");
            const filterFurnishedLink = document.getElementById("filter-furnished");
            const filterNotFurnishedLink = document.getElementById("filter-not-furnished");
            const filterWifiLink = document.getElementById("filter-wifi");
            const filterWaterBackupLink = document.getElementById("filter-waterbackup");

            filterAllLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?search_term=" + searchTerm;
            });
            filterHousesLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Houses&search_term=" + searchTerm;
            });
            filterBachelorLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Bachelor%Properties&search_term=" + searchTerm;
            });
            filterApartmentLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Apartment%Properties&search_term=" + searchTerm;
            });
            filterCottageLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Cottage%Properties&search_term=" + searchTerm;
            });
            filterModernLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Modern%Properties&search_term=" + searchTerm;
            });
            filterFurnishedLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Furnished%Properties&search_term=" + searchTerm;
            });
            filterNotFurnishedLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Non-Furnished%Properties&search_term=" + searchTerm;
            });
            filterWifiLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Wifi%Properties&search_term=" + searchTerm;
            });
            filterWaterBackupLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "searchProperties.php?type=Water Backup%Properties&search_term=" + searchTerm
            });

        });

    </script>

    <script src="index.js"></script>
</body>
</html>