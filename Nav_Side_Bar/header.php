<?php
// Ensure variables are set
$pageTitle = isset($pageTitle) ? $pageTitle : 'SmartNote';
$userName = isset($userName) ? $userName : (isset($user['nama']) ? $user['nama'] : 'Notulis');

// Handle photo logic
// Priority: $userPhoto (global/session) -> $userData['foto'] (fetched user) -> $foto (profile page) -> $user['foto'] (edit profile)
$displayPhoto = null;
if (isset($userPhoto)) $displayPhoto = $userPhoto;
elseif (isset($userData['foto'])) $displayPhoto = $userData['foto'];
elseif (isset($foto)) $displayPhoto = $foto;
elseif (isset($user['foto'])) $displayPhoto = $user['foto'];
// Fallback if none
if (!$displayPhoto && isset($_SESSION['foto'])) $displayPhoto = $_SESSION['foto'];

?>
<!-- Navbar (Header / Top Bar) -->
<div class="header-admin">
    <!-- Mobile Toggle -->
    <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Page Title -->
    <div class="page-title"><?= htmlspecialchars($pageTitle) ?></div>
    
    <!-- Right Section (User Info) -->
    <div class="right-section">
        <div class="d-none d-md-block text-end me-2">
            <div class="fw-bold small"><?= htmlspecialchars($userName) ?></div>
            <small class="text-muted" style="font-size: 0.75rem;">Notulis</small>
        </div>
        
        <?php if ($displayPhoto && file_exists("../file/" . $displayPhoto)): ?>
            <img src="../file/<?= htmlspecialchars($displayPhoto) ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
        <?php else: ?>
            <i class="bi bi-person-circle fs-2 text-secondary"></i>
        <?php endif; ?>
    </div>
</div>
