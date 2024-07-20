<?php
session_start();
include('auth_session.php');
include('db.php');

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['school_id'])) {
        throw new Exception('Missing required session parameter: school_id.');
    }

    $school_id = $_SESSION['school_id'];
    error_log("Fetching templates for school_id: " . $school_id); // Log school_id

    $stmt = $connection->prepare("SELECT metadata_id, category_name FROM Metadata WHERE school_id = :school_id AND metadata_template = 1");
    $stmt->bindParam(':school_id', $school_id, PDO::PARAM_INT);
    $stmt->execute();
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($templates === false) {
        throw new Exception("Error fetching metadata templates.");
    }

    if (empty($templates)) {
        error_log("No templates found for school_id: " . $school_id); // Log no templates found
    }

    error_log("Templates: " . json_encode($templates)); // Log data
    echo json_encode($templates);
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode(["error" => "Error fetching metadata templates: " . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>

