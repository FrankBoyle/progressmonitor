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
</body>
</html>
