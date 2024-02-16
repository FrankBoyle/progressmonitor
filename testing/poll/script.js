$(document).ready(function() {
    let medalsAssigned = { 'gold': null, 'silver': null, 'bronze': null };
    let itemsPoints = { /* To store the points for each item */ };

    // Initialize draggable medals
    $('#medals img').on('dragstart', function(event) {
        event.originalEvent.dataTransfer.setData("text/plain", event.target.id);
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
        const medal = event.originalEvent.dataTransfer.getData("text");

        // If the medal is already assigned to another item, subtract points from that item
        if (medalsAssigned[medal] !== null) {
            itemsPoints[medalsAssigned[medal]] -= getMedalPoints(medal);
        }

        // Assign the medal to the new item and add points
        medalsAssigned[medal] = itemId;
        itemsPoints[itemId] = (itemsPoints[itemId] || 0) + getMedalPoints(medal);
        placeMedal(medal, itemId, this);

        // After updating points, reorder the items
        reorderItems();
    });

    function displayItems() {
        // Fetch items from server and populate itemsPoints with initial points
        const items = [{id: 1, name: "Issue 1"}, {id: 2, name: "Issue 2"}, {id: 3, name: "Issue 3"}];
        items.forEach(item => {
            itemsPoints[item.id] = 0; // Initialize points to 0
        });
        // Now display the items in the DOM
        renderItems(items);
    }

    function renderItems(items) {
        let itemsHtml = items.map(item => `<div class="item" data-id="${item.id}" data-points="${itemsPoints[item.id]}" style="border: 1px solid #ccc; margin: 10px; padding: 10px; position: relative;">${item.name}</div>`).join('');
        $('#itemsList').html(itemsHtml);
    }

    function placeMedal(medal, itemId, itemElement) {
        // ... same as before ...
    }

    function getMedalPoints(medal) {
        // Define the points for each medal
        const points = { 'gold': 3, 'silver': 2, 'bronze': 1 };
        return points[medal] || 0;
    }

    function reorderItems() {
        // Get the array of item elements, sort them by points, and re-append them to the list
        let itemsArray = $('.item').toArray();
        itemsArray.sort((a, b) => {
            let pointsA = $(a).data('points');
            let pointsB = $(b).data('points');
            return pointsB - pointsA; // Sort in descending order
        });
        // Re-append items to the list in the new order
        itemsArray.forEach(item => $('#itemsList').append(item));
    }
});






