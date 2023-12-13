<?php
require_once("config.php");

$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');


// Increment the view count for the viewed accommodation
if (isset($_REQUEST['accommodationid'])) {
    $accommodationID = $_REQUEST['accommodationid']; //passed via URL
    define("ACCOMODATION", $accommodationID);
    $incrementViewsQuery = "UPDATE accomodation SET ViewCount = (ViewCount + 1) WHERE AccomodationID = '$accommodationID'";
    mysqli_query($conn, $incrementViewsQuery) or die(mysqli_error($conn));
}

// Query to fetch property details and main image
$accommodation_query = "SELECT a.*, ta.TenantID, aa.AgentID
                    FROM accomodation AS a
                    LEFT JOIN tenant_accomodation AS ta ON a.AccomodationID = ta.AccomodationID
                    LEFT JOIN agent_accomodation AS aa ON a.AccomodationID = aa.AccomodationID
                    WHERE a.AccomodationID = '$accommodationID'";

$photos_query = "SELECT * FROM photographs WHERE AccomodationID = '$accommodationID' LIMIT 4";
$videos_query = "SELECT * FROM videos WHERE AccomodationID = '$accommodationID'";

$accommodation_result = mysqli_query($conn, $accommodation_query);
$photos_result = mysqli_query($conn, $photos_query);
$videos_result = mysqli_query($conn, $videos_query);
//display this on the big section, else display the main pic, if no video found...

if (!$accommodation_result) {
    // Handle property not found
    echo "<script>alert('Property not found.');</script>";
} else {
    $propertyData = mysqli_fetch_assoc($accommodation_result);
    // Retrieve property data

    // Query to fetch the main property image
    $main_photo_query = "SELECT * FROM main_pictures WHERE AccomodationID = '$accommodationID'";
    $main_photo_result = mysqli_query($conn, $main_photo_query);

    if ($main_photo_result) {
        $mainPhotoData = mysqli_fetch_assoc($main_photo_result);
    }
}

// Query to fetch ratings and reviews
$query = "SELECT * FROM tenants_accomodation_rating WHERE AccomodationID = '$accommodationID'";

$result = mysqli_query($conn, $query);

// Initialize with a default value
$overallRating = 0.00;
$safetyRating = 0.00;
$maintenanceRating = 0.00;
$noiselevels = 0.00;
$convenience = 0.00;
$value = 0.00;

if ($result && mysqli_num_rows($result) > 0) {
    $ratings = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Calculate average ratings
    $safetyRating = calculateAverageRating($ratings, 'Safety_Rating');
    $maintenanceRating = calculateAverageRating($ratings, 'Maintenance_Rating');
    $noiselevels = calculateAverageRating($ratings, 'Noise_Levels_Rating');
    $convenience = calculateAverageRating($ratings, 'Convenience_Rating');
    $value = calculateAverageRating($ratings, 'Value_For_Money_Rating');

    // Calculate overall rating
    $overallRating = calculateOverallRating($ratings);
}

function calculateAverageRating($ratings, $category)
{
    $total = 0;
    $count = 0;
    foreach ($ratings as $rating) {
        if (!empty($rating[$category])) {
            $total += (float)$rating[$category];
            $count++;
        }
    }
    return $count > 0 ? round($total / $count, 2) : 0.00;
}

function calculateOverallRating($ratings)
{
    // Calculate the overall rating as an average of individual category ratings
    // Adjust weights as needed
    $safetyWeight = 1;
    $maintenanceWeight = 1;
    $noicelevelsWeight = 1;
    $convenienceWeight = 1;
    $value = 1;

    $totalRating =
        ($safetyWeight * calculateAverageRating($ratings, 'Safety_Rating') +
            $maintenanceWeight * calculateAverageRating($ratings, 'Maintenance_Rating') +
            $noicelevelsWeight * calculateAverageRating($ratings, 'Noise_Levels_Rating') +
            $convenienceWeight * calculateAverageRating($ratings, 'Convenience_Rating') +
            $value * calculateAverageRating($ratings, 'Value_For_Money_Rating')
        ) /
        ($safetyWeight + $maintenanceWeight + $noicelevelsWeight + $convenienceWeight + $value
        );

    return round($totalRating, 2);
}

