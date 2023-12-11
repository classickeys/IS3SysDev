<?php
    require_once('config.php');

    //connect to the db
    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABSE) 
    or die('<script type="text/javascript">alert("Failed To Connect to our Server/Database. Please try Again Later!")</script>');

    if(isset($_POST['myrole'])){
        $role = mysqli_real_escape_string($conn, $_POST['role']);
    }
    
    if(isset($_POST['sign_up'])){
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $surname = mysqli_real_escape_string($conn, $_POST['surname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $dob = mysqli_real_escape_string($conn, $_POST['date']);
        $tel = mysqli_real_escape_string($conn, $_POST['tel']);
        $profile =  $_FILES['profile']['name'];

        if($role = "admin"){
            //first adding the admin as a new user
            $rolea = 'A';
            //create username of the format T23M1234...
            $username = $rolea . date('y') . substr($_POST['surname'], 0, 1) . substr(time(), -4);

            $password = generate_password();

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $insert_user_query = "INSERT INTO users (Password, Role, Username) VALUES ('$hashedPassword', '$rolea', '$username')";

            $user_result = mysqli_query($conn, $insert_user_query);

            if ($user_result) {
                // User added successfully, retrieve the auto-generated(incremented) UserID. using respective function
                $userID = mysqli_insert_id($conn);

                //Handle main picture uploading
                $unique_part = substr(uniqid(), -5); // Ensure a total length of 5 characters
                $profilepic = 'PP' . $unique_part;

                $destination = "profile_pictures/" . $unique_part . $profilepic;
                move_uploaded_file($_FILES['profile']['tmp_name'], $destination);

                $insert_admin_query = "INSERT INTO admin (AdminID, Name, Surname, Email, PhoneNumber, Gender, DateOfBirth, ProfilePicture, UserID) 
                VALUES (CONCAT('A', $userID), '$name', '$surname', '$email', '$tel', '$destination', '$gender', '$dob', '$userID')";

                $admin_result = mysqli_query($conn, $insert_admin_query);

                if ($admin_result) {
                    // Admin added successfully
                    echo('<script type="text/javascript">alert("Admin Added Successfully");</script>');

                    echo('<script type="text/javascript">alert("Please Keep These Login Details somewhere SAFE! \n\nUSERNAME:' . $username . ' \nPASSWORD:' . $password . '");</script>');
                    //header("Location: admin.php");
                } else {
                    // Handle the error
                    // Use JavaScript to display the error message as a pop-up for failed
                    echo('<script type="text/javascript">alert("Error: Failed to add new Admin!"' . mysqli_error($conn) . ');</script>');
                }
            } else {
                // Handle the error

                // Use JavaScript to display the error message as a pop-up
                echo('<script type="text/javascript">alert("Error: Failed to add admin as a new user!");</script>');
            }
        }
        else if($role = "lecturer"){

        }
        else if($role = "student"){

        }
    }

    function generate_password(){
        // Characters that can be included in the random string
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
        // Length of the random string
        $length = 6;
    
        // Initialize the random string
        $random_password = '';
    
        // Generate a random string
        for ($i = 0; $i < $length; $i++) {
            $random_password .= $characters[rand(0, strlen($characters) - 1)];
        }
    
        return $random_password;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="forms.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .container {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: slateblue;
        }

        .signup-form label,
        .signup-form input,
        .signup-form select {
            display: block;
            margin-bottom: 10px;
        }

        .signup-form input[type="submit"] {
            background-color: slateblue;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .signup-form input[type="submit"]:hover {
            background-color: #4527a0;
        }

        .back-button {
            margin-top: 10px;
            cursor: pointer;
            color: slateblue;
        }
    </style>
    <script>
        function showForm(role) {
            var forms = document.querySelectorAll('.signup-form');
            for (var i = 0; i < forms.length; i++) {
                forms[i].style.display = 'none';
            }

            var selectedForm = document.getElementById(role + '_register');
            if (selectedForm) {
                selectedForm.style.display = 'block';
            }

            // Hide the "Select Your Role" form
            document.getElementById('role-form').style.display = 'none';
        }

        function showRoleForm() {
            var forms = document.querySelectorAll('.signup-form');
            for (var i = 0; i < forms.length; i++) {
                forms[i].style.display = 'none';
            }

            // Show the "Select Your Role" form
            document.getElementById('role-form').style.display = 'block';
        }

        // Show the "Select Your Role" form when the page loads
        window.onload = function () {
            showRoleForm();
        };
    </script>

</head>
<body>
    <div class="container">
        <form action="signup.php" method="post" class="signup-form" id="role-form">
            <label for="role">Select Your Role</label>
            <select name="role" id="role" required onchange="showForm(this.value)">
                <option value="">---Choose Your Role---</option>
                <option value="admin" name>Admin</option>
                <option value="lecturer">Lecturer</option>
                <option value="student">Student</option>
            </select>
            <br>
        </form>

        <form action="signup.php" method="post" id="admin_register" class="signup-form" style="display: none;" enctype="multipart/form-data">
            <h2>Sign Up As An Admin</h2>
            <div>
                <span class="back-button" onclick="showRoleForm()">Back</span>
            </div>
            <input type="text" name="name" id="name" required placeholder="Name">
            <input type="text" name="surname" id="surname" required placeholder="Surname">
            <input type="email" name="email" id="email" required placeholder="Email">
            <input type="tel" name="tel" id="tel" required placeholder="Contact Number">
            <select name="gender" id="gender" required>
                <option value="">---Choose Your Gender---</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <br>
            <label for="dob">Date Of Birth</label><br>
            <input type="date" name="date" id="date" required><br>
            <label for="profile">Choose Your Profile Picture</label> <br>
            <input type="file" name="profile" id="profile">
            <input type="submit" value="Sign Up" name="sign_up">
        </form>

        <form action="signup.php" method="post" id="lecturer_register" class="signup-form" style="display: none;" enctype="multipart/form-data">
            <h2>Sign Up As A Lecturer</h2>
            <div>
                <span class="back-button" onclick="showRoleForm()">Back</span>
            </div>
            <input type="text" name="name" id="name" required placeholder="Name">
            <input type="text" name="surname" id="surname" required placeholder="Surname">
            <input type="email" name="email" id="email" required placeholder="Email">
            <input type="tel" name="tel" id="tel" required placeholder="Contact Number">
            <select name="gender" id="gender" required>
                <option value="">---Choose Your Gender---</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <br>
            <label for="dob">Date Of Birth</label><br>
            <input type="date" name="date" id="date" required><br>
            <label for="profile">Choose Your Profile Picture</label> <br>
            <input type="file" name="profile" id="profile" placeholder="profile">
            <input type="submit" value="Sign Up" name="sign_up">
        </form>

        <form action="signup.php" method="post" id="student_register" class="signup-form" style="display: none;" enctype="multipart/form-data">
            <h2>Sign Up As A Student</h2>
            <div>
                <span class="back-button" onclick="showRoleForm()">Back</span>
            </div>
            <input type="text" name="name" id="name" required placeholder="Name">
            <input type="text" name="surname" id="surname" required placeholder="Surname">
            <input type="email" name="email" id="email" required placeholder="Email">
            <input type="tel" name="tel" id="tel" required placeholder="Contact Number">
            <select name="gender" id="gender" required>
                <option value="">---Choose Your Gender---</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select> <br>
            <label for="dob">Date Of Birth</label><br>
            <input type="date" name="date" id="date" required><br>
            <label for="profile">Choose Your Profile Picture</label> <br>
            <input type="file" name="profile" id="profile" placeholder="profile">
            <input type="submit" value="Sign Up" name="sign_up">
        </form>
    </div>
    
</body>
</html>