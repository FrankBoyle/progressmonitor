document.addEventListener("DOMContentLoaded", function () {

// Function to create a new row with empty cells
function createEmptyRow() {
    const newRow = document.createElement("tr");
    newRow.innerHTML = `
        <td class="id">New</td>
        <td data-cell="Date" data-id="New" contenteditable="true"></td>
        <td data-cell="Score" data-id="New" contenteditable="true"></td>
        <td data-cell="Baseline" data-id="New" contenteditable="true"></td>
    `;
    return newRow;
}

// Function to add a new row to the table
function addRow() {
    const tableBody = document.querySelector(".table-group-divider tbody");
    tableBody.appendChild(createEmptyRow());
}

// Function to add a new column to the table
function addColumn() {
    const table = document.querySelector(".table-group-divider");
    const headerRow = table.querySelector("thead tr");
    const newColumnHeader = document.createElement("th");
    const columnHeaderName = prompt("Enter the column header name:");
    newColumnHeader.textContent = columnHeaderName || "New Column";
    headerRow.appendChild(newColumnHeader);

    const dataRows = table.querySelectorAll("tbody tr");
    dataRows.forEach(row => {
        const newCell = document.createElement("td");
        newCell.setAttribute("data-cell", columnHeaderName || "NewColumn");
        newCell.setAttribute("data-id", row.querySelector(".id").textContent);
        newCell.setAttribute("contenteditable", "true");
        row.appendChild(newCell);
    });
}

// Add event listeners to the "Add Row" and "Add Column" buttons
document.getElementById("addRowBtn").addEventListener("click", addRow);
document.getElementById("addColumnBtn").addEventListener("click", addColumn);

// ... The rest of your existing code for fetching data, displaying it in the table,
// and allowing real-time cell editing (as shown in your original code) ...


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
}