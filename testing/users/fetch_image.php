<?php
include('auth_session.php');
include('db.php');

if (isset($_GET['goal_id'])) {
    $goalId = $_GET['goal_id'];

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

        // Set the Content-Type header
        header('Content-Type: ' . $mimeType);

        // Output the image data
        echo $imageData;
    } else {
        echo 'Image not found';
    }
} else {
    echo 'Invalid request';
}
?>


