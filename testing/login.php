<?php
    include('./users/login_backend.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9YXLSJ50NV"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9YXLSJ50NV');
    </script>
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
    </style>
</head>
<body>
    <div class="dashboard">

       <header class="dashboard-header">
          <div class="logo">
            <img src="IEPreport_logo.jpg" alt="Logo">
          </div>

          <div class="header-icons">
            <a href="students.php" class="nav-link">
              <i class="nav-icon"></i>
              <p>Home</p>
            </a>             
            
            <!--<span>Icon 2</span>-->

            <a href="./users/logout.php" class="nav-link">
              <i class="nav-icon"></i>
              <p>Sign Out</p>
            </a> 

          </div>
        </header>

        <div class="center-content">
            <div class="grid-container">
                <div class="grid-item login-box">
                        <h1 class="login-box-msg">Sign in</h1>
                        <form method="post" action="users/login_backend.php" name="login">
                            <div style="position: relative;">
                                <input type="text" class="form-control" name="email" placeholder="E-mail" required>
                                <span class="fas fa-envelope"></span>
                            </div>
                            <div style="position: relative;">
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                                <span class="fas fa-lock"></span>
                            </div>
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember Me</label>
                            </div>
                            <button type="submit" value="login" name="login" class="btn btn-primary">Sign In</button>
                        </form>

                        <div class="forgot-password">
                            <p class="mb-1">
                                <?php
                                    if (isset($_GET['reset']) && $_GET['reset'] == 1) {
                                        echo '<p class="info">If this email exists in our system, a reset link has been sent. Please check your inbox (and spam folder).</p>';
                                    }
                                ?>
                                <form action="forgot_password.php" method="post">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control" name="email" required>
                                    <input type="submit" class="btn btn-primary" name="forgot_password" value="Request Password Reset">
                                </form>
                            </p>
                        </div>

                        <p class="mb-0">
                            <a href="register.php" class="text-center">Register a new membership</a>
                        </p>
                    </div>

                    <div class="grid-item">
                        <p>"I use it for all my own progress reporting for new IEPs, IEP revisions, and quarterly reporting." - Fran Boyle</p>
                    </div>

                    <div class="grid-item">
                        <h3>Sample Reports</h3>
                        <img src="sample_report1.png" alt="Sample Report 1">
                    </div>

                    <div class="grid-item about-us">
                        <p>
                            <strong>IEPreport.com</strong> is a pioneering platform dedicated to empowering special education teachers through efficient and effective progress monitoring tools. Founded by educators for educators, our mission is to streamline the progress monitoring process, making it more intuitive and impactful for those in the field of special education.
                        </p>
                        <p>
                            Our platform is built on the belief that the best tools come from those who understand the unique challenges of education firsthand. At IEPreport.com, we harness the power of collaboration and innovation to create solutions that enhance the teaching and learning experience.
                        </p>
                        <p>
                            Administrators will find IEPreport.com invaluable for gaining insights into classroom progress, ensuring that teachers are supported and that students' needs are being met. Our tools provide clear and comprehensive data that aids in informed decision-making without adding extra pressure on educators.
                        </p>
                        <p>
                            While our ultimate goal is to offer this invaluable resource to a wider audience, we remain focused on supporting and uplifting educators, ensuring they have the resources they need to succeed. Join us on our journey to transform special education progress monitoring, and experience the difference that comes from a tool designed by those who know the field best.
                        </p>
                    </div>
                    
                    <div class="grid-item">
                        <p>"It makes progress monitoring so much easier. It gives me the graphs and statistics I need to help make decisions for my IEP Goals." - Joe Dattilo</p>
                    </div>

                    <div class="grid-item">
                        <img src="sample_report2.png" alt="Sample Report 2">
                    </div>
                
                </div>
            </div>

</body>
</html>