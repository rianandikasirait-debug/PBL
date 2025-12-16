<!-- Sidebar Desktop -->
<div class="sidebar-admin d-none d-lg-flex">
    <div class="sidebar-top">
        <div class="title text-success">SmartNote</div>
        
        <a href="dashboard_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>
        
        <a href="kelola_rapat_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i> Kelola Pengguna
        </a>
    </div>

    <div class="sidebar-bottom">
        <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
            <i class="bi bi-person-circle me-2"></i> Profile
        </a>
        <a href="#" id="logoutBtn" class="text-danger">
            <i class="bi bi-box-arrow-left me-2"></i> Keluar
        </a>
    </div>
</div>

<!-- Sidebar Mobile -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile">
        <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold text-success">SmartNote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between">
        <ul class="nav flex-column gap-2">
            <li class="nav-item">
                <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="dashboard_admin.php">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'bg-success text-white rounded' : '' ?>" href="kelola_rapat_admin.php">
                    <i class="bi bi-people me-2"></i> Kelola Pengguna
                </a>
            </li>
        </ul>

        <ul class="nav flex-column gap-2 mt-4 border-top pt-3">
                <li class="nav-item">
                <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'bg-success text-white rounded' : '' ?>" href="profile.php">
                    <i class="bi bi-person-circle me-2"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a id="logoutBtnMobile" class="nav-link text-danger fw-medium" href="#">
                    <i class="bi bi-box-arrow-left me-2"></i> Keluar
                </a>
            </li>
        </ul>
    </div>
</div>
