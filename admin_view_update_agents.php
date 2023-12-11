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
        
        // Create and execute the SQL query to fetch admin data
        $admin_query = "SELECT AdminID FROM administrator WHERE UserID = '$user_id'";
        $admin_result = mysqli_query($conn, $admin_query);

        // Check if the query was successful and if the user is an admin
        if ($admin_result && mysqli_num_rows($admin_result) > 0) {
            // Admin exists, fetch their AdminID
            $admin_data = mysqli_fetch_assoc($admin_result);
            $admin_id = $admin_data['AdminID'];
        } else {
            // User is not an admin, handle accordingly
            echo '<script type="text/javascript">alert("User is not an admin or an error occurred.");</script>';
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

    // Include your config.php for database connection
    require_once("config.php");
    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    // Function to fetch agent's listings
    function fetchAgents($conn) {
        $agents = array();

        // Join the AgentAccommodation and Accommodation tables to fetch listing details
        $query = "SELECT A.*, U.Active FROM agent A, users U 
                    WHERE A.UserID = U.UserID";
        $result = mysqli_query($conn, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $agents[] = $row;
            }
        }

        return $agents;
    }

    // Function to update the active status of a listing
    function updateAgentStatus($conn, $agent_id, $status) {
        $user_id = intval(ltrim($agent_id, 'A'));
        $query = "UPDATE users SET Active = '$status' WHERE UserID = '$user_id'";
        return mysqli_query($conn, $query);
    }

    // Define a flag to differentiate between regular listings and search results
    $isSearchResult = false;

    // Fetch the agent's listings if it's not a search result
    if (!$isSearchResult) {
        $agents = fetchAgents($conn);
    }

    // Check if the "Clear Search" button is clicked
    if (isset($_POST["clear"])) {
        // Clear the search results and fetch all agents again
        $agents = fetchAgents($conn);
        // Unset the filter_active and filter_inactive options
        unset($_POST['filter_active_search']);
        unset($_POST['filter_inactive_search']);
    }

    // Handle search functionality
    if (isset($_POST["search_submit"])) {
        // Get the search term
        $searchTerm = mysqli_real_escape_string($conn, $_POST["search"]);
        
        // Get the filter values
        $filterActive = isset($_POST['filter_active']) ? 1 : null;
        $filterInactive = isset($_POST['filter_inactive']) ? 0 : null;

        // Build the SQL query based on search term and filters
        $searchQuery = "SELECT A.*, U.Active FROM agent A, users U WHERE A.UserID = U.UserID AND
                        (A.Name LIKE '%$searchTerm%' OR A.Surname LIKE '%$searchTerm%')";

        if ($filterActive !== null && $filterInactive !== null) {
            // Both "Active" and "Inactive" checkboxes are selected, fetch all agents
        } elseif ($filterActive !== null) {
            // Only "Active" checkbox is selected
            $searchQuery .= " AND U.Active = 1";
        } elseif ($filterInactive !== null) {
            // Only "Inactive" checkbox is selected
            $searchQuery .= " AND U.Active = 0";
        }

        $searchResult = mysqli_query($conn, $searchQuery);

        if ($searchResult && mysqli_num_rows($searchResult) > 0) {
            // Store the search results in the $agents array
            $agents = array();
            while ($row = mysqli_fetch_assoc($searchResult)) {
                $agents[] = $row;
            }
            $isSearchResult = true; // Set the flag for search results
        } else {
            // No results found, you can display a message to the user
            echo '<script type="text/javascript">alert("No matching agents found.");</script>';
        }
    }

    // Check if the "Toggle Status" button is clicked
    if (isset($_POST["update"])) {
        // Get the agent_id and status from the form submission
        $agent_id = $_POST["agent_id"];
        $status = $_POST["status"];

        // Toggle the status (if it's 1, set it to 0, and vice versa)
        $new_status = ($status == 1) ? 0 : 1;

        // Update the agent's status in the database
        $update_result = updateAgentStatus($conn, $agent_id, $new_status);

        if ($update_result) {
            // Successfully updated the status, you can optionally show a success message
            //echo '<script type="text/javascript">alert("Agent status updated successfully.");</script>';
            header("Location: admin_view_update_agents.php");
        } else {
            // Error occurred while updating, you can show an error message
            echo '<script type="text/javascript">alert("Error updating agent status.");</script>';
        }
    }

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View & Update My Agents</title>
    <link rel="stylesheet" href="admin_view_update.css">
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

            <!-- all items on the right side of navbar -->
            <li class="right">
                <?php
                    // fetch admin's data from session 
                    $admin_data_sess = $_SESSION['admin_data'];

                    // Check if a profile picture URL is available
                    if (!empty($admin_data_sess['ProfilePicture'])) {
                        $profilePictureURLlo = $admin_data_sess['ProfilePicture'];
                    } else {
                        // Assign the default profile picture URL
                        $profilePictureURLlo = 'profile_pictures/default_profile_pic.png';
                    }
                        
                    // Display their profile button 
                    echo '
                        <a href="logout.php" class="navBtn" id="loginBtn" onClick="return confirm(\'Are you sure you want to Logout?\')" >
                            Logout
                            <img id="login_png" class="navImg" src="./images/icons/arrow.png" alt="arrow icon" height="18px" width="18px">
                        </a>
                    '; 
                ?>
            </li>
        </ul>
    </nav>
    <main>
        <div class="content_container">
        <section id="manage_agents">
                            
            <!-- Back button -->
            <a href="admin.php" id="profile_edit_back">Back</a>
            
            
            <h2 >Current Agents</h2>

           <!-- Agent search form -->
            <form action="" method="post">
    
                <input type="text" name="search" id="search" placeholder="Search by Name or Surname...">  
                
                <!-- Create a line for filter elements -->
                <div class="filter-line">
                    <label for="filter_active">Filter Active Agents:</label>
                    <input type="checkbox" name="filter_active" id="filter_active" <?php if (isset($_POST['filter_active'])) echo 'checked'; ?>>
                    
                    <label for="filter_inactive">Filter Inactive Agents:</label>
                    <input type="checkbox" name="filter_inactive" id="filter_inactive" <?php if (isset($_POST['filter_inactive'])) echo 'checked'; ?>>
                </div>
                
                <!-- Create a line for search and clear search buttons -->
                <div class="search-buttons">
                    <input type="submit" name="search_submit" value="Search">
                    <input type="submit" name="clear" value="Clear Search">
                </div>

            </form>

            <!-- Display Listings Table -->
            <div class="table_wrapper">
            <!-- Your table or other content that should scroll horizontally -->
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Gender</th>
                            <th>Date Of Birth</th>
                            <th>Email</th>
                            <th>Mobile Contact</th>
                            <th>Agency</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent) { ?>
                            
                            <tr>
                                <td style="text-align: left;"><?php echo $agent['Name']; ?></td>
                                <td><?php echo $agent['Surname']; ?></td>  
                                <td><?php echo $agent['Gender']; ?></td>
                                <td><?php echo $agent['Date_Of_Birth']; ?></td>
                                <td><?php echo $agent['Email']; ?></td>
                                <td><?php echo $agent['Contact_Number']; ?></td>
                                <td><?php echo $agent['Agency']; ?></td>
                                <td><?php echo ($agent['Active'] == 1) ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="agent_id" value="<?php echo $agent['AgentID']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $agent['Active']; ?>">
                                        <input type="submit" name="update" value="Toggle Status">
                                    </form>
                                    
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        </div>
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
