<?php
session_start();
include 'db_connect.php';

$login_error   = '';
$add_errors    = [];
$add_success   = '';
$action        = $_POST['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT UserID, Username, Password, Status FROM Users WHERE Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['Password']) {
            if ($user['Status'] !== 'Active') {
                $login_error = 'Account is not active.';
            } else {
                $_SESSION['user_id'] = $user['UserID'];
                header('Location: main.php');
                exit;
            }
        } else {
            $login_error = 'Invalid username or password.';
        }

    } elseif ($action === 'add_user') {
        $new_username = trim($_POST['new_username'] ?? '');
        $new_name     = trim($_POST['new_name']     ?? '');
        $new_email    = trim($_POST['new_email']    ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if (!$new_username || !$new_name || !$new_password) {
            $add_errors[] = 'Username, Name & Password are required.';
        }

        if (empty($add_errors)) {
            $sql = "INSERT INTO Users (Username, Password, Name, Email, Status) VALUES (?, ?, ?, ?, 'Active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_username, $new_password, $new_name, $new_email]);
            $add_success = "User '{$new_username}' added successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login / Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4">Login</h1>
    <?php if ($login_error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="post" class="mb-5">
      <input type="hidden" name="action" value="login">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <h1 class="mb-4">Add New User</h1>
    <?php if ($add_success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($add_success) ?></div>
    <?php endif; ?>
    <?php if ($add_errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach($add_errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="post" class="mb-5">
      <input type="hidden" name="action" value="add_user">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="new_username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="new_name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="new_email" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Add User</button>
    </form>

    <h2>All Users (Debug)</h2>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <?php
            $stmt = $pdo->query("DESCRIBE Users");
            while ($col = $stmt->fetch()) {
              echo "<th>{$col['Field']}</th>";
            }
            ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $pdo->query("SELECT * FROM Users");
          while ($row = $stmt->fetch()) {
            echo "<tr>";
            foreach ($row as $val) {
              echo "<td>" . htmlspecialchars($val) . "</td>";
            }
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
