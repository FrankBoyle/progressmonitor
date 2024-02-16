//script.js
$(document).ready(function() {
    // Function to fetch items and display them in the form
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                    <div class="item" data-id="${item.id}">
                        <h3>${item.name}</h3>
                        <label><input type="radio" name="first" value="${item.id}"> 1st Place</label>
                        <label><input type="radio" name="second" value="${item.id}"> 2nd Place</label>
                        <label><input type="radio" name="third" value="${item.id}"> 3rd Place</label>
                    </div>
                `;
            });
            $('#itemsList').html(itemsHtml);
        });
    }

    // Call displayItems when the page loads
    displayItems();

    // Handle form submission
    $('#votingForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        
        $.post('vote.php', formData, function(response) {
            alert("Votes submitted successfully!");
            // Optionally, you can refresh the items list here if you want to show the updated vote counts
            displayItems();
        });
    });
});






