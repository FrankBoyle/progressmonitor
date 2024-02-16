<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medal Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="medals">
        <img src="goldmedal.png" id="gold" draggable="true">
        <img src="silvermedal.png" id="silver" draggable="true">
        <img src="bronzemedal.png" id="bronze" draggable="true">
    </div>
    <div id="itemsList"></div>
    <form id="votingForm">
    <div class="item" data-id="1">
        Issue 1
        <input type="radio" name="first" value="1"> 1st
        <input type="radio" name="second" value="1"> 2nd
        <input type="radio" name="third" value="1"> 3rd
    </div>
    <div class="item" data-id="2">
        Issue 2
        <input type="radio" name="first" value="2"> 1st
        <input type="radio" name="second" value="2"> 2nd
        <input type="radio" name="third" value="2"> 3rd
    </div>
    <div class="item" data-id="3">
        Issue 3
        <input type="radio" name="first" value="3"> 1st
        <input type="radio" name="second" value="3"> 2nd
        <input type="radio" name="third" value="3"> 3rd
    </div>
    <!-- Add more items as needed -->
    <input type="submit" value="Submit Vote">
</form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
