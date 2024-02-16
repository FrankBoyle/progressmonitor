$(document).ready(function() {
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                    <div class="item" data-id="${item.id}">
                        <h3>${item.name}</h3>
                        <div class="vote-buttons">
                            <button class="first-place" data-id="${item.id}">1st</button>
                            <button class="second-place" data-id="${item.id}">2nd</button>
                            <button class="third-place" data-id="${item.id}">3rd</button>
                        </div>
                    </div>
                `;
            });
            $('#itemsList').html(itemsHtml);
        });
    }

    $('#votingForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        console.log("Form data being sent:", formData); // Debug log
        
        $.post('vote.php', formData, function(response) {
            console.log("Response from server:", response); // Debug log
            alert("Votes submitted successfully!");
            displayItems(); // Refresh the items list
        });
    });

    // Toggle button functionality
    $(document).on('click', '.vote-buttons button', function() {
        $(this).toggleClass('selected').siblings().removeClass('selected');
    });

    displayItems(); // Initial display
});





