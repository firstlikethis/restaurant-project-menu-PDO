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

// Handle Add Category
if (isset($_POST['add'])) {
    $name = $_POST['name'];

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        $alertType = "success";
        $alertMessage = "Category added successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error adding category: " . $e->getMessage();
    }
}

// Handle Edit Category
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];

    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        $alertType = "success";
        $alertMessage = "Category updated successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error updating category: " . $e->getMessage();
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "Category deleted successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error deleting category: " . $e->getMessage();
    }
}

// Fetch categories from the database
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch counts for summary
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM categories");
    $totalCategories = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <!-- Summary Box -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalCategories; ?></h3>
                        <p>Total Categories</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Categories</h3>
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


                        <!-- Table for displaying categories -->
                        <table id="categoriesTable" class="table table-bordered table-hover text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo $category['name']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-category"
                                            data-id="<?php echo $category['id']; ?>"
                                            data-name="<?php echo $category['name']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-category"
                                            data-id="<?php echo $category['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal">Add New
                            Category</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for category details -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for category details -->
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
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
    $('#categoriesTable').DataTable({
        "dom": 't', // This will hide the search and entries elements
        "paging": false // This will hide pagination
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

    // Open the Edit Category Modal with pre-filled data
    $('.edit-category').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#edit-id').val(id);
        $('#edit-name').val(name);

        $('#editCategoryModal').modal('show');
    });

    // Handle delete button click
    $('.delete-category').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var deleteUrl = 'manage_categories.php?delete=' + id;

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