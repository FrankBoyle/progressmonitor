<?php
include('auth_session.php');
include('db.php');

if (isset($_GET['goal_id']) && isset($_GET['note_id'])) {
    $goalId = $_GET['goal_id'];
    $noteId = $_GET['note_id'];

    $stmt = $connection->prepare("SELECT report_image FROM Goal_notes WHERE goal_id = ? AND note_id = ?");
    $stmt->execute([$goalId, $noteId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['report_image']) {
        $imageData = $result['report_image'];

        // Determine the image type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        // Set the Content-Type header and send the image data
        header('Content-Type: ' . $mimeType);
        echo $imageData;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Image not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request, missing goal_id or note_id']);
}
?>




