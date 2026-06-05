<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="dashboard.php" class="app-brand-link">
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
        <li class="menu-item">
          <a href="dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Analytics">Dashboard</div>
          </a>
        </li>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Pages</span>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Account Settings">Enroll Students</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="student-records.php" class="menu-link"><div data-i18n="Account">Students Information</div></a></li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
            <div data-i18n="Authentications">Authentications</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="auth-login-basic.html" class="menu-link" target="_blank"><div data-i18n="Basic">Login</div></a></li>
            <li class="menu-item"><a href="auth-register-basic.html" class="menu-link" target="_blank"><div data-i18n="Basic">Register</div></a></li>
            <li class="menu-item"><a href="auth-forgot-password-basic.html" class="menu-link" target="_blank"><div data-i18n="Basic">Forgot Password</div></a></li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-cube-alt"></i>
            <div data-i18n="Misc">Misc</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="pages-misc-error.html" class="menu-link"><div data-i18n="Error">Error</div></a></li>
            <li class="menu-item"><a href="pages-misc-under-maintenance.html" class="menu-link"><div data-i18n="Under Maintenance">Under Maintenance</div></a></li>
          </ul>
        </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">