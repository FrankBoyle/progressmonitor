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
        // Real-time Cell Editing Function (Updated)
        function handleCellEdit(cell) {
            cell.contentEditable = "true";
            cell.oninput = function () {
                const targetId = parseInt(cell.getAttribute("data-id"));
                const targetCell = cell.getAttribute("data-cell");
                const targetNewData = cell.innerText;

            if (cell.parentElement.classList.contains("new-row")) {
            // If it's a new row, update the data in the table but don't send to the API
                cell.setAttribute("data-id", targetNewData); // Update the data-id attribute with the new ID value
                cell.parentElement.classList.remove("new-row"); // Remove the "new-row" class to signify it's no longer a new row
            } else {
            // If it's an existing row, send the data to the API for updating
            const myHeaders = new Headers();
            myHeaders.append("Content-Type", "application/json");

            const rawData = JSON.stringify({
                "target_id": targetId,
                "target_cell": targetCell,
                "target_new_data": targetNewData
            });

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: rawData,
                redirect: "follow"
            };

            fetch("api/", requestOptions)
                .then(response => response.json())
                .then(result => {
                    // Handle the API response as before
                    // ...
                })
                .catch(error => console.log("error", error));
            };
        };
    };
}, 1000);

// Function to add a new empty row to the table
function addEmptyRow() {
    const newRow = `
        <tr class="new-row">
            <th class="id"></th>
            <td data-cell="Date" data-id=""></td>
            <td data-cell="Score" data-id=""></td>
            <td data-cell="Baseline" data-id=""></td>
        </tr>
    `;
    document.querySelector(".table-group-divider").insertAdjacentHTML("beforeend", newRow);
}