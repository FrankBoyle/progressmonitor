<?php
include("./users/auth_session.php");
?>

<?php
session_start(); // Start the session

$servername = "localhost";
$username = "AndersonSchool";
$password = "SpecialEd69$";
$dbname = "AndersonSchool";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedTable = $_POST['selected_table'] ?? $_SESSION['selected_table'] ?? 'JaylaBrazzle1'; // Set a default table name

//echo "Updating records in table: $selectedTable<br>";

// Handle updates for ID, date, score, and baseline
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    foreach ($_POST['id'] as $key => $id) {
        $date = $_POST["date"][$key];
        $score = $_POST["score"][$key];
        $baseline = $_POST["baseline"][$key];

        $update_sql = "UPDATE $selectedTable SET date='$date', score='$score', baseline='$baseline' WHERE id=$id";
       
        if ($conn->query($update_sql) !== TRUE) {
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Handle goal update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_goal'])) {
    $newGoal = $_POST["edit_goal"];
    
    // Update the goal in the database
    $updateGoalSql = "UPDATE $selectedTable SET goal='$newGoal' WHERE 1";
    if ($conn->query($updateGoalSql) !== TRUE) {
        echo "Error updating goal: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select_table'])) {
    // Handle student selection
    $selectedTable = $_POST['selected_table'];
    $_SESSION['selected_table'] = $selectedTable; // Store the selected table value in a session variable
}

// Fetch data for the table
$tableDataArray = array();
$tableSql = "SELECT id, date, score, baseline, goal FROM $selectedTable";
$tableResult = $conn->query($tableSql);
if ($tableResult->num_rows > 0) {
    while ($row = $tableResult->fetch_assoc()) {
        $tableDataArray[] = $row;
    }
}

// Fetch and store data from the database for the chart
$chartDataArray = array();
$chartSql = "SELECT date, score FROM $selectedTable";
$chartResult = $conn->query($chartSql);
if ($chartResult->num_rows > 0) {
    while ($row = $chartResult->fetch_assoc()) {
        $chartDataArray[] = array(
            'x' => $row['date'],     // Use the 'date' column as the x-variable
            'y' => $row['score'],   // Use the 'score' column as the first y-variable
        );
    }
}

$chartDataArray1 = array();
$chartSql1 = "SELECT date, baseline FROM $selectedTable";
$chartResult1 = $conn->query($chartSql1);
if ($chartResult1->num_rows > 0) {
    while ($row = $chartResult1->fetch_assoc()) {
        $chartDataArray1[] = array(
            'x1' => $row['date'],     // Use the 'date' column as the x-variable
            'y1' => $row['baseline'] // Use the 'baseline' column as the second y-variable
        );
    }
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
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- jsGrid -->
  <link rel="stylesheet" href="../plugins/jsgrid/jsgrid.min.css">
  <link rel="stylesheet" href="../plugins/jsgrid/jsgrid-theme.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.min.css">
  <script src="
https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/apexcharts@3.41.1/dist/apexcharts.min.css
" rel="stylesheet">

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

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
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
                <a href="./starter.php" class="nav-link active">
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
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Progress Monitoring Starter Page</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Landing Page</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title"><?php echo($selectedTable);?></h5>

                <h1>Progress Monitoring Testing Area</h1>

                  <form method="post">
                    <select name="selected_table">
                      <option value='JaylaBrazzle1'<?= $selectedTable === 'JaylaBrazzle1' ? ' selected' : '' ?>>JaylaBrazzle1</option>
                      <option value='JaylaBrazzle2'<?= $selectedTable === 'JaylaBrazzle2' ? ' selected' : '' ?>>JaylaBrazzle2</option>
                      <option value='JaylaBrazzle3'<?= $selectedTable === 'JaylaBrazzle3' ? ' selected' : '' ?>>JaylaBrazzle3</option>
                      <option value='JaylaBrazzle4'<?= $selectedTable === 'JaylaBrazzle4' ? ' selected' : '' ?>>JaylaBrazzle4</option>
                      <option value='NicoleElkins1'<?= $selectedTable === 'NicoleElkins1' ? ' selected' : '' ?>>NicoleElkins1</option>
                      <option value='NicoleElkins2'<?= $selectedTable === 'NicoleElkins2' ? ' selected' : '' ?>>NicoleElkins2</option>
                      <option value='NicoleElkins3'<?= $selectedTable === 'NicoleElkins3' ? ' selected' : '' ?>>NicoleElkins3</option>
                      <option value='NicoleElkins4'<?= $selectedTable === 'NicoleElkins4' ? ' selected' : '' ?>>NicoleElkins4</option>
                    </select>
                    <input type="submit" name="select_table" value="Select Student">
                  </form>


<!-- Form for updating the goal -->
                  <form method="post" action="">
                    <?php
                    // Fetch the current goal value from the database
                      $goalSql = "SELECT goal FROM $selectedTable LIMIT 1";
                      $goalResult = $conn->query($goalSql);

                        if ($goalResult && $goalResult->num_rows > 0) {
                          $goalRow = $goalResult->fetch_assoc();
                          $currentGoal = $goalRow["goal"];
                          echo '<label for="edit_goal">Edit Goal: </label>';
                          echo '<textarea name="edit_goal" id="edit_goal" rows="5" cols="40">' . htmlspecialchars($currentGoal) . '</textarea>';
                        }
                    ?>
                    <input type="submit" name="save_goal" value="Save Goal">
                  </form>


<!-- Form for updating ID, date, score, and baseline -->
                  <form method='post' action="">
                    <table border='1'>
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Baseline</th>
                      </tr>
                    <?php
                      foreach ($tableDataArray as $row) {
                        echo "<tr>";
                        echo "<td><input type='hidden' name='id[]' value='{$row["id"]}'>{$row["id"]}</td>";
                        echo "<td><input type='date' name='date[]' value='{$row["date"]}'></td>";
                        echo "<td><input type='number' name='score[]' value='{$row["score"]}'></td>";
                        echo "<td><input type='number' name='baseline[]' value='{$row["baseline"]}'></td>";
                        echo "</tr>";
                      }
                    ?>
                    </table>
                    <input type='submit' name='update' value='Update'>
                  </form>
  

                <a href="#" class="card-link">Card link</a>
                <a href="#" class="card-link">Another link</a>
              </div>
            </div>

            <div class="card card-primary card-outline">
              <div class="card-body">
                <h5 class="card-title">Graph</h5>
                <div id="chart"></div>
    

                <a href="#" class="card-link">Card link</a>
                <a href="#" class="card-link">Another link</a>
              </div>
            </div><!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header">
                <h5 class="m-0">Featured</h5>
              </div>
              <div class="card-body">
                <h6 class="card-title">Special title treatment</h6>
                <!--
                   <script>
        // Processed PHP data
        const chartData = <?php echo json_encode($chartDataArray); ?>;
        
        // Transform data for ApexCharts
        const series = Object.keys(chartData[0])
            .filter(key => key !== 'x') // Exclude the x-variable
            .map(key => ({
                name: key,
                data: chartData.map(item => [item.x, item[key]])
            }));
        
        // Create ApexCharts instance
        const options = {
            chart: {
                type: 'line'
            },
            xaxis: {
                type: 'datetime' // If your x-variable is a date, use 'datetime' type
            },
            series: series
        };
        
        const chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script>
      -->

                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div>

            <div class="card card-primary card-outline">
              <div class="card-header">
                <h5 class="m-0">Featured</h5>
              </div>
              <div class="card-body">
                <h6 class="card-title">Special title treatment</h6>

                <div id="chart"></div>
<script>
    // Processed PHP data
    const chartData = <?php echo json_encode($chartDataArray); ?>;
    const baselineData = <?php echo json_encode($chartDataArray1); ?>;

    // Transform the date strings to Date objects
    chartData.forEach(item => {
        item.x = new Date(item.x).getTime();
    });

    baselineData.forEach(item => {
        item.x1 = new Date(item.x1).getTime();
    });

    const scatterSeries = {
        name: 'Score',
        type: 'scatter',
        data: chartData.map(item => ({
            x: item.x,
            y: item.y1
        })),
        markers: {
            size: 6
        }
    };

    const baselineSeries = {
        name: 'Baseline',
        type: 'line',
        data: baselineData.map(item => ({
            x: item.x1,
            y: item.y1
        })),
        // Customizing the line series
        strokeDashArray: 3,
        colors: ['#FF0000']
    };

    // Create ApexCharts instance for the scatter plot and baseline line
    const options = {
        chart: {
            type: 'scatter'
        },
        xaxis: {
            type: 'datetime'
        },
        series: [scatterSeries, baselineSeries]
    };

    const chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>



                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
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
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
