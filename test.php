<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta charset="UTF-8">
    <title>Your Page Title</title>


</head>
<body>
<script>
        $(document).ready(function() {
            // Add click event handler to editable cells
            $('.editable').click(function() {
                const cell = $(this);
                const originalValue = cell.data('value');

                // Create an input field for editing
                const input = $('<input type="text">');
                input.val(originalValue);

                // Replace the cell content with the input field
                cell.html(input);

                // Focus on the input field
                input.focus();

                // Add blur event handler to save changes
                input.blur(function() {
                    const newValue = input.val();
                    cell.data('value', newValue);

                    // Update the cell content with the new value
                    cell.text(newValue);

                    // Perform AJAX request to update the database with the new value
                    const performanceId = cell.closest('tr').data('performance-id');
                    const fieldName = cell.index() - 1;

                    alert('Data updated locally. Remember to hit "Update" to save all changes.');


                });

                // Pressing Enter key while editing should save changes
                input.keypress(function(e) {
                    if (e.which === 13) {
                        input.blur();
                    }
                });
            });
        });
    </script>
<?php
// Error tracking: Log PHP errors to a file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function logError($error) {
    $logFile = 'error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $error\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Update logic
if(isset($_POST['update'])) {
    $performanceIds = $_POST['performance_id'];
    foreach ($performanceIds as $index => $performanceId) {
        $updatedScores = [];
        for ($i = 1; $i <= 10; $i++) {
            $updatedScores['score'.$i] = $_POST['score'.$i][$index];
        }
        // Prepare the SQL update query using the retrieved data
        $sql = "UPDATE Performance SET ";
        $setClauses = [];
        for ($i = 1; $i <= 10; $i++) {
            $setClauses[] = "score{$i} = ?";
        }
        $sql .= implode(', ', $setClauses) . " WHERE performance_id = ?";
        
        $stmt = $conn->prepare($sql);
        $values = array_values($updatedScores);
        $values[] = $performanceId;
        $stmt->bind_param(str_repeat('i', count($values)), ...$values);
        $stmt->execute();

        // Check for errors during the update
        if ($stmt->error) {
            throw new Exception("Error updating performance data: " . $stmt->error);
        }
    }
    echo "Data updated successfully!";
}

// Your existing PHP code here
try {
    $servername = "localhost";
    $username = "AndersonSchool";
    $password = "SpecialEd69$";
    $dbname = "bFactor-test";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully to the database";
    }

    // ... [Rest of your code]

    if (isset($_GET['student_id'])) {
        // ... [Previous code]

        echo "<form method='post' action=''>";
        echo "<table border='1'>";
        // ... [Other rows and headers]

        foreach ($performanceData as $data) {
            echo "<tr data-performance-id='" . $data['performance_id'] . "'>";
            echo "<td><input type='hidden' name='performance_id[]' value='{$data["performance_id"]}'>{$data["performance_id"]}</td>";
            echo "<td>{$data['week_start_date']}</td>"; // Assuming week_start_date is not editable
            for ($i = 1; $i <= 10; $i++) {
                echo "<td><input type='text' name='score{$i}[]' data-value='{$data["score" . $i]}' class='editable' value='{$data["score" . $i]}'></td>";
            }
            echo "</tr>";
        }
        // ... [Rest of your form]
    }
} catch (Exception $e) {
    // ... [Error handling]
}
?>

</body>
</html>
