<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Progress Monitor</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">Progress Monitor</a>
                <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="#student">Students</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="#about">About</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="#contact">Contact</a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>
      <?php
        require('db.php');
        session_start();
        // When form submitted, check and create user session.
        if (isset($_POST['username'])) {
          $username = stripslashes($_REQUEST['username']);    // removes backslashes
          $username = mysqli_real_escape_string($con, $username);
          $password = stripslashes($_REQUEST['password']);
          $password = mysqli_real_escape_string($con, $password);
          // Check user is exist in the database
          $query    = "SELECT * FROM `accounts` WHERE username='$username'
                     AND password='" . md5($password) . "'";
          $result = mysqli_query($con, $query) or die(mysql_error());
          $rows = mysqli_num_rows($result);
          if ($rows == 1) {
            $_SESSION['username'] = $username;
            // Redirect to user dashboard page
            header("Location: index.php");
          } else {
            echo "<div class='form'>
                  <h3>Incorrect Username/password.</h3><br/>
                  <p class='link'>Click here to <a href='login.php'>Login</a> again.</p>
                  </div>";
           }
         } else {
      ?>
      <header class="masthead bg-primary text-white text-center">
        <form class="form" method="post" name="login">
          <h1 class="login-title">Login</h1>
          <input type="text" class="login-input" name="username" placeholder="Username" autofocus="true"/>
          <input type="password" class="login-input" name="password" placeholder="Password"/>
          <input type="submit" value="Login" name="submit" class="login-button"/>
          <p class="link"><a href="registration.php">New Registration</a></p>
        </form>
      </header>
<?php
    }
?>
</body>
</html>