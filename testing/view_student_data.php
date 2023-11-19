<?php
include ('./users/fetch_data.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$metadata_id = !empty($_GET['metadata_id']) ? $_GET['metadata_id'] : null;
//echo '<pre>';
//print_r($_GET);
//echo '</pre>';

foreach ($students as $student) {
    if ($student['student_id'] == $studentId) { // If the IDs match
        $studentName = $student['name']; // Get the student name
        break;
    }
}

if (isset($_GET['metadata_id'])) {
    $selectedMetadataId = $_GET['metadata_id'];

    // Now fetch the corresponding category name based on this metadata_id
    foreach ($metadataEntries as $metadataEntry) {
        if ($metadataEntry['metadata_id'] == $selectedMetadataId) {
            $selectedCategoryName = $metadataEntry['category_name'];
            break; // We found our category, no need to continue the loop
        }
    }
} else {
    // Optional: Handle cases where no metadata_id is specified, if needed
    // $selectedCategoryName = "Default Category or message"; // for example
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $studentName; ?></title>
    
    <!-- DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
   
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <script src="student_data.js"  defer></script>
    
    <script>
    // Get the metadata_id from the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const metadata_id = urlParams.get('metadata_id');
    var scoreNamesFromPHP = <?php echo json_encode($scoreNames); ?>;
    </script>

<style>
    #dataTable_wrapper .col-md-6:eq(0) {
        position: relative;
        z-index: 1000;
    }

    .editable {
        cursor: pointer;
    }

    .editable.editing {
        background-color: #f4f4f4;
    }

    .editable input {
        border: none;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        background-color: transparent;
        outline: none;
    }

    .fixed-width {
        width: 100px; /* Set your desired width here */
    }

    .fixed-width-cell {
        width: 100px; /* Set your desired width here */
        white-space: nowrap; /* Prevent text from wrapping */
        padding: 0;
        margin: 0;
    }
</style>

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
            <h1><?php echo $studentName; ?> Performance Data - <?php echo $selectedCategoryName; ?></h1>
            <a href="home.php" class="btn btn-primary">Home</a>  
        </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="./home.php">Home</a></li>
              <li class="breadcrumb-item active">Text Editors</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>


    <section class="content">
      <div class="row">
         <div class="col-md-4 col-sm-6 col-12">
          <div class="card card-outline card-info">
            <div class="card-header">
              <h3 class="card-title">
                Categories
              </h3><br>
                <?php foreach ($metadataEntries as $metadataEntry): ?>
                  <a href="?student_id=<?php echo $student_id; ?>&metadata_id=<?php echo $metadataEntry['metadata_id']; ?>">
                    <?php echo $metadataEntry['category_name']; ?>
                  </a><br>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </section>    

      
    <!-- Main content -->
    <section class="content">
    <div class="card card-outline card-info">
  <div class="card-header">
    <h3 class="card-title">Goals</h3>
  </div>
  <div class="card-body">
        <div class="row">
         <div class="col-md-4 col-sm-6 col-12">
          <div class="info-box">
            <!-- If you want to add an icon to the box, uncomment this
              <span class="info-box-icon bg-info"><i class="far fa-star"></i></span>
            -->
            <div class="info-box-content">
              <span class="info-box-text">Goal 1</span>
               <!-- Summernote editor -->
              <textarea id="summernote1" class = "goaltext">
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
            <textarea id="summernote2" class = "goaltext">
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
            <textarea id="summernote3" class = "goaltext">
              Place <em>some</em> <u>text</u> <strong>here</strong>
            </textarea>
          </div>
        </div>
      </div>    
    </div>
  </div>
</section>

<div>
<input type="hidden" id="schoolIdInput" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>">
<input type="hidden" id="currentStudentId" value="<?php echo htmlspecialchars($student_id); ?>" />
<input type="hidden" id="currentWeekStartDate" value="<?php echo htmlspecialchars($currentWeekStartDate); ?>" />
</div>   

<section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="card card-outline card-info">
            <div class="card-header">
              <h3 class="card-title"></h3>

                <table border="1" id="dataTable">
                  <thead>
                    <tr>
                      <th>Date</th>
                        <?php 
                          foreach ($scoreNames as $category => $values) {
                            if (is_array($values)) {
                              foreach ($values as $score) {
                                echo "<th>" . htmlspecialchars($score) . "</th>";
                              }
                            } else {
                              echo "<th>" . htmlspecialchars($values) . "</th>";
                            }
                          }
                          ?>
                      <th>Action</th>
                    </tr>
                  </thead>
                <tbody>
          
                <?php if (empty($performanceData)): ?>
                  <tr>
                    <td colspan="11">No Data Found. Click "Add Data Row" to add new data.</td>
                  </tr>
                <?php else: ?>
                <?php foreach ($performanceData as $data): ?>
                  <tr data-performance-id="<?php echo $data['performance_id']; ?>">
                    <td class="editable" data-field-name="score_date">
                        <?php echo isset($data['score_date']) ? date("m/d/Y", strtotime($data['score_date'])) : ""; ?>
                    </td>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <td class="editable" data-field-name="score<?php echo $i; ?>">
                            <?php echo isset($data['score'.$i]) ? $data['score'.$i] : ""; ?>
                        </td>
                      <?php endfor; ?>
                    <td><button class="deleteRow btn btn-block btn-primary" data-performance-id="<?php echo $data['performance_id']; ?>">Delete</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<button id="addDataRow" class="btn btn-primary">Add Data Row</button>
<input type="text" id="newRowDate" style="display: none;">
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
              <h3 class="card-title"></h3>
<!-- Existing checkboxes for column selection -->
<div id="columnSelector">
    <label>Select Columns to Display:</label>
    <?php
    foreach ($scoreNames as $category => $scores) {
        foreach ($scores as $index => $scoreName) {
            $scoreColumnName = 'score' . ($index + 1);
            $customColumnName = htmlspecialchars($scoreName); // Custom column name
            echo '<label>';
            echo '<input type="checkbox" name="selectedColumns[]" value="' . htmlspecialchars($scoreColumnName) . '"';
            echo ' data-column-name="' . $customColumnName . '">'; // Include custom name as data attribute
            echo htmlspecialchars($scoreName);
            echo '</label>';
        }
    }
    ?>
</div>

  <!--
    <label>Enter Benchmark Value:</label>
    <input type="text" id="benchmarkValue">
    <button type ="button" id="updateBenchmark" class="btn btn-primary">Update Benchmark</button>
  -->

<div id="accordion">
    <h3>Line Graph</h3>
    <div>
    <div id="chart" style="width: 1000px;"></div>
    </div>
    <h3>Bar Graph</h3>
    <div>
    <div id="barChart" style="width: 1000px;"></div>
    </div>
</div>
</div>
</div>
</div>
</div>
</section>


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