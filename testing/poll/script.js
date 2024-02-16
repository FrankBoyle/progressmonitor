$(document).ready(function() {
    let medalsAssigned = { 'gold': null, 'silver': null, 'bronze': null };

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

        // Check if the medal is already assigned to another item
        if (medalsAssigned[medal] !== null && medalsAssigned[medal] !== itemId) {
            // Remove the medal from the previous item
            $(`.item[data-id=${medalsAssigned[medal]}]`).data(medal, null);
            $(`.item[data-id=${medalsAssigned[medal]}] .medal[data-medal=${medal}]`).remove();
        }

        // Assign the medal to the new item and update the display
        medalsAssigned[medal] = itemId;
        $(this).data(medal, 'true'); // Store which medals the item has
        placeMedal(medal, itemId, this);

        // Update the order of items based on votes
        updateItemsOrder();
    });

    function displayItems() {
        // This function should ideally fetch items from the server
        // For demonstration, let's manually create some items
        const items = [{id: 1, name: "Issue 1"}, {id: 2, name: "Issue 2"}, {id: 3, name: "Issue 3"}];
        let itemsHtml = items.map(item => `<div class="item" data-id="${item.id}" data-gold="false" data-silver="false" data-bronze="false" style="border: 1px solid #ccc; margin: 10px; padding: 10px; position: relative;">${item.name}</div>`).join('');
        $('#itemsList').html(itemsHtml);
    }

    function placeMedal(medal, itemId, itemElement) {
        // Append a clone of the medal to the item
        let medalClone = $(`#${medal}`).clone().removeAttr('id').attr('data-medal', medal).addClass('medal');
        $(itemElement).append(medalClone);
        
        // Send the updated vote to the server here...
        console.log(`Placed ${medal} on item ${itemId}`);
    }

    function updateItemsOrder() {
        // Calculate scores and reorder the items
        let itemsArray = $('.item').toArray();
        itemsArray.sort(function(a, b) {
            let scoreA = calculateScore($(a));
            let scoreB = calculateScore($(b));
            return scoreB - scoreA; // Sort in descending order of score
        });

        // Re-append items to the container in the new order
        $('#itemsList').empty();
        itemsArray.forEach(function(item) {
            $('#itemsList').append(item);
        });
    }

    function calculateScore(item) {
        // Assign points for gold, silver, and bronze
        let score = 0;
        score += item.data('gold') === 'true' ? 3 : 0;
        score += item.data('silver') === 'true' ? 2 : 0;
        score += item.data('bronze') === 'true' ? 1 : 0;
        return score;
    }
});






