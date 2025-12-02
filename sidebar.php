<!-- SIDEBAR -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-brain"></i>
        </div>
        <div class="sidebar-brand-text mx-3">HelpDesk Selector</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Penilaian</div>

    <li class="nav-item">
        <a class="nav-link" href="penilaian.php">
            <i class="fas fa-fw fa-edit"></i>
            <span>Form Penilaian</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="hasil.php">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Hasil Analisis</span></a>
    </li>

    <?php if (isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'manager') : ?>
        <hr class="sidebar-divider">

        <div class="sidebar-heading">Manager Only</div>

        <li class="nav-item">
            <a class="nav-link" href="hasil.php#borda-section">
                <i class="fas fa-fw fa-check-circle"></i>
                <span>Hitung Borda</span></a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
