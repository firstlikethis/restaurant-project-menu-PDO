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

// Handle Add Menu Item
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $icon = $_POST['icon'];
    $link = $_POST['link'];
    $order_index = $_POST['order_index'];
    $active = $_POST['active'];

    try {
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, icon, link, order_index, active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $icon, $link, $order_index, $active]);
        $alertType = "success";
        $alertMessage = "Menu item added successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error adding menu item: " . $e->getMessage();
    }
}

// Handle Edit Menu Item
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $icon = $_POST['icon'];
    $link = $_POST['link'];
    $order_index = $_POST['order_index'];
    $active = $_POST['active'];

    try {
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, icon = ?, link = ?, order_index = ?, active = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $link, $order_index, $active, $id]);
        $alertType = "success";
        $alertMessage = "Menu item updated successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error updating menu item: " . $e->getMessage();
    }
}

// Handle Delete Menu Item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "Menu item deleted successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error deleting menu item: " . $e->getMessage();
    }
}

// Fetch menu items from the database
try {
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY order_index ASC");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch counts for summary
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM menu_items");
    $totalItems = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS active FROM menu_items WHERE active = 1");
    $activeItems = $stmt->fetch()['active'];

    $stmt = $pdo->query("SELECT COUNT(*) AS inactive FROM menu_items WHERE active = 0");
    $inactiveItems = $stmt->fetch()['inactive'];
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalItems; ?></h3>
                        <p>Total Menu Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $activeItems; ?></h3>
                        <p>Active Menu Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $inactiveItems; ?></h3>
                        <p>Inactive Menu Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Menu Items</h3>
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
                        <!-- Summary Box -->

                        <!-- Table for displaying menu items -->
                        <table class="table table-bordered table-hover text-center" style="width:100%" id="menuItems">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th>Link</th>
                                    <th>Order Index</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menuItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><i class="<?php echo $item['icon']; ?>"></i></td>
                                    <td><?php echo $item['link']; ?></td>
                                    <td><?php echo $item['order_index']; ?></td>
                                    <td><?php echo $item['active'] ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-menu-item"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo $item['name']; ?>"
                                            data-icon="<?php echo $item['icon']; ?>"
                                            data-link="<?php echo $item['link']; ?>"
                                            data-order-index="<?php echo $item['order_index']; ?>"
                                            data-active="<?php echo $item['active']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-menu-item"
                                            data-id="<?php echo $item['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addMenuItemModal">Add New Menu
                            Item</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" role="dialog" aria-labelledby="addMenuItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMenuItemModalLabel">Add Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for menu item details -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        <input type="text" class="form-control" id="icon" name="icon" required>
                    </div>
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="text" class="form-control" id="link" name="link" required>
                    </div>
                    <div class="form-group">
                        <label for="order_index">Order Index</label>
                        <input type="number" class="form-control" id="order_index" name="order_index" required>
                    </div>
                    <div class="form-group">
                        <label for="active">Active</label>
                        <select class="form-control" id="active" name="active" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
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

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuItemModal" tabindex="-1" role="dialog" aria-labelledby="editMenuItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuItemModalLabel">Edit Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for menu item details -->
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-icon">Icon</label>
                        <input type="text" class="form-control" id="edit-icon" name="icon" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-link">Link</label>
                        <input type="text" class="form-control" id="edit-link" name="link" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-order_index">Order Index</label>
                        <input type="number" class="form-control" id="edit-order_index" name="order_index" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-active">Active</label>
                        <select class="form-control" id="edit-active" name="active" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
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

<?php include 'admin_footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#menuItems').DataTable({
        "dom": 't', // This will hide the search and entries elements
        "paging": false, // This will hide pagination
        "scrollX": true // Enable horizontal scrolling
    });

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

    // Open the Edit Menu Item Modal with pre-filled data
    $('.edit-menu-item').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var icon = $(this).data('icon');
        var link = $(this).data('link');
        var orderIndex = $(this).data('order-index');
        var active = $(this).data('active');

        $('#edit-id').val(id);
        $('#edit-name').val(name);
        $('#edit-icon').val(icon);
        $('#edit-link').val(link);
        $('#edit-order_index').val(orderIndex);
        $('#edit-active').val(active);

        $('#editMenuItemModal').modal('show');
    });

    // Handle delete button click
    $('.delete-menu-item').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var deleteUrl = 'manage_menu_items.php?delete=' + id;

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>
</body>

</html>