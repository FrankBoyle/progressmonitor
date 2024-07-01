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

            // Separate approved and unapproved users
            const approvedUsers = data.filter(user => user.approved);
            const unapprovedUsers = data.filter(user => !user.approved);

            const approvedUsersTableContainer = document.getElementById('approved-users-table-container');
            const unapprovedUsersTableContainer = document.getElementById('unapproved-users-table-container');

            // Create tables for approved and unapproved users
            createApprovedUsersTable(approvedUsersTableContainer, approvedUsers);
            createUnapprovedUsersTable(unapprovedUsersTableContainer, unapprovedUsers);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function createApprovedUsersTable(container, users) {
    const table = document.createElement('table');
    table.classList.add('users-table');

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    const headers = ['Is Admin', 'Name', 'Subject Taught', 'Delete'];
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.is_admin ? 'Yes' : 'No'}</td>
            <td>${user.name}</td>
            <td>${user.subject_taught || ''}</td>
            <td>
                <button class="delete-btn" onclick="deleteUser(${user.teacher_id})">🗑️</button>
            </td>
        `;
        tbody.appendChild(row);
    });
    table.appendChild(tbody);

    container.innerHTML = '';
    container.appendChild(table);
}

function createUnapprovedUsersTable(container, users) {
    const table = document.createElement('table');
    table.classList.add('users-table');

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    const headers = ['Is Admin', 'Name', 'Subject Taught', 'Approve?'];
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.is_admin ? 'Yes' : 'No'}</td>
            <td>${user.name}</td>
            <td>${user.subject_taught || ''}</td>
            <td>
                <button class="approve-btn" onclick="toggleApproval(${user.teacher_id}, 1)" style="color: green;">✔️</button>
                <button class="delete-btn" onclick="deleteUser(${user.teacher_id})" style="color: red;">❌</button>
            </td>
        `;
        tbody.appendChild(row);
    });
    table.appendChild(tbody);

    container.innerHTML = '';
    container.appendChild(table);
}

function toggleApproval(teacherId, newStatus) {
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
