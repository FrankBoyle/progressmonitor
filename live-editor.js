/* -------------------------------- */
/* -------------------------------- */
/* GET DATA FROM API AND DISPLAY IT */
/* -------------------------------- */
/* -------------------------------- */
fetch("api/", { method: "GET" })
    // Get Data From API
    .then(response => response.json())
    .then(result => {
        // Display Data Inside The Table
        result.forEach(data => {
            document.querySelector(".table-group-divider").innerHTML += `
                <tr>
                    <th class="id">` + data.id + `</th>
                    <td data-cell="Date" data-id="` + data.id + `">` + data.Date + `</td>
                    <td data-cell="Score" data-id="` + data.id + `">` + data.Score + `</td>
                    <td data-cell="Baseline" data-id="` + data.id + `">` + data.Baseline + `</td>
                </tr>
            `;
        });
    })
    .catch(error => console.log("error", error));






/* -------------------------------- */
/* -------------------------------- */
/* EDIT (UPDATE) CELLS IN REAL-TIME */
/* -------------------------------- */
/* -------------------------------- */
setTimeout(function () {
    // Get All Cells
    const all_cells = document.querySelectorAll("td");

    // Actions For All Of The Cells
    all_cells.forEach(cell => {
        // When User Clicks On Every Cells
        cell.onclick = function () {
            // Convert Cells To Editable Mode
            cell.contentEditable = "true";
            // Every Change In A Cell
            cell.oninput = function () {

                // Get Required Data From HTML DOM
                const target_id = parseInt(cell.getAttribute("data-id"));
                const target_cell = cell.getAttribute("data-cell");
                const target_new_data = (cell.innerText);

                // Set Headers
                const my_headers = new Headers();
                my_headers.append("Content-Type", "application/json");

                // Set Data For Sending To API
                const raw_data = JSON.stringify({
                    "target_id": target_id,
                    "target_cell": target_cell,
                    "target_new_data": target_new_data
                });

                // Set Request Options
                const request_options = {
                    method: "POST",
                    headers: my_headers,
                    body: raw_data,
                    redirect: "follow"
                };

                // Send Data To API
                fetch("api/", request_options)
                    .then(response => response.json())
                    .then(result => {
                        // Get Pop-Up Notification
                        const popup = document.querySelector("#notification");

                        // Successful Status From API
                        if (result.cell_updated == true) {
                            // Display Notification
                            popup.style.display = "block";
                            popup.setAttribute(
                                "class", "animate__animated animate__fadeInDown animate__fast"
                            );
                            // Hide Notification After 2 Seconds
                            setTimeout(function () {
                                popup.setAttribute(
                                    "class", "animate__animated animate__fadeOutLeft animate__fast"
                                );
                            }, 2000);
                        }
                    })
                    .catch(error => console.log("error", error));
            };
        };
    });
}, 1000);

// Function to add a new row to the table
function addRow() {
    const id = parseInt(document.getElementById("new-id").value); // Get the new row's ID from user input
    const date = document.getElementById("new-date").value; // Get the new row's Date from user input
    const score = document.getElementById("new-score").value; // Get the new row's Score from user input
    const baseline = document.getElementById("new-baseline").value; // Get the new row's Baseline from user input

    // Append the new row to the table
    document.querySelector(".table-group-divider").innerHTML += `
        <tr>
            <th class="id">${id}</th>
            <td data-cell="Date" data-id="${id}">${date}</td>
            <td data-cell="Score" data-id="${id}">${score}</td>
            <td data-cell="Baseline" data-id="${id}">${baseline}</td>
        </tr>
    `;
}

// Function to add a new column to the table
function addColumn() {
    const columnName = document.getElementById("new-column-name").value; // Get the new column's name from user input

    // Loop through each row and add a new cell with the specified column name
    const allRows = document.querySelectorAll("tr");
    allRows.forEach(row => {
        const newCell = document.createElement("td");
        newCell.setAttribute("data-cell", columnName);
        newCell.setAttribute("data-id", parseInt(row.querySelector(".id").innerText));
        row.appendChild(newCell);
    });
}