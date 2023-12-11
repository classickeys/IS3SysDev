<?php

// Include your config.php for database connection
require_once("config.php");
$conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
    or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["search_submit"])) {
            // Handle search functionality here
            $searchTerm = mysqli_real_escape_string($conn, $_POST["search"]);
            header("Location: searchProperties.php?search_term=" . $searchTerm);
        } 
    }
    
    // Check if the user is logged in and is a tenant or agent
    session_start();

    // Function to check if the user is a tenant
    function isTenant($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $tenant_query = "SELECT TenantID FROM tenant WHERE UserID = '$user_id'";
        $tenant_result = mysqli_query($conn, $tenant_query);
        
        return ($tenant_result && mysqli_num_rows($tenant_result) > 0);
    }

    // Function to check if the user is an agent
    function isAgent($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $agent_query = "SELECT AgentID FROM agent WHERE UserID = '$user_id'";
        $agent_result = mysqli_query($conn, $agent_query);

        
        return ($agent_result && mysqli_num_rows($agent_result) > 0);

    }

    // Function to check if the user is an admin
    function isAdmin($user_id) {
        require_once("config.php");

        $conn = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DATABASE)
            or die('<script type="text/javascript">alert("Error 0:" . mysqli_error($conn))</script>');
        
        $admin_query = "SELECT AdminID FROM administrator WHERE UserID = '$user_id'";
        $admin_result = mysqli_query($conn, $admin_query);
        
        return ($admin_result && mysqli_num_rows($admin_result) > 0);
    }
?>