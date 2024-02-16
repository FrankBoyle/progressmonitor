$(document).ready(function() {
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                <div class="item" data-id="${item.id}">
                    <h3>${item.name}</h3>
                    <div class="toggle-button">
                        <input type="radio" id="first${item.id}" name="first" value="${item.id}">
                        <label for="first${item.id}"><i class="far fa-square"></i> 1st Place</label>
                    </div>
                    <div class="toggle-button">
                        <input type="radio" id="second${item.id}" name="second" value="${item.id}">
                        <label for="second${item.id}"><i class="far fa-square"></i> 2nd Place</label>
                    </div>
                    <div class="toggle-button">
                        <input type="radio" id="third${item.id}" name="third" value="${item.id}">
                        <label for="third${item.id}"><i class="far fa-square"></i> 3rd Place</label>
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

    displayItems(); // Initial display
});





