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


// script.js
$(document).ready(function() {
    let currentDraggedMedal;

    // Example function to display items (this should be dynamically fetching the items)
    function displayItems() {
        const items = [
            {id: 1, name: "Issue 1"},
            {id: 2, name: "Issue 2"},
            {id: 3, name: "Issue 3"},
            // Add more items as necessary
        ];
        
        $('#itemsList').empty(); // Clear current list
        items.forEach(function(item) {
            $('#itemsList').append(`<div class="item" data-id="${item.id}" style="border: 1px solid #ccc; margin: 10px; padding: 10px; position: relative;">${item.name}</div>`);
        });
    }

    displayItems(); // Call this to initially display items

    $('img[draggable=true]').on('dragstart', function(event) {
        currentDraggedMedal = event.target.id; // Track the medal being dragged
    });

    $(document).on('dragover', '.item', function(event) {
        event.preventDefault(); // Allow dropping by preventing default behavior
    });

    $(document).on('drop', '.item', function(event) {
        event.preventDefault(); // Prevent default action
        let itemId = $(this).data('id'); // Get the item's ID
        placeMedal(currentDraggedMedal, itemId, this); // Pass the item element too
    });

    function placeMedal(medal, itemId, itemElement) {
        // Example of updating UI: append a medal image to the item
        let medalImageSrc = $('#' + medal).attr('src'); // Get the src of the medal
        $(itemElement).find('.medal').remove(); // Remove existing medal
        $(itemElement).append(`<img src="${medalImageSrc}" class="medal" style="width: 30px; position: absolute; top: 5px; right: 5px;">`); // Append new medal
        // Additionally, send vote to server here...
    }
});

