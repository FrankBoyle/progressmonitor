<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Page Title</title>
</head>
<body>

    <!-- Add New Student Form -->
    <form method="post" action="">
        <label for="new_student_name">New Student Name:</label>
        <input type="text" id="new_student_name" name="new_student_name">
        <input type="submit" name="add_new_student" value="Add New Student">
    </form>

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
        // Database connection
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
        if (!isset($_SESSION['teacher_id'])) {
            throw new Exception("Teacher ID not set in session");
        }

        $teacherId = $_SESSION['teacher_id'];
        echo "Teacher ID from session: " . $teacherId . "<br>";

        // Add New Student
        if (isset($_POST['add_new_student'])) {
            $newStudentName = $_POST['new_student_name'];

            if (!empty($newStudentName)) {
                $stmt = $conn->prepare("INSERT INTO Students (name) VALUES (?)");
                $stmt->bind_param('s', $newStudentName);
                $stmt->execute();

                if ($stmt->error) {
                    throw new Exception("Error adding new student: " . $stmt->error);
                }

                $newStudentId = $conn->insert_id;

                $stmt = $conn->prepare("INSERT INTO Teacher_Student_Assignment (teacher_id, student_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $teacherId, $newStudentId);
                $stmt->execute();

                if ($stmt->error) {
                    throw new Exception("Error associating new student with teacher: " . $stmt->error);
                }

                echo "New student added successfully.<br>";
            }
        }

        // Existing Students
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
                echo "<a href='view_student_data.php?student_id=" . $student['student_id'] . "'>" . $student['name'] . "</a><br>";
            }
        }

    } catch (Exception $e) {
        logError($e->getMessage());
        echo "An error occurred. Please try again later.";
    }
    ?>

</body>
</html>


