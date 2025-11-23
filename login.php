<?php session_start(); ?>
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
    <link rel="stylesheet" href="login.css">
</head>

<!-- PENTING! Tanpa ini CSS tidak aktif -->
<body data-page="login">

<div class="auth-container">

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
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email Anda" required>
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