// function displayStars($rating)
// {
//     $maxStars = 5; // Maximum number of stars
//     $roundedRating = round($rating); // Round the rating to the nearest whole number
//     if( $rating > 0){
//         // Output the star icons based on the rounded rating
//         $starsHtml = '';
//         for ($i = 1; $i <= $roundedRating; $i++) {
//             $starsHtml .= '<span class="nyeredzi">★</span>';
//         }
//     } else {
//         $starsHtml = '';
//         for ($i = 1; $i <= $maxStars; $i++) {
//             $starsHtml .= '<span class="nyeredzi">☆</span>';
//         }
//     }

//     return $starsHtml;
// }

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

//getting the previous rating and the review for the accomodation
$rev_query = " SELECT r.*, t.ProfilePicture, t.Name, t.Surname
                            FROM tenants_accomodation_rating AS r
                            JOIN tenant AS t ON r.TenantID = t.TenantID
                            WHERE r.AccomodationID = '$accommodationID'
                            ";
$rev_result = mysqli_query($conn, $rev_query);


// Check if there are reviews and ratings
if ($rev_result && mysqli_num_rows($rev_result) > 0) {
    $reviews = mysqli_fetch_all($rev_result, MYSQLI_ASSOC);
} else {
    // Handle no reviews found
    $reviews = [];
}

// Check if the user is logged in and is a tenant or agent
session_start();

// Function to check if the user is a tenant
function isTenant($user_id)
{
    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    $tenant_query = "SELECT TenantID FROM tenant WHERE UserID = '$user_id'";
    $tenant_result = mysqli_query($conn, $tenant_query);

    return ($tenant_result && mysqli_num_rows($tenant_result) > 0);
}

// Function to check if the user is an agent
function isAgent($user_id)
{
    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    $agent_query = "SELECT AgentID FROM agent WHERE UserID = '$user_id'";
    $agent_result = mysqli_query($conn, $agent_query);


    return ($agent_result && mysqli_num_rows($agent_result) > 0);
}

// Function to check if the user is an admin
function isAdmin($user_id)
{
    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    $admin_query = "SELECT AdminID FROM administrator WHERE UserID = '$user_id'";
    $admin_result = mysqli_query($conn, $admin_query);


    return ($admin_result && mysqli_num_rows($admin_result) > 0);
}

