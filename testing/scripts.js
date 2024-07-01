document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
});

function loadUsers() {
    fetch('./users/fetch_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching users:', data.error);
                return;
            }

            const usersTableContainer = document.getElementById('users-table-container');
            const table = document.createElement('table');
            table.classList.add('users-table');

            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            const headers = ['ID', 'School ID', 'Is Admin', 'Teacher ID', 'Name', 'Subject Taught', 'Approve'];
            headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.account_id}</td>
                    <td>${user.school_id}</td>
                    <td>${user.is_admin ? 'Yes' : 'No'}</td>
                    <td>${user.teacher_id}</td>
                    <td>${user.name}</td>
                    <td>${user.subject_taught || ''}</td>
                    <td>
                        <button class="approve-btn" onclick="toggleApproval(${user.teacher_id}, ${user.approved})">
                            ${user.approved ? '✔️' : '❌'}
                        </button>
                        <button class="delete-btn" onclick="deleteUser(${user.teacher_id})">🗑️</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            table.appendChild(tbody);

            usersTableContainer.innerHTML = '';
            usersTableContainer.appendChild(table);
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
