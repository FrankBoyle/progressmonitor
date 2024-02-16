$(document).ready(function() {
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                    <div class="item" data-id="${item.id}">
                        <h3>${item.name}</h3>
                        <p>Total Votes: ${item.total_votes}</p>
                        <label><input type="radio" name="first" value="${item.id}"> 1st Place</label>
                        <label><input type="radio" name="second" value="${item.id}"> 2nd Place</label>
                        <label><input type="radio" name="third" value="${item.id}"> 3rd Place</label>
                    </div>
                `;
            });
            $('#itemsList').html(itemsHtml);
        });
    }

    $('#votingForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        
        // Check if the same item is selected in multiple positions
        var firstVote = $('input[name="first"]:checked').val();
        var secondVote = $('input[name="second"]:checked').val();
        var thirdVote = $('input[name="third"]:checked').val();
        if (firstVote && (firstVote === secondVote || firstVote === thirdVote)) {
            $('#message').text("Please select different items for 1st, 2nd, and 3rd place votes.");
            $('#message').show();
            return;
        }
        if (secondVote && secondVote === thirdVote) {
            $('#message').text("Please select different items for 1st, 2nd, and 3rd place votes.");
            $('#message').show();
            return;
        }
        
        $.post('vote.php', formData, function(response) {
            alert("Votes submitted successfully!");
            displayItems(); // Refresh the items list
        });
    });

    // Hide the message when the mouse moves
    $(document).mousemove(function(event) {
        $('#message').hide();
    });

    // Disable the radio buttons when clicking on them
    $('input[type="radio"]').on('click', function() {
        var name = $(this).attr('name');
        $('input[type="radio"][name="' + name + '"]').not(this).prop('disabled', true);
    });

    displayItems(); // Initial display
});



