<?php
session_start();

// Initialize the PDO connection to the database
try {
  $pdo = new PDO('mysql:host=localhost;dbname=tracker-app', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
  exit;
}

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$userId = $_SESSION['user_id'];

// Fetch the user's name from the database
$stmt = $pdo->prepare("SELECT Name FROM Users WHERE UserID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo 'User not found.';
  exit;
}

$userName = htmlspecialchars($user['Name']);

// Reset once per day (using a session flag)
if (!isset($_SESSION['did_reset']) || $_SESSION['did_reset'] !== date('Y-m-d')) {
  $pdo->exec("
    UPDATE HABITS
    SET Status = 'Incomplete'
    WHERE HabitID IN (SELECT HabitID FROM RECURRING_HABITS)
  ");
  $_SESSION['did_reset'] = date('Y-m-d');
}

// Handle form submissions (Add recurring task, daily task, or note)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add a recurring task
  if (!empty($_POST['recurring-task'])) {
    $task = trim($_POST['recurring-task']);
    $stmt = $pdo->prepare("
      INSERT INTO HABITS (UserID, HabitName)
      VALUES (?, ?)
    ");
    $stmt->execute([$userId, $task]);
    $newId = $pdo->lastInsertId();
    // Default to daily recurrence for now
    $pdo->prepare("
      INSERT INTO RECURRING_HABITS (HabitID, RecurrencePattern, DueDate)
      VALUES (?, 'Daily', CURRENT_DATE)
    ")->execute([$newId]);

    header("Location: main.php");
    exit;
  }

  // Add a daily task
  if (!empty($_POST['daily-task'])) {
    $task = trim($_POST['daily-task']);
    $stmt = $pdo->prepare("
      INSERT INTO HABITS (UserID, HabitName)
      VALUES (?, ?)
    ");
    $stmt->execute([$userId, $task]);
    $newId = $pdo->lastInsertId();
    $pdo->prepare("
      INSERT INTO DAILY_HABITS (HabitID, UserID)
      VALUES (?, ?)
    ")->execute([$newId, $userId]);

    header("Location: main.php");
    exit;
  }

  // Add a note
  if (!empty($_POST['user-note'])) {
    $note = trim($_POST['user-note']);
    $stmt = $pdo->prepare("
      INSERT INTO NOTES (UserID, Content)
      VALUES (?, ?)
    ");
    $stmt->execute([$userId, $note]);

    header("Location: main.php");
    exit;
  }
}

// Fetch recurring tasks
$recurringTasks = $pdo->query("
  SELECT H.HabitID, H.HabitName, H.Status
  FROM HABITS H
  JOIN RECURRING_HABITS R ON H.HabitID = R.HabitID
  WHERE H.UserID = $userId
")->fetchAll();

// Fetch daily tasks
$stmt = $pdo->prepare("
  SELECT H.HabitID, H.HabitName, H.Status
  FROM HABITS H
  JOIN DAILY_HABITS D ON H.HabitID = D.HabitID
  WHERE H.UserID = ?
    AND H.HabitID NOT IN (
      SELECT HabitID FROM RECURRING_HABITS
    )
");
$stmt->execute([$userId]);
$dailyTasks = $stmt->fetchAll();

// Fetch notes
$notes = $pdo->query("
  SELECT NoteID, Content
  FROM NOTES
  WHERE UserID = $userId
  ORDER BY EntryDate DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tracker App – Main</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    section { margin-bottom: 2rem; }
    h2 { margin-bottom: 0.5rem; }
    ul { list-style: none; padding: 0; }
    li { margin: 0.5rem 0; }
    form.inline { display: inline; }
    input[type="text"], textarea { width: 200px; }
    button { margin-left: 0.5rem; }
  </style>
</head>
<body>
  <h1>Tracker App – Main</h1>
  <p>Welcome, <?= $userName ?>!</p>

  <!-- Recurring Tasks -->
  <section>
    <h2>Recurring Tasks</h2>
    <form method="POST">
      <input type="text" name="recurring-task" placeholder="New recurring task" required>
      <button type="submit">Add</button>
    </form>
    <ul>
      <?php foreach ($recurringTasks as $t): ?>
      <li>
        <form class="inline" action="update_task.php" method="POST">
          <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
          <input type="hidden" name="status" value="0">
          <input
            type="checkbox" name="status" value="1"
            onchange="this.form.submit()"
            <?= $t['Status'] === 'Complete' ? 'checked' : '' ?>
          >
        </form>
        <?= htmlspecialchars($t['HabitName']) ?>

        <form class="inline" action="delete_task.php" method="POST">
          <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
          <button>Delete</button>
        </form>
      </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <!-- Daily Tasks -->
  <section>
    <h2>Daily Tasks</h2>
    <form method="POST">
      <input type="text" name="daily-task" placeholder="New daily task" required>
      <button type="submit">Add</button>
    </form>
    <ul>
      <?php foreach ($dailyTasks as $t): ?>
      <li>
        <form class="inline" action="update_task.php" method="POST">
          <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
          <input type="hidden" name="status" value="0">
          <input
            type="checkbox" name="status" value="1"
            onchange="this.form.submit()"
            <?= $t['Status'] === 'Complete' ? 'checked' : '' ?>
          >
        </form>
        <?= htmlspecialchars($t['HabitName']) ?>

        <form class="inline" action="delete_task.php" method="POST">
          <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
          <button>Delete</button>
        </form>
      </li>
      <?php endforeach; ?>
      <?php if (empty($dailyTasks)): ?>
        <li>No daily tasks yet.</li>
      <?php endif; ?>
    </ul>
  </section>

  <!-- Notes -->
  <section>
    <h2>Notes</h2>
    <form method="POST">
      <textarea name="user-note" rows="2" placeholder="New note" required></textarea>
      <button type="submit">Add</button>
    </form>
    <ul>
      <?php foreach ($notes as $n): ?>
      <li>
        <?= htmlspecialchars($n['Content']) ?>
        <form class="inline" action="delete_notes.php" method="POST">
          <input type="hidden" name="note_id" value="<?= $n['NoteID'] ?>">
          <button>Delete</button>
        </form>
      </li>
      <?php endforeach; ?>
      <?php if (empty($notes)): ?>
        <li>No notes yet.</li>
      <?php endif; ?>
    </ul>
  </section>

  <nav>
    <a href="streaks.php">Streaks</a> &middot;
    <a href="graphs.php">Graphs</a>
    <form action="logout.php" method="post" onsubmit="return confirm('Are you sure you want to log out?');">
      <button type="submit">Logout</button>
    </form>
  </nav>
</body>
</html>
