<?php
// delete_task.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['habit_id'])) {
    $habitID = (int)$_POST['habit_id'];
    // Remove from HABITS, and let ON DELETE CASCADE clear out the child table
    $stmt = $pdo->prepare("DELETE FROM HABITS WHERE HabitID = ?");
    $stmt->execute([$habitID]);
}

header("Location: main.php");
exit;

