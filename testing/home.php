<?php
include './users/fetch_students.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}

usort($students, function($a, $b) {
  $aLastName = extractLastName($a['name']);
  $bLastName = extractLastName($b['name']);
  return strcmp($aLastName, $bLastName);
});

function extractLastName($fullName) {
  $parts = explode(' ', $fullName);
  return end($parts); // Assumes the last word is the last name
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bfactor</title>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>

<!-- Select2 CSS & JS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="./plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- jsGrid -->
<link rel="stylesheet" href="./plugins/jsgrid/jsgrid.min.css"> 
<link rel="stylesheet" href="./plugins/jsgrid/jsgrid-theme.min.css"> 
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/adminlte.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="./plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="./plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="./plugins/jqvmap/jqvmap.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="./plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../plugins/summernote/summernote-bs4.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed" data-panel-auto-height-mode="height">

<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item dropdown d-none d-sm-inline-block">
      <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-envelope"></i></a>
            <div class="dropdown-menu">
              <a href="mailto: sales@bfactor.org" class="dropdown-item">
                <span href="#" class="dropdown-item">Sales</button>
                </a>
            <div class="dropdown-divider"></div>
              <a href="mailto: support@bfactor.org" class="dropdown-item">
                <span href="#" class="dropdown-item">Support</span>
              </a>
            </div>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>
  </nav>
  <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Bfactor</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo($_SESSION['user']);?></a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item menu-closed">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Students
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="./home.php" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Students</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inactive Page</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
              <a href="./users/logout.php" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Sign Out<span class="right badge badge-danger"></span></p>
              </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Students</h1>
            <?php if ($isAdmin): ?>
            <!-- Toggle Button -->
            <form method="post">
              <button type="submit" name="toggle_view"><?= $showArchived ? 'Show Active Students' : 'Show Archived Students' ?></button>
              <input type="hidden" name="show_archived" value="<?= $showArchived ? '0' : '1' ?>">
            </form>
          <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="./home.php">Home</a></li>
              <li class="breadcrumb-item active">Student List</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <!-- Form to create a new group -->
          <form method="post">
            <input type="text" name="group_name" placeholder="Group Name">
            <button type="submit" name="create_group">Create Group</button>
          </form>

<?php if (!empty($message)): ?>
    <div class="alert">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>


<!-- List groups with edit, delete, and share options -->
<table>
  <?php foreach ($groups as $group): ?>
    <tr>
    <td>
            <!-- Clickable star with class and data attribute -->
            <a href="javascript:void(0);" class="set-default-group-star" data-group-id="<?= $group['group_id'] ?>">
                <?= $group['is_default'] ? '&#9733;' : '&#9734;' ?> <!-- Star icon -->
            </a>
        </td>
      <td>
        <form method="post">
          <input type="hidden" name="group_id" value="<?= htmlspecialchars($group['group_id']) ?>">
          <input type="text" name="edited_group_name" value="<?= htmlspecialchars($group['group_name']) ?>">
          <button type="submit" name="edit_group">Update</button>
        </form>
      </td>
      <td>
        <button type="button" class="delete-group" data-group-id="<?= htmlspecialchars($group['group_id']) ?>">Delete Group</button>
      </td>
      <td>
<!-- Share Group Form for each group -->
<form method="post">
    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group['group_id']) ?>">
    <select name="shared_teacher_id">
        <option value="">Select staff here</option>
        <?php foreach ($teachers as $teacher): ?>
            <option value="<?= htmlspecialchars($teacher['teacher_id']) ?>"><?= htmlspecialchars($teacher['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="share_group">Share</button>
</form>

      </td>
    </tr>
  <?php endforeach; ?>
</table>

        </div>
      </div>
    </div>
  </div>
</section>


<!-- Section 1: Student Groups Filter -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <!--<h3 class="card-title">Student Groups Filter</h3><br>-->

<!-- Form to Assign Students to Group -->
<form method="post" id="assign_multiple_students_form" style="margin-bottom: 20px;">
  <div style="display: flex; align-items: center;">
    <div style="margin-right: 10px;">
    <select name="student_ids[]" multiple class="select2" style="width: 200px; height: 100px;" data-placeholder="Student name here">
    <option></option> <!-- Empty option for placeholder -->
    <?php foreach ($allStudents as $student): ?>
        <option value="<?= htmlspecialchars($student['student_id']) ?>"><?= htmlspecialchars($student['name']) ?></option>
    <?php endforeach; ?>
</select>

    </div>
              <div style="margin-right: 10px;">
                <select name="group_id" class="select2">
                  <?php foreach ($groups as $group): ?>
                    <option value="<?= htmlspecialchars($group['group_id']) ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" name="assign_to_group">Assign to Group</button>
            </div>
          </form>

<!-- Dropdown to select a group for filtering -->
<form method="post" id="group_filter_form">
    <label for="selected_group_id">Sort Students by Group:</label>
    <select name="selected_group_id" id="selected_group_id" onchange="document.getElementById('group_filter_form').submit();">
        <option value="all_students" <?= (!isset($_POST['selected_group_id']) && $defaultGroupId === null) ? "selected" : "" ?>>All Students</option>
        <?php foreach ($groups as $group): ?>
            <option value="<?= htmlspecialchars($group['group_id']) ?>" 
                <?= (isset($_POST['selected_group_id']) && $_POST['selected_group_id'] == $group['group_id']) || (!isset($_POST['selected_group_id']) && $group['group_id'] == $defaultGroupId) ? "selected" : "" ?>>
                <?= htmlspecialchars($group['group_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>



  <!-- Display filtered student list -->
  <?php if (!empty($students)): ?>
    <div style="display: flex; flex-direction: column;">
      <?php foreach ($students as $student): ?>
        <?php $metadataId = getSmallestMetadataId($student['school_id']); ?>
        <div style="display: flex; align-items: center; margin-bottom: 10px;">
          <span style="margin-right: 10px;">
            <a href='view_student_data.php?student_id=<?= $student['student_id'] ?>&metadata_id=<?= htmlspecialchars($metadataId) ?>'>
              <?= htmlspecialchars($student['name']) ?>
            </a>
          </span>

          <?php if ($isGroupFilterActive): ?>
            <!-- Red X Button to Remove Student from Group -->
            <form method="post" style="display: inline;">
              <input type="hidden" name="student_id_to_remove" value="<?= $student['student_id'] ?>">
              <button type="button" class="remove-student" data-student-id="<?= $student['student_id'] ?>" name="remove_from_group" style="color: red; background: none; border: none; cursor: pointer; font-size: 16px; line-height: 1;">&times;</button>
            </form>
          <?php endif; ?>

          <?php if ($isAdmin): ?>
            <?php if (!$isGroupFilterActive): ?>
              <form method="post" style="display: inline; margin-right: 10px;">
                <input type="hidden" name="student_id_to_toggle" value="<?= $student['student_id'] ?>">
                <button type="submit" name="<?= $showArchived ? 'unarchive_student' : 'archive_student' ?>" onclick="return confirmArchive('<?= $showArchived ? 'Unarchive' : 'Archive' ?>');">
                  <?= $showArchived ? 'Unarchive' : 'Archive' ?>
                </button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    No students found for this teacher.
  <?php endif; ?>
  </div>
  </div>
    </div>
  </div>
</section>

<!-- Section 2: Student List for Admin -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title">STUDENT LIST</h3><br>

          <!-- Add New Student Form -->
          <form method="post" action="">
            <label for="new_student_name">New Student Name:</label>
            <input type="text" id="new_student_name" name="new_student_name">
            <input type="submit" name="add_new_student" value="Add New Student">
          </form>

          <?php if (!empty($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

    <div class="content">
      <div class="container-fluid">
              <div class="card-body">
                <h5 class="card-title"></h5>

                <a href="#" class="card-link">Card link</a>
                <a href="#" class="card-link">Another link</a>
              </div>
            </div>
            
            <!-- solid sales graph -->
            <div class="card info">
              <div class="card-header border-0">
                <h3 class="card-title">
                  <i class="fas fa-th mr-1"></i>
                  Graph
                </h3>

                <div class="card-tools">
                  <button type="button" class="btn bg-info btn-sm" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-body -->
                  </div>
                  <!-- ./col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- /.card-footer -->
            </div>
            <!-- /.card -->


                <a href="#" class="card-link">Card link</a>
                <a href="#" class="card-link">Another link</a>
              </div>
            </div><!-- /.card -->
          </div>
          <!-- /.col-md-6 -->

      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      Anything you want
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2023 <a href="https://bfactor.org">Bfactor</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="./dist/js/adminlte.min.js"></script>
  <script>
    document.querySelectorAll('.set-default-group-star').forEach(star => {
        star.addEventListener('click', function() {
            var groupId = this.getAttribute('data-group-id');

            // Send AJAX request to update the default group
            fetch('./users/set_default_group.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'group_id=' + groupId
            })
            .then(response => response.text())
            .then(data => {
                // Update the stars on the page
                document.querySelectorAll('.set-default-group-star').forEach(otherStar => {
                    if (otherStar.getAttribute('data-group-id') === groupId) {
                        otherStar.innerHTML = '&#9733;'; // Filled star for the selected group
                    } else {
                        otherStar.innerHTML = '&#9734;'; // Empty star for other groups
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

function confirmArchive(action) {
    var message = action === 'Archive' ? 'Are you sure you want to archive this student?' : 'Are you sure you want to unarchive this student?';
    return confirm(message);
}
    $(document).ready(function() {
        $('.select2').select2();
    });

    
    $(".delete-group").click(function() {
        var groupId = $(this).data("group-id");
        console.log(groupId); // Debugging line
        var confirmation = confirm("Are you sure you want to delete this group?");
        if (confirmation) {
            // Send an AJAX request to delete the group
            $.ajax({
                method: "POST",
                url: "./users/delete_group.php", // Replace with the actual path to delete_group.php
                data: { group_id: groupId },
                success: function(response) {
                    // Handle the response (e.g., refresh the group list)
                    location.reload();
                },
                error: function() {
                    alert("Error deleting group.");
                }
            });
        }
    });    

    $(document).ready(function() {
    $(document).off('click', '.remove-student').on('click', '.remove-student', function() {
        var studentId = $(this).data("student-id");
        var groupId = $("#selected_group_id").val(); // or another logic to get the correct groupId
        var $thisButton = $(this); // Store the reference to the button

        if (groupId === 'all_students') {
            alert("Please select a group first.");
            return;
        }

        if (confirm("Are you sure you want to remove this student from the group?")) {
            $.ajax({
                method: "POST",
                url: "./users/remove_student_from_group.php",
                data: { student_id: studentId, group_id: groupId },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        $thisButton.closest('div').remove(); // Remove the closest div that wraps student info
                    } else {
                        alert(data.message);
                    }
                },
                error: function() {
                    alert("Error removing student from the group.");
                }
            });
        }
    });
});


</script>
</body>
</html>
