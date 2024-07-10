<?php
include('auth_session.php');
include('db.php');

header('Content-Type: application/json'); // Default to JSON

if (isset($_GET['goal_id'])) {
    $goalId = $_GET['goal_id'];
    error_log("Fetching image for goal_id: " . $goalId);

    $stmt = $connection->prepare("SELECT report_image FROM Goal_notes WHERE goal_id = ?");
    $stmt->execute([$goalId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['report_image']) {
        $imageData = $result['report_image'];

        // Determine the image type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        // Log the mime type for debugging
        error_log("Image mime type: " . $mimeType);

        // Set the Content-Type header and send the image data
        header('Content-Type: ' . $mimeType);
        echo $imageData;
    } else {
        error_log("Image not found for goal_id: " . $goalId);
        echo json_encode(['error' => 'Image not found']);
    }
} else {
    error_log("Invalid request, missing goal_id");
    echo json_encode(['error' => 'Invalid request, missing goal_id']);
}
?>


