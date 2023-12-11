<?php
    require_once("config.php");

    $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
        or die('<script type="text/javascript">alert("Failed To Connect to our Server/Database. Please try Again Later!")</script>');

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = mysqli_real_escape_string($conn, $_POST["username"]);
        $password = mysqli_real_escape_string($conn, $_POST["password"]);

        // Query the database to retrieve user data by username
        $query = "SELECT * FROM users WHERE UserName = '$username' ";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            // User found, fetch user data
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['Password'])) {
                if ($row['Active'] == '1' || $row['Active'] == 1) {
                    // User is active, start a session
                    session_start();

                    // Store user information in session variables
                    $_SESSION["user_id"] = $row["UserID"];
                    $_SESSION["username"] = $row["UserName"];
                    $_SESSION["role"] = $row["Role"];
                    $_SESSION["access"] = "yessir";

                    // Redirect to the respective page based on the user's role
                    if ($row["Role"] === "T") {
                        header("Location: tenant.php");
                        exit();
                    } elseif ($row["Role"] === "A") {
                        header("Location: agent.php");
                        exit();
                    } elseif ($row["Role"] === "S") {
                        header("Location: admin.php");
                        exit();
                    }
                } else {
                    echo "<script>alert('Your Account Is Deactivated!');</script>";
                }
            } else {
                echo "<script>alert('Invalid username or password');</script>";
            }
        } else {
            echo "<script>alert('User not found');</script>";
        }
    }

    // Disconnecting the database
    mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="login_style.css">
</head>
<body>
    <main>
        <div class="login_overlay" id="loginOverlay">
            <div class="login_container">
                
                <div class="login_form">
                    <h2>Login</h2>
                    <form action="loginPage.php" method="post">

                        <input type="text" name="username" id="username" placeholder= "Username"  autofocus required>
                        <br>

                        <input type="password" name="password" id="password" placeholder="Password"  required>
                        <br>

                        <div class="login-button-container">
                            <button type="submit">Login</button>
                        </div>
                
                    </form>
                </div>
                <div class="back_button">
                    <a href="index.php" id="closeButton" class="close_button">Back</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>