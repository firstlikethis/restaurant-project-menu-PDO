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

// Handle Add User
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $full_name = $_POST['full_name'];
    $nick_name = $_POST['nick_name'];
    $tels = $_POST['tels'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, nick_name, tels, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $nick_name, $tels, $role]);
        $alertType = "success";
        $alertMessage = "User added successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error adding user: " . $e->getMessage();
    }
}

// Handle Edit User
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $nick_name = $_POST['nick_name'];
    $tels = $_POST['tels'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, nick_name = ?, tels = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $full_name, $nick_name, $tels, $role, $id]);
        $alertType = "success";
        $alertMessage = "User updated successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error updating user: " . $e->getMessage();
    }
}

// Handle Reset Password
if (isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password'])) {
            if ($new_password === $confirm_new_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_new_password, $id]);
                $alertType = "success";
                $alertMessage = "Password reset successfully!";
            } else {
                $alertType = "error";
                $alertMessage = "New password and confirm password do not match!";
            }
        } else {
            $alertType = "error";
            $alertMessage = "Old password is incorrect!";
        }
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error resetting password: " . $e->getMessage();
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $alertType = "success";
        $alertMessage = "User deleted successfully!";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertMessage = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch users from the database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch counts for summary
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) AS managers FROM users WHERE role = 'manager'");
    $totalManagers = $stmt->fetch()['managers'];

    $stmt = $pdo->query("SELECT COUNT(*) AS employees FROM users WHERE role = 'employee'");
    $totalEmployees = $stmt->fetch()['employees'];
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

include 'admin_header.php';
?>

<section class="content" style="padding-top: 20px;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Users</h3>
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
                        <div class="row mb-3">
                            <div class="col-lg-4 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?php echo $totalUsers; ?></h3>
                                        <p>Total Users</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?php echo $totalManagers; ?></h3>
                                        <p>Total Managers</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?php echo $totalEmployees; ?></h3>
                                        <p>Total Employees</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table for displaying users -->
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Nick Name</th>
                                    <th>Telephone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['full_name']; ?></td>
                                    <td><?php echo $user['nick_name']; ?></td>
                                    <td><?php echo $user['tels']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-user"
                                            data-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo $user['username']; ?>"
                                            data-full-name="<?php echo $user['full_name']; ?>"
                                            data-nick-name="<?php echo $user['nick_name']; ?>"
                                            data-tels="<?php echo $user['tels']; ?>"
                                            data-role="<?php echo $user['role']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-warning reset-password"
                                            data-id="<?php echo $user['id']; ?>">Reset Password</button>
                                        <button class="btn btn-sm btn-danger delete-user"
                                            data-id="<?php echo $user['id']; ?>">Delete</button>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">Add New
                            User</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for user details -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nick_name">Nick Name</label>
                            <input type="text" class="form-control" id="nick_name" name="nick_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tels">Telephone</label>
                            <input type="text" class="form-control" id="tels" name="tels" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
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


<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for user details -->
                    <input type="hidden" id="edit-id" name="id">
                    <div class="form-group">
                        <label for="edit-username">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-full_name">Full Name</label>
                        <input type="text" class="form-control" id="edit-full_name" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-nick_name">Nick Name</label>
                        <input type="text" class="form-control" id="edit-nick_name" name="nick_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-tels">Telephone</label>
                        <input type="text" class="form-control" id="edit-tels" name="tels" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-role">Role</label>
                        <select class="form-control" id="edit-role" name="role" required>
                            <option value="employee">Employee</option>
                            <option value="manager">Manager</option>
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

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Changed modal size to large -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <!-- Form fields for resetting password -->
                    <input type="hidden" id="reset-id" name="id">
                    <div class="form-group">
                        <label for="old_password">Old Password</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_new_password"
                            name="confirm_new_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="reset_password">Save changes</button>
                </div>
            </form>
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

    // Open the Edit User Modal with pre-filled data
    $('.edit-user').on('click', function() {
        var id = $(this).data('id');
        var username = $(this).data('username');
        var fullName = $(this).data('full-name');
        var nickName = $(this).data('nick-name');
        var tels = $(this).data('tels');
        var role = $(this).data('role');

        $('#edit-id').val(id);
        $('#edit-username').val(username);
        $('#edit-full_name').val(fullName);
        $('#edit-nick_name').val(nickName);
        $('#edit-tels').val(tels);
        $('#edit-role').val(role);

        $('#editUserModal').modal('show');
    });

    // Handle delete button click
    $('.delete-user').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var deleteUrl = 'manage_user.php?delete=' + id;

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

    // Open the Reset Password Modal with pre-filled data
    $('.reset-password').on('click', function() {
        var id = $(this).data('id');

        $('#reset-id').val(id);

        $('#resetPasswordModal').modal('show');
    });
});
</script>
</body>

</html>