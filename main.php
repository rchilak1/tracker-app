<?php
// main.php
include 'db_connect.php';
$userId = 1;  // TODO: swap out for session/user logic later

// 1) Handle form submissions
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

// 2) Fetch recurring tasks (with their status)
$recurringTasks = $pdo->query("
    SELECT H.HabitID, H.HabitName, H.Status
    FROM HABITS H
    JOIN RECURRING_HABITS R ON H.HabitID = R.HabitID
    WHERE H.UserID = $userId
")->fetchAll();

// 3) Fetch daily tasks, excluding any that are in RECURRING_HABITS
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


// 4) Fetch notes
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
  <title>Routine App – Main</title>
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
  <h1>Routine App – Main</h1>

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
        <!-- toggle complete/incomplete -->
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

        <!-- delete -->
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
      <!-- example if you want to show a “no tasks” fallback -->
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
  </nav>
</body>
</html>
