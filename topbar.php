<!-- TOPBAR -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto">

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- User -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?= $_SESSION["user"] ?? "User"; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
                        <span class="badge badge-danger">Admin</span>
                    <?php elseif (isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'manager') : ?>
                        <span class="badge badge-info">Manager</span>
                    <?php else : ?>
                        <span class="badge badge-secondary">Staff</span>
                    <?php endif; ?>
                </span>
                <i class="fas fa-user-circle fa-2x text-gray-300"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="logout.php">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
