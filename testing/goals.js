document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const studentIdNew = urlParams.get('student_id');
    const metadataId = urlParams.get('metadata_id');

    if (!studentIdNew || !metadataId) {
        console.error('Student ID or Metadata ID is missing in the URL parameters.');
        alert('Student ID or Metadata ID is missing. Please check the URL parameters.');
        return;
    }

    // Fetch and display goals
    fetchGoals(studentIdNew, metadataId);
});

function fetchGoals(studentId, metadataId) {
    fetch(`./users/fetch_goals.php?student_id=${studentId}&metadata_id=${metadataId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Goals data fetched:', data);
            if (data && Array.isArray(data)) {
                displayGoals(data.filter(goal => goal.metadata_id == metadataId));
            } else {
                console.error('Invalid or incomplete goals data:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching goals data:', error);
        });
}

function displayGoals(goals) {
    const goalsContainer = document.getElementById('goalsContainer');
    goalsContainer.innerHTML = ''; // Clear any existing goals

    goals.forEach(goal => {
        const goalElement = document.createElement('div');
        goalElement.className = 'goal-item';
        goalElement.innerHTML = `
            <div class="goal-content" ondblclick="editGoal(${goal.goal_id})">
                <div class="goal-text" id="goal-text-${goal.goal_id}">${goal.goal_description}</div>
                <div class="goal-actions">
                    <button class="btn btn-danger" onclick="archiveGoal(${goal.goal_id})">Archive</button>
                </div>
            </div>
            <div class="goal-edit" id="goal-edit-${goal.goal_id}" style="display: none;">
                <div id="editor-${goal.goal_id}" class="quill-editor"></div>
                <button class="btn btn-primary" onclick="saveGoal(${goal.goal_id})">Save</button>
                <button class="btn btn-secondary" onclick="cancelEdit(${goal.goal_id})">Cancel</button>
            </div>
        `;
        goalsContainer.appendChild(goalElement);
        initializeQuillEditor(goal.goal_id, goal.goal_description);
    });
}

function initializeQuillEditor(goalId, content) {
    const quill = new Quill(`#editor-${goalId}`, {
        theme: 'snow'
    });
    quill.root.innerHTML = content;

    window[`quillEditor${goalId}`] = quill; // Save the editor instance to a global variable for later use
}

function editGoal(goalId) {
    document.getElementById(`goal-text-${goalId}`).style.display = 'none';
    document.getElementById(`goal-edit-${goalId}`).style.display = 'block';
}

function saveGoal(goalId) {
    const quill = window[`quillEditor${goalId}`];
    const content = quill.root.innerHTML;

    fetch('./users/update_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            goal_id: goalId,
            goal_description: content
        })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              document.getElementById(`goal-text-${goalId}`).innerHTML = content;
              cancelEdit(goalId);
          } else {
              alert('Failed to save goal. Please try again.');
          }
      }).catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving the goal.');
      });
}

function cancelEdit(goalId) {
    document.getElementById(`goal-text-${goalId}`).style.display = 'block';
    document.getElementById(`goal-edit-${goalId}`).style.display = 'none';
}

function archiveGoal(goalId) {
    if (!confirm('Are you sure you want to archive this goal?')) return;

    fetch('./users/archive_goal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            goal_id: goalId
        })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              document.querySelector(`#goal-edit-${goalId}`).closest('.goal-item').remove();
          } else {
              alert('Failed to archive goal. Please try again.');
          }
      }).catch(error => {
          console.error('Error:', error);
          alert('An error occurred while archiving the goal.');
      });
}
