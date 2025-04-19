<?php
include 'db_connect.php';
$userId = 1; // placeholder

// Example: fetch last 7 days’ streak counts from STREAK_HISTORY
$stmt = $pdo->prepare("
  SELECT StartDate AS label, StreakCount AS value
  FROM STREAK_HISTORY
  WHERE HabitID IN (
    SELECT HabitID FROM RECURRING_HABITS
    WHERE HabitID IN (
      SELECT HabitID FROM HABITS WHERE UserID = ?
    )
  )
  ORDER BY StartDate ASC
");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

echo json_encode([
  'labels' => array_column($rows,'label'),
  'values' => array_column($rows,'value'),
]);

