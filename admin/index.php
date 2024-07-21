<?php
    session_start();
    require_once '../includes/config.php'; // Adjust the path as necessary to match your project structure

    // Redirect if not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Fetch statistics from the database
    try {
        // Database queries to fetch various statistics
        $stmt = $pdo->query("SELECT COUNT(*) AS totalUsers FROM users");
        $totalUsers = $stmt->fetch()['totalUsers'];

        $stmt = $pdo->query("SELECT COUNT(*) AS totalOrders FROM orders");
        $totalOrders = $stmt->fetch()['totalOrders'];

        $stmt = $pdo->query("SELECT SUM(total_price) AS totalSales FROM orders");
        $totalSales = $stmt->fetch()['totalSales'];
        $totalSales = $totalSales ? '฿' . number_format($totalSales, 2) : '฿0.00';

        $stmt = $pdo->query("SELECT COUNT(*) AS totalTables FROM tables WHERE status = 'free'");
        $totalTables = $stmt->fetch()['totalTables'];

        $stmt = $pdo->query("SELECT COUNT(*) AS totalOrdersToday FROM orders WHERE DATE(order_date) = CURDATE()");
        $totalOrdersToday = $stmt->fetch()['totalOrdersToday'];

        $stmt = $pdo->query("SELECT SUM(total_price) AS totalSalesToday FROM orders WHERE DATE(order_date) = CURDATE()");
        $totalSalesToday = $stmt->fetch()['totalSalesToday'];
        $totalSalesToday = $totalSalesToday ? number_format($totalSalesToday, 2).' ฿' : '0.00฿';

        $stmt = $pdo->query("SELECT COUNT(*) AS totalEmployees FROM users ");
        $totalEmployees = $stmt->fetch()['totalEmployees'];
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }

    include 'admin_header.php'; // Includes the header and navigation menu
?>

<!-- Main content with padding added -->
<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalTables; ?></h3>
                        <p>Total Tables Available</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chair"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $totalOrdersToday; ?></h3>
                        <p>Total Orders Today</p>
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
                        <h3><?php echo $totalSalesToday; ?></h3>
                        <p>Total Sales Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totalEmployees; ?></h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<?php include 'admin_footer.php'; ?>

</body>
</html>