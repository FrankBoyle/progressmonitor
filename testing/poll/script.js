$(document).ready(function() {
    // Function to fetch items and display them in the form
    function displayItems() {
        $.get('getItems.php', function(items) {
            items = JSON.parse(items);
            let formHtml = '';
            items.forEach(function(item) {
                formHtml += `
                    <div class="item" data-id="${item.id}">
                        <h3>${item.name}</h3>
                        <label><input type="radio" name="gold" value="${item.id}"> Gold</label>
                        <label><input type="radio" name="silver" value="${item.id}"> Silver</label>
                        <label><input type="radio" name="bronze" value="${item.id}"> Bronze</label>
                    </div>
                `;
            });
            $('#itemsList').html(formHtml);
        });
    }

    displayItems();

    // Handle form submission
    $('#votingForm').on('submit', function(event) {
        event.preventDefault();
        let formData = $(this).serialize();

        $.post('vote.php', formData, function(response) {
            alert("Votes submitted successfully!");
            displayItems(); // Re-fetch and display items to show updated vote counts
        });
    });
});






