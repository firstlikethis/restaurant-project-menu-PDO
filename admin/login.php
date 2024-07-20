<?php
session_start();
require_once '../includes/config.php';

if (isset($_COOKIE['remember_me'])) {
    parse_str($_COOKIE['remember_me'], $user_details);
    $_SESSION['user_id'] = $user_details['id'];
    $_SESSION['username'] = $user_details['username'];
    $_SESSION['role'] = $user_details['role'];
    header('Location: index.php');
    exit;
}

$message = '';
$type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $message = 'Login successful!';
        $type = 'success';
    } else {
        $message = 'Invalid username or password';
        $type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/adminlte.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease-in-out;
        }
        .login-box {
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Admin</b>Panel</a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>
                <form action="login.php" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember_me" id="remember_me" class="custom-control-input">
                            <label class="custom-control-label" for="remember_me">Remember Me</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8"></div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            if ('<?php echo $message; ?>' !== '') {
                setTimeout(function() {
                    Swal.fire({
                        icon: '<?php echo $type; ?>',
                        title: '<?php echo $message; ?>',
                        showConfirmButton: false,
                        timer: 1500
                    }).then((result) => {
                        if ('<?php echo $type; ?>' === 'success') {
                            window.location.href = 'index.php';
                        }
                    });
                }, 200);
            }
        });
    </script>
</body>
</html>
