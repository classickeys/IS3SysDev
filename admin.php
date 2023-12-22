<?php
    //secure.php: file that has session verification
    require_once("secure.php");


    function generate_password(){
        // Characters that can be included in the random string
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Length of the random string
        $length = 10;

        // Initialize the random string
        $random_password = '';

        // Generate a random string
        for ($i = 0; $i < $length; $i++) {
            //make the password by taking one random index from the string $characters, and appending it to the $random_password variable
            $random_password .= $characters[rand(0, strlen($characters) - 1)];
        }
        //return the new password
        return $random_password;
    }
    
    // Check if the UserID is stored in the session
    if (isset($_SESSION["user_id"])) {
        // Retrieve the UserID from the session
        $user_id = $_SESSION["user_id"];

        require_once("config.php");
        
        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Failed to connect to our Server/Database. Please try again Later!")</script>');
        
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

    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Failed to connect to our Server/Database. Please try again Later!")</script>');

    // Create and execute the SQL query to fetch admin data
    $admin_query = "SELECT * FROM administrator WHERE AdminID = '$admin_id'";
    $admin_result = mysqli_query($conn, $admin_query);

    // Check if the query was successful and if the admin exists
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
        // Admin exists, fetch their data
        $admin_data = mysqli_fetch_assoc($admin_result);

        // Check if a profile picture URL is available
        if (!empty($admin_data['ProfilePicture'])) {
            $profilePictureURL =  $admin_data['ProfilePicture'];
        } else {
            // Assign the default profile picture URL otherwise
            $profilePictureURL = 'profile_pictures/default_profile_pic.png';
        }
        

        // Store admin's data in session for later use
        $_SESSION['admin_data'] = $admin_data;

    } else {
        // Admin not found, handle accordingly
        echo'<script type="text/javascript">alert("Admin not found or an error occurred.");</script>';
    }

    // Check if the Edit profile form is submitted
    if (isset($_POST['submit_changes'])) {
        // Retrieve the updated data from the form
        $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
        $admin_surname = mysqli_real_escape_string($conn, $_POST['admin_surname']);
        $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
        

        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $adminProfilePic =mysqli_real_escape_string($conn, $_FILES['profile_pic']['name']);
            $adminProfilePic = 'SP' . substr(time(), -5) . $adminProfilePic;
            $destination = "profile_pictures/" . $adminProfilePic;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination); 

            // Update the agent's data in the database
                $update_query = "UPDATE administrator SET 
                Name = '$admin_name',
                Surname = '$admin_surname',
                Email = '$admin_email',
                ProfilePicture = '$destination'
                WHERE AdminID = '$admin_id'";
        }else{
            // Update the agent's data in the database
            $update_query = "UPDATE administrator SET 
                        Name = '$admin_name',
                        Surname = '$admin_surname',
                        Email = '$admin_email'
                        WHERE AdminID = '$admin_id'";
        }
        
        $result = mysqli_query($conn, $update_query);

        if ($result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile updated successfully!");</script>';
            header("Location: admin.php");
        } else {
            // Handle the update error
            // $errorMessage = "Error: " . mysqli_error($conn);
            echo '<script type="text/javascript">alert("Error: Failed to Update Profile.");</script>';
        }
    }

    if(isset($_REQUEST['delProfPic'])){
        $del_query= "UPDATE administrator
                    SET ProfilePicture = NULL
                    WHERE AdminID = '$admin_id'";

        $del_result = mysqli_query($conn, $del_query);
        if ($del_result) {
            // Update successful
            echo '<script type="text/javascript">alert("Profile Picture Deleted Successfully!");</script>';
            header("Location: admin.php");
        } else {
            // Handle the update error
            echo '<script type="text/javascript">alert("Error: Failed to Delete Profile Picture.");</script>';
        }
    }

    if(isset($_POST['add_agent'])){
        // Check if the agent with the same email already exists
        $agentEmail = $_POST['agent_email'];
        $check_agent_query = "SELECT * FROM agent WHERE Email = '$agentEmail'";
        $check_agent_result = mysqli_query($conn, $check_agent_query);

        if (mysqli_num_rows($check_agent_result) > 0) {
            // Agent with the same email already exists, display an error message
            echo('<script type="text/javascript">alert("An Agent with this email already exists.");</script>');
        } else {
            // Continue with adding the agent, starting by first adding the agent as a new user

            //create username of the format A23M1234...
            $username = 'A' . date('y') . substr($_POST['agent_surname'], 0, 1) . substr(time(), -4);
            
            //create the agents password 
            $password = generate_password();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'A';

            //insert agent as a new user into the users table 
            $insert_user_query = "INSERT INTO users (Password, Role, UserName) 
                                    VALUES ('$hashedPassword', '$role', '$username')";

            $user_result = mysqli_query($conn, $insert_user_query);

            if ($user_result) {
                // User added successfully, retrieve the auto-generated(incremented) UserID. using respective function
                $userID = mysqli_insert_id($conn);

                //now adding them as an agent
                //first getting all their details from the form entered by the admin
                $agentName = $_POST['agent_name']; 
                $agentSurname = $_POST['agent_surname']; 
                $agentGender = $_POST['agent_gender'];
                $agentDOB = $_POST['agent_dob']; 
                $agentEmail = $_POST['agent_email']; 
                $agentContact = $_POST['agent_contact'];
                $agentAgency = $_POST['agent_agency'];

                // $agentProfilePic = 'AP' . substr(time(), -5) . $agentProfilePic;
                // $destination = "profile_pictures/" . $agentProfilePic;
                // move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination);

                //Insert agent details into the agent table
                $insert_agent_query = "INSERT INTO agent (AgentID, Name, Surname, Email, Contact_Number, Agency, Gender, Date_Of_Birth, UserID) 
                VALUES (CONCAT('A', $userID), '$agentName', '$agentSurname', '$agentEmail', '$agentContact', '$agentAgency', '$agentGender', '$agentDOB', '$userID')";

                $agent_result = mysqli_query($conn, $insert_agent_query);

                if ($agent_result) {
                    // Agent added successfully
                    echo('<script type="text/javascript">alert("Agent Added Successfully");</script>');

                    echo('<script type="text/javascript">alert("Please Keep These Login Details somewhere SAFE! \n\nUSERNAME:' . $username . ' \nPASSWORD:' . $password . '");</script>');
                    //header("Location: admin.php");
                } else {
                    // Handle the error
                    // Use JavaScript to display the error message as a pop-up for failed
                    echo('<script type="text/javascript">alert("Error: Failed to add new agent!"' . mysqli_error($conn) . ');</script>');
                }
            } else {
                // Handle the error
                // $errorMessage = "Error 2(Failed to add agent as a new user! ): " . mysqli_error($conn);

                // Use JavaScript to display the error message as a pop-up
                echo('<script type="text/javascript">alert("Error: Failed to add agent as a new user!");</script>');
            }
        }
    }   //add_agent

    if(isset($_POST['add_admin'])){
        // Check if the admin with the same email already exists
        $adminEmail = $_POST['admin_email'];
        //fetch data to check
        $check_admin_query = "SELECT * FROM administrator WHERE Email = '$adminEmail'";
        $check_admin_result = mysqli_query($conn, $check_admin_query);

        if (mysqli_num_rows($check_admin_result) > 0) {
            // Admin with the same email already exists, display an error message
            echo('<script type="text/javascript">alert("Admin with this email already exists.");</script>');
        } else {
            // Continue with adding the admin, by first adding the admin as a new user

            //create username of the format S23M1234...
            $username = 'S' . date('y') . substr($_POST['admin_surname'], 0, 1) . substr(time(), -4);
            
            //generate random length 10 password for them
            $password = generate_password();
            //Hash the password using the password_hash() function
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'S';

            //query to insert the admin as a new user
            $insert_user_query = "INSERT INTO users (Password, Role, UserName) VALUES ('$hashedPassword', '$role', '$username')";
            $user_result = mysqli_query($conn, $insert_user_query);

            if ($user_result) {
                // User added successfully, retrieve the generated UserID
                $userID = mysqli_insert_id($conn);

                //now adding them as an admin
                $adminName = $_POST['admin_name']; 
                $adminSurname = $_POST['admin_surname']; 
                $adminEmail = $_POST['admin_email']; 

                // Insert admin details into the admin table
                $insert_admin_query = "INSERT INTO administrator (AdminID, Name, Surname, Email, UserID) VALUES (CONCAT('S', $userID), '$adminName', '$adminSurname', '$adminEmail', '$userID')";

                $admin_result = mysqli_query($conn, $insert_admin_query);

                if ($admin_result) {
                    // Admin added successfully
                    echo('<script type="text/javascript">alert("Admin Added Successfully");</script>');

                    echo('<script type="text/javascript">alert("Please Keep These Login Details somewhere SAFE! \n\nUSERNAME:' . $username . ' \nPASSWORD:' . $password . '");</script>');
                } else {
                    // Handle the error
                    $errorMessage = "Error 5(Failed to add admin as a new admin! ): " . mysqli_error($conn);

                    // Use JavaScript to display the error message as a pop-up
                    echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
                }
            } else {
                // Handle the error
                $errorMessage = "Error 6(Failed to add admin as a new user) ";

                // Use JavaScript to display the error message as a pop-up
                echo('<script type="text/javascript">alert("' . $errorMessage . '");</script>');
            }
        }   
    }   //add admin

    // Check if the "Confirm" button is clicked
    if (isset($_POST["confirm_new_pass"])) {
        // Retrieve form data
        $oldPassword = mysqli_real_escape_string($conn, $_POST["old_pass"]);
        $newPassword = mysqli_real_escape_string($conn, $_POST["new_pass"]);
        $reenteredPassword = mysqli_real_escape_string($conn, $_POST["reenter"]);
        
        // Validate the input, e.g., check if the new password matches the reentered password
        
        if ($newPassword === $reenteredPassword) {
            // You should also perform additional validation, such as checking the strength of the new password
            
            // Check if the old password matches the agent's current password (you need to fetch the current password from your database)
            //$admin_id = $_SESSION["agent_id"]; // Assuming you have an agent ID stored in the session
            $currentPassword = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Password FROM users WHERE UserID = '$user_id'")); // Replace with code to fetch the current password
            
            if (password_verify($oldPassword, $currentPassword['Password'])) {
                // Hash the new password before saving it to the database
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                
                // Update the agent's password in the database
                $updatePasswordQuery = "UPDATE users SET Password = '$hashedPassword' WHERE UserID = '$user_id'";
                
                // Execute the query to update the password
                $updatePasswordResult = mysqli_query($conn, $updatePasswordQuery);
                
                if ($updatePasswordResult) {
                    // Password updated successfully
                    echo '<script>alert("Password updated successfully.");</script>';

                    echo('<script type="text/javascript">alert("Please Keep This Password somewhere SAFE! \n\nNEW PASSWORD: ' . $newPassword . '");</script>');
                } else {
                    // Handle the database update error
                    echo '<script>alert("Error updating password.");</script>';
                }
            } else {
                // Old password does not match the current password
                echo '<script>alert("Old password is incorrect.");</script>';
            }
        } else {
            // New password and reentered password do not match
            echo '<script>alert("New passwords do not match.");</script>';
        }
    }

    

    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?php echo $admin_data['Name']; ?>'s Dashboard</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="dashboards.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://kit.fontawesome.com/8320e0ead0.js" crossorigin="anonymous"></script>
    <script type="text/javascript">
        <?php
            require_once("config.php");
            $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                
            $tenants_query = "SELECT T.*, U.Active FROM tenant T, users U
                                WHERE T.UserID = U.UserID ORDER BY T.Name ";

            $agents_query = "SELECT A.*, U.Active FROM agent A, users U
                                WHERE A.UserID = U.UserID ORDER BY A.Name";

            $accomodation_query1 = "SELECT * FROM accomodation ORDER BY Name";

            $topDigs_query = "SELECT A.*, A.AccomodationID, AVG(TAR.Final_Rating) AS AverageRating
                                FROM accomodation A
                                INNER JOIN tenants_accomodation_rating TAR ON A.AccomodationID = TAR.AccomodationID
                                GROUP BY A.AccomodationID
                                ORDER BY AverageRating DESC
                                LIMIT 10
                                ";

            $accomodation_querys = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR 
                                    WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";
            $accomodation_querym = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR 
                                    WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";
            $accomodation_queryc = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR 
                                    WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";
            $accomodation_queryv = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR 
                                    WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";

            $accomodation_queryn = "SELECT * FROM accomodation A, tenants_accomodation_rating TAR 
                                    WHERE A.AccomodationID = TAR.AccomodationID
                                    ORDER BY A.Name";

            $topTen_Tenants = "SELECT T.*, T.TenantID, AVG(ATR.Rating_Value) AS AverageRating
                                FROM tenant T
                                INNER JOIN agents_tenant_rating ATR ON T.TenantID = ATR.TenantID
                                GROUP BY T.TenantID
                                ORDER BY AverageRating DESC
                                LIMIT 10;
                                ";

            $topTen_Agents = "SELECT A.*, A.AgentID, AVG(TAR.Rating_Value) AS AverageRating
                                FROM agent A
                                INNER JOIN tenants_agent_rating TAR ON A.AgentID = TAR.AgentID
                                GROUP BY A.AgentID
                                ORDER BY AverageRating DESC
                                LIMIT 10;
                                ";

            $rentalPricesQuery = "SELECT YEAR(From_Date) AS Year, 
                                    MONTH(aa.From_Date) AS Month, 
                                    AVG(a.Rent) AS AvgRentalPrice
                                    FROM accomodation a
                                    JOIN agent_accomodation aa ON a.AccomodationID = aa.AccomodationID
                                    JOIN agent ag ON aa.AgentID = ag.AgentID
                                    GROUP BY Year, Month
                                    ORDER BY Year, Month
                                    ";

            $occupancyRatesQuery = "SELECT YEAR(From_Date) AS Year, 
                                    MONTH(From_Date) AS Month, 
                                    COUNT(*) AS OccupiedAccommodations
                                    FROM tenant_accomodation
                                    GROUP BY Year, Month
                                    ORDER BY Year, Month;
                                    ";

            $accommodationRatingsQuery = "SELECT YEAR(r.Timestamp) AS Year, 
                                            MONTH(r.Timestamp) AS Month, 
                                            AVG(r.Final_Rating) AS AvgRating
                                            FROM tenants_accomodation_rating r
                                            GROUP BY Year, Month
                                            ORDER BY Year, Month, AvgRating DESC";

            $tenants_result = mysqli_query($conn, $tenants_query) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF TENANTS!")</script>');

            $agents_result = mysqli_query($conn, $agents_query) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF AGENTS!")</script>');
            
            $accomodation_result1 = mysqli_query($conn, $accomodation_query1) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');
            
            $accomodation_results = mysqli_query($conn, $accomodation_querys) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            $accomodation_resultn = mysqli_query($conn, $accomodation_queryn) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            $accomodation_resultm = mysqli_query($conn, $accomodation_querym) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            $accomodation_resultv = mysqli_query($conn, $accomodation_queryv) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            $accomodation_resultc = mysqli_query($conn, $accomodation_queryc) or die('<script type="text/javascript">alert("FAILED TO FETCH LIST OF ACCOMODATIONS!")</script>');

            $top10_tenants_res = mysqli_query($conn, $topTen_Tenants) or die('<script type="text/javascript">alert("FAILED TO FETCH THE TOP TEN TENANTS!")</script>');
            
            $top10_agents_res = mysqli_query($conn, $topTen_Agents) or die('<script type="text/javascript">alert("FAILED TO FETCH THE TOP TEN AGENTS!")</script>');

            $topDigs_res = mysqli_query($conn, $topDigs_query) or die('<script type="text/javascript">alert("FAILED TO FETCH THE TOP TEN ACCOMODATIONS!")</script>');

            $rentalPricesResult = mysqli_query($conn, $rentalPricesQuery) or die('<script type="text/javascript">alert("FAILED TO FETCH THE AVERAGE RENTAL PRICES PER MONTH!")</script>');

            $occupancyRatesResult = mysqli_query($conn, $occupancyRatesQuery) or die('<script type="text/javascript">alert("FAILED TO FETCH THE AVERAGE OCCUPANCY RATES PER MONTH!")</script>');

            $accommodationRatingsResult = mysqli_query($conn, $accommodationRatingsQuery) or die('<script type="text/javascript">alert("FAILED TO FETCH THE AVERAGE ACCOMODATION RATINGS PER MONTH!")</script>');

        ?>
        // reports for Lists
        document.addEventListener("DOMContentLoaded", function () {
            // This code will run after the HTML document is fully loaded

            const agentsData = [
               
                <?php
                    while($age = mysqli_fetch_assoc($agents_result)){
                        echo "['" . $age['Name'] . "', '" . $age['Surname'] . "'],"; 
                    }
                ?>
            ];

            // Define your tenantsData and accommodationsData arrays here
            const tenantsData = [
                // ['Name', 'Surname', 'Email', 'Contact', 'Status'],
                <?php
                    while($ten = mysqli_fetch_assoc($tenants_result)){
                        echo "['" . $ten['Name'] . "', '" . $ten['Surname'] ."'],"; //. "', '" . $ten['Email'] . "', '" . $ten['Contact_Number'] . "', '" . $ten['Active'] 
                    }
                ?>
            ];

            const accommodationsData = [
                // ['Name', 'Views'],
                <?php
                    while($acc = mysqli_fetch_assoc($accomodation_result1)){
                        echo "['" . $acc['Name'] ."'],"; 
                    }
                ?>
            ];

            // Add an event listener for the "showAgents" button
            const showAgentsButton = document.getElementById('showAgents');
            if (showAgentsButton) { // Check if the button exists
                showAgentsButton.addEventListener('click', () => {
                showList(agentsData, 'List of Current Agents');
                });
            }

            // Add an event listener for the "showTenants" button
            const showTenantsButton = document.getElementById('showTenants');
            if (showTenantsButton) { // Check if the button exists
                showTenantsButton.addEventListener('click', () => {
                showList(tenantsData, 'List of Current Tenants');
                });
            }

            // Add an event listener for the "showAccommodations" button
            const showAccommodationsButton = document.getElementById('showAccommodations');
            if (showAccommodationsButton) { // Check if the button exists
                showAccommodationsButton.addEventListener('click', () => {
                showList(accommodationsData, 'List of Current Accomodations');
                });
            }
        });

        function showList(dataArray, listTitle) {
            const reportContainer = document.getElementById('reportContainer');
            reportContainer.innerHTML = ''; // Clear the previous content

            const list = document.createElement('ul');
            list.className = 'report-list';

            // Create list items from the data
            for (let i = 0; i < dataArray.length; i++) {
                const item = document.createElement('li');
                item.textContent = (i + 1) + '. ' + dataArray[i].join(' '); // Add numbers to list items
                list.appendChild(item);
            }

            // Create a paragraph element to display the count
            const countElement = document.createElement('p');
            countElement.textContent = 'Total Results found: ' + dataArray.length;

            const title = document.createElement('h2');
            title.textContent = listTitle;

            reportContainer.appendChild(title);
            reportContainer.appendChild(countElement); // Add the count element
            reportContainer.appendChild(list);
        }


        // reports for Digs ratings
        document.addEventListener("DOMContentLoaded", function () {
            google.charts.load('current', {'packages': ['corechart']});

            // Sample data (replace with actual PHP data)
            const safetyData = [
                ['Accommodation Name', 'Safety Rating'],
                    // Sample data (replace with actual PHP data)
                <?php
                    if (mysqli_num_rows($accomodation_results) > 0) {
                        while ($rate = mysqli_fetch_assoc($accomodation_results)) {
                            echo "['" . $rate['Name'] . "', " . $rate['Safety_Rating'] . "],";
                        }
                    }else{
                        echo "['N/A', " . 0 . "],";
                    }
                ?>
            ];

            const maintenanceData = [
                ['Accommodation Name', 'Maintenance Rating'],
                <?php
                    if (mysqli_num_rows($accomodation_resultm) > 0) {
                        while ($rate = mysqli_fetch_assoc($accomodation_resultm)) {
                            echo "['" . $rate['Name'] . "', " . $rate['Maintenance_Rating'] . "],";
                        }
                    }else{
                        echo "['N/A', " . 0 . "],";
                    }
                ?>
            ];

            const convenienceData = [
                ['Accommodation Name', 'Convenience Rating'],
                <?php
                    if (mysqli_num_rows($accomodation_resultc) > 0) {
                        while ($rate = mysqli_fetch_assoc($accomodation_resultc)) {
                            echo "['" . $rate['Name'] . "', " . $rate['Convenience_Rating'] . "],";
                        }
                    }else{
                        echo "['N/A', " . 0 . "],";
                    }
                ?>
            ];

            const noiseLevelsData = [
                ['Accommodation Name', 'Noise Levels Rating'],
                <?php
                    if (mysqli_num_rows($accomodation_resultn) > 0) {
                        while ($rate = mysqli_fetch_assoc($accomodation_resultn)) {
                            echo "['" . $rate['Name'] . "', " . $rate['Noise_Levels_Rating'] . "],";
                        }
                    }else{
                        echo "['N/A', " . 0 . "],";
                    }
                ?>
            ];

            const valueForMoneyData = [
                ['Accommodation Name', 'Value for Money Rating'],
                <?php
                    if (mysqli_num_rows($accomodation_resultv) > 0) {
                        while ($rate = mysqli_fetch_assoc($accomodation_resultv)) {
                            echo "['" . $rate['Name'] . "', " . $rate['Value_For_Money_Rating'] . "],";
                        }
                    }else{
                        echo "['N/A', " . 0 . "],";
                    }
                ?>
            ];

            // Function to draw column chart
            function drawColumnChart(dataArray, chartTitle) {
                const data = google.visualization.arrayToDataTable(dataArray);

                const options = {
                    title: chartTitle,
                    width: 600,  // Adjust width as needed
                    height: 400, // Adjust height as needed
                    bars: 'vertical',
                    hAxis: {
                        title: 'Accommodation Name',
                        slantedText: true,         // Set to true to enable slanted text
                        slantedTextAngle: 45       // Set the angle (45 degrees in this case)
                    },
                    vAxis: {
                        title: 'Rating',
                        minValue: 0,
                        maxValue: 5, // Set the maximum rating value
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.ColumnChart(document.getElementById('accommodationChart'));
                chart.draw(data, options);
            }

            // Event listeners for buttons
            document.getElementById('showSafety').addEventListener('click', () => {
                drawColumnChart(safetyData, 'Safety Ratings');
            });

            document.getElementById('showMaintenance').addEventListener('click', () => {
                drawColumnChart(maintenanceData, 'Maintenance Ratings');
            });

            document.getElementById('showConvenience').addEventListener('click', () => {
                drawColumnChart(convenienceData, 'Convenience Ratings');
            });

            document.getElementById('showNoiseLevels').addEventListener('click', () => {
                drawColumnChart(noiseLevelsData, 'Noise Levels Ratings');
            });

            document.getElementById('showValueForMoney').addEventListener('click', () => {
                drawColumnChart(valueForMoneyData, 'Value for Money Ratings');
            });
        });

        // reports for TOP 10
        document.addEventListener("DOMContentLoaded", function () {
            google.charts.load('current', { 'packages': ['corechart'] });

            // Sample data (replace with actual PHP data)
            const topTenants = [
                ['Tenant Name', 'Average Rating'],
                // Sample data (replace with actual PHP data)
                <?php
                if (mysqli_num_rows($top10_tenants_res) > 0) {
                    while ($top = mysqli_fetch_assoc($top10_tenants_res)) {
                        echo "['" . $top['Name'] . "', " . $top['AverageRating'] . "],";
                    }
                } else {
                    echo "['N/A', " . 0 . "],";
                }
                ?>
            ];

            const topAgents = [
                ['Agent Name', 'Average Rating'],
                <?php
                if (mysqli_num_rows($top10_agents_res) > 0) {
                    while ($top = mysqli_fetch_assoc($top10_agents_res)) {
                        echo "['" . $top['Name'] . "', " . $top['AverageRating'] . "],";
                    }
                } else {
                    echo "['N/A', " . 0 . "],";
                }
                ?>
            ];

            const topAccomodation = [
                ['Accommodation Name', 'Overall Rating'],
                <?php
                if (mysqli_num_rows($topDigs_res) > 0) {
                    while ($rate = mysqli_fetch_assoc($topDigs_res)) {
                        echo "['" . $rate['Name'] . "', " . $rate['AverageRating'] . "],";
                    }
                } else {
                    echo "['N/A', " . 0 . "],";
                }
                ?>
            ];

            // Function to draw column chart
            function drawTopColumnChart(dataArray, chartTitle) {
                const data = google.visualization.arrayToDataTable(dataArray);

                const options = {
                    title: chartTitle,
                    width: 600,  // Adjust width as needed
                    height: 400, // Adjust height as needed
                    bars: 'vertical',
                    hAxis: {
                        title: 'Name',
                        slantedText: true,         // Set to true to enable slanted text
                        slantedTextAngle: 45       // Set the angle (45 degrees in this case)
                    },
                    vAxis: {
                        title: 'Average Rating',
                        minValue: 0,
                        maxValue: 5, // Set the maximum rating value
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.ColumnChart(document.getElementById('topReportContainer'));
                chart.draw(data, options);
            }

            // Event listeners for buttons
            document.getElementById('showTopTenants').addEventListener('click', () => {
                drawTopColumnChart(topTenants, 'Top 10 Tenants');
            });

            document.getElementById('showTopAgents').addEventListener('click', () => {
                drawTopColumnChart(topAgents, 'Top 10 Agents');
            });

            document.getElementById('showTopAccommodations').addEventListener('click', () => {
                drawTopColumnChart(topAccomodation, 'Top 10 Accommodations');
            });
        });
        
        // reports for General
        document.addEventListener("DOMContentLoaded", function () {
            google.charts.load('current', {'packages': ['corechart']});
            // Ensure Google Charts is loaded before drawing the chart
            google.charts.setOnLoadCallback(drawRentalPricesChart);
            google.charts.setOnLoadCallback(drawOccupancyRatesChart);
            google.charts.setOnLoadCallback(drawAccommodationRatingsChart);

            // Sample data (replace with actual PHP data)
            const rentalPricesData = [
                ['Date', 'Rental Price'],
                <?php
                    // Loop through your database query result to populate data
                    while ($row = mysqli_fetch_assoc($rentalPricesResult)) {
                        echo "['" . $row['Month'] . "', " . $row['AvgRentalPrice'] . "],";
                    }
                ?>
            ];

            // Sample data (replace with actual PHP data)
            const occupancyRatesData = [
                ['Date', 'Occupancy Rate'],
                <?php
                    // Loop through your database query result to populate data
                    while ($row = mysqli_fetch_assoc($occupancyRatesResult)) {
                        echo "['" . $row['Month'] . "', " . $row['OccupiedAccommodations'] . "],";
                    }
                ?>
            ];

            function drawRentalPricesChart() {
                const data = google.visualization.arrayToDataTable(rentalPricesData);

                const options = {
                    title: 'Accommodation Rental Prices Over Time',
                    width: 600,
                    height: 400,
                    curveType: 'function',
                    legend: { position: 'bottom' },
                    hAxis: {
                        title: 'Date (Months)',
                    },
                    vAxis: {
                        title: 'Rental Price',
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.LineChart(document.getElementById('rentalPricesChartContainer'));
                chart.draw(data, options);
            }

            function drawOccupancyRatesChart() {
                const data = google.visualization.arrayToDataTable(occupancyRatesData);

                const options = {
                    title: 'Accommodation Occupancy Rates Over Time',
                    width: 600,
                    height: 400,
                    curveType: 'function',
                    legend: { position: 'bottom' },
                    hAxis: {
                        title: 'Date (Months)',
                    },
                    vAxis: {
                        title: 'Occupancy Rate',
                        minValue: 0,
                        maxValue: 50, // Assuming occupancy rate is in percentage
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.LineChart(document.getElementById('rentalPricesChartContainer'));
                chart.draw(data, options);
            }

            function drawAccommodationRatingsChart() {
                // Your chart drawing code for accommodation ratings over time
                const data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', 'Average Rating');
                <?php
                    // Loop through your database query result to populate data
                    while ($row = mysqli_fetch_assoc($accommodationRatingsResult)) {
                        echo "data.addRow(['" . $row['Month'] . "', " . $row['AvgRating'] . "]);";
                    }
                ?>

                const options = {
                    title: 'Accommodation Ratings Over Time ',
                    width: 600,
                    height: 400,
                    legend: { position: 'bottom' },
                    hAxis: {
                        title: 'Date (Months)',
                    },
                    vAxis: {
                        title: 'Average Rating',
                        minValue: 0,
                        maxValue: 5, // Assuming ratings are on a scale of 0-5
                    },
                    colors: ['#c6ab7c'], // Set the color for all bars to #213644
                };

                const chart = new google.visualization.LineChart(document.getElementById('rentalPricesChartContainer'));
                chart.draw(data, options);
            }

            // Event listener for button to display the Rental Prices Over Time chart
            document.getElementById('showRentalPricesChart').addEventListener('click', () => {
                drawRentalPricesChart();
            });

            // Event listener for button to display the Occupancy Rates Over Time chart
            document.getElementById('showOccupancyRatesChart').addEventListener('click', () => {
                drawOccupancyRatesChart();
            });

            document.getElementById('showAccommodationRatingsChart').addEventListener('click', () => {
                drawAccommodationRatingsChart();
            });


        });

        <?php
            //close connection when done
            mysqli_close($conn);
        ?>

    </script>
    <style>
        /* Style the reports lists */
        .report-list {
            list-style: none;
            padding: 0;
            width: 90%;
        }
        .report-list li {
            padding: 3%;
            margin-right: 1%;

        }
        .report-list li:nth-child(odd) {
            width: 100%;
            background-color: #e3e3e3;
            border-radius: 5px;
        }
    </style>
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

    <h1 id="header">My Dashboard</h1>

    <div class="grid-container">
        <div class="grid-item item1" alt="my_profile" style="border: none; margin-right: 5%;">
            
            <div class="profile-card">
            <h2>My Profile </h2>
                <!-- Profile Picture -->
                <?php
                    // Check if agent data is available in session
                    if (isset($_SESSION['admin_data'])) {
                        //display the data as a profile card
                        $admin_data = $_SESSION['admin_data'];
                        //echo $profilePictureURL;
                        echo '<div id="profile-picture">
                                <img id="profile_picture" src="' . htmlspecialchars($profilePictureURL) . '" alt="Profile Picture" > 
                            </div>';
                        echo '<div class="profile-details">
                                <h3>' . htmlspecialchars($admin_data['Name']) . ' ' . htmlspecialchars($admin_data['Surname']) . '</h3>
                            </div>';
                        } else {
                            // Handle the case where session data is missing
                            echo '<p>Your data was not found or an error occurred.</p>';
                    }
                ?>
                <!--<a href="admin_edit_profile.php" class="link" target="update_my_details">Edit Profile</a>-->
                <button class="show_form" data-target="update_my_details"> Edit Profile</button>
                <button class="show_form" data-target="change_password">Change Password</button>
                <form action="admin.php" method="post" id="change_password" class="form_container">

                    <input type="password" name="old_pass" id="old_pass" required placeholder="Enter Old Password"> 

                    <input type="password" name="new_pass" id="new_pass" required placeholder="Enter New Password"> 

                    <input type="password" name="reenter" id="reenter" required placeholder="ReEnter New Password"> 

                    <input type="submit" value="Confirm" name="confirm_new_pass" id="confirm" onclick="return confirm('Are You Sure You Want To Change Your Password?')">
                </form>

                <!--Form for editing the profile of admin-->
                <form action="admin.php" method="post" id="update_my_details" class="form_container" enctype="multipart/form-data">
                <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h3> Edit My Details </h3>

                    <label for="admin_name">Name: </label>
                    <input type="text" name="admin_name" id="admin_name" required value="<?php echo ($admin_data['Name']); ?>"> 

                    <label for="admin_surname">Surname: </label>
                    <input type="text" name="admin_surname" id="admin_surname" required value="<?php echo ($admin_data['Surname']); ?>"> 

                    <label for="admin_email">Email: </label>
                    <input type="email" name="admin_email" id="admin_email" required value="<?php echo ($admin_data['Email']); ?>"> 

                    <label for="profile_pic">Profile Picture: </label>
                    <input type="file" name="profile_pic" id="profile_pic" >

                    <input type="submit" value="Submit Changes" name="submit_changes" class="submit" onclick="return confirm('Are you sure you want to Submit changes you have made to your profile?')"> <br>

                    <input type="submit" value="Remove Profile Picture" name="delProfPic" class="submit" onclick="return confirm('Are you sure you want to delete your current profile picture?')"> <br>
                    <hr style="color:#213644; width:85%; margin-left:1%;">
                    
                </form>
            </div>
        </div>

        <div class="grid-item item2" alt="reports">
            <section class="controls">
                <div class="tab">
                    <button class="tablinks" onclick="openReport(event, 'Lists')" id="defaultOpen"> Lists</button>
                    <button class="tablinks" onclick="openReport(event, 'General')">General</button>
                    <button class="tablinks" onclick="openReport(event, 'Top 10')">Top 10</button>
                    <button class="tablinks" onclick="openReport(event, 'Digs Ratings')"> Digs Ratings</button>
                    <button class="tablinks" onclick="openReport(event, 'Manage Agent')"> Manage Agent </button>
                    <button class="tablinks" onclick="openReport(event, 'Manage Admin')"> Manage Admin</button>
                </div>

                <div id="Lists" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h2>Lists</h2>

                    <button class="show_form" id="showTenants">All Tenants</button>
                    <button class="show_form" id="showAgents">All Agents</button>
                    <button class="show_form" id="showAccommodations">All Accommodations</button>

                    <div id="reportContainer">
                        <!-- Reports will be displayed here -->
                    </div>
                </div>
                <div id="Top 10" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times;</span>
                    <h3>Top Ten</h3>

                    <!-- Buttons to trigger reports -->
                    <button class="show_form" id="showTopTenants">Top Ten Tenants</button>
                    <button class="show_form" id="showTopAgents">Top Ten Agents</button>
                    <button class="show_form" id="showTopAccommodations">Top Ten Accommodations</button>

                    <!-- HTML container for reports -->
                    <div id="topReportContainer"></div>
                </div>

                <div id="General" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h3>General</h3>

                    <button class="show_form" id="showRentalPricesChart">Rental Prices Over Time</button>

                    <button class="show_form" id="showOccupancyRatesChart">Show Occupancy Rates Over Time</button>

                    <button class="show_form" id="showAccommodationRatingsChart">Show Ratings Chart</button>

                    <div id="rentalPricesChartContainer" class="chart-container"></div>

                </div>

                <div id="Digs Ratings" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h3>Accommodations / Rating Criteria</h3>
                    <!-- <div id="top_properties" style="width: 100%; height: 100%;"></div> -->
                    <!-- Create button elements to trigger the reports -->
                    <button class="show_form" id="showSafety">Show Safety Ratings</button>
                    <button class="show_form" id="showMaintenance">Show Maintenance Ratings</button>
                    <button class="show_form" id="showConvenience">Show Convenience Ratings</button>
                    <button class="show_form" id="showNoiseLevels">Show Noise Levels Ratings</button>
                    <button class="show_form" id="showValueForMoney">Show Value for Money Ratings</button>

                    <!-- Create a container div for the column chart -->
                    <div id="accommodationChart"></div>
                </div>

                <div id="Manage Agent" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h2>Manage Agent</h2>
                    <div class="controls">
                        <button class="show_form" data-target="add_new_agent_form"> Add New Agent </button>
                        <button class="link">
                            <a href="admin_view_update_agents.php" id="view_my_listing">View & Update Agents</a>
                        </button>
                    </div>

                    <form action="admin.php" method="post" id="add_new_agent_form" class="form_container" enctype="multipart/form-data">
                        <h3> Enter New Agent Details </h3>

                        <label for="agent_name">Name: </label>
                        <input type="text" name="agent_name" id="agent_name" required> 

                        <label for="agent_surname">Surname: </label>
                        <input type="text" name="agent_surname" id="agent_surname" required> 

                        <label for="agent_gender">Gender: </label>
                        <select name="agent_gender" id="agent_gender" required >
                            <option value="">---Choose Gender---</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>

                        <label for="agent_dob">Date Of Birth: </label>
                        <input type="date" name="agent_dob" id="agent_dob" required> 

                        <label for="agent_email">Email: </label>
                        <input type="email" name="agent_email" id="agent_email" required> 

                        <label for="agent_contact">Mobile Contact: </label>
                        <input type="tel" name="agent_contact" id="agent_contact" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number"> <br>

                        <label for="agent_agency">Agency: </label>
                        <input type="text" name="agent_agency" id="agent_agency" required> 

                        <input type="submit" value="Add Agent" name="add_agent" class="submit">
                    </form>
                </div>

                <div id="Manage Admin" class="tabcontent">
                    <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
                    <h2>Manage Admin</h2>
                    <section class="controls">
                        <button class="show_form" data-target="add_new_admin_form"> Add New Admin </button>
                        <button class="link">
                            <a href="admin_view_update_admins.php" id="view_my_listing">View & Update Admins</a>
                        </button>
                        
                    </section>
                    
                    <form action="admin.php" method="post" id="add_new_admin_form" class="form_container" enctype="multipart/form-data">
                        <h3> Enter New Admin Details </h3>

                        <label for="admin_name">Name: </label>
                        <input type="text" name="admin_name" id="admin_name" required> 

                        <label for="admin_surname">Surname: </label>
                        <input type="text" name="admin_surname" id="admin_surname" required> 

                        <label for="agent_email">Email: </label>
                        <input type="email" name="admin_email" id="admin_email" required> 

                        <input type="submit" value="Add Admin" name="add_admin" class="submit">
                    </form>
                </div>
            </section>
        </div>
    </div>

    <footer>
        <p>
            The A Team &copy; 2023
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

    <script>
        // Get a reference to the navbar element
        const navbar = document.getElementById("navbar");

        // Function to handle scroll event
        function handleScroll() {
        // Check the current scroll position
        const currentScrollPos = window.scrollY;
        
        const scrollThreshold = 5;

        // Check if the user has scrolled down
        if (currentScrollPos > scrollThreshold) {
            // Scrolled down: Hide the navbar
            navbar.classList.add("hidden");
        } else {
            // Scrolled to the top: Show the navbar
            navbar.classList.remove("hidden");
        }
        }

        // Add a scroll event listener to the window
        window.addEventListener("scroll", handleScroll);

        // Initially, check the scroll position and hide the navbar if necessary
        handleScroll();

        // search bar 
        // Get a reference to the search button and search input
        const searchButton = document.querySelector(".searchBtn");
        const searchInput = document.querySelector(".searchInput");

        // Function to toggle the search input on click
        function toggleSearchInput(event) {
        // Prevent the anchor tag from navigating
        event.preventDefault();

        // Toggle visibility of the search button and input
        searchButton.style.display = "none";
        searchInput.style.display = "flex";
        searchInput.focus();

        // Remove the search input when clicking outside
        document.addEventListener("click", function (event) {
            if (event.target !== searchInput && event.target !== searchButton) {
            searchButton.style.display = "flex";
            searchInput.style.display = "none";
            }
        });
        }

        function openReport(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
    </script>
    <script src="admin_script.js"></script>
</body>
</html>