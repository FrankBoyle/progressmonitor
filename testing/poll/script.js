$(document).ready(function() {
    function fetchAndDisplayItems() {
        $.get('getItems.php', function(items) {
            $('#itemsList').empty(); // Clear current list
            items = JSON.parse(items); // Assuming the response is a JSON string
            items.forEach(function(item) {
                // Display the item with vote counts
                $('#itemsList').append(`<div>${item.name}: First place votes - ${item.first_place_votes}, Second place votes - ${item.second_place_votes}, Third place votes - ${item.third_place_votes}</div>
                <button onclick="vote(${item.id}, 'first')">Vote 1st</button>
                <button onclick="vote(${item.id}, 'second')">Vote 2nd</button>
                <button onclick="vote(${item.id}, 'third')">Vote 3rd</button>`);
            });
        });
    }

    window.vote = function(id, position) {
        $.post('vote.php', { id: id, position: position }, function(response) {
            // Handle response from the server
            alert(response); // Show a simple alert with the server's response
            fetchAndDisplayItems(); // Refresh the list to show updated vote counts
        });
    };

    fetchAndDisplayItems(); // Initial fetch and display of items
});
