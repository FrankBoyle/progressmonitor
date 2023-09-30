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
        // Fetch the SchoolID of the current teacher
        $schoolQuery = $conn->prepare("SELECT SchoolID FROM Teachers WHERE teacher_id = ?");
        $schoolQuery->bind_param('i', $teacherId);
        $schoolQuery->execute();
        $schoolResult = $schoolQuery->get_result();
        $teacherInfo = $schoolResult->fetch_assoc();
        $teacherSchoolID = $teacherInfo['SchoolID'];

        // Check if the student with the same name and SchoolID already exists
        $checkDuplication = $conn->prepare("SELECT student_id FROM Students WHERE name = ? AND SchoolID = ?");
        $checkDuplication->bind_param('si', $newStudentName, $teacherSchoolID);
        $checkDuplication->execute();
        $duplicateStudent = $checkDuplication->get_result()->fetch_assoc();

        if ($duplicateStudent) {
            echo "Student with the same name already exists.<br>";
        } else {
            // Insert the new student with the same SchoolID
            $stmt = $conn->prepare("INSERT INTO Students (name, SchoolID) VALUES (?, ?)");
            $stmt->bind_param('si', $newStudentName, $teacherSchoolID);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception("Error adding new student: " . $stmt->error);
            }

            echo "New student added successfully.<br>";
        }
    }
}

        // Fetch Existing Students for the Teacher based on the School
        $stmt = $conn->prepare("SELECT s.* FROM Students s INNER JOIN Teachers t ON s.SchoolID = t.SchoolID WHERE t.teacher_id = ?");
        $stmt->bind_param('i', $teacherId);
        $stmt->execute();

        if ($stmt->error) {
            throw new Exception("Error fetching students: " . $stmt->error);
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


