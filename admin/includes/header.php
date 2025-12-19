<?php
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Sambal Belut Bu Raden</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="dashboard-container active">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Sambal Belut</div>
                <div class="sidebar-subtitle">Admin Panel</div>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="order_management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'order_management.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                    Order Management
                </a>
                <a href="menu_management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'menu_management.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Menu Management
                </a>
                <a href="stock_management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'stock_management.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-5 12H9v-2h6v2zm5-7H4V4h16v3z"/>
                    </svg>
                    Stock Management
                </a>
            </nav>
            
            <button class="logout-btn" onclick="if(confirm('Yakin ingin logout?')) window.location.href='logout.php'">
                Logout
            </button>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div>
                    <h1 class="page-title"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                    <p class="page-subtitle"><?php echo $page_subtitle ?? 'Welcome back, ' . getAdminName(); ?></p>
                </div>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo getAdminName(); ?></div>
                        <div class="user-email"><?php echo getAdminEmail(); ?></div>
                    </div>
                </div>
            </header>
