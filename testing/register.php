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
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
                <a href="./users/logout.php" class="nav-link">
                    <i class="nav-icon"></i>
                    <p>Sign Out</p>
                </a>
            </div>
        </header>

        <div class="center-content">
            <div class="login-box">
                <h1 class="login-box-msg">Register</h1>
                <form method="post" action="./users/register_backend.php" name="registration">
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="fname" id="fname" placeholder="First Name" autocomplete="given-name" required>
                        <span class="fas fa-envelope"></span>
                    </div>
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="lname" id="lname" placeholder="Last Name" autocomplete="family-name" required>
                        <span class="fas fa-user"></span>
                    </div>
                    
                    <!-- Hidden decoy field to prevent autofill -->
                    <input type="text" name="decoy" id="decoy" style="display:none;" autocomplete="off">
                    
                    <div style="position: relative;">
                        <input type="text" class="form-control" name="school_uuid" id="school_uuid" placeholder="blank if registering as an individual or School ID" autocomplete="off" data-gtm-form-interact-field-id="0">
                        <span class="fas fa-school"></span>
                    </div>
                    <div style="position: relative;" id="new_school_container" style="display: none;">
                    <small>Please take care while naming your school. You will need to email us to change it later.</small>
                        <input type="text" class="form-control" name="school_name" id="school_name" placeholder="New School Name" autocomplete="organization">
                        <span class="fas fa-school"></span>

                    </div>
                    <div style="position: relative;">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" autocomplete="email" required>
                        <span class="fas fa-envelope"></span>
                    </div>
                    <div style="position: relative;">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="new-password" required>
                        <span class="fas fa-lock"></span>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
                                <label for="agreeTerms">I agree to the <a href="#">terms</a></label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" name="register" id="register" value="Register" class="btn btn-primary btn-block">Register</button>
                        </div>
                    </div>
                    <a href="login.php" class="text-center">I already have a membership</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('form[name="registration"]').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            $.ajax({
                type: "POST",
                url: "./users/register_backend.php",
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        alert("Registration successful!");
                        window.location.href = "login.php"; // Redirect to login page or any other page
                    } else {
                        alert("Registration failed: " + data.message); // Show error message from server
                    }
                },
                error: function() {
                    alert("There was an error processing your registration.");
                }
            });
        });
    });

    document.getElementById('school_uuid').addEventListener('blur', function() {
            if (this.value.trim() === '') {
                document.getElementById('new_school_container').style.display = 'block';
            } else {
                document.getElementById('new_school_container').style.display = 'none';
            }
        });
    </script>

    <script>

    </script>
</body>
</html>
