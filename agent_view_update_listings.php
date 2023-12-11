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
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

// Function to fetch agent's listings
function fetchListings($conn, $agent_id)
{
    $listings = array();

    // Join the AgentAccommodation and Accommodation tables to fetch listing details
    $query = "SELECT A.*, AA.* FROM accomodation A
                INNER JOIN agent_accomodation AA ON A.AccomodationID = AA.AccomodationID
                WHERE AA.AgentID = '$agent_id' AND AA.To_Date IS NULL";

    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $listings[] = $row;
        }
    }

    return $listings;
}

// Function to update the active status of a listing
function updateListingStatus($conn, $accommodation_id, $status)
{
    $query = "UPDATE accomodation SET Active = '$status' WHERE AccomodationID = '$accommodation_id'";
    return mysqli_query($conn, $query);
}

// Function to update the active status of a listing
function updateListingAvailability($conn, $accommodation_id, $avail)
{
    $query = "UPDATE accomodation SET Availability = '$avail' WHERE AccomodationID = '$accommodation_id'";
    return mysqli_query($conn, $query);
}

// Define a flag to differentiate between regular listings and search results
$isSearchResult = false;

// Check if the "Clear Search" button is clicked
if (isset($_POST["clear"])) {
    // Clear the search results and fetch all agents again
    $listings = fetchListings($conn, $agent_id);
    // Unset the filter_active and filter_inactive options
    unset($_POST['filter_active_search']);
    unset($_POST['filter_inactive_search']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["search_submit"])) {
        // Handle search functionality here
        $searchTerm = mysqli_real_escape_string($conn, $_POST["search"]);

        // Get the filter values
        $filterActive = isset($_POST['filter_active']) ? 1 : null;
        $filterInactive = isset($_POST['filter_inactive']) ? 0 : null;

        // Modify your SQL query to filter listings based on the search term
        $searchQuery = "SELECT A.*, AA.* FROM accomodation A
                        INNER JOIN agent_accomodation AA ON A.AccomodationID = AA.AccomodationID
                        WHERE AA.AgentID = '$agent_id' AND AA.To_Date IS NULL
                        AND (Name LIKE '%$searchTerm%' OR Address LIKE '%$searchTerm%' OR Description LIKE '%$searchTerm%')
                        ";

        if ($filterActive !== null && $filterInactive !== null) {
            // Both "Active" and "Inactive" checkboxes are selected, fetch all agents
        } elseif ($filterActive !== null) {
            // Only "Active" checkbox is selected
            $searchQuery .= " AND Active = 1";
        } elseif ($filterInactive !== null) {
            // Only "Inactive" checkbox is selected
            $searchQuery .= " AND Active = 0";
        }

        $searchResult = mysqli_query($conn, $searchQuery);

        if ($searchResult && mysqli_num_rows($searchResult) > 0) {
            // Store the search results in the $listings array
            $listings = array();
            while ($row = mysqli_fetch_assoc($searchResult)) {
                $listings[] = $row;
            }
            $isSearchResult = true; // Set the flag for search results
        } else {
            // No results found, you can display a message to the user
            echo '<script type="text/javascript">alert("No matching listings found.")</script>';
        }
    } elseif (isset($_POST["update_status"])) {
        // Handle update status functionality here
        $accommodation_id = mysqli_real_escape_string($conn, $_POST["accommodation_id"]);
        $newStatus = ($_POST["status"] == 1) ? 0 : 1; // Toggle the status (1 to 0 or 0 to 1)

        if (updateListingStatus($conn, $accommodation_id, $newStatus)) {
            // Listing status updated successfully
            // You can display a success message or redirect to a different page
            header("Location: agent_view_update_listings.php");
        } else {
            // Handle error 
            echo '<script type="text/javascript">alert("Failed to toggle accomodation status")</script>';
        }
    } elseif (isset($_POST["update_availability"])) {
        // Handle update status functionality here
        $accommodation_id = mysqli_real_escape_string($conn, $_POST["accommodation_id"]);
        $newStatus = ($_POST["available"] == 1) ? 0 : 1; // Toggle the status (1 to 0 or 0 to 1)

        if (updateListingAvailability($conn, $accommodation_id, $newStatus)) {
            // Listing status updated successfully
            // You can display a success message or redirect to a different page
            header("Location: agent_view_update_listings.php");
        } else {
            // Handle error
            echo '<script type="text/javascript">alert("Failed to toggle accomodation availability")</script>';
        }
    }
}

// Fetch the agent's listings if it's not a search result
if (!$isSearchResult) {
    $listings = fetchListings($conn, $agent_id);
}


mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        table {
            border-radius: 15px;
            box-shadow: 0px 0px 15px rgb(0, 0, 0, 0.3);
        }

        table tr:last-child {
            padding-bottom: 2%;
            border: none;
            outline: red;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View & Update My Listings</title>
    <link rel="stylesheet" href="admin_view_update.css">
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
            <a href="agent.php?agentid=<?php echo $agent_id; ?>" id="profile_edit_back">Back</a>

            <h2>My Listings</h2>

            <!-- Agent search form -->
            <form action="" method="post">
                <input type="text" name="search" id="search" placeholder="Search Listing by Name, Address or part of Description...">

                <!-- Create a line for filter elements -->
                <div class="filter-line">
                    <label for="filter_active">Filter Active Accomodations:</label>
                    <input type="checkbox" name="filter_active" id="filter_active" <?php if (isset($_POST['filter_active'])) echo 'checked'; ?>>

                    <label for="filter_inactive">Filter Inactive Accomodations:</label>
                    <input type="checkbox" name="filter_inactive" id="filter_inactive" <?php if (isset($_POST['filter_inactive'])) echo 'checked'; ?>>
                </div>

                <!-- Create a line for search and clear search buttons -->
                <div class="search-buttons">
                    <input type="submit" name="search_submit" value="Search">
                    <input type="submit" name="clear" value="Clear Search">
                </div>
            </form>

            <!-- Display Listings Table -->
            <table>
                <thead>
                    <tr>
                        <th>Photographs</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Rent</th>
                        <th>Deposit</th>
                        <th>Type</th>
                        <th>Furnished</th>
                        <th>Status</th>
                        <th>Available</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing) { ?>

                        <tr>
                            <td>
                                <?php
                                require_once("config.php");
                                $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                                    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

                                // Assuming $listing is an array containing the listing details, including image paths
                                $imagePaths = []; // Create an empty array to store image paths

                                // Query to retrieve image paths for a specific accommodation (you should modify this query to suit your needs)
                                $query5 = "SELECT PhotoPath FROM main_pictures WHERE AccomodationID = '{$listing['AccomodationID']}'";
                                $result3 = mysqli_query($conn, $query5);

                                if ($result3) {
                                    while ($row = mysqli_fetch_assoc($result3)) {
                                        $imagePaths[] = $row['PhotoPath']; // Add image paths to the array
                                    }

                                    foreach ($imagePaths as $imagePath) {
                                        echo '<img src="' . $imagePath . '" alt="Accommodation Image" width="100px" style"border-radius: 10px;">';
                                    }
                                } else {
                                    // Handle error while retrieving image paths
                                    echo "Error: " . mysqli_error($conn);
                                }
                                mysqli_close($conn);
                                ?>
                            </td>
                            <td><?php echo $listing['Name']; ?></td>
                            <td><?php echo $listing['Address']; ?></td>

                            <td><?php echo $listing['Rent']; ?></td>
                            <td><?php echo $listing['Deposit']; ?></td>
                            <td><?php echo $listing['Type']; ?></td>
                            <td><?php echo $listing['Furnished']; ?></td>
                            <td>
                                <?php echo ($listing['Active'] == 1) ? 'Active' : 'Inactive'; ?>

                                <form action="" method="post">
                                    <input type="hidden" name="accommodation_id" value="<?php echo $listing['AccomodationID']; ?>">

                                    <input type="hidden" name="status" value="<?php echo $listing['Active']; ?>">

                                    <input type="submit" name="update_status" value="Toggle Status" class="link" style="margin-bottom: 10px;">
                                </form>

                            </td>

                            <td>
                                <?php echo ($listing['Availability'] == 1) ? 'Available' : 'Not Available'; ?>

                                <form action="" method="post">
                                    <input type="hidden" name="accommodation_id" value="<?php echo $listing['AccomodationID']; ?>">

                                    <input type="hidden" name="available" value="<?php echo $listing['Availability']; ?>">

                                    <input type="submit" name="update_availability" value="Toggle Availability" class="link" style="margin-bottom: 10px;">
                                </form>
                            </td>

                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="accommodation_id" value="<?php echo $listing['AccomodationID']; ?>">

                                    <a href="agent_edit_listing.php?accommodationid=<?php echo $listing['AccomodationID']  . '&' . 'agentid=' . $agent_id; ?>" class="link">Update</a>

                                    <a href="propertyPage.php?accommodationid=<?php echo $listing['AccomodationID']; ?>" class="link"> View </a>
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
            <a href="aboutus.php">About Us</a>
            <a href="faqs.php">FAQs</a>
            <a href="privacy.php">Privacy</a>
            <a href="contactus.php">Contact Us</a>
            <a href="tenant.php" target="_self">I am a Tenant</a>
            <a href="agent.php" target="_self">I am an Agent</a>
        </small>
    </footer>
</body>

</html>