$(document).ready(function() {
    let currentDraggedMedal;

    // Initialize draggable medals
    $('#medals img').on('dragstart', function(event) {
        currentDraggedMedal = event.target; // Store the entire element for more control
    });

    // Ensure items are displayed and set up for drop events
    displayItems();

    // Enable dropping on items
    $(document).on('dragover', '.item', function(event) {
        event.preventDefault(); // This is crucial for allowing a drop
    });

    $(document).on('drop', '.item', function(event) {
        event.preventDefault(); // Prevent default to allow custom drop behavior
        let itemId = $(this).data('id');
        let itemElement = this; // The item element being dropped on
        placeMedal(currentDraggedMedal, itemId, itemElement);
    });
});

function displayItems() {
    // This function should fetch items and render them in #itemsList
    // For demonstration, let's manually create some items
    const items = [{id: 1, name: "Issue 1"}, {id: 2, name: "Issue 2"}];
    let itemsHtml = items.map(item => `<div class="item" data-id="${item.id}" style="border: 1px solid #ccc; margin: 10px; padding: 10px; position: relative;">${item.name}</div>`).join('');
    $('#itemsList').html(itemsHtml);
}

function placeMedal(draggedMedal, itemId, itemElement) {
    // Handle the logic to visually place the medal and send the vote to the server
    console.log(`Dropped medal ${draggedMedal.id} on item ${itemId}`);
    // Example: append a copy of the medal image to the item
    let medalClone = $(draggedMedal).clone().removeAttr('id').addClass('medal');
    $(itemElement).append(medalClone); // Modify as needed for your layout
}

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





