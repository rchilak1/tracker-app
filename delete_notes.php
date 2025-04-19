<?php
// delete_notes.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_id'])) {
    $noteID = (int)$_POST['note_id'];
    $stmt = $pdo->prepare("DELETE FROM NOTES WHERE NoteID = ?");
    $stmt->execute([$noteID]);
}

header("Location: main.php");
exit;

