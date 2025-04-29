<?php
session_start();

include 'db_connect.php'; 

$error = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT UserID, Username, Password, Status FROM Users WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['Password']) {
        // Check if the account status is 'Active'
        if ($user['Status'] !== 'Active') {
            $error = 'Account is not active.';
        } else {
            // Set session to userid and redirect to main.php
            $_SESSION['user_id'] = $user['UserID'];
            header('Location: main.php');
            exit;
        }
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
</head>
<body>
  <h1>Login</h1>

  <?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
  </form>

  <!-- Debugging: Display all users -->
  <h2>Debugging: All Users</h2>
  <table border="1">
    <tr>
      <?php
      $stmt = $pdo->query("DESCRIBE Users");
      while ($column = $stmt->fetch()) {
          echo "<th>{$column['Field']}</th>";
      }
      ?>
    </tr>
    <?php
    // Fetch all users for debugging purposes
    $stmt = $pdo->query("SELECT * FROM Users");
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    ?>
  </table>
</body>
</html>
