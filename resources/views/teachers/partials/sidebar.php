<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="../../../public/assets/img/favicon/logo.png" alt="Logo" style="width: 50x; height: 50px;">
          </span>
          <span class="app-brand-text demo menu-text fw-bolder ms-2">LIMSACS</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">
        <li class="menu-item <?php echo ($currentPage == 'home.php') ? 'active' : ''; ?>">
          <a href="home.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Analytics">Dashboard</div>
          </a>
        </li>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Pages</span>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'students.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Account Settings">My Students</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="students.php" class="menu-link"><div data-i18n="Account">Students</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'parent-guardians.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="Parents & Guardians">Parents</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="parent-guardians.php" class="menu-link"><div data-i18n="Parents & Guardians">Parents & Guardians</div></a></li>
          </ul>
        </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">