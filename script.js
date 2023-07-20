$(document).ready(function() {
    // Function to add a new row to the table
    function addRow() {
        $('#xy-table tbody').append(`
            <tr>
                <td><input type="text" class="x-value" value=""></td>
                <td><input type="text" class="y-value" value=""></td>
            </tr>
        `);
    }

    // Function to save the state of the table in localStorage
    function saveState() {
        const data = [];
        $('#xy-table tbody tr').each(function() {
            const xValue = $(this).find('.x-value').val();
            const yValue = $(this).find('.y-value').val();
            data.push({ x: xValue, y: yValue });
        });
        localStorage.setItem('tableState', JSON.stringify(data));
        alert('Table state saved successfully!');
    }

    // Load the saved state from localStorage on page load (if available)
    function loadState() {
        const savedState = localStorage.getItem('tableState');
        if (savedState) {
            const data = JSON.parse(savedState);
            data.forEach(function(item) {
                $('#xy-table tbody').append(`
                    <tr>
                        <td><input type="text" class="x-value" value="${item.x}"></td>
                        <td><input type="text" class="y-value" value="${item.y}"></td>
                    </tr>
                `);
            });
        }
    }

    // Attach event handlers
    $('#add-row-btn').on('click', addRow);
    $('#save-state-btn').on('click', saveState);

    // Load saved state on page load
    loadState();
});
