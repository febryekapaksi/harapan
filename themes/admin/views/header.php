<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set'; ?><?= isset($template['title']) ? ' | ' . $template['title'] : ''; ?></title>
  <link rel="shortcut icon" href="<?php echo base_url('assets/'); ?>images/harapan_logo.png" />

  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Sweet Alert -->
  <link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert.css'); ?>">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/bootstrap/css/bootstrap.min.css'); ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/font-awesome/css/font-awesome.min.css'); ?>">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/ionicons/css/ionicons.min.css'); ?>">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css'); ?>">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/AdminLTE.min.css'); ?>">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/iCheck/all.css'); ?>">
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect.
  -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/skins/_all-skins.min.css'); ?>">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css'); ?>">
  <!-- bootstrap datepicker -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css'); ?>">
  <!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datepicker/datepicker3.css'); ?>"> -->
  <link rel="stylesheet" href="<?= base_url('assets/jquery-ui-1.12.1/jquery-ui-1.12.1/jquery-ui.min.css'); ?>">
  <!-- Bootstrap time Picker -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/timepicker/bootstrap-timepicker.min.css'); ?>">
  <!-- bootstrap datetimepicker -->
  <link rel="stylesheet" href="<?= base_url('assets/plugins/datetimepicker/bootstrap-datetimepicker.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/custom_ddr.css'); ?>">

  <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css">

  <!-- jQuery 2.2.3 -->
  <script src="<?= base_url('assets/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
  <!-- bootstrap datepicker -->
  <script src="<?= base_url('assets/plugins/daterangepicker/moment.min.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js'); ?>"></script>
  <!-- <script src="<?= base_url('assets/plugins/datepicker/bootstrap-datepicker.js'); ?>"></script> -->
  <script src="<?= base_url('assets/jquery-ui-1.12.1/jquery-ui-1.12.1/jquery-ui.min.js'); ?>"></script>
  <!-- bootstrap time picker -->
  <script src="<?= base_url('assets/plugins/timepicker/bootstrap-timepicker.min.js'); ?>"></script>
  <!-- bootstrap datetimepicker -->
  <script src="<?= base_url('assets/plugins/datetimepicker/bootstrap-datetimepicker.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/datetimepicker/moment-with-locales.js'); ?>"></script>

  <!-- Sweet Alert -->
  <script src="<?= base_url('assets/dist/sweetalert.min.js'); ?>"></script>
  <!-- Form Jquery -->
  <script src="<?= base_url('assets/plugins/jqueryform/jquery.form.js'); ?>"></script>
  <script src="<?= base_url('assets/plugins/slimScroll/jquery.slimscroll.min.js'); ?>"></script>
  <script src="<?= base_url('assets/js/scripts.js'); ?>" type="text/javascript"></script>
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script type="text/javascript">
    var baseurl = "<?= base_url(); ?>";
    var siteurl = "<?php echo site_url(); ?>";
    var base_url = siteurl;
    var active_controller = '<?php echo $this->uri->segment(1); ?>' + '/';
    var active_function = '<?php echo $this->uri->segment(2); ?>' + '/';
  </script>
</head>

