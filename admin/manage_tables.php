<?php
session_start();
require_once '../includes/config.php'; // Adjust the path as necessary to match your project structure

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables for SweetAlert2
$alertType = "";
$alertMessage = "";

// Handle Add Table
if (isset($_POST['add'])) {
    $table_number = $_POST['table_number'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("INSERT INTO tables (table_number, status) VALUES (?, ?)");
        $stmt->execute([$table_number, $status]);
        $alertType = "success";
        $alertMessage = "Table added successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error adding table: " . $e->getMessage();
    }
}

// Handle Edit Table
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $table_number = $_POST['table_number'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE tables SET table_number = ?, status = ? WHERE id = ?");
        $stmt->execute([$table_number, $status, $id]);
        $alertType = "success";
        $alertMessage = "Table updated successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error updating table: " . $e->getMessage();
    }
}

// Handle Delete Table
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "Table deleted successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error deleting table: " . $e->getMessage();
    }
}

// Fetch tables and their latest order status
try {
    $stmt = $pdo->query("
        SELECT t.id, t.table_number, t.status, o.status AS order_status
        FROM tables t
        LEFT JOIN (
            SELECT table_id, status
            FROM orders
            WHERE status IN ('pending', 'processing', 'completed', 'cancelled')
            ORDER BY order_date DESC
        ) o ON t.id = o.table_id
        GROUP BY t.id
        ORDER BY t.table_number ASC
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<!-- Style and script setup -->
<style>
    .status-circle {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }
    .table-card {
        position: relative;
        height: 150px; /* Adjust this value to increase the height */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .button-group {
        display: flex;
        justify-content: center;
        margin-bottom: 10px;
    }
    .button-group button {
        margin: 0 5px;
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Tables</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($tables as $table): ?>
                            <div class="col-md-4">
                                <div class="card mb-3 table-card">
                                    <div class="card-body">
                                        <h5 class="card-title">TABLE: <?= htmlspecialchars($table['table_number']) ?></h5>
                                        <div class="status-circle" style="background-color: <?= $table['status'] === 'occupied' ? 'red' : 'green' ?>;"></div>
                                        <p class="card-text">Status: <?= ucfirst($table['status']) ?></p>
                                        <p class="card-text">Order Status: <?= ucfirst($table['order_status'] ?? 'No Order') ?></p>
                                    </div>
                                    <div class="button-group">
                                        <button class="btn btn-primary btn-sm edit-btn" data-id="<?= htmlspecialchars($table['id']) ?>" data-number="<?= htmlspecialchars($table['table_number']) ?>" data-status="<?= htmlspecialchars($table['status']) ?>">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-id="<?= htmlspecialchars($table['id']) ?>">Delete</button>
                                        <?php if ($table['order_status']): ?>
                                        <button class="btn btn-secondary btn-sm view-order-btn" data-id="<?= htmlspecialchars($table['id']) ?>">View Order</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addTableModal">Add New Table</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

 <!-- Add Table Modal -->
<div class="modal fade" id="addTableModal" tabindex="-1" role="dialog" aria-labelledby="addTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTableModalLabel">Add Table</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="table_number">Table Number</label>
                        <input type="number" class="form-control" id="table_number" name="table_number" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="free">Free</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Table Modal -->
<div class="modal fade" id="editTableModal" tabindex="-1" role="dialog" aria-labelledby="editTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTableModalLabel">Edit Table</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit_table_number">Table Number</label>
                        <input type="number" class="form-control" id="edit_table_number" name="table_number" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="free">Free</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="edit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">View Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="order-details">
                <!-- Order details will be loaded here dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

<script>
    $(document).ready(function() {
        // Show success or error alerts
        var alertType = "<?php echo $alertType; ?>";
        var alertMessage = "<?php echo $alertMessage; ?>";
        if (alertType && alertMessage) {
            Swal.fire({
                icon: alertType,
                title: alertType === 'success' ? 'Success' : 'Error',
                text: alertMessage,
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Open the Edit Table Modal with pre-filled data
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var tableNumber = $(this).data('number');
            var status = $(this).data('status');

            $('#edit-id').val(id);
            $('#edit_table_number').val(tableNumber);
            $('#edit_status').val(status);

            $('#editTableModal').modal('show');
        });

        // Handle delete button click
        $('.delete-btn').on('click', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('', { delete: true, id: id }, function(response) {
                        $('body').html(response);
                    });
                }
            });
        });

        // View Order button click
        $('.view-order-btn').on('click', function () {
            var table_id = $(this).data('id');
            $.ajax({
                url: 'fetch_order.php', // Ensure you have a PHP file to handle this AJAX request
                method: 'GET',
                data: { table_id: table_id },
                success: function (response) {
                    $('#order-details').html(response);
                    $('#viewOrderModal').modal('show');
                }
            });
        });
    });
</script>

</body>
</html>
