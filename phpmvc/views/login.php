<?php
session_start();
include('../controllers/UserController.php');
$db = include( '../dbconnection.php');

$userController = new UserController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->login();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/Login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
  <div class="login">
    <div class="login-compo">
      <div class="login-box">
        <div class="login-img">
          <img src="../assets/img-1.jpg" alt="no-image">
        </div>
        <div class="login-form">
          <h2>Login</h2>
          <hr>
          <form action="" method="post">
            <label for="username">User Name</label>
            <input type="text" name="username" class="form-control" required>
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
            <br>
            <button type="submit" class="btn btn-primary">Login</button>
          </form>
          <p class="message text-center mt-3">
            No account? <a href="signup.php">Signup</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
