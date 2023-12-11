<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://kit.fontawesome.com/8320e0ead0.js" crossorigin="anonymous"></script>
</head>
<body>
        <!-- Safety Rating -->
    <div class="rating-category">

        <div class="rating">
            <!--elements are in reversed order, to allow "previous sibling selectors" in CSS-->
            <input type="radio" name="rating" value="5" id="5"><label for="5">☆</label>
            <input type="radio" name="rating" value="4" id="4"><label for="4">☆</label>
            <input type="radio" name="rating" value="3" id="3"><label for="3">☆</label>
            <input type="radio" name="rating" value="2" id="2"><label for="2">☆</label>
            <input type="radio" name="rating" value="1" id="1"><label for="1">☆</label>
        </div>
        /*_______________CSS FOR div.rating !!!!!!!!______________________________ */
        /*shows the stars side by side, centered, and in reverse order than the HMTL*/
            .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            }

            /*hides the radio buttons*/
            .rating > input{ display:none;}

            /*style the empty stars, sets position:relative as base for pseudo-elements*/
            .rating > label {
            position: relative;
            width: 1.1em;
            font-size: 15vw;
            color: #FFD700;
            cursor: pointer;
            }

            /* sets filled star pseudo-elements */
            .rating > label::before{ 
            content: "\2605";
            position: absolute;
            opacity: 0;
            }
            /*overlays a filled start character to the hovered element and all previous siblings*/
            .rating > label:hover:before,
            .rating > label:hover ~ label:before {
            opacity: 1 !important;
            }

            /*overlays a filled start character on the selected element and all previous siblings*/
            .rating > input:checked ~ label:before{
            opacity:1;
            }

            /*when an element is selected and pointer re-enters the rating container, selected rate and siblings get semi transparent, as reminder of current selection*/
            .rating:hover > input:checked ~ label:before{ opacity: 0.4; }

            /*just aesthetics*/
            body{ background: #222225; color: white;}
            h1, p{ text-align: center;}
            p{ font-size: 1.2rem;}
            @media only screen and (max-width: 600px) {
            h1{font-size: 14px;}
            p{font-size: 12px;}
            }

    </div>
    <?php echo date("Y-m-d H:i:s"); ?>

</body>
</html>