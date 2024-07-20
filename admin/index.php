<?php
    session_start();
    require_once '../includes/config.php';

    // Redirect if not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Fetch statistics from the database
    try {
        // Total Users
        $stmt = $pdo->query("SELECT COUNT(*) AS totalUsers FROM users");
        $totalUsers = $stmt->fetch()['totalUsers'];

        // Total Orders
        $stmt = $pdo->query("SELECT COUNT(*) AS totalOrders FROM orders");
        $totalOrders = $stmt->fetch()['totalOrders'];

        // Total Sales
        $stmt = $pdo->query("SELECT SUM(total_price) AS totalSales FROM orders");
        $totalSales = $stmt->fetch()['totalSales'];
        $totalSales = $totalSales ? '฿' . number_format($totalSales, 2) : '฿0.00';

    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }

    include 'admin_header.php';
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
        <span class="brand-text font-weight-light">Admin Dashboard</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-th"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Users</p>
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
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $totalOrders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cart-plus"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $totalSales; ?></h3>
                            <p>Total Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<footer class="main-footer">
    <strong>&copy; 2021 <a href="#">Your Company</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.1.0
    </div>
</footer>

<!-- jQuery -->
<script src="../assets/js/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../assets/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="../assets/js/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        console.log('Logout button clicked');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });
});
</script>
</body>
</html>
