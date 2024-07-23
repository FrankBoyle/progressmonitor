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
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
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
                            <a href="Groups_Walkthrough.jpg" class="dropdown-item" data-image="Groups_Walkthrough.jpg">1 - Create a group with +.</a>
                            <a href="Group_Select.jpg" class="dropdown-item sub-item" data-image="Group_Select.jpg">a - Select a group.</a>
                            <a href="Students_Walkthrough.jpg" class="dropdown-item" data-image="Students_Walkthrough.jpg">2 - Add students to school and/or groups with +.</a>
                            <a href="Students_Select.jpg" class="dropdown-item sub-item" data-image="Students_Select.jpg">a - Select a student.</a>
                            <a href="Goal_Create_Walkthrough.jpg" class="dropdown-item" data-image="Goal_Create_Walkthrough.jpg">3 - Add Goals with +.</a>
                            <a href="Rubric_Select.jpg" class="dropdown-item sub-item" data-image="Rubric_Select.jpg">a - Select a rubric.</a>
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
                <h1 class="login-box-msg">Register</h1>
                <form method="post" action="users/register_backend.php" name="registration">
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
$(document).ready(function() {
    $('form[name="registration"]').on('submit', function(e) {
        e.preventDefault(); // This line prevents the form's default submission method

        var formData = $(this).serialize(); // This captures all form data

        // Optional: Append the 'register' button value manually if not included
        formData += '&register=Register';

        $.ajax({
            type: 'POST',
            url: 'users/register_backend.php', // Endpoint where the form data should be submitted
            data: formData,
            dataType: 'json', // Specify that you expect a JSON response
            success: function(response) {
                // SweetAlert to handle success
                if (response.success) {
                    swal({
                        title: "Registration Successful!",
                        text: "You have been registered successfully.",
                        icon: "success",
                        button: "Ok",
                    }).then((value) => {
                        window.location.href = 'login.php'; // Redirect on confirmation
                    });
                } else {
                    swal("Error", response.message || "There was a problem with your registration. Please try again.", "error");
                }
            },
            error: function(xhr, status, error) {
                swal("Error", "Failed to process your request. Please try again.", "error");
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



</script>

</body>
</html>
