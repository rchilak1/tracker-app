<?php
include 'db_connect.php';
$pdo->exec("
  UPDATE HABITS
  SET Status = 'Incomplete'
  WHERE HabitID IN (SELECT HabitID FROM RECURRING_HABITS)
");

