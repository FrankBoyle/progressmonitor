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

    let currentDraggedMedal;

    // Fetch and display items from the server
    fetchAndDisplayItems();

    // Set up drag events for medals
    $('#gold, #silver, #bronze').on('dragstart', function(event) {
        currentDraggedMedal = event.target.id; // Track the medal being dragged
    });

    // Allow items to be droppable
    $(document).on('dragover', '.item', function(event) {
        event.preventDefault(); // Necessary to allow dropping
    });

    $(document).on('drop', '.item', function(event) {
        event.preventDefault(); // Prevent default action (open as link for some elements)
        let itemId = $(this).data('id'); // Get the item's ID
        placeMedal(currentDraggedMedal, itemId);
    });

    function placeMedal(medal, itemId) {
        // Handle the placement of the medal (e.g., update the item visually, send vote to server)
        console.log(`Placed ${medal} on item ${itemId}`);
        // Send vote to server here...
    }

    function fetchAndDisplayItems() {
        // Fetch items and display them, similar to previous examples
        // Add class 'item' to each item for drag-and-drop functionality
    }
});

/*
// script.js
$(document).ready(function() {
    let currentDraggedMedal;

    // Fetch and display items from the server
    fetchAndDisplayItems();

    // Set up drag events for medals
    $('#gold, #silver, #bronze').on('dragstart', function(event) {
        currentDraggedMedal = event.target.id; // Track the medal being dragged
    });

    // Allow items to be droppable
    $(document).on('dragover', '.item', function(event) {
        event.preventDefault(); // Necessary to allow dropping
    });

    $(document).on('drop', '.item', function(event) {
        event.preventDefault(); // Prevent default action (open as link for some elements)
        let itemId = $(this).data('id'); // Get the item's ID
        placeMedal(currentDraggedMedal, itemId);
    });

    function placeMedal(medal, itemId) {
        // Handle the placement of the medal (e.g., update the item visually, send vote to server)
        console.log(`Placed ${medal} on item ${itemId}`);
        // Send vote to server here...
    }

    function fetchAndDisplayItems() {
        // Fetch items and display them, similar to previous examples
        // Add class 'item' to each item for drag-and-drop functionality
    }
});
*/