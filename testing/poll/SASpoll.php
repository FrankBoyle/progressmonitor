<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medal Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="itemsList"></div>

    <!-- Assume your items will be dynamically loaded here -->
    <form id="votingForm">
        <div id="itemsList"></div>
        <button class="vote-btn first" type="button" name="first">1st</button>
        <button class="vote-btn second" type="button" name="second">2nd</button>
        <button class="vote-btn third" type="button" name="third">3rd</button>
        <input type="submit" value="Submit Votes">
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
