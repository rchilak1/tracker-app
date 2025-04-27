<?php
include 'db_connect.php';
$userId = 1; // TODO: use session in production

// Query to count completed vs missed progress
$progressStmt = $pdo->prepare("
  SELECT Progress.Status, COUNT(*) AS Total
  FROM Progress
  JOIN Habits ON Habits.HabitID = Progress.HabitID
  WHERE Habits.UserID = ?
  GROUP BY Progress.Status
");

$progressStmt->execute([$userId]);
$progressData = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize values for Completed and Missed
$completed = 0;
$missed = 0;

// Process the result
foreach ($progressData as $row) {
    if ($row['Status'] == 'Completed') {
        $completed = $row['Total'];
    } elseif ($row['Status'] == 'Missed') {
        $missed = $row['Total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Routine App – Graphs</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Progress Tracker</h1>

  <section id="progress-overview">
    <h2>Habit Completion Overview</h2>
    <ul>
      <li>Completed: <?= (int)$completed ?> days</li>
      <li>Missed: <?= (int)$missed ?> days</li>
    </ul>
  </section>

  <section id="progress-graph">
    <h2>Progress Chart</h2>
    <canvas id="progressChart"></canvas>
  </section>

  <nav>
    <a href="main.php">← Main</a> |
    <a href="streaks.php">Streaks →</a>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const progressData = {
      labels: ['Completed', 'Missed'],
      datasets: [{
        label: 'Progress Summary',
        data: [
          <?= $completed ?>,
          <?= $missed ?>
        ],
        backgroundColor: ['#4CAF50', '#F44336'], // Green for Completed, Red for Missed
        hoverOffset: 4, // Small hover effect for user interaction
      }]
    };

    const config = {
      type: 'doughnut',
      data: progressData,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom',
          },
          tooltip: {
            callbacks: {
              label: function(tooltipItem) {
                return tooltipItem.label + ': ' + tooltipItem.raw + ' days'; // Display total days on hover
              }
            }
          }
        }
      },
    };

    new Chart(document.getElementById('progressChart'), config);
  </script>
</body>
</html>
