<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->

<?php 
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; 
	$obj = explode('/', $_SERVER['REQUEST_URI']);
	$end = end($obj);
?>

<aside class="left-sidebar" data-sidebarbg="skin6">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<li class="sidebar-item <?php (($end == 'dashboard') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="index" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span></a></li>
				<li class="sidebar-item <?php (($end == 'products') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="products" aria-expanded="false"><i class="mdi mdi-security"></i><span class="hide-menu">Products</span></a></li>
				<li class="sidebar-item <?php (($end == 'contents') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="contents" aria-expanded="false"><i class="mdi mdi-earth"></i><span class="hide-menu">Contents</span></a></li>
				<li class="sidebar-item <?php (($end == 'profile') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="profile" aria-expanded="false"><i class="mdi mdi-account-network"></i><span class="hide-menu">Profile</span></a></li>
				<!-- <li class="sidebar-item <?php (($end == 'test') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="table-basic" aria-expanded="false"><i class="mdi mdi-border-all"></i><span class="hide-menu">Table</span></a></li>
				<li class="sidebar-item <?php (($end == 'test') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="icon-material" aria-expanded="false"><i class="mdi mdi-face"></i><span class="hide-menu">Icon</span></a></li>
				<li class="sidebar-item <?php (($end == 'test') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="starter-kit" aria-expanded="false"><i class="mdi mdi-file"></i><span class="hide-menu">Blank</span></a></li>
				<li class="sidebar-item <?php (($end == 'test') ? print('selected') : null); ?>"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="error-404" aria-expanded="false"><i class="mdi mdi-alert-outline"></i><span class="hide-menu">404</span></a></li> -->
				<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" id="logoutbtn" href="#logout" aria-expanded="false"><i class="mdi mdi-menu-left"></i><span class="hide-menu">Logout</span></a></li>
			</ul>

		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>
<!-- ============================================================== -->
<!-- End Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->