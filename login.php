<?php 
session_start(); 

// TEST MODE - uncomment untuk debugging
$TEST_MODE = true;

if ($TEST_MODE) {
    // Test hash
    $password = "lopolo9090";
    $hash_db = "$2y$10$4DGMpOZB3r6u9Zk2L8mj7uTqY9vWxC1N2fP5sQ8rT3mU4vV5w6xY7";
    $verify = password_verify($password, $hash_db);
    
    // Test koneksi
    require_once __DIR__ . '/koneksi.php';
    $db_ok = ($conn !== null && !mysqli_connect_error());
    
    if ($db_ok) {
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        $users_count = $row['count'];
    } else {
        $users_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="css/login.css">
</head>

<!-- PENTING! Tanpa ini CSS tidak aktif -->
<body data-page="login">

<div class="auth-container">

    <!-- DEBUG INFO -->
    <?php if ($TEST_MODE): ?>
    <div style="position: fixed; top: 10px; right: 10px; background: #f8f9fa; border: 2px solid #dc3545; padding: 15px; border-radius: 5px; font-size: 12px; max-width: 300px; z-index: 9999;">
        <strong style="color: #dc3545;">🔧 DEBUG INFO</strong>
        <hr style="margin: 5px 0;">
        <div><strong>Database:</strong> 
            <?php echo $db_ok ? '<span style="color: green;">✓ Connected</span>' : '<span style="color: red;">✗ Error</span>'; ?>
        </div>
        <div><strong>Users in DB:</strong> <?php echo $users_count; ?></div>
        <div><strong>Password Hash:</strong> 
            <?php echo $verify ? '<span style="color: green;">✓ Valid</span>' : '<span style="color: red;">✗ Invalid</span>'; ?>
        </div>
        <hr style="margin: 5px 0;">
        <div style="color: #666;">Test: lopolo9090</div>
    </div>
    <?php endif; ?>

    <!-- SISI KIRI -->
    <div class="auth-sidebar">
        <h1>
            Akses <span class="text-green">notulen</span>, dokumen, dan fitur lainnya dengan mudah.
        </h1>
        <p class="lead">Silakan masuk ke akun Anda untuk melanjutkan.</p>
    </div>

    <!-- SISI KANAN -->
    <div class="auth-main">
        <div class="auth-card">
            <h3>Login</h3>

            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger small mb-3">'.htmlspecialchars($_SESSION['login_error']).'</div>';
                unset($_SESSION['login_error']);
            }
            ?>

            <form action="proses/proses_login.php" method="POST">
                <div class="form-group mb-3">
                    <label>Email atau NIK</label>
                    <input type="text" name="email" class="form-control" placeholder="Masukkan email atau NIK" required>
                </div>

                <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="auth-footer justify-content-end">
                    <button type="submit" class="btn btn-green">Login</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>
