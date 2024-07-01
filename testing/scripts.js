document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
});

function loadUsers() {
    fetch('./users/fetch_staff.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching users:', data.error);
                return;
            }

            const approvedUsersTableContainer = document.getElementById('approved-users-table-container');
            const waitingApprovalTableContainer = document.getElementById('waiting-approval-table-container');
            
            // Clear existing tables
            if (approvedUsersTableContainer && waitingApprovalTableContainer) {
                approvedUsersTableContainer.innerHTML = '';
                waitingApprovalTableContainer.innerHTML = '';
            } else {
                console.error('Table container elements not found');
                return;
            }

            const approvedTableData = data.filter(user => user.approved);
            const waitingApprovalTableData = data.filter(user => !user.approved);

            // Create approved users table
            new Tabulator(approvedUsersTableContainer, {
                data: approvedTableData,
                layout: "fitColumns",
                columns: [
                    { title: "Is Admin", field: "is_admin", editor: "select", editorParams: { values: ["Yes", "No"] } },
                    { title: "Name", field: "name", editor: "input" },
                    { title: "Subject Taught", field: "subject_taught", editor: "input" },
                    {
                        title: "Delete", field: "teacher_id", formatter: function(cell, formatterParams, onRendered) {
                            return '<button class="delete-btn" onclick="deleteUser(' + cell.getValue() + ')">🗑️</button>';
                        }
                    }
                ],
                cellEdited: function(cell) {
                    updateUser(cell.getRow().getData());
                }
            });

            // Create users waiting for approval table
            new Tabulator(waitingApprovalTableContainer, {
                data: waitingApprovalTableData,
                layout: "fitColumns",
                columns: [
                    { title: "Is Admin", field: "is_admin", editor: "select", editorParams: { values: ["Yes", "No"] } },
                    { title: "Name", field: "name", editor: "input" },
                    { title: "Subject Taught", field: "subject_taught", editor: "input" },
                    {
                        title: "Approve?", field: "teacher_id", formatter: function(cell, formatterParams, onRendered) {
                            return '<button class="approve-btn" onclick="toggleApproval(' + cell.getValue() + ', 0)">✔️</button>' +
                                   '<button class="delete-btn" onclick="deleteUser(' + cell.getValue() + ')">❌</button>';
                        }
                    }
                ],
                cellEdited: function(cell) {
                    updateUser(cell.getRow().getData());
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function toggleApproval(teacherId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    fetch('./users/toggle_approval.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ teacher_id: teacherId, approved: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadUsers(); // Reload the users to reflect the change
        } else {
            console.error('Error updating approval status:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function deleteUser(teacherId) {
    if (!confirm('Are you sure you want to delete this user?')) return;

    fetch('./users/delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ teacher_id: teacherId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadUsers(); // Reload the users to reflect the deletion
        } else {
            console.error('Error deleting user:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateUser(userData) {
    fetch('./users/update_staff.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error updating user:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
