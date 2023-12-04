<?php
include './users/fetch_students.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Teacher ID not set in session");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bfactor</title>
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
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/adminlte.min.css">
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
                Starter Pages
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
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Simple Link
                <span class="right badge badge-danger">New</span>
              </p>
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
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="./test.php">Home</a></li>
              <li class="breadcrumb-item active">Text Editors</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>


    <!-- Main content -->
    <section class="content">
  <div class="container-fluid">
    <h5 class="mb-2">GOALS</h5>
    <div class="row">
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <!-- If you want to add an icon to the box, uncomment this
          <span class="info-box-icon bg-info"><i class="far fa-star"></i></span>
          -->
          <div class="info-box-content">
            <span class="info-box-text">Goal 1</span>
            <!-- Summernote editor -->
            <textarea id="summernote" class = "goaltext">
              Place <em>some</em> <u>text</u> <strong>here</strong>
            </textarea>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <!-- If you want to add an icon to the box, uncomment this
          <span class="info-box-icon bg-info"><i class="far fa-star"></i></span>
          -->
          <div class="info-box-content">
            <span class="info-box-text">Goal 2</span>
            <!-- Summernote editor -->
            <textarea id="summernote" class = "goaltext">
              Place <em>some</em> <u>text</u> <strong>here</strong>
            </textarea>
          </div>
        </div>
      </div>   
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <!-- If you want to add an icon to the box, uncomment this
          <span class="info-box-icon bg-info"><i class="far fa-star"></i></span>
          -->
          <div class="info-box-content">
            <span class="info-box-text">Goal 3</span>
            <!-- Summernote editor -->
            <textarea id="summernote" class = "goaltext">
              Place <em>some</em> <u>text</u> <strong>here</strong>
            </textarea>
          </div>
        </div>
      </div>    
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <!-- Add New Student Form -->
          <form method="post" action="">
            <label for="new_student_name">New Student Name:</label>
            <input type="text" id="new_student_name" name="new_student_name">
            <input type="submit" name="add_new_student" value="Add New Student">
          </form>
          
          <!-- Form to create a new group -->
          <form method="post">
            <input type="text" name="group_name" placeholder="Group Name">
            <button type="submit" name="create_group">Create Group</button>
          </form>

          <!-- List groups with edit options -->
<?php foreach ($groups as $group): ?>
    <form method="post">
        <input type="hidden" name="group_id" value="<?= htmlspecialchars($group['group_id']) ?>">
        <input type="text" name="edited_group_name" value="<?= htmlspecialchars($group['group_name']) ?>">
        <button type="submit" name="edit_group">Update</button>
    </form>
<?php endforeach; ?>

<!-- Dropdown to select a group for filtering -->
<form method="post" id="group_filter_form">
    <select name="selected_group_id" onchange="document.getElementById('group_filter_form').submit();">
        <option value="all_students" <?= (!isset($_POST['selected_group_id']) || $_POST['selected_group_id'] == "all_students") ? "selected" : "" ?>>All Students</option>
        <?php foreach ($groups as $group): ?>
            <option value="<?= htmlspecialchars($group['group_id']) ?>" <?= (isset($_POST['selected_group_id']) && $_POST['selected_group_id'] == $group['group_id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($group['group_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

</div>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title">STUDENT LIST</h3><br>

          <!-- Toggle Button -->
          <form method="post">
            <button type="submit" name="toggle_view"><?= $showArchived ? 'Show Active Students' : 'Show Archived Students' ?></button>
            <input type="hidden" name="show_archived" value="<?= $showArchived ? '0' : '1' ?>">
          </form>

          <?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>


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

                  <?php if (!$isGroupFilterActive): ?>
                    <form method="post" style="display: inline; margin-right: 10px;">
                      <input type="hidden" name="student_id_to_toggle" value="<?= $student['student_id'] ?>">
                      <button type="submit" name="<?= $showArchived ? 'unarchive_student' : 'archive_student' ?>">
                        <?= $showArchived ? 'Unarchive' : 'Archive' ?>
                      </button>
                    </form>

                    <form method="post" style="display: flex; align-items: center;">
                      <select name="group_id" style="margin-right: 5px;">
                        <?php foreach ($groups as $group): ?>
                          <option value="<?= htmlspecialchars($group['group_id']) ?>">
                            <?= htmlspecialchars($group['group_name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                      <button type="submit" name="assign_to_group">Assign to Group</button>
                    </form>
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

<?php if (!empty($message)): ?>
  <script type="text/javascript">
    alert("<?= addslashes($message) ?>");
  </script>
<?php endif; ?>


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

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- Summernote -->
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
      $('.goaltext').summernote({
        toolbar: [
          // Only include buttons for font type and basic styling
          ['font', ['fontname']], // Font type
          ['style', ['bold', 'italic', 'underline']] // Bold, italic, underline
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Merriweather'] // Add custom font types if needed
      });
    });
  </script>
</body>
</html>
