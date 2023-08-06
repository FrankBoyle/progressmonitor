<?php
// Replace with your database connection details
$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "AndersonSchool";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve the data from the table
$sql = "SELECT * FROM accounts";
$result = $conn->query($sql);

// Convert the data to a JSON format
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Close the connection
$conn->close();

// Convert the data to JSON
$jsonData = json_encode($data);
?>


<!DOCTYPE html>
<html>
<head>
    <title>jsGrid Table</title>
    <!-- Include CSS file for jsGrid -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.js" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid-theme.min.css" />
</head>
<body>
    <script>
        // Load the data from PHP into a JavaScript variable
        var data = <?php echo $jsonData; ?>;
    
        // Initialize jsGrid
        $(function() {
            $("#jsGrid").jsGrid({
                width: "100%",
                height: "400px",
                sorting: true,
                paging: true,
                data: data, // Set the data from PHP here
                fields: [
                    { name: "id", type: "number", width: 50 },
                    { name: "name", type: "text", width: 150 },
                    { name: "email", type: "text", width: 200 },
                    { name: "age", type: "number", width: 50 },
                    // Add other columns as needed
                ]
            });
        });
    </script>
    <div id="jsGrid"></div>

    <!-- Include JavaScript files for jQuery and jsGrid -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</body>
</html>