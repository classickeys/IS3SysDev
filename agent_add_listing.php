<?php

//secure.php: file that has session verification
require_once("secure.php");

// Fetch the agent's current details from the session
$agent_data = $_SESSION['agent_data'];

//get the agents id from the previous page using the url
$agent_id = $_REQUEST['agentid'];

// Include your config.php for database connection
require_once("config.php");

$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $property_name = mysqli_real_escape_string($conn, $_POST["name"]);
    $check_listing_query = "SELECT * FROM accomodation WHERE Name = '$property_name'";
    $check_listing_result = mysqli_query($conn, $check_listing_query);

    if (mysqli_num_rows($check_listing_result) > 0) {
        // Agent with the same email already exists, display an error message
        echo('<script type="text/javascript">alert("A Property with this Name already exists!");</script>');
    } else {

        // Retrieve and sanitize form data
        $property_name = mysqli_real_escape_string($conn, $_POST["name"]);
        $property_address = mysqli_real_escape_string($conn, $_POST["address"]);
        $property_rent = mysqli_real_escape_string($conn, $_POST["rent"]);
        $property_deposit = mysqli_real_escape_string($conn, $_POST["deposit"]);
        $property_distance = mysqli_real_escape_string($conn, $_POST["distance"]);
        $property_beds = mysqli_real_escape_string($conn, $_POST["beds"]);
        $property_baths = mysqli_real_escape_string($conn, $_POST["baths"]);
        $property_type = mysqli_real_escape_string($conn, $_POST["type"]);
        $property_furnished = mysqli_real_escape_string($conn, $_POST["furnished"]);
        $property_nsfas = mysqli_real_escape_string($conn, $_POST["nsfas"]);
        $property_modern = mysqli_real_escape_string($conn, $_POST["modern"]);
        $property_water_backup = mysqli_real_escape_string($conn, $_POST["water_backup"]);
        $property_electricity_backup = mysqli_real_escape_string($conn, $_POST["electricity_backup"]);
        $property_wifi = mysqli_real_escape_string($conn, $_POST["wifi"]);
        $property_electricity = mysqli_real_escape_string($conn, $_POST["electricity"]);
        $property_water = mysqli_real_escape_string($conn, $_POST["water"]);
        $property_balcony = mysqli_real_escape_string($conn, $_POST["balcony"]);
        $property_parking = mysqli_real_escape_string($conn, $_POST["parking"]);
        $property_pets = mysqli_real_escape_string($conn, $_POST["pets"]);
        $property_smoking = mysqli_real_escape_string($conn, $_POST["smoking"]);
        $property_security = mysqli_real_escape_string($conn, $_POST["security"]);
        $property_description = mysqli_real_escape_string($conn, $_POST["description"]);
        $property_availability = mysqli_real_escape_string($conn, $_POST["available"]);

        $timestamp = time();
        $last_four_digits = substr($timestamp, -4); // Get the last four digits of the timestamp
        $first_letter = substr($property_name, 0, 1); // Get the first letter of the property name
        $accomodation_id = 'P' . $first_letter . $last_four_digits;

        // Insert accommodation details into the Accommodation table
        $insert_accommodation_query = "INSERT INTO accomodation (AccomodationID, Name, Address, Rent, Deposit, Distance_From_Campus, Bedrooms, Bathrooms, Type, Furnished, NSFAS_Accredited, Modern, Water_Backup, Electricity_Backup, WiFi, Electricity, Water, Balcony, Parking, Pets, Smoking, Security, Description, Availability) VALUES ('$accomodation_id', '$property_name', '$property_address', '$property_rent', '$property_deposit', '$property_distance', '$property_beds', '$property_baths', '$property_type', '$property_furnished', '$property_nsfas', '$property_modern', '$property_water_backup', '$property_electricity_backup','$property_wifi', '$property_electricity', '$property_water', '$property_balcony', '$property_parking', '$property_pets', '$property_smoking', '$property_security', '$property_description', '$property_availability')";

        $result = mysqli_query($conn, $insert_accommodation_query);

        if ($result) {
            $currentDate = date('Y-m-d');
            // Associate the accommodation with the agent in AgentAccommodation table
            $insert_agent_accommodation_query = "INSERT INTO agent_accomodation (AgentID, AccomodationID, From_Date) VALUES ('$agent_id', '$accomodation_id', '$currentDate')";

            $result2 = mysqli_query($conn, $insert_agent_accommodation_query);

            if ($result2) {
                // // Handle video uploads
                // $target_video_dir = "propertyVideos/"; // Specify your upload directory for videos

                // foreach ($_FILES["videos"]["tmp_name"] as $key => $tmp_name) {
                //     $video_name = $_FILES["videos"]["name"][$key];
                //     $target_video_file = $target_video_dir . basename($video_name);

                //     $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                //     $video_id = 'V' . $unique_part;
                //     //echo $target_video_file;

                //     // Upload video
                //     if (move_uploaded_file($tmp_name, $target_video_file)) {
                //         // Insert video path into the Videos table
                //         $insert_video_query = "INSERT INTO videos (VideoID, AccomodationID, VideoPath) VALUES ('$video_id', '$accomodation_id', '$target_video_file')";
                //         $result5 = mysqli_query($conn, $insert_video_query) or die('<script type="text/javascript">alert("Error 15:' . mysqli_error($conn) . '")</script>');
                //     }
                //     else{
                //         die("FAILED TO MOVE UPLOADED VIDEO!");
                //     }
                // }

                // Upload and save images to the Photographs table
                $target_dir = "propertyImages/"; // Specify your upload directory
                foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
                    $image_name = $_FILES["images"]["name"][$key];
                    $target_file = $target_dir . basename($image_name);
                    
                    $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                    $photograph_id = 'I' . $unique_part;
                        

                    // Upload image
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // Insert image path into the Photographs table
                    
                        $insert_photo_query = "INSERT INTO photographs (PhotographID, AccomodationID, Photo_Path) VALUES ('$photograph_id', '$accomodation_id', '$target_file')";
                        $result3 = mysqli_query($conn, $insert_photo_query) or die('<script type="text/javascript">alert("Error 11:' . mysqli_error($conn) . '")</script>');

                    }
                }
                             
                //Handle main picture uploading
                $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                $main_photo_id = 'IM' . $unique_part;

                $main_pic = $_FILES['main_image']['name'];
                $destination = "propertyImages/" . $main_pic;
                move_uploaded_file($_FILES['main_image']['tmp_name'], $destination);

                $insert_main_photo_query = "INSERT INTO main_pictures (MainPhotoID, AccomodationID, PhotoPath) VALUES ('$main_photo_id', '$accomodation_id', '$destination')";
                $result4 = mysqli_query($conn, $insert_main_photo_query) or die('<script type="text/javascript">alert("Error 12:' . mysqli_error($conn) . '")</script>');

                // Redirect to a success page or show a success message
                echo('<script type="text/javascript">alert("Property Added Successfully")</script>');
            } else {
                // Handle error while associating accommodation with agent
                echo('<script type="text/javascript">alert("Error 13:' . mysqli_error($conn) . '")</script>');
            }
        } else {
            // Handle error while inserting accommodation details
            echo('<script type="text/javascript">alert("Error 14:' . mysqli_error($conn) . '")</script>');
        }
    }
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Listing</title>  
    <link rel="stylesheet" href="dashboards.css">
