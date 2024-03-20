<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="./css/style.css">

<div class="wrapper d-flex align-items-stretch">
	<nav id="sidebar">
		<div class="custom-menu">
					<button type="button" id="sidebarCollapse" class="btn btn-primary">
	          <i class="fa fa-bars"></i>
	          <span class="sr-only">Toggle Menu</span>
	        </button>
        </div>
		<div class="p-4">
			<ul class="list-unstyled components mb-5">
				<?php
					foreach( $arrAdminMenu as $slug => $menu ){
						$active = "";

						$splitLink = explode('?', $_SERVER['REQUEST_URI']);
						$actionLink = $splitLink[0];

						if( $actionLink == ADMIN_INDEX.$slug ) $active = "active";
						if( is_array( $menu) ){

							// check Parent Menu Active

							$sub_active = "";
							$subMenuHTML = "";
							foreach( $menu['sub'] as $s => $v ){
								$sub_active = "";
								if( $actionLink == ADMIN_INDEX.$s ) {
									$sub_active = "active";
									$active = "active";
								}
								$subMenuHTML .= '<li class="'.$sub_active.'"><a href="'.$s.'">'.$v.'</a></li>';
							}

							if( $active == "active" )
								echo '<li class="active"><a href="#sub-'.$slug.'" data-toggle="collapse" aria-expanded="true" class="dropdown-toggle">'.$menu['title'].'</a><ul class="collapse list-unstyled show" id="sub-'.$slug.'">';
							else
								echo '<li><a href="#sub-'.$slug.'" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">'.$menu['title'].'</a><ul class="collapse list-unstyled" id="sub-'.$slug.'">';

							echo $subMenuHTML . '</ul></li>';
						} else {
							echo '<li class="'.$active.'"><a href="'.$slug.'">'.$menu.'</a></li>';
						}
					}
				?>
			</ul>
		</div>
	</nav>
	
	<!-- Page Content  -->
	<div id="content" class="p-4">
