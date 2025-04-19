<?php
// update_task.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['habit_id'])
    && isset($_POST['status']))
{
    $habitId = (int)$_POST['habit_id'];
    // status '1' means checked → Complete; anything else → Incomplete
    $newStatus = $_POST['status'] === '1' ? 'Complete' : 'Incomplete';

    $stmt = $pdo->prepare("UPDATE HABITS SET Status = ? WHERE HabitID = ?");
    $stmt->execute([$newStatus, $habitId]);
}

header("Location: main.php");
exit;

