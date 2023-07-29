<?php
require('db.php');
include("auth_session.php");
?>

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
        <script src="js/scripts.js"></script>
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
        <!-- Masthead-->
        <header class="masthead bg-primary text-white text-center">
            <div class="container d-flex align-items-center flex-column">
                <!-- Masthead Avatar Image-->
                <img class="masthead-avatar mb-5" src="assets/img/9721645.png" alt="..." />
                <!-- Masthead Heading-->
                <h1 class="masthead-heading text-uppercase mb-0">Hey, <?php echo $_SESSION['username']; ?>!</h1>
                <!-- Icon Divider-->
                <div class="divider-custom divider-light">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <!-- Masthead Subheading-->
                <p class="masthead-subheading font-weight-light mb-0">Data Collection & Analysis</p>
            </div>
        </header>
        <section class="page-section student" id="student">
            <div class="container">
                <!-- Students Section Heading-->
                <h2 class="page-section-heading text-center text-uppercase text-secondary mb-0">Students</h2>
                <!-- Icon Divider-->
                <div class="divider-custom">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>
                <h1>Customizable Table</h1>
    <div>
        <label for="columnName">New Column Name:</label>
        <input type="text" id="columnName" />
        <button onclick="addColumn()">Add Column</button>
    </div>
    <table id="editableTable">
        <thead>
            <tr id="headerRow">
                <th contenteditable="true">Name</th>
                <th contenteditable="true">Age</th>
                <th contenteditable="true">Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Table rows will be dynamically added here -->
        </tbody>
    </table>

    <button onclick="addRow()">Add Row</button>
    <button onclick="saveTableData()">Save Table</button>

    <script>
        // JavaScript code for handling table editing
        function addRow() {
            const tableBody = document.querySelector("#editableTable tbody");
            const newRow = document.createElement("tr");

            // Customize the number of columns and their content based on your requirements
            newRow.innerHTML = `
                <td contenteditable="true">John Doe</td>
                <td contenteditable="true">30</td>
                <td contenteditable="true">johndoe@example.com</td>
                <td><button onclick="deleteRow(this)">Delete</button></td>
            `;

            tableBody.appendChild(newRow);
        }

        function deleteRow(button) {
            const rowToDelete = button.closest("tr");
            rowToDelete.remove();
        }

        function addColumn() {
            const columnNameInput = document.getElementById("columnName");
            const newColumnName = columnNameInput.value.trim();
            if (newColumnName !== "") {
                const headerRow = document.getElementById("headerRow");
                const newHeaderCell = document.createElement("th");
                newHeaderCell.contentEditable = true;
                newHeaderCell.textContent = newColumnName;
                headerRow.appendChild(newHeaderCell);

                // Clear the input field after adding the column
                columnNameInput.value = "";
            }
        }

        function saveTableData() {
            const tableData = [];
            const rows = document.querySelectorAll("#editableTable tbody tr");

            rows.forEach(row => {
                const cells = row.querySelectorAll("td");
                const rowData = {
                    name: cells[0].textContent,
                    age: cells[1].textContent,
                    email: cells[2].textContent
                };
                tableData.push(rowData);
            });

            // Convert the table data to a JSON string
            const jsonData = JSON.stringify(tableData);

            // Send the JSON data to the server using an HTTP request (e.g., using fetch or XMLHttpRequest)
            // In this example, we will use fetch to send a POST request to the server

            fetch('save_table.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: jsonData
            })
            .then(response => response.text())
            .then(data => console.log(data))
            .catch(error => console.error('Error:', error));
        }

        // Load table data from the server when the page is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Send a request to the server to get the table data
            fetch('get_table.php')
            .then(response => response.json())
            .then(tableData => {
                const tableBody = document.querySelector("#editableTable tbody");

                tableData.forEach(rowData => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                        <td contenteditable="true">${rowData.name}</td>
                        <td contenteditable="true">${rowData.age}</td>
                        <td contenteditable="true">${rowData.email}</td>
                        <td><button onclick="deleteRow(this)">Delete</button></td>
                    `;
                    tableBody.appendChild(newRow);
                });
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
    </body>
</html>