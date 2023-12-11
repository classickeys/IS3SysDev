<?php

    //secure.php: file that has session verification
    require_once("check.php");

    // Fetch the agent's current details from the session
    $agent_data = $_SESSION['agent_data'];

    //get the agents id from the previous page using the url
    $agent_id = $_SESSION['agent_id'];

    // Include your config.php for database connection
    require_once("config.php");
    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:' . mysqli_error($conn) . '")</script>');

    // Function to fetch agent's tenants and their accommodations, including rating and review
    function fetchTenants($conn, $agent_id, $searchTerm) {
        $tenants = array();

        // Build the query to fetch tenants and their accommodations along with rating and review
        $query = "SELECT T.*, TA.*, A.Name AS AccommodationName, A.AccomodationID AS AccommodationID, R.Rating_Value AS TenantRating, R.Review AS TenantReview
                FROM tenant T
                INNER JOIN tenant_accomodation TA ON T.TenantID = TA.TenantID
                INNER JOIN accomodation A ON TA.AccomodationID = A.AccomodationID
                INNER JOIN agent_accomodation AA ON A.AccomodationID = AA.AccomodationID
                LEFT JOIN agents_tenant_rating R ON T.TenantID = R.TenantID
                WHERE AA.AgentID = '$agent_id' AND TA.To_Date IS NULL";

        // Add search condition if a search term is provided
        if (!empty($searchTerm)) {
            $query .= " AND (T.Name LIKE '%$searchTerm%' OR T.Surname LIKE '%$searchTerm%' OR A.Name LIKE '%$searchTerm%')";
        }

        $result = mysqli_query($conn, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $tenants[] = $row;
            }
        }else {
            // Handle the database query error
            echo "Error: " . mysqli_error($conn);
        }

        return $tenants;
    }

    // Define $searchTerm and set it to an empty string initially
    $searchTerm = "";

    if (isset($_POST["search_submit"])) {
        // Handle search functionality here
        $searchTerm = mysqli_real_escape_string($conn, $_POST["search"]);
    }

    // Fetch the agent's tenants
    $tenants = fetchTenants($conn, $agent_id, $searchTerm);

    // Check if the "Clear Search" button is clicked
    if (isset($_POST["clear"])) {
        // Clear the search results and fetch all agents again
        $tenants = fetchTenants($conn, $agent_id, "");

    }
    // Handle rating and review updates
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["update_rating"])) {
            // Handle rating update here
            $tenant_id = mysqli_real_escape_string($conn, $_POST["tenant_id"]);
            $tenant_rating = mysqli_real_escape_string($conn, $_POST["tenant_rating"]);

            // Update the agent_tenant_rating table with the new rating
            $update_rating_query = "INSERT INTO agents_tenant_rating (AgentID, TenantID, Rating_Value) 
                                    VALUES ('$agent_id', '$tenant_id', '$tenant_rating') 
                                    ON DUPLICATE KEY UPDATE Rating_Value = '$tenant_rating'";

            $update_rating_result = mysqli_query($conn, $update_rating_query);

            if ($update_rating_result) {
                // Rating updated successfully
                header("Location: agent_view_update_tenants.php");
            } else {
                // Handle the error
                echo "Error updating rating: " . mysqli_error($conn);
            }
        } elseif (isset($_POST["update_review"])) {
            // Handle review update here
            $tenant_id = mysqli_real_escape_string($conn, $_POST["tenant_id"]);
            $tenant_review = mysqli_real_escape_string($conn, $_POST["tenant_review"]);

            // Update the agent_tenant_rating table with the new review
            $update_review_query = "INSERT INTO agents_tenant_rating (AgentID, TenantID, Review) 
                                    VALUES ('$agent_id', '$tenant_id', '$tenant_review') 
                                    ON DUPLICATE KEY UPDATE Review = '$tenant_review'";

            $update_review_result = mysqli_query($conn, $update_review_query);

            if ($update_review_result) {
                // Review updated successfully
                header("Location: agent_view_update_tenants.php");
            } else {
                // Handle the error
                echo "Error updating review: " . mysqli_error($conn);
            }
        }
    }

    if(isset($_POST['deactivate_lease'])){
        $tenantid = $_POST['tenant_id'];
        $deactivate = "UPDATE tenant_accomodation
                        SET To_Date = CURDATE()
                        WHERE TenantID = '$tenantid'
                        ";
        $deactivate_res = mysqli_query($conn, $deactivate);

        if($deactivate_res){
            header("Location: agent_view_update_tenants.php");
        }else{
            echo '<script type="text/javascript">alert("Error 21: Failed to Deactivate Tenants Lease!")</script>' ;
        }
    }

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View & Update Tenants</title>
    <link rel="stylesheet" href="admin_view_update.css">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
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
                <?php
                $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

                // Check if the user is logged in
                if (isset($_SESSION["user_id"])) {
                    $agent_id = $_SESSION["user_id"];
                    
                    // Check if the user is an agent
                    if (isAgent($agent_id)) {
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
                            ';
                    }
                }
                ?>
            </li>
        </ul>
    </nav>
    <main>
        <section>
            <!-- Back button -->
            <a href="agent.php?<?php echo $agent_id;?>" id="profile_edit_back">Back</a>
            
            <h2>My Tenants</h2>

           <!-- tenant search form -->
            <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">
                
                <input type="text" name="search" id="search" placeholder="Search by: Tenant Name, Tenant Surname, or Accomodation Name...">
                
                <!-- Create a line for search and clear search buttons -->
                <div class="search-buttons">
                    <input type="submit" name="search_submit" value="Search">
                    <input type="submit" name="clear" value="Clear Search">
                </div>

            </form>

            <!-- Display Tenants Table -->
            <table>
                <thead>
                    <tr>
                        <th>Tenant Name</th>
                        <th>Accommodation</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant) { ?>
                        <tr>
                            <td><?php echo $tenant['Name'] . ' ' . $tenant['Surname']; ?></td>
                            <td><?php echo $tenant['AccommodationName']; ?></td>

                            <td>
                                <?php if (isset($_POST['update_rating_' . $tenant['TenantID']])) { ?>
                                    <!-- Display input field for updating rating -->
                                    <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">
                                        <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">

                                        <input type="number" name="tenant_rating" value="<?php echo $tenant['TenantRating']; ?>" max ="5" min ="0">

                                        <input type="submit" name="update_rating" value="Submit Rating" >
                                    </form>
                                <?php } else { ?>
                                    <!-- Display current rating -->
                                    <?php echo $tenant['TenantRating']; ?>
                                    <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">

                                        <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">

                                        <input type="submit" name="update_rating_<?php echo $tenant['TenantID']; ?>" value="Update Rating">
                                    </form>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if (isset($_POST['update_review_' . $tenant['TenantID']])) { ?>
                                    <!-- Display input field for updating review -->
                                    <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">
                                        <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">
                                        <textarea name="tenant_review"><?php echo $tenant['TenantReview']; ?></textarea>
                                        <input type="submit" name="update_review" value="Submit Review">
                                    </form>
                                <?php } else { ?>
                                    <!-- Display current review -->
                                    <?php echo $tenant['TenantReview']; ?>
                                    <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">
                                        <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">
                                        <input type="submit" name="update_review_<?php echo $tenant['TenantID']; ?>" value="Update Review">
                                    </form>
                                <?php } ?>
                            </td>
                            <td>
                                 <form action="agent_view_update_tenants.php?agentid=<?php echo $agent_id; ?>" method="post">
                                    <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">
                                    <input type="submit" name="deactivate_lease" value="Deactivate Lease" onclick="return confirm('Are you sure you want to deactivate the lease for tenant <?php echo $tenant['Name'] . ' ' . $tenant['Surname']; ?>? \nPlease note that doing so will remove them as your tenant and from our system.')">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>
            The A-Team &copy; 2023
        </p>
        <small>
            <a href="index.php" target="_self">Home</a>
            <a href="aboutus.html">About Us</a>
            <a href="faqs.php">FAQs</a>
            <a href="privacy.php">Privacy</a>
            <a href="contactus.php">Contact Us</a>
            <a href="tenant.php" target="_self">I am a Tenant</a>
            <a href="agent.php" target="_self">I am an Agent</a>
        </small>
    </footer>

</body>
</html>
