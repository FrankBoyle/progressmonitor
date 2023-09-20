<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta charset="UTF-8">
    <title>Your Page Title</title>
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

</head>
<body>

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

        echo "PHP is working!";

        // Start session
        session_start();

        // Ensure teacher_id is in the session
        if (!isset($_SESSION['teacher_id'])) {
            throw new Exception("Teacher ID not set in session");
        }

        $teacherId = $_SESSION['teacher_id'];
        echo "Teacher ID from session: " . $teacherId;

        $stmt = $conn->prepare("SELECT s.* FROM Students s INNER JOIN Teacher_Student_Assignment tsa ON s.student_id = tsa.student_id WHERE tsa.teacher_id = ?");
        $stmt->bind_param('i', $teacherId);
        $stmt->execute();

        // Check for errors during student fetch
        if ($stmt->error) {
            throw new Exception("Error during student fetch: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);

        // Check if any students are retrieved
        if (empty($students)) {
            throw new Exception("No students found for this teacher.");
        }

        foreach ($students as $student) {
            echo "<a href='view_student_data.php?student_id=" . $student['student_id'] . "'>" . $student['name'] . "</a><br>";
        }

        if (isset($_GET['student_id'])) {
            $studentId = $_GET['student_id'];

            $stmt = $conn->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");
            $stmt->bind_param('i', $studentId);
            $stmt->execute();

            // Check for errors during performance data fetch
            if ($stmt->error) {
                throw new Exception("Error during performance data fetch: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $performanceData = $result->fetch_all(MYSQLI_ASSOC);

            echo "<form method='post' action=''>"; // The action can be the same page or another script
            echo "<table border='1'>";
            echo "<tr><th>Performance ID</th><th>Week Start Date</th>";
            for ($i = 1; $i <= 10; $i++) {
                echo "<th>Score" . $i . "</th>";
            }
            echo "</tr>";
            
            foreach ($performanceData as $data) {
                echo "<tr data-performance-id='" . $data['performance_id'] . "'>";
                echo "<td><input type='hidden' name='performance_id[]' value='{$data["performance_id"]}'>{$data["performance_id"]}</td>";
                echo "<td>{$data['week_start_date']}</td>"; // Assuming week_start_date is not editable
                for ($i = 1; $i <= 10; $i++) {
                    echo "<td data-value='{$data["score" . $i]}' class='editable'>{$data["score" . $i]}</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo "<input type='submit' name='update' value='Update'>";
            echo "</form>";
            
        }
    } catch (Exception $e) {
        // Log the error
        logError($e->getMessage());

        // Display a user-friendly error message
        echo "An error occurred. Please try again later.";
    }
    ?>
</body>
</html>
