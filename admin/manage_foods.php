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

// Handle Add Food Menu Item
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $picture = $_FILES['picture']['name'];
    $target_dir = "../assets/img/menu-foods/";
    $target_file = $target_dir . basename($picture);

    if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO menus (name, description, price, category_id, stock, picture) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category_id, $stock, $picture]);
            $alertType = "success";
            $alertMessage = "Food menu item added successfully!";
        } catch (PDOException $e) {
            $alertType = "error";
            $alertMessage = "Error adding food menu item: " . $e->getMessage();
        }
    } else {
        $alertType = "error";
        $alertMessage = "Error uploading picture.";
    }
}

// Handle Edit Food Menu Item
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $picture = $_FILES['picture']['name'];
    $target_dir = "../assets/img/menu-foods/";
    $target_file = $target_dir . basename($picture);

    if (!empty($picture) && move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
        try {
            $stmt = $pdo->prepare("UPDATE menus SET name = ?, description = ?, price = ?, category_id = ?, stock = ?, picture = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $category_id, $stock, $picture, $id]);
            $alertType = "success";
            $alertMessage = "Food menu item updated successfully!";
        } catch (PDOException $e) {
            $alertType = "error";
            $alertMessage = "Error updating food menu item: " . $e->getMessage();
        }
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE menus SET name = ?, description = ?, price = ?, category_id = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $category_id, $stock, $id]);
            $alertType = "success";
            $alertMessage = "Food menu item updated successfully!";
        } catch (PDOException $e) {
            $alertType = "error";
            $alertMessage = "Error updating food menu item: " . $e->getMessage();
        }
    }
}

// Handle Delete Food Menu Item
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "Food menu item deleted successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error deleting food menu item: " . $e->getMessage();
    }
}

// Fetch food menu items and categories from the database
try {
    $stmt = $pdo->query("SELECT menus.*, categories.name as category_name FROM menus LEFT JOIN categories ON menus.category_id = categories.id ORDER BY menus.id ASC");
    $foodItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch counts for summary
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM menus");
    $totalFoods = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS in_stock FROM menus WHERE stock > 0");
    $totalInStock = $stmt->fetch()['in_stock'];

    $stmt = $pdo->query("SELECT COUNT(*) AS out_of_stock FROM menus WHERE stock <= 0");
    $totalOutOfStock = $stmt->fetch()['out_of_stock'];

} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<style>
.circle-image {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    object-fit: cover;
}
</style>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalFoods; ?></h3>
                        <p>Total Foods</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $totalInStock; ?></h3>
                        <p>Foods In Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totalOutOfStock; ?></h3>
                        <p>Foods Out of Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
            <!-- Add more summary boxes if needed -->
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Food Menu Items</h3>
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
                        <!-- Table for displaying food menu items -->
                        <table class="table table-bordered table-hover text-center" id="foodItems" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Foods Picture</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foodItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo $item['description']; ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td><?php echo $item['stock']; ?></td>
                                    <td><img src="../assets/img/menu-foods/<?php echo $item['picture']; ?>"
                                            alt="<?php echo $item['name']; ?>" class="circle-image"></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-food-item"
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo $item['name']; ?>"
                                            data-description="<?php echo $item['description']; ?>"
                                            data-price="<?php echo $item['price']; ?>"
                                            data-category-id="<?php echo $item['category_id']; ?>"
                                            data-stock="<?php echo $item['stock']; ?>"
                                            data-picture="<?php echo $item['picture']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-food-item"
                                            data-id="<?php echo $item['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addFoodItemModal">Add New Food
                            Item</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Food Menu Item Modal -->
<div class="modal fade" id="addFoodItemModal" tabindex="-1" role="dialog" aria-labelledby="addFoodItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFoodItemModalLabel">Add Food Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Form fields for food menu item details -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stock">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group text-center">
                        <img id="addPicturePreview" class="circle-image mt-2" src="#" alt="Picture Preview"
                            style="display: none; border: 1px solid black; width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="form-group">
                        <label for="picture">Picture</label>
                        <input type="file" class="form-control mt-2" id="picture" name="picture" required>
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

<!-- Edit Food Menu Item Modal -->
<div class="modal fade" id="editFoodItemModal" tabindex="-1" role="dialog" aria-labelledby="editFoodItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFoodItemModalLabel">Edit Food Menu Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Form fields for food menu item details -->
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit-name">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit-category_id">Category</label>
                            <select class="form-control" id="edit-category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit-price">Price</label>
                            <input type="number" class="form-control" id="edit-price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit-stock">Stock</label>
                            <input type="number" class="form-control" id="edit-stock" name="stock" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" required></textarea>
                    </div>
                    <div class="form-group text-center">
                        <img id="currentPicture" class="circle-image" style="border: 1px solid black; width: 150px; height: 150px; object-fit: cover;" src="#" alt="Current Picture">
                        <img id="editPicturePreview" class="circle-image mt-2" src="#" alt="Picture Preview"
                            style="display: none;border: 1px solid black; width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="form-group">
                        <label for="edit-picture">Picture</label>
                        <input type="file" class="form-control" id="edit-picture" name="picture">
                        
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
    $('#foodItems').DataTable({
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

    // Open the Edit Food Menu Item Modal with pre-filled data
    $('.edit-food-item').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var price = $(this).data('price');
        var category_id = $(this).data('category-id');
        var stock = $(this).data('stock');
        var picture = $(this).data('picture');

        $('#edit-id').val(id);
        $('#edit-name').val(name);
        $('#edit-description').val(description);
        $('#edit-price').val(price);
        $('#edit-category_id').val(category_id);
        $('#edit-stock').val(stock);

        $('#currentPicture').attr('src', '../assets/img/menu-foods/' + picture);
        $('#currentPicture').css('filter', 'grayscale(100%)'); // Make current picture grayscale

        $('#editFoodItemModal').modal('show');
    });

    // Handle delete button click
    $('.delete-food-item').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var deleteUrl = 'manage_foods.php?delete=' + id;

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

    // Preview picture before upload in Add Modal
    $('#picture').on('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#addPicturePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Preview picture before upload in Edit Modal
    $('#edit-picture').on('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#currentPicture').css('filter', 'grayscale(100%)'); // Make current picture grayscale
            $('#editPicturePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(this.files[0]);
    });
});
</script>
</body>

</html>
