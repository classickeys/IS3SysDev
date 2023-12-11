<?php
//secure.php: file that has session verification
require_once("secure.php");

// Fetch the agent's current details from the session
$agent_data = $_SESSION['agent_data'];

//get the agents id from the previous page using the url
$agent_id = $_REQUEST['agentid'];

require_once("config.php");

$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

// Function to fetch listing details
function fetchListingDetails($conn, $accommodation_id) {
    $query = "SELECT * FROM accomodation WHERE AccomodationID = '$accommodation_id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        return mysqli_fetch_assoc($result);
    } else {
        // Handle error
        return null;
    }
}

// Get the accommodation ID from the URL parameter
if (isset($_GET['accommodationid'])) {
    $accommodation_id = mysqli_real_escape_string($conn, $_GET['accommodationid']);

    // Fetch the listing details
    $listing = fetchListingDetails($conn, $accommodation_id);

    if (!$listing) {
        // Handle the case where the listing is not found
        die("Listing not found.");
    }
} else {
    // Handle the case where accommodation_id is not provided in the URL
    die("Accommodation ID not provided.");
}

// Check if the form is submitted
if (isset($_POST['update_listing'])) {

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
    $property_modern = mysqli_real_escape_string($conn, $_POST["mordern"]);
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
    // echo $property_availability;

    // Update the property (listing) data in the database
    $update_query = "UPDATE accomodation SET 
                    Name = '$property_name',
                    Address = '$property_address',
                    Distance_From_Campus = '$property_distance',
                    Description = '$property_description',
                    Bedrooms = '$property_beds',
                    Bathrooms = '$property_baths',
                    Rent = '$property_rent',
                    Deposit = '$property_deposit', 
                    Modern = '$property_modern',
                    Furnished = '$property_furnished',
                    NSFAS_Accredited = '$property_nsfas',
                    Security = '$property_security',
                    Smoking = '$property_smoking',
                    Pets = '$property_pets',
                    Parking = '$property_parking',
                    Balcony = '$property_balcony',
                    Water = '$property_water',
                    Electricity = '$property_electricity',
                    Electricity_Backup = '$property_electricity_backup',
                    Water_Backup = '$property_water_backup',
                    WiFi = '$property_wifi',
                    Availability = '$property_availability'
                    WHERE AccomodationID = '$accommodation_id'";

    $result = mysqli_query($conn, $update_query);

    if ($result) {
        // Update successful
        // You can redirect the user or display a success message here
        // Example: header("Location: listings.php");
        echo '<script type="text/javascript">alert("Listing updated successfully!");</script>';
    } else {
        // Handle the update error
        $errorMessage = "Error: " . mysqli_error($conn);
        echo '<script type="text/javascript">alert("' . $errorMessage . '");</script>';
    }
}

// Fetch the property (listing) details from the database
$listing_data = array(); // Initialize an empty array to store the listing details

