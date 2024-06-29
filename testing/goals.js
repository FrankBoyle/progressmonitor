document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const studentIdNew = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    if (!studentIdNew || !metadataId) {
        console.error('Student ID or Metadata ID is missing in the URL parameters.');
        alert('Student ID or Metadata ID is missing. Please check the URL parameters.');
        return;
    }

    // Fetch initial data and setup the table
    fetchInitialData(studentIdNew, metadataId);

    // Fetch and display goals
    fetchGoals(studentIdNew, metadataId);

    // Setup event listener for the filter button
    document.getElementById('filterData').addEventListener('click', function() {
        const iepDate = document.getElementById('iep_date').value;
        console.log('Filter data button clicked, IEP Date:', iepDate);
        if (iepDate) {
            saveIEPDate(iepDate, studentIdNew);
        }
    });

    // Initialize charts on page load
    initializeCharts();

    // Event listener for adding a new data row
    document.getElementById("addDataRow").addEventListener("click", function() {
        const newRowDateInput = document.getElementById("newRowDate");
        newRowDateInput.style.display = "block";
        newRowDateInput.focus();

        newRowDateInput.addEventListener("change", function() {
            const newDate = newRowDateInput.value;

            if (newDate === "") {
                alert("Please select a date.");
                return;
            }

            if (isDateDuplicate(newDate)) {
                alert("An entry for this date already exists. Please choose a different date.");
                return;
            }

            const newData = {
                student_id_new: studentIdNew,
                school_id: schoolId, // Use the schoolId from the session variable
                metadata_id: metadataId,
                score_date: newDate,
                scores: {}
            };

            for (let i = 1; i <= 10; i++) {
                newData.scores[`score${i}`] = null;
            }

            console.log('Sending new data:', newData); // Add debugging statement

            fetch('./users/insert_performance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newData)
            }).then(response => {
                console.log('Fetch response:', response);
                return response.text(); // Get response as text
            }).then(text => {
                console.log('Response text:', text);
                let result;
                try {
                    result = JSON.parse(text); // Parse text as JSON
                } catch (error) {
                    throw new Error('Response is not valid JSON');
                }
                console.log('Parsed result:', result);
                if (result.success) {
                    newData.performance_id = result.performance_id;
                    table.addRow(newData);
                    newRowDateInput.value = "";
                    newRowDateInput.style.display = "none";
                } else {
                    alert('Failed to add new data: ' + result.error);
                    console.error('Error info:', result.missing_data);
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding new data.');
            });
        }, { once: true });
    });
});

function fetchGoals(studentIdNew, metadataId) {
    fetch(`./users/fetch_goals.php?student_id=${studentIdNew}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Goals data fetched:', data);
            if (data && Array.isArray(data)) {
                displayGoals(data);
            } else {
                console.error('Invalid or incomplete goals data:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching goals data:', error);
        });
}

function displayGoals(goals) {
    const goalsContainer = document.getElementById('goals-container');
    goalsContainer.innerHTML = ''; // Clear existing goals

    goals.forEach(goal => {
        if (!goal.goal_id || !goal.goal_description) {
            console.error('Invalid goal structure:', goal);
            return;
        }

        const goalItem = document.createElement('div');
        goalItem.classList.add('goal-item');
        goalItem.innerHTML = `
            <div class="quill-editor" id="editor-${goal.goal_id}"></div>
            <button class="edit-btn">Edit</button>
            <button class="save-btn">Save</button>
            <button class="cancel-btn">Cancel</button>
            <button class="archive-btn">Archive</button>
        `;

        goalsContainer.appendChild(goalItem);

        const quill = new Quill(`#editor-${goal.goal_id}`, {
            theme: 'snow',
            readOnly: true
        });

        quill.root.innerHTML = goal.goal_description; // Load the goal content

        goalItem.querySelector('.edit-btn').addEventListener('click', () => {
            quill.enable(true);
            goalItem.classList.add('editing');
        });

        goalItem.querySelector('.save-btn').addEventListener('click', () => {
            const updatedContent = quill.root.innerHTML;
            saveGoal(goal.goal_id, updatedContent, goalItem);
        });

        goalItem.querySelector('.cancel-btn').addEventListener('click', () => {
            quill.root.innerHTML = goal.goal_description;
            quill.enable(false);
            goalItem.classList.remove('editing');
        });

        goalItem.querySelector('.archive-btn').addEventListener('click', () => {
            archiveGoal(goal.goal_id, goalItem);
        });
    });
}

function saveGoal(goalId, content, goalItem) {
    fetch('./users/save_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: goalId, content: content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            goalItem.classList.remove('editing');
            const quill = Quill.find(goalItem.querySelector('.quill-editor'));
            quill.enable(false);
        } else {
            alert('Failed to save goal. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error saving goal:', error);
    });
}

function archiveGoal(goalId, goalItem) {
    if (confirm('Are you sure you want to archive this goal?')) {
        fetch('./users/archive_goal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: goalId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                goalItem.remove();
            } else {
                alert('Failed to archive goal. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error archiving goal:', error);
        });
    }
}
