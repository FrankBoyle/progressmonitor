<?php
include('db.php');
include('auth_session.php');

if (isset($_GET['student_id']) && isset($_GET['metadata_id']) && isset($_GET['iep_date'])) {
    $student_id = $_GET['student_id'];
    $metadata_id = $_GET['metadata_id'];
    $iep_date = $_GET['iep_date'];

    $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id_new = ? AND metadata_id = ? AND score_date >= ? ORDER BY score_date ASC");
    $stmt->execute([$student_id, $metadata_id, $iep_date]);
    $performanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($performanceData as $data) {
        echo "<tr data-performance-id='{$data['performance_id']}'>";
        echo "<td class='editable' data-field-name='score_date'>" . (isset($data['score_date']) ? date("m/d/Y", strtotime($data['score_date'])) : "") . "</td>";
        for ($i = 1; $i <= 10; $i++) {
            echo "<td class='editable' data-field-name='score{$i}'>" . (isset($data["score{$i}"]) ? $data["score{$i}"] : "") . "</td>";
        }
        echo "<td><button class='deleteRow btn btn-block btn-primary' data-performance-id='{$data['performance_id']}'>Delete</button></td>";
        echo "</tr>";
    }
} else {
    echo "Invalid data.";
}
?>