<!-- === CUSTOM LOOK & FEEL (safe to remove) === -->
<style>
  /* ==== WARNA BRAND (ubah sesukamu) ==== */
  :root {
    --brand: #1775b4ff;
    /* biru lebih terang */
    --sidebar: #1f3b52;
    --accent: #5dade2;
    /* garis kiri menu aktif */
    --content: #f5f7fb;
  }

  /* ==== HEADER / NAVBAR / LOGO ==== */
  .skin-blue .main-header .logo,
  .skin-blue .main-header .navbar {
    background-color: var(--brand);
  }

  .skin-blue .main-header .navbar .sidebar-toggle:hover {
    background: rgba(255, 255, 255, .08);
  }

  .main-header .logo {
    height: 52px;
    line-height: 52px;
  }

  .main-header .navbar {
    min-height: 52px;
  }

  /* ==== SIDEBAR ==== */
  .skin-blue .main-sidebar {
    background-color: var(--sidebar);
  }

  .skin-blue .sidebar-menu>li.header {
    background: #173247;
    color: #a9c9e0;
  }

  .skin-blue .sidebar-menu>li>a {
    color: #d0e1ed;
  }

  .skin-blue .sidebar-menu>li:hover>a,
  .skin-blue .sidebar-menu>li.active>a {
    background: #22577a;
    color: #fff;
  }

  .skin-blue .sidebar-menu>li.active>a {
    border-left: 3px solid var(--accent);
  }

  .sidebar-menu .treeview-menu>li>a {
    color: #b9d0de;
  }

  .sidebar-menu .treeview-menu>li>a:hover {
    color: #fff;
  }

  /* ==== AREA KONTEN & BOX ==== */
  .content-wrapper {
    background-color: var(--content);
  }

  .box {
    border-radius: 10px;
    border: 2px solid #e6ecf3;
  }

  .box.box-primary {
    border-top-color: var(--accent);
  }

  .box-header .box-title {
    font-weight: 600;
  }

  /* ==== TABEL / DATATABLES ==== */
  .table {
    border-radius: 8px;
    overflow: hidden;
  }

  .table>thead>tr>th {
    background: #eaf2f9;
    color: #2a4a62;
    border-bottom: 1px solid #dbe6f0;
  }

  /* ==== BUTTON ==== */
  .btn {
    border-radius: 8px !important;
  }

  .btn-primary {
    background: #3498db;
    border-color: #2e86c1;
  }

  .btn-primary:hover {
    background: #2e86c1;
  }

  /* ==== FORM & SELECT2 ==== */
  .select2-container .select2-selection--single {
    height: 34px;
    border-radius: 4px;
    border-color: #cfd9e3;
  }

  .select2-selection__rendered {
    line-height: 32px;
  }

  .select2-selection__arrow {
    height: 32px;
  }

  /* ==== MODAL (Bootstrap 3 / AdminLTE2) ==== */
  .modal-content {
    border-radius: 12px;
    border: 1px solid #e6ecf3;
    box-shadow: 0 18px 40px rgba(3, 24, 43, .25);
    overflow: hidden;
    /* header/footer ikut rounded */
    background-clip: padding-box;
  }

  .modal-header {
    border-bottom: 1px solid #eef2f6;
    padding: 12px 16px;
  }

  .modal-title {
    font-weight: 600;
    color: #2a4a62;
  }

  .modal-body {
    padding: 16px;
  }

  .modal-footer {
    border-top: 1px solid #eef2f6;
    padding: 12px 16px;
  }

  /* Tombol di modal ikut tumpul */
  .modal-footer .btn {
    border-radius: 8px !important;
  }

  /* Backdrop lebih lembut */
  .modal-backdrop.in {
    opacity: .45;
    background-color: #000;
  }

  /* Animasi muncul (slide-up ringan) */
  .modal.fade .modal-dialog {
    -webkit-transform: translate(0, 20px);
    transform: translate(0, 20px);
    transition: transform .18s ease, -webkit-transform .18s ease;
  }

  .modal.in .modal-dialog {
    -webkit-transform: translate(0, 0);
    transform: translate(0, 0);
  }

  /* Ukuran small & large tetap proporsional */
  @media (min-width: 768px) {
    .modal-sm .modal-content {
      border-radius: 10px;
    }

    .modal-lg .modal-content {
      border-radius: 12px;
    }
  }

  /* (Opsional) Select2 di dalam modal biar konsisten rounded */
  .select2-dropdown {
    border-radius: 8px;
    border-color: #cfd9e3;
  }

  .select2-container--open .select2-selection--single {
    border-color: #3498db;
  }

  /* ==== SweetAlert v1 (swal) ==== */
  .sweet-overlay {
    background: rgba(0, 0, 0, .45) !important;
    /* backdrop lebih lembut */
  }

  .sweet-alert {
    border-radius: 12px !important;
    padding: 18px 16px !important;
    box-shadow: 0 18px 40px rgba(3, 24, 43, .25);
  }

  /* Judul & teks */
  .sweet-alert h2 {
    margin: 0 0 8px;
    font-weight: 600;
    color: #2a4a62;
    font-size: 20px;
  }

  .sweet-alert p {
    color: #4a6075;
    font-size: 16px;
  }

  /* Tombol */
  .sweet-alert button {
    border-radius: 8px !important;
    padding: 6px 16px !important;
    font-weight: 500;
  }

  .sweet-alert .confirm {
    background: var(--brand, #3498db) !important;
  }

  .sweet-alert .confirm:hover {
    background: #2e86c1 !important;
  }

  .sweet-alert .cancel {
    background: #e5e9f0 !important;
    color: #2a4a62 !important;
  }

  .sweet-alert .cancel:hover {
    background: #d8dee9 !important;
  }

  /* Ikon warning biar senada */
  .sweet-alert .sa-icon.sa-warning {
    border-color: #f39c12;
  }

  .sweet-alert .sa-icon.sa-warning .sa-body,
  .sweet-alert .sa-icon.sa-warning .sa-dot {
    background-color: #f39c12;
  }

  /* ==== UTILITIES SPACING RINGAN ==== */
  .mt-8 {
    margin-top: 8px
  }

  .mr-8 {
    margin-right: 8px
  }
</style>
<!-- === /CUSTOM LOOK & FEEL === -->

<body class="hold-transition skin-blue sidebar-mini fixed">
  <div class="ajax_loader">
    <img src="<?php echo base_url('assets/images/ajax_loader_new.gif'); ?>">
  </div>
  <div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

      <!-- Logo -->
      <a href="<?= site_url(); ?>" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><img src="<?php echo base_url('assets/'); ?>images/harapan_logo.png" width="70%"></span>
        <!-- logo for regular state and mobile devices -->
        <span class=" logo-lg"><b><?= isset($idt->nm_perusahaan) ? $idt->nm_perusahaan : 'not-set'; ?></b></span>
      </a>

      <!-- Header Navbar -->
      <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Notifications Menu -->
            <!-- 
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="hidden-xs">
                  Inbox <i class="fa fa-angle-down"></i>
              </a>
              <ul class="dropdown-menu">
                <li style="background-color: #0080ff; min-width: 500px; max-width:500px; border: 1px solid #ccc;">

                  <table border="1">
                    <tr>
                      <th width="60" class="text-center" rowspan="2">
                        <img src="assets/images/Kaya_NFS.jpg" alt="" class="img-circle" width="50" height="50">
                      </th>
                      <th style="color: white; padding-left: 15px; word-wrap: break-word; white-space: normal;">
                        <h4>Testing Inbox</h4>
                      </th>
                    </tr>
                    <tr>
                      <td style="max-width: 500px; word-wrap: break-word; white-space: normal; padding: 15px; text-align: justify; color: white;">
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum asperiores ea nesciunt soluta nulla. Praesentium blanditiis quisquam facilis esse tempora nemo, iusto sunt itaque! Vitae dicta totam sapiente minus. Enim?
                      </td>
                    </tr>
                  </table>

                </li>
                <li style="background-color: #0080ff; min-width: 500px; max-width:500px; border: 1px solid #ccc;">

                  <table border="1">
                    <tr>
                      <th width="60" class="text-center" rowspan="2">
                        <img src="assets/images/Kaya_NFS.jpg" alt="" class="img-circle" width="50" height="50">
                      </th>
                      <th style="color: white; padding-left: 15px; word-wrap: break-word; white-space: normal;">
                        <h4>Testing Inbox</h4>
                      </th>
                    </tr>
                    <tr>
                      <td style="max-width: 500px; word-wrap: break-word; white-space: normal; padding: 15px; text-align: justify; color: white;">
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum asperiores ea nesciunt soluta nulla. Praesentium blanditiis quisquam facilis esse tempora nemo, iusto sunt itaque! Vitae dicta totam sapiente minus. Enim?
                      </td>
                    </tr>
                  </table>

                </li>
                <li style="background-color: #0080ff; min-width: 500px; max-width:500px; border: 1px solid #ccc;">

                  <table border="1">
                    <tr>
                      <th width="60" class="text-center" rowspan="2">
                        <img src="assets/images/Kaya_NFS.jpg" alt="" class="img-circle" width="50" height="50">
                      </th>
                      <th style="color: white; padding-left: 15px; word-wrap: break-word; white-space: normal;">
                        <h4>Testing Inbox</h4>
                      </th>
                    </tr>
                    <tr>
                      <td style="max-width: 500px; word-wrap: break-word; white-space: normal; padding: 15px; text-align: justify; color: white;">
                        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum asperiores ea nesciunt soluta nulla. Praesentium blanditiis quisquam facilis esse tempora nemo, iusto sunt itaque! Vitae dicta totam sapiente minus. Enim?
                      </td>
                    </tr>
                  </table>

                </li>
              </ul>
            </li> -->

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
              <!-- Menu Toggle Button -->
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                <img src="<?= (isset($userData->photo) && file_exists('assets/images/users/' . $userData->photo)) ? base_url('assets/images/users/' . $userData->photo) : base_url('assets/images/male-def.png'); ?>" class="user-image" alt="User Image">
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                <span class="hidden-xs">
                  <?php echo isset($userData->nm_lengkap) ? ucwords($userData->nm_lengkap) : $userData->username; ?></span>
              </a>
              <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header">
                  <img src="<?= (isset($userData->photo) && file_exists('assets/images/users/' . $userData->photo)) ? base_url('assets/images/users/' . $userData->photo) : base_url('assets/images/male-def.png'); ?>" class="img-circle" alt="User Image">

                  <p>
                    <?= isset($userData->nm_lengkap) ? ucwords($userData->nm_lengkap) : $userData->username; ?> - <?= isset($userData->groups) ? $userData->groups : '-'; ?>
                    <small>Member since <?= isset($userData->created_on) ? date('M Y', strtotime($userData->created_on)) : '-'; ?></small>
                  </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="#" class="btn btn-default btn-flat bg-green">Profile</a>
                  </div>
                  <div class="pull-right">
                    <a href="<?= site_url('users/logout'); ?>" class="btn btn-default btn-flat bg-red">Sign out</a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar Menu -->
        <?=
        $this->menu_generator->build_menus();
        // $this->menu_generator->build_menus_new(); 
        ?>
        <!-- /.sidebar-menu -->
      </section>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          <i class="<?= isset($template['page_icon']) ? $template['page_icon'] : 'fa fa-table'; ?>"></i>
          <?= isset($template['title']) ? $template['title'] : ''; ?>
        </h1>
      </section>

      <!-- Main content -->
      <section class="content">