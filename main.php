<?php
session_start();

// Initialize PDO connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=tracker_app', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user name
$stmt = $pdo->prepare('SELECT Name FROM Users WHERE UserID = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo 'User not found.';
    exit;
}
$userName = htmlspecialchars($user['Name']);

// Reset recurring tasks status once per day
if (!isset($_SESSION['did_reset']) || $_SESSION['did_reset'] !== date('Y-m-d')) {
    $pdo->exec(
        "UPDATE HABITS
         SET Status = 'Incomplete'
         WHERE HabitID IN (SELECT HabitID FROM RECURRING_HABITS)"
    );
    $_SESSION['did_reset'] = date('Y-m-d');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['recurring-task'])) {
        $task = trim($_POST['recurring-task']);
        $pdo->prepare('INSERT INTO HABITS (UserID, HabitName) VALUES (?, ?)')
            ->execute([$userId, $task]);
        $newId = $pdo->lastInsertId();
        $pdo->prepare(
            'INSERT INTO RECURRING_HABITS (HabitID, RecurrencePattern, DueDate) VALUES (?, "Daily", CURRENT_DATE)'
        )->execute([$newId]);
        header('Location: main.php');
        exit;
    }
    if (!empty($_POST['daily-task'])) {
        $task = trim($_POST['daily-task']);
        $pdo->prepare('INSERT INTO HABITS (UserID, HabitName) VALUES (?, ?)')
            ->execute([$userId, $task]);
        $newId = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO DAILY_HABITS (HabitID, UserID) VALUES (?, ?)')
            ->execute([$newId, $userId]);
        header('Location: main.php');
        exit;
    }
    if (!empty($_POST['user-note'])) {
        $note = trim($_POST['user-note']);
        $pdo->prepare('INSERT INTO NOTES (UserID, Content) VALUES (?, ?)')
            ->execute([$userId, $note]);
        header('Location: main.php');
        exit;
    }
}

// Fetch recurring tasks
$recurringTasks = $pdo->prepare(
    'SELECT H.HabitID, H.HabitName, H.Status
     FROM HABITS H
     JOIN RECURRING_HABITS R ON H.HabitID = R.HabitID
     WHERE H.UserID = ?'
);
$recurringTasks->execute([$userId]);
$recurringTasks = $recurringTasks->fetchAll(PDO::FETCH_ASSOC);

// Fetch daily tasks
$dailyTasks = $pdo->prepare(
    'SELECT H.HabitID, H.HabitName, H.Status
     FROM HABITS H
     JOIN DAILY_HABITS D ON H.HabitID = D.HabitID
     WHERE H.UserID = ?
       AND H.HabitID NOT IN (SELECT HabitID FROM RECURRING_HABITS)'
);
$dailyTasks->execute([$userId]);
$dailyTasks = $dailyTasks->fetchAll(PDO::FETCH_ASSOC);

// Fetch notes
$notes = $pdo->prepare(
    'SELECT NoteID, Content
     FROM NOTES
     WHERE UserID = ?
     ORDER BY EntryDate DESC'
);
$notes->execute([$userId]);
$notes = $notes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tracker App – Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">Welcome, <?= $userName ?>!</h1>
      <nav>
        <a href="streaks.php" class="btn btn-outline-secondary me-2">Streaks</a>
        <a href="graphs.php" class="btn btn-outline-secondary me-2">Graphs</a>
        <form action="logout.php" method="post" class="d-inline" onsubmit="return confirm('Log out?');">
          <button type="submit" class="btn btn-danger">Logout</button>
        </form>
      </nav>
    </header>

    <div class="row">
      <!-- Recurring Tasks -->
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Recurring Tasks</h5>
            <form method="POST" class="mb-3">
              <div class="input-group">
                <input type="text" name="recurring-task" class="form-control" placeholder="New recurring task" required>
                <button class="btn btn-primary" type="submit">Add</button>
              </div>
            </form>
            <ul class="list-group list-group-flush">
              <?php if ($recurringTasks): foreach ($recurringTasks as $t): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <form action="update_task.php" method="POST" class="d-inline me-2">
                      <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
                      <input type="hidden" name="status" value="<?= $t['Status'] === 'Complete' ? '0' : '1' ?>">
                      <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $t['Status'] === 'Complete' ? 'checked' : '' ?>>
                    </form>
                    <?= htmlspecialchars($t['HabitName']) ?>
                  </div>
                  <form action="delete_task.php" method="POST" class="d-inline">
                    <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
                    <button class="btn btn-sm btn-outline-danger">&times;</button>
                  </form>
                </li>
              <?php endforeach; else: ?>
                <li class="list-group-item">No recurring tasks.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Daily Tasks -->
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Daily Tasks</h5>
            <form method="POST" class="mb-3">
              <div class="input-group">
                <input type="text" name="daily-task" class="form-control" placeholder="New daily task" required>
                <button class="btn btn-primary" type="submit">Add</button>
              </div>
            </form>
            <ul class="list-group list-group-flush">
              <?php if ($dailyTasks): foreach ($dailyTasks as $t): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <form action="update_task.php" method="POST" class="d-inline me-2">
                      <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
                      <input type="hidden" name="status" value="<?= $t['Status'] === 'Complete' ? '0' : '1' ?>">
                      <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $t['Status'] === 'Complete' ? 'checked' : '' ?>>
                    </form>
                    <?= htmlspecialchars($t['HabitName']) ?>
                  </div>
                  <form action="delete_task.php" method="POST" class="d-inline">
                    <input type="hidden" name="habit_id" value="<?= $t['HabitID'] ?>">
                    <button class="btn btn-sm btn-outline-danger">&times;</button>
                  </form>
                </li>
              <?php endforeach; else: ?>
                <li class="list-group-item">No daily tasks.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title">Notes</h5>
            <form method="POST" class="mb-3">
              <textarea name="user-note" class="form-control" rows="3" placeholder="New note" required></textarea>
              <button class="btn btn-primary mt-2" type="submit">Add</button>
            </form>
            <ul class="list-group list-group-flush">
              <?php if ($notes): foreach ($notes as $n): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?= htmlspecialchars($n['Content']) ?>
                  <form action="delete_notes.php" method="POST" class="d-inline">
                    <input type="hidden" name="note_id" value="<?= $n['NoteID'] ?>">
                    <button class="btn btn-sm btn-outline-danger">&times;</button>
                  </form>
                </li>
              <?php endforeach; else: ?>
                <li class="list-group-item">No notes yet.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
