<?php
    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    $accommodationID = "PL0421";

    // Increment the view count for the viewed accommodation
    if (isset($_REQUEST['accommodationid'])) {
        $accommodationID = $_REQUEST['accommodationid']; //passed via URL
        $incrementViewsQuery = "UPDATE accomodation SET ViewCount = (ViewCount + 1) WHERE AccomodationID = '$accommodationID'";
        mysqli_query($conn, $incrementViewsQuery) or die(mysqli_error($conn));
    }

    // Query to fetch property details and main image
    $accommodation_query = "SELECT a.*, ta.TenantID, aa.AgentID
            FROM accomodation AS a
            LEFT JOIN tenant_accomodation AS ta ON a.AccomodationID = ta.AccomodationID
            LEFT JOIN agent_accomodation AS aa ON a.AccomodationID = aa.AccomodationID
            WHERE a.AccomodationID = '$accommodationID'";

    $photos_query = "SELECT * FROM photographs WHERE AccomodationID = '$accommodationID'";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Page</title>
    <style>
        /* Include styles from Code1 */
        body {
            font-family: Arial;
            margin: 0;
        }
        * {
        box-sizing: border-box;
        }
        /* ... (copy other CSS styles from Code1) ... */
        img {
        vertical-align: middle;
        }

        /* Position the image container (needed to position the left and right arrows) */
        .container {
        position: relative;
        }

        /* Hide the images by default */
        .mySlides {
        display: none;
        }

        /* Add a pointer when hovering over the thumbnail images */
        .cursor {
        cursor: pointer;
        }

        /* Next & previous buttons */
        .prev,
        .next {
        cursor: pointer;
        position: absolute;
        top: 40%;
        width: auto;
        padding: 16px;
        margin-top: -50px;
        color: white;
        font-weight: bold;
        font-size: 20px;
        border-radius: 0 3px 3px 0;
        user-select: none;
        -webkit-user-select: none;
        color: #c6ab7c;
        }

        /* Position the "next button" to the right */
        .next {
        right: 50px;
        border-radius: 3px 0 0 3px;
        }

        /* On hover, add a black background color with a little bit see-through */
        .prev:hover,
        .next:hover {
        background-color: rgba(0, 0, 0, 0.8);
        }

        /* Number text (1/3 etc) */
        /* .numbertext {
        color: #f2f2f2;
        font-size: 12px;
        padding: 8px 12px;
        position: absolute;
        top: 0;
        } */

        /* Container for image text */
        .caption-container {
        text-align: center;
        background-color: #222;
        padding: 2px 16px;
        color: white;
        border: solid 1px green;
        }

        .row:after {
        content: "";
        display: table;
        clear: both;
        margin: auto;
        }

        /* Six columns side by side */
        .column {
        float: left;
        width: 16.66%;
        }

        /* Add a transparency effect for thumnbail images */
        .demo {
        opacity: 0.6;
        }

        .active,
        .demo:hover {
        opacity: 1;
        }
        /* Gallery-specific styles */
        .gallery {
            background-color: black;
            /* border: solid 1px red; */
            border-radius: 5px;
            margin: auto;
            /* padding: 5%; */
            height: auto;
            width: 100%;
        }
        /* .gallery button {
            position: relative;
            margin: auto;
            padding:;
            border: none;
            border-radius: 5px;
            background-color: #c6ab7c;
            color: white;
            font-size: 18px;
        } */
        .slideshow-container {
            position: relative;
            max-width: 800px;
            margin: auto;
            /* border-color: red; */
            width: fit-content;
        }
        .mySlides {
            display: none;
        }
        img {
            width: 100%;
            height: auto;
        }
        .carousel-container {
            max-width: 800px;
            margin: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel {
            display: flex;
            overflow: hidden;
            width: 100%;
        }
        .carousel img {
            width: 100%;
            height: auto;
        }
        .carousel-button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="gallery">
        <div class="slideshow-container">
            <?php
            // Loop through the images and create slideshow slides
            $index = 1;
            while ($images = mysqli_fetch_assoc($photos_result)) {
                echo '<div class="mySlides">';
                echo '<div class="numbertext">' . $index . ' / ' . mysqli_num_rows($photos_result) . '</div>';
                echo '<img src="' . $images['Photo_Path'] . '" alt="Image ' . $index . '">';
                echo '</div>';
                $index++;
            }
            ?>
        </div>

        <!-- Add Next and Previous buttons -->
        <a class="prev" onclick="plusSlides(-1)">❮</a>
        <a class="next" onclick="plusSlides(1)">❯</a>
    </div>
    <div class="caption-container">
    <p id="caption"></p>
  </div>
    <!-- Add a container for thumbnail images -->
    <div class="row">
        <?php
        // Reset the index for thumbnail images
        $index = 1;
        mysqli_data_seek($photos_result, 0);
        while ($images = mysqli_fetch_assoc($photos_result)) {
            echo '<div class="column">';
            echo '<img class="demo cursor" src="' . $images['Photo_Path'] . '" style="width:100%" onclick="currentSlide(' . $index . ')" alt="Image ' . $index . '">';
            echo '</div>';
            $index++;
        }
        ?>
    </div>
    
</body>
<script>
    // Include JavaScript from Code1
    let slideIndex = 1;
        showSlides(slideIndex);

    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("demo");
        let captionText = document.getElementById("caption");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
        captionText.innerHTML = dots[slideIndex-1].alt;
    }

    // Function to update the current slide when a thumbnail is clicked
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    // Function to show slides
    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("demo");
        let captionText = document.getElementById("caption");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
        captionText.innerHTML = dots[slideIndex-1].alt;
    }

    // Event listeners for clicking next and previous buttons
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('.prev').addEventListener('click', function () {
            plusSlides(-1);
        });
        document.querySelector('.next').addEventListener('click', function () {
            plusSlides(1);
        });
    })
</script>