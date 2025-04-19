<?php
include 'db_connect.php';
$userId = 1; // TODO: replace with your session‑based user ID

// 1) Current streak: for each habit, grab the latest StreakCount
$currentStmt = $pdo->prepare("
  SELECT H.HabitName, SH.StreakCount
  FROM STREAK_HISTORY SH
  JOIN HABITS H ON H.HabitID = SH.HabitID
  WHERE H.UserID = ?
    AND SH.StartDate = (
      SELECT MAX(StartDate)
      FROM STREAK_HISTORY
      WHERE HabitID = SH.HabitID
    )
");
$currentStmt->execute([$userId]);
$currentStreaks = $currentStmt->fetchAll();

// 2) Longest streak per habit
$historyStmt = $pdo->prepare("
  SELECT H.HabitName, MAX(SH.StreakCount) AS Longest
  FROM STREAK_HISTORY SH
  JOIN HABITS H ON H.HabitID = SH.HabitID
  WHERE H.UserID = ?
  GROUP BY SH.HabitID
");
$historyStmt->execute([$userId]);
$streakHistory = $historyStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Routine App – Streaks</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Streaks Tracker</h1>

  <section id="current-streaks">
    <h2>Current Streaks</h2>
    <ul>
      <?php if (count($currentStreaks)): ?>
        <?php foreach ($currentStreaks as $row): ?>
          <li>
            <?= htmlspecialchars($row['HabitName']) ?> – 
            <?= (int)$row['StreakCount'] ?> days
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li>No streak data yet.</li>
      <?php endif; ?>
    </ul>
  </section>

  <section id="streak-history">
    <h2>Longest Streaks</h2>
    <ul>
      <?php if (count($streakHistory)): ?>
        <?php foreach ($streakHistory as $row): ?>
          <li>
            <?= htmlspecialchars($row['HabitName']) ?> – 
            Longest: <?= (int)$row['Longest'] ?> days
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li>No history yet.</li>
      <?php endif; ?>
    </ul>
  </section>

  <nav>
    <a href="main.php">← Main</a> |
    <a href="graphs.php">Graphs →</a>
  </nav>
</body>
</html>

