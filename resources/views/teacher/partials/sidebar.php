<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="<?= BASE_URL ?>/resources/views/teacher/home.php" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="Logo" style="width: 50px; height: 50px;">
          </span>
          <span class="app-brand-text demo menu-text fw-bolder ms-2">LIMSACS</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">
        <li class="menu-item <?php echo ($currentPage === 'home.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/teacher/home.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Dashboard">Dashboard</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'my-students.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/teacher/my-students.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="My Students">My Students</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'documents.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/teacher/documents.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-file-blank"></i>
            <div data-i18n="Documents">Documents</div>
          </a>
        </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">
