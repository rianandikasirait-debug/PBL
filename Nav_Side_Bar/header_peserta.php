<!-- CSS Header -->
<style>
    /* ===== HEADER (TOP BAR) ===== */
    .header-admin {
        position: fixed;
        top: 0;
        left: 250px;
        right: 0;
        height: 70px;
        background: white;
        border-bottom: 1px solid #e6e6e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 25px;
        z-index: 998;
    }

    .header-admin .page-title {
        font-size: 20px;
        font-weight: 700;
    }

    .header-admin .right-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* ===== MAIN CONTENT ADJUSTMENT ===== */
    .main-content {
        margin-left: 250px;
        padding: 90px 20px 20px 20px;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    /* ===== MOBILE ONLY ===== */
    @media (max-width: 991px) {
        .sidebar-admin {
            display: none;
        }

        .header-admin {
            left: 0 !important;
        }

        .main-content {
            margin-left: 0;
            padding-top: 90px;
        }
    }
</style>

<!-- Header / Top Bar -->
<div class="header-admin">
    <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
        <i class="bi bi-list"></i>
    </button>

    <div class="page-title"><?= $pageTitle ?? 'Dashboard Peserta' ?></div>

    <div class="right-section">
        <div class="d-none d-md-block text-end me-2">
            <div class="fw-bold small"><?= htmlspecialchars($userName ?? 'Peserta') ?></div>
            <small class="text-muted" style="font-size: 0.75rem;">Peserta</small>
        </div>
        
        <?php if (!empty($userPhoto) && file_exists("../uploads/" . $userPhoto)): ?>
            <img src="../uploads/<?= htmlspecialchars($userPhoto) ?>" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
        <?php else: ?>
            <i class="bi bi-person-circle fs-2 text-secondary"></i>
        <?php endif; ?>
    </div>
</div>
