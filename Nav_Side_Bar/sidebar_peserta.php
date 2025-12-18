<!-- CSS Header & Sidebar -->
<style>
    /* ===== SIDEBAR DESKTOP ===== */
    .sidebar-admin {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background: #ffffff;
        border-right: 1px solid #e6e6e6;
        padding: 20px 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        z-index: 999;
    }

    .sidebar-admin .title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 25px;
        padding-left: 10px;
    }

    .sidebar-admin a {
        display: block;
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        color: #222;
        font-weight: 500;
        text-decoration: none !important;
        display: flex;
        align-items: center;
    }

    .sidebar-admin a:hover,
    .sidebar-admin a.active {
        background: #00C853;
        color: #fff !important;
    }

   
</style>

<!-- Sidebar Desktop -->
<div class="sidebar-admin d-none d-lg-flex">
    <div class="sidebar-top">
        <div class="title text-success">SmartNote</div>
        
        <a href="dashboard_peserta.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_peserta.php' ? 'active' : '' ?>">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>
    </div>

    <div class="sidebar-bottom">
        <a href="profile_peserta.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile_peserta.php' ? 'active' : '' ?>">
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
                <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'dashboard_peserta.php' ? 'bg-success text-white rounded' : '' ?>" href="dashboard_peserta.php">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
        </ul>

        <ul class="nav flex-column gap-2 mt-4 border-top pt-3">
             <li class="nav-item">
                <a class="nav-link text-dark fw-medium <?= basename($_SERVER['PHP_SELF']) === 'profile_peserta.php' ? 'bg-success text-white rounded' : '' ?>" href="profile_peserta.php">
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
