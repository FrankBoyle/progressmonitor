<?php
    include('./users/login_backend.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bfactor | Registration Page</title>

  <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body class="">
  <div class="">

  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in</p>
    


      <form method="post" action="./users/login_backend.php" name="login">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="email" placeholder="E-mail">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" value="login" name="login" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>



      <!--
      <div class="social-auth-links text-center mb-3">
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
        /.social-auth-links -->

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
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->
</section>
  <!-- /.content-wrapper -->
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
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
