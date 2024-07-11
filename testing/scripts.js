document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    loadActiveStudents();
    loadArchivedStudents();
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

            if (approvedUsersTableContainer && waitingApprovalTableContainer) {
                approvedUsersTableContainer.innerHTML = '';
                waitingApprovalTableContainer.innerHTML = '';
            } else {
                console.error('Table container elements not found');
                return;
            }

            const approvedTableData = data.filter(user => user.approved);
            const waitingApprovalTableData = data.filter(user => !user.approved);

            const approvedTable = new Tabulator(approvedUsersTableContainer, {
                data: approvedTableData,
                layout: "fitDataStretch",
                columns: [
                    { 
                        title: "Admin?", 
                        field: "is_admin", 
                        editor: "list", 
                        editorParams: {
                            values: [
                                {label: "Yes", value: 1},
                                {label: "No", value: 0}
                            ]
                        },
                        formatter: function(cell, formatterParams, onRendered) {
                            return cell.getValue() == 1 ? "Yes" : "No";
                        },
                        width: 100
                    },
                    { title: "First Name", field: "fname", editor: "input", widthGrow: 2 },
                    { title: "Last Name", field: "lname", editor: "input", widthGrow: 2 },
                    { title: "Subject Taught", field: "subject_taught", editor: "input", widthGrow: 2 },
                    {
                        title: "Delete", field: "teacher_id", formatter: function(cell, formatterParams, onRendered) {
                            return '<button class="delete-btn" onclick="deleteUser(' + cell.getValue() + ')">❌</button>';
                        },
                        width: 100
                    }
                ],
            });

            approvedTable.on("cellEdited", function(cell) {
                updateUser(cell.getRow().getData());
            });

            const waitingApprovalTable = new Tabulator(waitingApprovalTableContainer, {
                data: waitingApprovalTableData,
                layout: "fitDataStretch",
                columns: [
                    { 
                        title: "Is Admin", 
                        field: "is_admin", 
                        editor: "list", 
                        editorParams: {
                            values: [
                                {label: "Yes", value: 1},
                                {label: "No", value: 0}
                            ]
                        },
                        formatter: function(cell, formatterParams, onRendered) {
                            return cell.getValue() == 1 ? "Yes" : "No";
                        },
                        width: 100
                    },
                    { title: "Name", field: "name", editor: "input", widthGrow: 2 },
                    { title: "Subject Taught", field: "subject_taught", editor: "input", widthGrow: 2 },
                    {
                        title: "Approve?", field: "teacher_id", formatter: function(cell, formatterParams, onRendered) {
                            return '<button class="approve-btn" onclick="toggleApproval(' + cell.getValue() + ', 1)">✅</button>' +
                                   '<button class="delete-btn" onclick="deleteUser(' + cell.getValue() + ')">❌</button>';
                        },
                        width: 150
                    }
                ],
            });

            waitingApprovalTable.on("cellEdited", function(cell) {
                updateUser(cell.getRow().getData());
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadActiveStudents() {
    fetch('./users/fetch_students.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching students:', data.error);
                return;
            }

            const activeStudentsTableContainer = document.getElementById('active-students-table-container');
            if (activeStudentsTableContainer) {
                activeStudentsTableContainer.innerHTML = '';
            } else {
                console.error('Table container element not found');
                return;
            }

            const activeStudentsTable = new Tabulator("#active-students-table-container", {
                data: data,
                layout: "fitDataStretch", // This makes sure columns use up the available space
                initialSort: [
                    {column:"last_name", dir:"asc"} // Sort by last name ascending
                ],
                columns: [
                    { title: "First Name", field: "first_name", widthGrow: 2 },
                    { title: "Last Name", field: "last_name", widthGrow: 2 },
                    { title: "Date of Birth", field: "date_of_birth", widthGrow: 2 },
                    { title: "Grade Level", field: "grade_level", widthGrow: 2 },
                    {
                        title: "Archive", 
                        field: "student_id_new",
                        hozAlign: "center", // Centers the button horizontally
                        formatter: function(cell, formatterParams, onRendered) {
                            return '<button class="btn btn-archive">Archive</button>'; // Adding a class for styling
                        },
                        width: 100 // Set a fixed width for consistency
                    }
                ],
            });
            
            activeStudentsTable.on("cellEdited", function(cell) {
                updateStudent(cell.getRow().getData());
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadArchivedStudents() {
    fetch('./users/fetch_archived_students.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching students:', data.error);
                return;
            }

            const archivedStudentsTableContainer = document.getElementById('archived-students-table-container');
            if (archivedStudentsTableContainer) {
                archivedStudentsTableContainer.innerHTML = '';
            } else {
                console.error('Table container element not found');
                return;
            }

            const archivedStudentsTable = new Tabulator(archivedStudentsTableContainer, {
                data: data,
                layout: "fitDataStretch",
                initialSort: [
                    {column:"last_name", dir:"asc"} // Sort by last name ascending
                ],
                columns: [
                    { title: "First Name", field: "first_name", editor: "input", widthGrow: 2 },
                    { title: "Last Name", field: "last_name", editor: "input", widthGrow: 2 },
                    { title: "Date of Birth", field: "date_of_birth", editor: "input", widthGrow: 2 },
                    { title: "Grade Level", field: "grade_level", editor: "input", widthGrow: 2 },
                    {
                        title: "Activate", 
                        field: "student_id_new", 
                        hozAlign: "center", // Centers the button horizontally
                        formatter: function(cell, formatterParams, onRendered) {
                            return '<button onclick="activateStudent(' + cell.getValue() + ')">Activate</button>';
                        },
                        width: 100
                    }
                ]
            });

            archivedStudentsTable.on("cellEdited", function(cell) {
                // Update logic here
                console.log('Cell edited', cell.getRow().getData());
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function activateStudent(studentId) {
    console.log('Activating student with ID:', studentId);
    fetch('./users/activate_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ studentId: studentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Student activated successfully');
            // Reload both tables to reflect changes
            loadActiveStudents(); // Reload the active students table
            loadArchivedStudents(); // Reload the archived students table
        } else {
            console.error('Failed to activate student:', data.message);
        }
    })
    .catch(error => {
        console.error('Error activating student:', error);
    });
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

function archiveStudent(studentId) {
    fetch('./users/archive_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ student_id_new: studentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadActiveStudents(); // Reload the active students to reflect the change
            loadArchivedStudents(); // Reload the archived students to reflect the change
        } else {
            console.error('Error archiving student:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateStudent(studentData) {
    fetch('./users/update_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(studentData)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error updating student:', data.message);
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