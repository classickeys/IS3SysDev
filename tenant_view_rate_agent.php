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
    $tenant_query = "SELECT TenantID FROM tenant WHERE UserID = '$user_id'";
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
            $profilePictureURL = $agent_data['ProfilePicture'];
        } else {
            // Assign the default profile picture URL
            $profilePictureURL = '/profile_pictures/default_profile_pic.png';
        }
         // Handle rating and review updates
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                } else {
                    // Handle the error
                    echo "Error updating rating and review: " . mysqli_error($conn);
                }
            }
        }

    // Close the database connection
    mysqli_close($conn);

    } else {
        // Agent not found or no rating/review available, handle accordingly
        $agent_data = null;
        $agent_rating = null;
        $agent_review = null;
        echo mysqli_error($conn);
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Agent</title>
    <link rel="stylesheet" href="tenant_styles.css">
</head>
<body>
    <main id="my_agent">
        <h2>My Agent</h2>
        <section class="controls">
            <?php if ($agent_data) { ?>
                <div class="profile-card">
                    <!-- Agent's Profile Picture and Details -->
                    <div id="profile-picture">
                        <img src="<?php echo htmlspecialchars($profilePictureURL); ?>" alt="Agent's Profile Picture">
                    </div>
                    <div class="profile-details">
                        <p><?php echo htmlspecialchars($agent_data['Name'] . ' ' . $agent_data['Surname']); ?></p>
                        <p><?php echo htmlspecialchars($agent_data['Agency']); ?></p>
                    </div>
                </div>
                <!-- Agent's Rating and Review -->
                <div class="rating-review">
                    <?php if ($agent_rating !== null && $agent_review !== null) { ?>
                        <p>Current Rating: <?php echo htmlspecialchars($agent_rating); ?></p>
                        <p>Current Review: <?php echo htmlspecialchars($agent_review); ?></p>
                    <?php } else { ?>
                        <p>You Haven't Rated <?php echo $agent_data['Name'];?></p>
                    <?php } ?>
                    <!-- Display input fields for adding/updating rating and review -->
                    <form action="tenant_view_rate_agent.php" method="post">
                        <input type="hidden" name="agent_id" value="<?php echo htmlspecialchars($agent_data['AgentID']); ?>">
                        <label for="tenant_rating">Rating:</label>
                        <input type="number" name="tenant_rating" id="tenant_rating" min="0" max="5">
                        <label for="tenant_review">Review:</label>
                        <textarea name="tenant_review" id="tenant_review"></textarea>
                        <input type="submit" name="submit_rating_review" value="Submit Rating & Review" onclick="return confirm('Are you sure you want to submit new rating and/or review?')">
                    </form>
                </div>

            <?php } else { ?>
                <p>Agent data not found or an error occurred.</p>
            <?php } ?>
        </section>
        <a href="tenant.php">Back</a>
    </main>
</body>
</html>
