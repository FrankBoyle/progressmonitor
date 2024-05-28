<?php
include('db.php');
include('auth_session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iep_date = isset($_POST['iep_date']) ? $_POST['iep_date'] : null;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;

    if ($iep_date && $student_id) {
        $stmt = $connection->prepare("UPDATE Students SET IEP_Date = ? WHERE student_id = ?");
        $stmt->execute([$iep_date, $student_id]);

        $stmt = $connection->prepare("SELECT * FROM Performance WHERE student_id = ? AND score_date >= ? ORDER BY score_date DESC LIMIT 41");
        $stmt->execute([$student_id, $iep_date]);
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
} else {
    echo "Invalid request method.";
}
?>
