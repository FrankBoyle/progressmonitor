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

        // If the medal is already assigned to an item, remove it from that item
        if (medalsAssigned[medal] !== null && medalsAssigned[medal] !== itemId) {
            $(`.item[data-id=${medalsAssigned[medal]}] .medal[data-medal=${medal}]`).remove();
        }

        // Update the assignment
        medalsAssigned[medal] = itemId;
        placeMedal(medal, itemId, this);
    });

    function displayItems() {
        // This function should ideally fetch items from the server
        // For demonstration, let's manually create some items
        const items = [{id: 1, name: "Issue 1"}, {id: 2, name: "Issue 2"}, {id: 3, name: "Issue 3"}];
        let itemsHtml = items.map(item => `<div class="item" data-id="${item.id}" style="border: 1px solid #ccc; margin: 10px; padding: 10px; position: relative;">${item.name}</div>`).join('');
        $('#itemsList').html(itemsHtml);
    }

    function placeMedal(medal, itemId, itemElement) {
        // Remove any existing medal of the same type from the item
        $(itemElement).find(`.medal[data-medal=${medal}]`).remove();
        
        // Append a clone of the medal to the item
        let medalClone = $(`#${medal}`).clone().removeAttr('id').attr('data-medal', medal).addClass('medal');
        $(itemElement).append(medalClone);
        
        // Send the updated vote to the server here...
        console.log(`Placed ${medal} on item ${itemId}`);
    }
});