</head>

<body>
    <main>
        <div id="something" style="width: 50%;" >
            <!-- Back button -->
            <a href="agent.php?agentid=<?php echo $agent_id; ?>" id="profile_edit_back" class="link">Back</a>
            
            <form action="agent.php" method="post"  enctype="multipart/form-data" >
                <h2 style="margin-top: 40px;">Add New Listing</h2>
                <br> 
                <!-- Accommodation Details -->
                <label for="name">Name:</label>
                <input type="text" name="name" required>

                <label for="address">Address:</label>
                <input type="text" name="address" required> 

                <label for="rent">Rent/Month:</label>
                <input type="text" name="rent" required>

                <label for="deposit">Initial Deposit:</label>
                <input type="text" name="deposit" required>

                <label for="distance">Distance From Campus:</label>
                <input type="text" name="distance" required>

                <label for="beds">Bedrooms:</label>
                <input type="number" name="beds" required min="0" max="20">

                <label for="baths">Bathrooms:</label>
                <input type="number" name="baths" required min="0" max="20">

                <label for="type">Accommodation Type:</label>
                <select name="type" id="type" required>
                    <option value="">---Choose Type---</option>
                    <option value="Single">Single</option>
                        <option value="Sharing">Sharing</option>
                    <option value="House">House</option>
                </select>

                <label for="furnished">Furnished Status:</label>
                <select name="furnished" id="furnished" required>
                    <option value="">---Choose Furnish Level---</option>
                    <option value="No">Unfurnished</option>
                    <option value="Semi">Partly Furnished</option>
                    <option value="Yes">Fully Furnished</option>
                </select>

                <label for="description" style="margin-top: 5px;">Description:</label>
                <textarea name="description" id="description" cols="30" rows="2" placeholder="Enter as Much Extra information as you need to Capture Your Future Tenants..."></textarea>

                <label for="nsfas" >NSFAS Accredited:</label>
                <br>
                <label>
                    <input type="radio" name="nsfas" value="1">
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="nsfas" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="modern">Modern:</label>
                <br>
                <label>
                    <input type="radio" name="modern" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="modern" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="water_backup">Water Backup:</label>
                <br>
                <label>
                    <input type="radio" name="water_backup" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="water_backup" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="electricity_backup">Electricity Backup:</label>
                <br>
                <label>
                    <input type="radio" name="electricity_backup" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="electricity_backup" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="wifi">Comes With Wifi:</label>
                <br>
                <label>
                    <input type="radio" name="wifi" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="wifi" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="electricity">Prepaid Electricity:</label>
                <br>
                <label>
                    <input type="radio" name="electricity" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="electricity" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="water">Water Included in Rent?:</label>
                <br>
                <label>
                    <input type="radio" name="water" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="water" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="balcony">Has Balcony:</label>
                <br>
                <label>
                    <input type="radio" name="balcony" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="balcony" value="0">
                    <span></span> No
                </label>
                <br>
                
                <label for="parking">Parking Avaliable:</label>
                <br>
                <label>
                    <input type="radio" name="parking" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="parking" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="pets">Pets Allowed:</label>
                <br>
                <label>
                    <input type="radio" name="pets" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="pets" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="smoking">Smoking Allowed:</label>
                <br>
                <label>
                    <input type="radio" name="smoking" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="smoking" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="security">Is Secured?:</label>
                <br>
                <label>
                    <input type="radio" name="security" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="security" value="0">
                    <span></span> No
                </label>
                <br>

                <label for="available">Is Available?:</label>
                <br>
                <label>
                    <input type="radio" name="available" value="1" required>
                    <span></span> Yes
                </label>
                <label>
                    <input type="radio" name="available" value="0">
                    <span></span> No
                </label>
                <br>

                <!-- Upload Images -->
                <label for="images">Upload The Main Images (A Max of 18):</label>
                <input type="file" name="images[]" accept="image/*" multiple required>


                <label for="main_image">Upload The Main Image (Only 1)</label>
                <input type="file" name="main_image" accept="image/*" required>


                <!-- <label for="videos">Upload A Video ():</label>
                <input type="file" name="videos[]" accept="video/*" multiple> -->

                <input type="submit" name="submit" class="submit" value="Add Accommodation">

            </form>
        </div>
    </main>

</body>
</html>