<!DOCTYPE html>
<html>
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9YXLSJ50NV"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9YXLSJ50NV');
    </script>
    <title>Not Approved</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.rawgit.com/balzss/luxbar/ae5835e2/build/luxbar.min.css">


    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        p {
            color: #666;
        }
        .contact-admin {
            margin-top: 20px;
            font-size: 14px;
            color: #007BFF;
        }
    </style>
</head>
<body>
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
    <div class="container">
        <h1>Account Not Approved</h1>
        <p>Your account needs to be approved by an administrator before you can access this area. Please contact your administrator for more information.</p>
        <p class="contact-admin">Contact your administrator. <!--<a href="mailto:admin@example.com">admin@example.com</a>--></p>
    </div>

<script>
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
