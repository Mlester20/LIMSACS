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
        <!-- Student Management -->
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Student Management</span>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'enrollment.php' || $currentPage == 'student-records.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Account Settings">Enroll Students</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="enrollment.php" class="menu-link"><div data-i18n="Account">Enrollment</div></a></li>
            <li class="menu-item"><a href="student-records.php" class="menu-link"><div data-i18n="Account">Student Information</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'parent-guardians.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Parents & Guardians">Parents</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="parent-guardians.php" class="menu-link"><div data-i18n="Parents & Guardians">Parents & Guardians</div></a></li>
          </ul>
        </li>

        <!-- Academics -->
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Academics</span>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'sections.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-book"></i>
            <div data-i18n="Sections">Sections</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="sections.php" class="menu-link"><div data-i18n="Basic">Sections</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'school-year.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-calendar"></i>
            <div data-i18n="School Year">School Year</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="school-year.php" class="menu-link"><div data-i18n="School Year">School Year</div></a></li>
          </ul>
        </li>

        <!-- Documents -->
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Documents</span>
        </li>
        <li class="menu-item <?php echo ($currentPage == 'document-types.php' || $currentPage == 'student-documents.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-file"></i>
            <div data-i18n="Documents">Documents</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="document-types.php" class="menu-link"><div data-i18n="Document Types">Documents Types</div></a></li>

            <li class="menu-item"><a href="student-documents.php" class="menu-link"><div data-i18n="Student Documents">Student Documents</div></a></li>
          </ul>
        </li>

        <!-- Graduates -->
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Graduates</span>
        </li>
        <li class="menu-item <?php echo (in_array($currentPage, ['graduates.php', 'graduates-master-list.php', 'graduate-view.php'])) ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-medal"></i>
            <div data-i18n="Graduates">Graduates</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="graduates.php" class="menu-link"><div data-i18n="Dashboard">Dashboard</div></a></li>
            <li class="menu-item"><a href="graduates-master-list.php" class="menu-link"><div data-i18n="Master List">Master List</div></a></li>
          </ul>
        </li>

        <!-- Audits -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Audits</span></li>
          <li class="menu-item <?php echo ($currentPage === 'logs.php') ? 'active' : '' ?> ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon tf-icons bx bx-file"></i>
              <div data-i18n="Logs">Logs</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item"><a href="logs.php" class="menu-link"><div data-i18n="Activity Logs">Activity Logs</div></a></li>
            </ul>
        </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">