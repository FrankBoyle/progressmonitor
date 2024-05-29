<?php
    include('./users/login_backend.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
    </style>
</head>

<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="logo">Logo</div>
            <div class="header-icons">
                <span>Icon 1</span>
                <span>Icon 2</span>
                <span>Icon 3</span>
            </div>
        </header>

        
        <main class="content">
          <section class="box">
            <h1 class="login-box-msg">Sign in</h1>
              <form method="post" action="./users/login_backend.php" name="login">
                <input type="text" class="form-control" name="email" placeholder="E-mail">
                <input type="password" class="form-control" name="password" placeholder="Password">
                <span class="fas fa-lock"></span>
                <input type="checkbox" id="remember">
                <label for="remember">Remember Me</label>
                <button type="submit" value="login" name="login" class="btn btn-primary btn-block">Sign In</button>
              </form>
              <p class="mb-1">
      <?php
    if (isset($_GET['reset']) && $_GET['reset'] == 1) {
        echo '<p class="info">If this email exists in our system, a reset link has been sent. Please check your inbox (and spam folder).</p>';
    }
    ?>
      <form action="forgot_password.php" method="post">
    <label for="email">Email:</label>
    <input type="email" name="email" required>
    <input type="submit" name="forgot_password" value="Request Password Reset">
</form>
      </p>
      <p class="mb-0">
        <a href="register.php" class="text-center">Register a new membership</a>
      </p>
          </section>
        </main>
    </div>


      <!--
      <div class="social-auth-links text-center mb-3">
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
        /.social-auth-links -->



  <footer class="main-footer">
    <strong>Copyright &copy; 2023 <a href="https://bfactor.org">Bfactor.org</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>




<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>

</body>
</html>
