<?php
session_start();
require_once '../includes/config.php'; // Adjust the path as necessary to match your project structure

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Update Order Status
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = '';

    if ($currentStatus == 'Pending') {
        $newStatus = 'Processing';
    } elseif ($currentStatus == 'Processing') {
        $newStatus = 'Completed';
    }

    if ($newStatus) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            $alertType = "success";
            $alertMessage = "Order status updated to " . $newStatus;
        } catch (PDOException $e) {
            $alertType = "error";
            $alertMessage = "Error updating order status: " . $e->getMessage();
        }
    }
}

// Handle Downgrade Order Status
if (isset($_POST['downgrade_status'])) {
    $id = $_POST['id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = '';

    if ($currentStatus == 'Processing') {
        $newStatus = 'Pending';
    } elseif ($currentStatus == 'Completed') {
        $newStatus = 'Processing';
    }

    if ($newStatus) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            $alertType = "success";
            $alertMessage = "Order status downgraded to " . $newStatus;
        } catch (PDOException $e) {
            $alertType = "error";
            $alertMessage = "Error downgrading order status: " . $e->getMessage();
        }
    }
}

// Handle Cancel Order
if (isset($_POST['cancel_order'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "Order cancelled successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error cancelling order: " . $e->getMessage();
    }
}

// Fetch orders from the database
try {
    $stmt = $pdo->query("SELECT orders.*, menus.name as menu_name, tables.table_number 
                        FROM orders 
                        LEFT JOIN menus ON orders.menu_id = menus.id 
                        LEFT JOIN tables ON orders.table_id = tables.id 
                        ORDER BY orders.id DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $totals = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'cancelled' => 0];
    foreach ($orders as $order) {
        $totals[strtolower($order['status'])]++;
    }
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<style>
    .progress-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        position: relative;
    }

    .progress-step {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 14px;
        color: white;
        z-index: 1;
    }

    .progress-bar {
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        height: 2px;
        background-color: lightgray;
        z-index: 0;
    }

    .pending {
        background-color: gray;
    }

    .processing {
        background-color: green;
    }

    .completed {
        background-color: green;
    }

    .cancelled {
        background-color: red;
    }

    .progress-text {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-top: 10px;
    }

    .small-box-footer:hover {
        background: rgba(0, 0, 0, 0.15);
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <!-- Summary Box -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totals['pending']; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <a href="#" class="small-box-footer filter-btn" data-filter="pending">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $totals['processing']; ?></h3>
                        <p>Processing Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <a href="#" class="small-box-footer filter-btn" data-filter="processing">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $totals['completed']; ?></h3>
                        <p>Completed Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer filter-btn" data-filter="completed">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totals['cancelled']; ?></h3>
                        <p>Cancelled Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer filter-btn" data-filter="cancelled">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row" id="orders-container">
            <!-- Orders will be loaded here dynamically -->
        </div>
    </div>
</section>

<!-- Order Details Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body order-details">
                <!-- Order details will be loaded here dynamically -->
            </div>
            <div class="modal-footer">
                <form method="post" action="">
                    <input type="hidden" name="id" id="order-id">
                    <input type="hidden" name="current_status" id="current-status">
                    <button type="submit" name="update_status" class="btn btn-primary update-status">Update Status</button>
                    <button type="submit" name="downgrade_status" class="btn btn-secondary downgrade-status">Downgrade Status</button>
                    <button type="submit" name="cancel_order" class="btn btn-danger cancel-order">Cancel Order</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

<script>
$(document).ready(function() {
    function loadOrders(filter) {
        $('#orders-container').empty();
        <?php foreach ($orders as $order): ?>
        if (filter === '<?php echo strtolower($order['status']); ?>') {
            var progressStep1Class = '<?php echo ($order['status'] == 'Pending' || $order['status'] == 'Completed') ? 'completed' : 'processing'; ?>';
            var progressStep2Class = '<?php echo ($order['status'] == 'Processing' || $order['status'] == 'Completed') ? 'completed' : 'pending'; ?>';
            var progressStep3Class = '<?php echo ($order['status'] == 'Completed') ? 'completed' : 'pending'; ?>';

            var progressBar = `
                <div class="progress-container">
                    <div class="progress-bar"></div>
                    <div class="progress-step ${progressStep1Class}">${'<?php echo ($order['status'] == 'Pending' || $order['status'] == 'Processing' || $order['status'] == 'Completed') ? '✓' : '1'; ?>'}</div>
                    <div class="progress-step ${progressStep2Class}">${'<?php echo ($order['status'] == 'Processing' || $order['status'] == 'Completed') ? '✓' : '2'; ?>'}</div>
                    <div class="progress-step ${progressStep3Class}">${'<?php echo ($order['status'] == 'Completed') ? '✓' : '3'; ?>'}</div>
                </div>
                <div class="progress-text">
                    <span>Pending</span>
                    <span>Processing</span>
                    <span>Completed</span>
                </div>`;

            var orderBox = `<div class="col-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div><strong>Order #<?php echo $order['id']; ?></strong></div>
                        <div><strong>Table Number:</strong> <?php echo $order['table_number']; ?></div>
                        <div><strong>Menu Item:</strong> <?php echo $order['menu_name']; ?></div>
                        <div><strong>Quantity:</strong> <?php echo $order['quantity']; ?></div>
                        <div><strong>Order Date:</strong> <?php echo $order['order_date']; ?></div>
                        <div><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></div>
                        <div><strong>Paid Status:</strong> <?php echo $order['order_paid_status']; ?></div>
                        ${'<?php echo $order['status']; ?>' === 'Cancelled' ? '<div class="text-danger">This order is cancelled</div>' : progressBar}
                        <button class="btn btn-sm btn-primary view-order mt-3" 
                                data-id="<?php echo $order['id']; ?>" 
                                data-status="<?php echo $order['status']; ?>" 
                                data-menu-id="<?php echo $order['menu_id']; ?>" 
                                data-quantity="<?php echo $order['quantity']; ?>" 
                                data-order_date="<?php echo $order['order_date']; ?>" 
                                data-total-price="<?php echo $order['total_price']; ?>" 
                                data-paid-status="<?php echo $order['order_paid_status']; ?>">View Details</button>
                    </div>
                </div>
            </div>`;
            $('#orders-container').append(orderBox);
        }
        <?php endforeach; ?>
    }

    $('.filter-btn').on('click', function() {
        var filter = $(this).data('filter');
        loadOrders(filter);
    });

    // Initially load new orders
    loadOrders('pending');

    // Open the View Order Modal with pre-filled data
    $(document).on('click', '.view-order', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        var menuId = $(this).data('menu-id');
        var quantity = $(this).data('quantity');
        var order_date = $(this).data('order_date');
        var totalPrice = $(this).data('total-price');
        var paidStatus = $(this).data('paid-status');

        // Set hidden inputs
        $('#order-id').val(id);
        $('#current-status').val(status);

        // Dynamically show/hide buttons based on status
        if (status === 'Pending') {
            $('.update-status').show();
            $('.downgrade-status').hide();
            $('.cancel-order').show();
        } else if (status === 'Processing') {
            $('.update-status').show();
            $('.downgrade-status').show();
            $('.cancel-order').show();
        } else if (status === 'Completed') {
            $('.update-status').hide();
            $('.downgrade-status').hide();
            $('.cancel-order').hide();
        } else if (status === 'Cancelled') {
            $('.update-status').hide();
            $('.downgrade-status').hide();
            $('.cancel-order').hide();
        }

        // Load order details dynamically
        $('.order-details').html(
            `<div><strong>Order #</strong> ${id}</div>
             <div><strong>Table Number:</strong> ${menuId}</div>
             <div><strong>Menu Item:</strong> ${quantity}</div>
             <div><strong>Quantity:</strong> ${totalPrice}</div>
             <div><strong>Order Date:</strong> ${order_date}</div>
             <div><strong>Total Price:</strong> $${totalPrice}</div>
             <div><strong>Paid Status:</strong> ${paidStatus}</div>`
        );

        $('#orderModal').modal('show');
    });
});
</script>
</body>
</html>
