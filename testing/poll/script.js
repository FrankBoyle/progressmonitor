$(document).ready(function() {
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                    <div class="item" data-id="${item.id}">
                        <h3>${item.name}</h3>
                        <p>Total Votes: ${item.total_votes}</p>
                        <label><input type="radio" name="first" value="${item.id}"> 1st Place</label>
                        <label><input type="radio" name="second" value="${item.id}"> 2nd Place</label>
                        <label><input type="radio" name="third" value="${item.id}"> 3rd Place</label>
                    </div>
                `;
            });
            $('#itemsList').html(itemsHtml);
        });
    }

    $('#votingForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        
        $.post('vote.php', formData, function(response) {
            alert("Votes submitted successfully!");
            displayItems(); // Refresh the items list
        });
    });

    displayItems(); // Initial display
});




