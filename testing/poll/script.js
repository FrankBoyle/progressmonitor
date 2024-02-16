$(document).ready(function() {
    function displayItems() {
        $.get('getItems.php', function(data) {
            var items = JSON.parse(data);
            var itemsHtml = '';
            items.forEach(function(item) {
                itemsHtml += `
                <div class="item" data-id="${item.id}">
                    <div class="item-content">
                        <h3>${item.name}</h3>
                        <div class="toggle-button-container">
                            <div class="toggle-button">
                                <input type="radio" id="first${item.id}" name="first" value="${item.id}" hidden>
                                <label for="first${item.id}" class="toggle-label">1st Place</label>
                            </div>
                            <div class="toggle-button">
                                <input type="radio" id="second${item.id}" name="second" value="${item.id}" hidden>
                                <label for="second${item.id}" class="toggle-label">2nd Place</label>
                            </div>
                            <div class="toggle-button">
                                <input type="radio" id="third${item.id}" name="third" value="${item.id}" hidden>
                                <label for="third${item.id}" class="toggle-label">3rd Place</label>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            
            
            });
            $('#itemsList').html(itemsHtml);
            
            // Add event listeners to toggle buttons
            $('.toggle-button').click(function() {
                // Uncheck all other radio buttons in the same group
                $(this).siblings().find('input[type="radio"]').prop('checked', false);
                // Check the clicked radio button
                $(this).find('input[type="radio"]').prop('checked', true);
                // Update toggle button appearance
                updateToggleButtons();
            });
        });
    }

    function updateToggleButtons() {
        $('.toggle-button').each(function() {
            var isChecked = $(this).find('input[type="radio"]').prop('checked');
            if (isChecked) {
                $(this).find('i').removeClass('far fa-square').addClass('fas fa-check-square');
            } else {
                $(this).find('i').removeClass('fas fa-check-square').addClass('far fa-square');
            }
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




