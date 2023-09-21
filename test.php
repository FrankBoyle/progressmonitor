<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Page Title</title>
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

    try {
        $servername = "localhost";
        $username = "AndersonSchool";
        $password = "SpecialEd69$";
        $dbname = "bFactor-test";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        } else {
            echo "Connected successfully to the database<br>";
        }

        // Start session
        session_start();

        // Ensure teacher_id is in the session
        if (!isset($_SESSION['teacher_id'])) {
            throw new Exception("Teacher ID not set in session");
        }

        $teacherId = $_SESSION['teacher_id'];
        echo "Teacher ID from session: " . $teacherId . "<br>";

        $stmt = $conn->prepare("SELECT s.* FROM Students s INNER JOIN Teacher_Student_Assignment tsa ON s.student_id = tsa.student_id WHERE tsa.teacher_id = ?");
        $stmt->bind_param('i', $teacherId);
        $stmt->execute();

        if ($stmt->error) {
            throw new Exception("Error during student fetch: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($students)) {
            echo "No students found for this teacher.";
        } else {
            echo "<h2>Students:</h2>";
            foreach ($students as $student) {
                echo $student['name'] . "<br>";
            }
        }

    } catch (Exception $e) {
        logError($e->getMessage());
        echo "An error occurred. Please try again later.";
    }
    ?>

</body>
</html>