// Modify the query to retrieve property details using the $accommodation_id
$query = "SELECT * FROM accomodation WHERE AccomodationID = '$accommodation_id'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $listing_data = mysqli_fetch_assoc($result);
} else {
    // Handle the case where the property is not found
    // You can redirect or display an error message
    die("Property not found.");
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="dashboards.css">

    <style>
        /* Add your CSS styles for this page */
    </style>
</head>
<body>
    <main>
        <div id="something" >

            <a href="agent_view_update_listings.php?agentid=<?php echo $agent_id; ?>" id="add_listing_back" class="link" >Back</a>

            <h2 style="text-align: center;">Edit Listing</h2>

            <form action="" method="post">
                 <!-- Display listing details for editing -->
                 <label for="name">Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($listing_data['Name']); ?>" required>

                <label for="address">Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($listing_data['Address']); ?>" required>

                <!-- Add other fields for editing listing details -->
                <label for="rent">Rent/Month:</label>
                <input type="text" name="rent" value="<?php echo htmlspecialchars($listing_data['Rent']); ?>" required>

                <label for="deposit">Initial Deposit:</label>
                <input type="text" name="deposit" value="<?php echo htmlspecialchars($listing_data['Deposit']); ?>" required>

                <label for="distance">Distance From Campus:</label>
                <input type="text" name="distance"  value="<?php echo htmlspecialchars($listing_data['Distance_From_Campus']); ?>" required>

                <label for="beds">Bedrooms:</label>
                <input type="number" name="beds" value="<?php echo htmlspecialchars($listing_data['Bedrooms']); ?>" required>

                <label for="baths">Bathrooms:</label>
                <input type="number" name="baths" value="<?php echo htmlspecialchars($listing_data['Bathrooms']); ?>" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" cols="30" rows="2" placeholder="Enter as Much Extra information as you need to Capture Your Future Tenants..."><?php echo htmlspecialchars($listing_data['Description']); ?></textarea>

                <label for="type">Accomodation Type:</label>
                <select name="type" id="type" required>
                    <option value="">---Choose Type---</option>
                    <option value="Single" <?php if ($listing_data['Type'] === 'Single') echo 'selected'; ?>>Single</option>
                    <option value="Sharing" <?php if ($listing_data['Type'] === 'Sharing') echo 'selected'; ?>>Sharing</option>
                    <option value="House"  <?php if ($listing_data['Type'] === 'House') echo 'selected'; ?>>House</option>
                </select>
                <label for="furnished">Furnished Status: </label>
                <select name="furnished" id="furnished" required>
                    <option value="">---Choose Furnish Level---</option>
                    <option value="No"  <?php if ($listing_data['Furnished'] === 'No') echo 'selected'; ?>>Unfurnished</option>
                    <option value="Semi" <?php if ($listing_data['Furnished'] === 'Semi') echo 'selected'; ?>>Partly Furnished</option>
                    <option value="Yes" <?php if ($listing_data['Furnished'] === 'Yes') echo 'selected'; ?>>Fully Furnished</option>
                </select>
                
                <label for="nsfas">NSFAS Accredited:</label>
                <label>
                    <input type="radio" name="nsfas" value="1" <?php if ($listing_data['NSFAS_Accredited'] == 1) echo 'checked'; ?>> Yes
                    <input type="radio" name="nsfas" value="0" <?php if ($listing_data['NSFAS_Accredited'] == 0) echo 'checked'; ?>> No
                </label>

                <label for="mordern">Mordern: </label>
                <label>
                    <input type="radio" name="mordern" value="1" <?php if ($listing_data['Modern'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="mordern" value="0" <?php if ($listing_data['Modern'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="water_backup">Water BackUp: </label>
                <label>
                    <input type="radio" name="water_backup" value="1" <?php if ($listing_data['Water_Backup'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="water_backup" value="0" <?php if ($listing_data['Water_Backup'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="electricity_backup">Electricity BackUp: </label>
                <label>
                    <input type="radio" name="electricity_backup" value="1" <?php if ($listing_data['Electricity_Backup'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="electricity_backup" value="0" <?php if ($listing_data['Electricity_Backup'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="wifi">Comes With Wifi: </label>
                <label>
                    <input type="radio" name="wifi" value="1" <?php if ($listing_data['WiFi'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="wifi" value="0" <?php if ($listing_data['WiFi'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="electricity">Prepaid Electricity: </label>
                <label>
                    <input type="radio" name="electricity" value="1" <?php if ($listing_data['Electricity'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="electricity" value="0" <?php if ($listing_data['Electricity'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="water">Water Included in Rent?: </label>
                <label>
                    <input type="radio" name="water" value="1" <?php if ($listing_data['Water'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="water" value="0" <?php if ($listing_data['Water'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="balcony">Has Balcony: </label>
                <label>
                    <input type="radio" name="balcony" value="1" <?php if ($listing_data['Balcony'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="balcony" value="0" <?php if ($listing_data['Balcony'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="parking">Parking Available: </label>
                <label>
                    <input type="radio" name="parking" value="1" <?php if ($listing_data['Parking'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="parking" value="0" <?php if ($listing_data['Parking'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="pets">Pets Allowed?: </label>
                <label>
                    <input type="radio" name="pets" value="1" <?php if ($listing_data['Pets'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="pets" value="0" <?php if ($listing_data['Pets'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="smoking">Smoking Allowed:? </label>
                <label>
                    <input type="radio" name="smoking" value="1" <?php if ($listing_data['Smoking'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="smoking" value="0" <?php if ($listing_data['Smoking'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="security">Is Secured?: </label>
                <label>
                    <input type="radio" name="security" value="1" <?php if ($listing_data['Security'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="security" value="0" <?php if ($listing_data['Security'] === '0') echo 'checked'; ?>> No
                </label>

                <label for="available">Is Available?: </label>
                <label>
                    <input type="radio" name="available" value="1" <?php if ($listing_data['Availability'] === '1') echo 'checked'; ?>> Yes
                    <input type="radio" name="available" value="0" <?php if ($listing_data['Availability'] === '0') echo 'checked'; ?>> No
                </label>


                <!-- Display current images -->
                <label for="current_images">Current Images:</label>
                <div id="current_images">
                    <?php
                    require_once("config.php");
                    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
                        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
                    
                     // Assuming $listing is an array containing the listing details, including image paths
                     $imagePaths = []; // Create an empty array to store image paths

                    // Query to fetch current images for the listing
                    $currentImagesQuery = "SELECT Photo_Path FROM photographs WHERE AccomodationID = '$accommodation_id'";
                    $currentImagesResult = mysqli_query($conn, $currentImagesQuery);

                    if ($currentImagesResult) {
                        while ($row = mysqli_fetch_assoc($currentImagesResult)) {
                            $imagePaths[] = $row['Photo_Path']; // Add image paths to the array
                        }

                        foreach ($imagePaths as $imagePath) {
                            echo '<img src="' . $imagePath . '" alt="Accommodation Image" width="100">';
                        }
                    } else {
                        // Handle error while retrieving image paths
                        echo "Error: " . mysqli_error($conn);
                    }

                    mysqli_close($conn);
                    ?>
                </div>
                <br>
                
                <!--add this part when you are ready to delete and upload new images, if even allowed                <label for="new_images">Upload New Images (multiple):</label>
                <input type="file" name="images[]" accept="image/*" multiple>
                -->

                <input type="submit" name="update_listing" value="Update Listing">

            </form>

    </div>
    </main>
</body>
</html>