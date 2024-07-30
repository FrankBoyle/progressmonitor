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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="IEPreport_logo.jpg" />
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.rawgit.com/balzss/luxbar/ae5835e2/build/luxbar.min.css">
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header luxbar-fixed" id="luxbar">
            <input type="checkbox" class="luxbar-checkbox" id="luxbar-checkbox"/>

            <div class="luxbar-menu luxbar-menu-right luxbar-menu-material-indigo">
                <ul class="luxbar-navigation">

                    <li class="luxbar-header">
                        <div class="logo">
                            <img src="IEPreport_logo.jpg" alt="Logo">
                        </div>
                        <label class="luxbar-hamburger luxbar-hamburger-doublespin" id="luxbar-hamburger" for="luxbar-checkbox"> <span></span> </label>
                    </li>

                    <li class="luxbar-item dropdown">
                        <a href="#" class="nav-link" id="helpDropdown" aria-haspopup="true" aria-expanded="false"><span class="question-mark">?</span></a>
                        <div class="dropdown-menu" aria-labelledby="helpDropdown">
                            <a href="Register_How_To.jpg" class="dropdown-item" data-image="Register_How_To.jpg">You have to register an account.</a>
                            <a href="Reset_Password_Walkthrough.jpg" class="dropdown-item" data-image="Reset_Password_Walkthrough.jpg">Reset your password.</a>
                        </div>
                    </li>

                    <li class="luxbar-item"><a href="mailto:dan@iepreport.com">Support</a></li>
                    <li class="luxbar-item"><a href="students.php">Home</a></li>
                    <li class="luxbar-item"><a href="./users/logout.php">Logout</a></li>

                </ul>
            </div>
        </header>

        <div class="center-content">
            <div class="login-box">
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
    $(document).ready(function() {
        $('form[name="login"]').on('submit', function(e) {
            e.preventDefault();  // Prevent the default form submission

            var formData = $(this).serialize(); 
            formData += '&login=login'; // Append the 'login' button's value manually

            console.log('Form submitted:', formData);  // Log form data to console for debugging

            $.ajax({
                type: 'POST',
                url: 'users/login_backend.php',
                data: formData,
                dataType: 'json',  // Expect JSON response
                success: function(response) {
                    console.log('AJAX response:', response);  // Log response to console for debugging
                    if (response.success) {
                        // Google Analytics event for successful login
                        gtag('event', 'login', {
                            'event_category': 'Authentication',
                            'event_label': 'Success',
                            'value': 1
                        });

                        // Redirect or handle login success
                        window.location.href = 'students.php';
                    } else {
                        // Google Analytics event for failed login
                        gtag('event', 'login', {
                            'event_category': 'Authentication',
                            'event_label': 'Failure',
                            'value': 0
                        });

                        // Show an error message
                        alert(response.message || 'Login failed. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    // Google Analytics event for technical errors during login
                    gtag('event', 'login', {
                        'event_category': 'Authentication',
                        'event_label': 'Error',
                        'value': 0
                    });

                    // Log the error to console
                    console.error('AJAX Error:', status, error);

                    // Notify the user of a technical error
                    alert('There was a technical error. Please try again later.');
                }
            });
        });
    });
    
document.querySelectorAll('.dropdown-item').forEach(item => {
    let timer;
    item.addEventListener('mouseenter', function(event) {
        const imageUrl = this.getAttribute('data-image');
        timer = setTimeout(() => {
            const preview = document.createElement('img');
            preview.src = imageUrl;
            preview.className = 'image-preview';
            document.body.appendChild(preview);
            preview.style.display = 'block';
            preview.style.bottom = '20px'; // 20px from the bottom
            preview.style.left = '20px'; // 20px from the left
        }, 300); // Delay of 300 milliseconds
    });

    item.addEventListener('mouseleave', function() {
        clearTimeout(timer);
        const preview = document.querySelector('.image-preview');
        if (preview) {
            preview.remove();
        }
    });

    // Prevent the default hover action if the user is clicking
    item.addEventListener('click', function(event) {
        event.preventDefault(); // This stops the default navigation when clicking
        window.open(this.href, '_blank'); // Manually open the link in a new tab
    });
});

</script>

</body>
</html>
