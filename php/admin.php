<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
  <title>Library Management System</title>
</head>
<body>

  <?php 
  include 'announcement-bar.php';
  include 'navigation-bar.php';
  ?>
<div class="login-content">
  <div class="main-content text-center login-form">
    <h2>Login as Administrator</h2>
    <form style="max-width: 400px; margin: 0 auto;" action="admin-login.php" method="post">
      <div class="mb-3">
        <label for="username" class="form-label visually-hidden">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" class="form-control" id="username" placeholder="Enter your username" name="username">
        </div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label visually-hidden">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" class="form-control" id="password" placeholder="Enter your password" name="password">
        </div>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>
    <?php
        if (isset($_SESSION['error_message'])) {
                                echo "<p class='error-message'>".$_SESSION['error_message']."</p>";
                                unset($_SESSION['error_message']);
        }
    ?>
    <br />
  </div>
</div>

        <?php
        include 'footer.php';
        ?>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
  <script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  </script>
</body>
</html>