if (isset($_POST["submit_rating_review"])) {

    $tenant_id = mysqli_real_escape_string($conn, $_POST["tenant_id"]);
    $safety_rating = mysqli_real_escape_string($conn, $_POST["safety_rating"]);
    $main_rating = mysqli_real_escape_string($conn, $_POST["main_rating"]);
    $con_rating = mysqli_real_escape_string($conn, $_POST["con_rating"]);
    $noise_rating = mysqli_real_escape_string($conn, $_POST["noise_rating"]);
    $value_rating = mysqli_real_escape_string($conn, $_POST["value_rating"]);
    $tenant_review = mysqli_real_escape_string($conn, $_POST["tenant_review"]);

    $rate_query = "SELECT * FROM tenants_accomodation_rating 
                        WHERE AccomodationID = '$accommodationID' 
                        AND TenantID = '$tenant_id'";

    $result = mysqli_query($conn, $rate_query) or die(mysqli_error($conn));

    if ($result && mysqli_num_rows($result) > 0) {
        //tenant has made a rating/review before
        $update_rating_review_query = "UPDATE tenants_accomodation_rating
                                            SET Safety_Rating = '$safety_rating',
                                            Maintenance_Rating = '$main_rating',
                                            Noise_Levels_Rating= '$noise_rating',
                                            Value_For_Money_Rating = '$value_rating',
                                            Convenience_Rating = '$con_rating',
                                            Review = '$tenant_review'
                                            WHERE TenantID = '$tenant_id'
                                            AND AccomodationID = '$accommodationID'
                                            ";
    } else {
        $update_rating_review_query = "INSERT INTO tenants_accomodation_rating
                                            (TenantID, AccomodationID, Review, Safety_Rating, Maintenance_Rating, Noise_Levels_Rating, Value_For_Money_Rating, Convenience_Rating, Timestamp)
                                            VALUES ('$tenant_id', '$accommodationID', '$tenant_review','$safety_rating', '$main_rating', '$noise_rating', '$value_rating', '$con_rating', '" . date('Y-m-d H:i:s') . "')";
    }

    $update_rating_review_result = mysqli_query($conn, $update_rating_review_query);

    if ($update_rating_review_result) {
        // Rating and review updated successfully
        // $agent_rating = $tenant_rating;
        // $agent_review = $tenant_review;

        echo '<script type="text/javascript">alert("Success updating rating and review");</script>';
        header("Location: propertyPage.php?accommodationid=" . $accommodationID);
    } else {
        // Handle the error
        echo '<script type="text/javascript">alert("' . mysqli_error($conn) . '");</script>';
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $propertyData['Name']  ?></title>
    <link style="border-radius: 15px;" rel="shortcut icon" href="  <?php echo  $mainPhotoData['PhotoPath'] ?> " type="image/x-icon" height="3px" width="3px">
    <link rel="stylesheet" href="propertyPage.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/8320e0ead0.js" crossorigin="anonymous"></script>
    <style>
        /*  */
        /* .bigPicture{
            margin: auto;
            outline: red;
        } */
        .grid-container {
            display: grid;
            gap: 5%;
            margin: 5%;
            /* padding: 5%; */
            /* box-shadow: 0px 0px 15px rgb(0, 0, 0, 0.3);
            border-radius: 15px; */
            /*overflow-x: scroll;*/
        }

        .inner-grid {
            display: grid;
            gap: 5%;
            margin-right: 5%; 
        }

        @media (max-width: 968px) {

            /* Adjust the breakpoint as needed */
            .grid-container,
            .inner-grid {
                display: block;
                /* Make the grid stack on top of each other */
            }

            .grid-item {
                grid-template-columns: 1fr;
                margin-top: 5%;
            }
        }

        .grid-item {
            /* background-color: lightgray; */
            text-align: left;
            font-size: 18px;
            /* border: 2px solid #c6ab7c; */
            border-radius: 15px;
            box-shadow: 0px 0px 15px rgb(0, 0, 0, 0.3);
            padding: 5%;
        }

        /* .ig{
            /* text-align: left;
            font-size: 18px; */
        /* border: 2px solid #c6ab7c; */
        /* border-radius: 15px;
            box-shadow: 0px 0px 15px rgb(0, 0, 0, 0.3);
            padding: 5%; */


        /*property_about*/
        .item1 {
            grid-column: 1;
            grid-row: 1 / span 1;

            /* max-width: 50%;
            display: flex;
            margin: 0 auto; */
        }

        .inner1 {
            grid-column: 1 / span 2;
            grid-row: 1;
        }

        /*property_ratings*/
        .item2 {
            grid-column: 2;
            grid-row: 1;
            /* max-width: 350px; */
            min-width: 350px;
            padding: 5%;
            height: fit-content;
            
        }

        .inner2 {
            grid-column: 1;
            grid-row: 2;
        }

        /*property_reviews*/
        .item3 {
            grid-column: 1/span 2;
            grid-row: 2;
            overflow-x:auto;
        }

        .inner3 {
            grid-column: 2;
            grid-row: 2;
            text-align: center;
        }

        
        .overall_rating,
        .rating_category {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
            justify-content: space-between;
        }

        .rn,
        .ra {
            display: inherit;
            font-size: 14px;

        }
    </style>
</head>

<body>
    <nav id="navbar">
        <ul>
            <!-- Nav bar  -->
            <!-- img on nav bar -->
            <li class="left">
                <a href="index.php" id="homeBtn" rel="noopener noreferrer">
                    <img id="home_png" src="./images/home.png" height="40" width="85" alt="Off-Camp Review Icon">
                </a>
            </li>

            <!-- all items on the right side of navbar -->
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
    <main class="propertyMain">
        <h2><?php echo $propertyData['Name']; ?></h2>
        <div class="propertyMainContainer">
            <div class="mainPicture">
                <?php
                if (isset($mainPhotoData['PhotoPath'])) {
                    echo '<img class="bigPicture" src="' . $mainPhotoData['PhotoPath'] . '" alt="Main Property Image">';
                }
                ?>
            </div>
            <div class="slideshow-container">

                <div class="slideshow-breadcrumbs">
                    <?php
                    $count = 1;
                    mysqli_data_seek($photos_result, 0); // Reset the result set pointer
                    $new = $mainPhotoData['PhotoPath'];
                    echo '<div class="breadcrumb" data-slide="' . $count . '">';
                    echo '<img class="bigPicture" src="' .  $new . '" alt="Image of the property">';
                    echo '</div>';
                    $count++;
                    while ($photoData = mysqli_fetch_assoc($photos_result)) {
                        if (isset($photoData['Photo_Path'])) {
                            echo '<div class="breadcrumb" data-slide="' . $count . '">';
                            echo '<img src="' . $photoData['Photo_Path'] . '" alt="Image of the property">';
                            echo '</div>';
                            $count++;
                        }
                    }
                    ?>
                </div>

            </div>
            <div class="grid-container">
                <div class="grid-item item1" alt="property_about">
                    <section class="property_description">
                        <h3>Description</h3>
                        <div class="property_address">
                            <p><?php echo 'Address: ' . $propertyData['Address']; ?> </p>
                            <p><?php echo $propertyData['Description']; ?></p>
                        </div>
                    </section>
                    <div class="inner-grid">
                        <div class="ig inner2">
                            <section class="property_amenities">
                                <h3>Amenities</h3>
                                <ul>
                                    <?php
                                    // Define the columns representing amenities in the database
                                    $amenityColumns = array(
                                        'Modernised' => 'Modern',
                                        'Furnished' => 'Furnished',
                                        'Accommodation Type' => 'Type',
                                        'Rent' => 'Rent',
                                        'Deposit' => 'Deposit',
                                        'Bathrooms' => 'Bathrooms',
                                        'Bedrooms' => 'Bedrooms',
                                        'Distance From RU Campus' => 'Distance_From_Campus',
                                        'NSFAS Accredited' => 'NSFAS_Accredited',
                                        'Water Backup' => 'Water_Backup',
                                        'Electricity Backup' => 'Electricity_Backup',
                                        'Wifi' => 'WiFi',
                                        'Parking' => 'Parking',
                                        'Smoking Allowed' => 'Smoking',
                                        'Pets Allowed' => 'Pets',
                                        'Prepaid Electricity' => 'Electricity',
                                        'Water Included In Rent' => 'Water',
                                        'Balcony' => 'Balcony',
                                        'Security' => 'Security'
                                        //'Available From' => 'Availability'
                                        // Add more amenities here
                                    );

                                    // Loop through the amenity columns
                                    foreach ($amenityColumns as $displayName => $columnName) {
                                        if ($columnName == 'Type') {
                                            echo '<li>' . $displayName . ' : ' . $propertyData[$columnName] . '</li>';
                                        } elseif ($columnName == 'Furnished') {
                                            echo '<li>' . $displayName . ' : ' . $propertyData[$columnName] . '</li>';
                                        } elseif ($columnName == 'Rent' || $columnName == 'Deposit') {
                                            echo '<li>' . $displayName . ' : R' . intval($propertyData[$columnName]) . '</li>';
                                        } elseif ($columnName == 'Distance_From_Campus') {
                                            echo '<li>' . $displayName . ' : ' . intval($propertyData[$columnName]) . 'km' . '</li>';
                                        } elseif ($propertyData[$columnName] == 1) {
                                            echo '<li>' . $displayName . ' : Yes ' . '</li>';
                                        } elseif ($propertyData[$columnName] == 0) {
                                            echo '<li>' . $displayName . ' : No' . '</li>';
                                        } else {
                                            echo '<li>' . $displayName . ' : ' . $propertyData[$columnName]  . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </section>
                        </div>

                        <div class="ig inner3">


                                    

                                <h3>Agent Details</h3>
                                <?php 
                                require_once("config.php");

                                $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                                    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                                
                                    $accomodation_idd = ACCOMODATION;
                                    $a_query = "SELECT AgentID from if0_35600039_Thea_team.agent_accomodation WHERE AccomodationID = '$accommodationID'";
                                    $a_result = mysqli_query($conn, $a_query);

                                    $arating = mysqli_fetch_assoc($a_result);
                                    $agentsID = $arating['AgentID'];
                                    $r_query = "SELECT * from agent WHERE agent.AgentID = '$agentsID'";
                                    $r_result = mysqli_query($conn, $r_query);

                                    

                                    $rating = mysqli_fetch_assoc($r_result);

                                     $q_query = "SELECT AVG(TAR.Rating_Value) AS AVGRating
                                     FROM if0_35600039_Thea_team.tenants_agent_rating TAR
                                     WHERE TAR.AgentID = '$agentsID'
                                     ";

                                     $q_result = mysqli_query($conn, $q_query);

                                    if($q_result && mysqli_num_rows($q_result) > 0){
                                         $rrating = mysqli_fetch_assoc($q_result);
                                         $stars = displayStars($rrating['AVGRating']);
                                    } else {
                                         $stars = displayStars(0);
                                     }
                                
                                
                                if ($rating) { ?>
                                    <div class="profile-card">
                                        <!-- Agent's Profile Picture and Details -->
                                        <div id="profile-picture">
                                            <img src="<?php
                                                        if ($rating['ProfilePicture'] != NULL) {
                                                            echo htmlspecialchars($rating['ProfilePicture']);
                                                        } else {
                                                            echo 'profile_pictures/default_profile_pic.png';
                                                        }; ?>" alt="Agent's Profile Picture" id="agentProfilePicture" height="60px" width="60px" style="border-radius: 15px;">
                                        </div>
                                        <div class="profile-details">
                                            <p><?php echo "<strong>" . htmlspecialchars($rating['Name'] . ' ' . $rating['Surname'])  . "</strong>"; ?></p>
                                            <p><?php echo "<strong>" . htmlspecialchars($rating['Agency']) . "</strong>"; ?></p>
                                            <p><a style="text-decoration: none; color:#213644;" href="mailto:<?php echo htmlspecialchars($rating['Email']); ?>"><?php echo htmlspecialchars($rating['Email']); ?></a></p>
                                            <p><a style="text-decoration: none; color:#213644;" href="tel:<?php echo htmlspecialchars($rating['Contact_Number']); ?>"><?php echo htmlspecialchars($rating['Contact_Number']); ?></a></p>
                                            <p><?php echo $stars; ?></p>

                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <p>Agent data not found or an error occurred!</p>
                                <?php } ?>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="grid-item item2" alt="property_ratings" id="rate">
                    <h3>Property Ratings</h3>
                    <div class="ratingsP">
                        <div class="overall_rating">
                            <h4 class="rn">Overall Rating: </h4>
                            <p class="ra"> <?php echo displayStars($overallRating); ?></p>
                        </div>
                        <?php
                        // Define the categories and their corresponding column names in the database
                        $ratingCategories = array(
                            'Safety:' => $safetyRating,
                            'Maintenance:' => $maintenanceRating,
                            'Noise Level:' => $noiselevels,
                            'Convenience:' => $convenience,
                            'Value for Money:' => $value,
                        );

                        // Loop through the rating categories
                        foreach ($ratingCategories as $categoryName => $categoryValue) { ?>
                            <div class="rating_category">
                                <h4 class="rn"><?php echo $categoryName; ?></h4>
                                <p class="ra"><?php echo displayStars($categoryValue); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                    // Check if the user is logged in
                    if (isset($_SESSION["user_id"])) {
                        $user_id = $_SESSION["user_id"];

                        // Check if the user is a tenant
                        if (isTenant($user_id)) {
                            $tenantID = $_SESSION["tenant_id"];

                            $accommodation_query2 = "SELECT a.*, ta.TenantID, aa.AgentID
                                            FROM accomodation AS a
                                            LEFT JOIN tenant_accomodation AS ta ON a.AccomodationID = ta.AccomodationID
                                            LEFT JOIN agent_accomodation AS aa ON a.AccomodationID = aa.AccomodationID
                                            WHERE a.AccomodationID = '$accommodationID'";

                            $accommodation_result2 = mysqli_query($conn, $accommodation_query);

                            // Check if the property is associated with this tenant
                            while($row = mysqli_fetch_assoc($accommodation_result2)){
                                if ($row["TenantID"] === $tenantID) {
                                    // Display the "Rate & Review" button for the tenant
                                    echo '<button type="button" class="show_form" data-target="rate_property">
                                            Rate & Review</button>';
                                    echo '
                                                <form action="propertyPage.php?accommodationid=' . $accommodationID . '" id="rate_property" method="post" class="form_container" style="display: none;"> 
                                                <input type="hidden" name="tenant_id" value="'  . $tenantID . '">

                                                <hr>
                                                <i style="font-weight: 250;"> Please rate all 5 criteria </i>
                                                <br><br>

                                                <div class="ratingF">
                                                <label for="tenant_rating" class="main_label">Safety:</label>
                                                <div class="rating">
                                                    <input type="radio" name="safety_rating" value="5" id="s5" class="nyeredzi" required><label for="s5">☆</label>
                                                    <input type="radio" name="safety_rating" value="4" id="s4" class="nyeredzi" required><label for="s4">☆</label>
                                                    <input type="radio" name="safety_rating" value="3" id="s3" class="nyeredzi" required><label for="s3">☆</label>
                                                    <input type="radio" name="safety_rating" value="2" id="s2" class="nyeredzi" required><label for="s2">☆</label>
                                                    <input type="radio" name="safety_rating" value="1" id="s1" class="nyeredzi" required><label for="s1">☆</label>
                                                </div> <br>
                    
                                                <label for="tenant_rating" class="main_label"> Maintenance:</label>
                                                <div class="rating">
                                                    <input type="radio" name="main_rating" value="5" id="m5" class="nyeredzi" required><label for="m5">☆</label>
                                                    <input type="radio" name="main_rating" value="4" id="m4" class="nyeredzi" required><label for="m4">☆</label>
                                                    <input type="radio" name="main_rating" value="3" id="m3" class="nyeredzi" required><label for="m3">☆</label>
                                                    <input type="radio" name="main_rating" value="2" id="m2" class="nyeredzi" required><label for="m2">☆</label>
                                                    <input type="radio" name="main_rating" value="1" id="m1" class="nyeredzi" required><label for="m1">☆</label>
                                                </div><br>

                    
                                                <label for="tenant_rating" class="main_label"> Noise Levels:</label>
                                                <div class="rating">
                                                    <input type="radio" name="noise_rating" value="5" id="n5" class="nyeredzi" required><label for="n5">☆</label>
                                                    <input type="radio" name="noise_rating" value="4" id="n4" class="nyeredzi" required><label for="n4">☆</label>
                                                    <input type="radio" name="noise_rating" value="3" id="n3" class="nyeredzi" required><label for="n3">☆</label>
                                                    <input type="radio" name="noise_rating" value="2" id="n2" class="nyeredzi" required><label for="n2">☆</label>
                                                    <input type="radio" name="noise_rating" value="1" id="n1" class="nyeredzi" required><label for="n1">☆</label>
                                                </div><br>
                    
                                                <label for="tenant_rating" class="main_label">Convenience:</label>
                                                <div class="rating">
                                                    <input type="radio" name="con_rating" value="5" id="c5" class="nyeredzi" required><label for="c5">☆</label>
                                                    <input type="radio" name="con_rating" value="4" id="c4" class="nyeredzi" required><label for="c4">☆</label>
                                                    <input type="radio" name="con_rating" value="3" id="c3" class="nyeredzi" required><label for="c3">☆</label>
                                                    <input type="radio" name="con_rating" value="2" id="c2" class="nyeredzi" required><label for="c2">☆</label>
                                                    <input type="radio" name="con_rating" value="1" id="c1" class="nyeredzi" required><label for="c1">☆</label>
                                                </div><br>
                    
                                                <label for="tenant_rating" class="main_label"> Value For Money:</label>
                                                <div class="rating">
                                                    <input type="radio" name="value_rating" value="5" id="v5" class="nyeredzi" required><label for="v5">☆</label>
                                                    <input type="radio" name="value_rating" value="4" id="v4" class="nyeredzi" required><label for="v4">☆</label>
                                                    <input type="radio" name="value_rating" value="3" id="v3" class="nyeredzi" required><label for="v3">☆</label>
                                                    <input type="radio" name="value_rating" value="2" id="v2" class="nyeredzi" required><label for="v2">☆</label>
                                                    <input type="radio" name="value_rating" value="1" id="v1" class="nyeredzi" required><label for="v1">☆</label>
                                                </div><br>
                    
                                                <label for="tenant_review">Review:</label>
                                                <textarea name="tenant_review" id="tenant_review"></textarea><br>
                                                </div>
                                                <input type="submit" name="submit_rating_review" value="Submit Rating & Review" onclick="return confirm(\'Are you sure you want to submit new rating and/or review?\')">
                                            </form>
                                            ';
                                }
                            }
                
                        }

                        // Check if the user is an agent
                        if (isAgent($user_id)) {
                            $agentID = $_SESSION["agent_id"];

                            // Check if the property is associated with this agent
                            if ($propertyData["AgentID"] === $agentID) {
                                // Hide the "Rate & Review" button for the agent
                                echo '<button type="submit" class="show_form"><a href="agent.php#Manage Listings" style="text-decoration: none;">Update Property</a></button>';
                            }
                        }
                    }
                    ?>
                </div>

                <div class="grid-item item3" alt="property_reviews">
                    <h3>Previous Reviews and Ratings</h3>
                    <?php if (!empty($reviews)) : ?>
                        <div class="reviews_container">
                            <?php foreach ($reviews as $review) : ?>
                                <div class="tenant_rating_review">
                                    <div class="profile_picture">
                                        <img src="<?php echo isset($review['ProfilePicture']) ? $review['ProfilePicture'] : 'profile_pictures/default_profile_pic.png'; ?>" alt="Profile Picture">
                                    </div>
                                    <div class="review_details">
                                        <p class="tenant_name"><?php echo $review['Name'] . ' ' . $review['Surname']; ?></p><br>
                                        <p class="review_text"><?php echo $review['Review']; ?></p><br>
                                        <div>
                                            <!-- Display tenant's rating as stars -->
                                            <?php echo displayStars($review['Final_Rating']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p id="pNobody" >Nobody has reviewed this property yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <section class="similar-section">
                <header>
                    <h2>Similar Properties</h2>
                </header>
                <section class="filter">
                    <div class="carousel-container">
                        <div class="carousel">
                            <?php
                            require_once("config.php");

                            $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                                or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

                            // Query to fetch similar properties (modify this query accordingly)
                            $similarPropertiesQuery = "SELECT a.*, mp.PhotoPath
                                FROM accomodation AS a
                                LEFT JOIN main_pictures AS mp ON a.AccomodationID = mp.AccomodationID
                                WHERE (
                                        (a.Rent <= '{$propertyData['Rent']} + 1000' AND a.Rent >= '{$propertyData['Rent']} - 1000') OR
                                        (a.Deposit <= '{$propertyData['Deposit']} + 1000'  AND  a.Deposit <= '{$propertyData['Deposit']} - 1000') OR
                                        (a.Distance_From_Campus <= '{$propertyData['Distance_From_Campus']} + 2' OR a.Distance_From_Campus <= '{$propertyData['Distance_From_Campus']} - 2') OR
                                        a.Address LIKE '%{$propertyData['Address']}%' OR 
                                        a.Type = '{$propertyData['Type']}'
                                    ) AND a.AccomodationID != '$accommodationID' 
                                    LIMIT 25";

                            $similarPropertiesResult = mysqli_query($conn, $similarPropertiesQuery);


                            if ($similarPropertiesResult && mysqli_num_rows($similarPropertiesResult) > 0) {
                                while ($similarProperty = mysqli_fetch_assoc($similarPropertiesResult)) {
                                    $accid = $similarProperty['AccomodationID'] ;

                                    // Create and execute the SQL query to fetch agent data
                                    $a_query = "SELECT AVG(TAR.Final_Rating) AS AVGRating
                                    FROM if0_35600039_Thea_team.tenants_accomodation_rating TAR
                                    WHERE TAR.AccomodationID = '$accid'
                                    ";

                                    $a_result = mysqli_query($conn, $a_query);

                                    if($a_result && mysqli_num_rows($a_result) > 0){
                                        $rating = mysqli_fetch_assoc($a_result);
                                        $stars = displayStars($rating['AVGRating']);
                                    } else {
                                        $stars = displayStars(0);
                                    }

                                    // You may need to adjust the column names accordingly
                                    $similarPropertyName = $similarProperty['Name'];
                                    $similarPropertyDescription = $similarProperty['Description'];
                                    $similarPropertyOverallRating = calculateOverallRating($similarProperty);
                            ?>

                                    <a href="propertyPage.php?accommodationid=<?php echo $similarProperty['AccomodationID']; ?>">
                                    
                                        <div class="carousel-item">
                                        <div class="overlayD"></div>
                                            <img src="<?php echo $similarProperty['PhotoPath']; ?>" alt="Similar Property Image">
                                            
                                            <p><?php echo $similarPropertyName; ?></p>
                                            <p><?php echo $stars; ?></p>
                                        </div>
                                    </a>

                            <?php
                                }
                            } else {
                                echo "<div class=\"all-item\">";
                                echo "<h2>No similar properties found.</h2>";
                                echo "</div>";
                            }

                            mysqli_close($conn);
                            ?>
                        </div>
                    </div>
                </section>
            </section>


    </main>
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

    <script src="scroll.js"></script>
    <script>
        // Get references to the buttons and forms
        const toggleFormButtons = document.querySelectorAll(".show_form");
        const formContainers = document.querySelectorAll(".form_container");

        // Add click event listeners to the buttons
        toggleFormButtons.forEach((button) => {
            button.addEventListener("click", function() {
                // Get the target form ID
                const targetFormId = this.getAttribute("data-target");
                const targetForm = document.getElementById(targetFormId);

                // Toggle the form's visibility directly
                if (targetForm) {
                    if (targetForm.style.display === "none" || targetForm.style.display === "") {
                        targetForm.style.display = "block"; // Show the form
                    } else {
                        targetForm.style.display = "none"; // Hide the form
                    }
                }
                // Hide other forms when showing a new one
                formContainers.forEach((form) => {
                    if (form !== targetForm) {
                        form.style.display = "none";
                    }
                });
            });
        });

        window.onload = function() {
            // Get all the flex rows on the page
            var flexRows = document.querySelectorAll('.grid-item');

            // Loop through each flex row
            flexRows.forEach(function(row) {
                // Get all the images within the row
                var images = row.querySelectorAll('img');

                // Initialize a variable to store the minimum height
                var minHeight = null;

                // Loop through the images to find the smallest height
                images.forEach(function(img) {
                    var imgHeight = img.clientHeight; // Get the height of the image

                    // Update the minHeight if it's null or smaller than the current image height
                    if (minHeight === null || imgHeight < minHeight) {
                        minHeight = imgHeight;
                    }
                });

                // Set the minHeight as the height for all images in the row
                images.forEach(function(img) {
                    img.style.height = minHeight + 'px';
                });
            });
        };

        $(document).ready(function() {
            var breadcrumbs = $(".breadcrumb"); // Get all breadcrumb elements

            breadcrumbs.click(function() {
                // Get the data-slide attribute of the clicked breadcrumb
                var slideIndex = $(this).data("slide");

                // Find the corresponding image using the slide index
                var images = $(".slideshow-breadcrumbs img");
                if (slideIndex >= 1 && slideIndex <= images.length) {
                    // Update the main image source and alt attribute
                    var mainImage = $(".mainPicture img");
                    mainImage.attr("src", images.eq(slideIndex - 1).attr("src"));
                    mainImage.attr("alt", images.eq(slideIndex - 1).attr("alt"));

                    // Activate the clicked breadcrumb and deactivate others
                    breadcrumbs.removeClass("active");
                    $(this).addClass("active");
                }
            });
        });
    </script>
</body>

</html>