<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.2/css/bootstrap-select.min.css">
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.2/js/bootstrap-select.min.js"></script>

		<script>
		$(document).ready(function(){
			$('.selectpicker').selectpicker();
		});
		</script>
		<style>
			body { font-size : 4.5vw; }
			.navbar-nav .bootstrap-select { margin-bottom : 0px ; margin-top : 8px ;} 
			.image-container {margin:0;padding:0;list-style-type:none;}
			.image-attribute {margin : 0 10px;}
			@media screen and (max-width: 767px) {
				/* 如果使用者之視窗寬度 小於等於 768px，將會再載入這裡的 CSS。    */
				.cover-image { width : 100% ; height : auto ;}
				.navbar-nav .bootstrap-select:not([class*=span]):not([class*=col-]):not([class*=form-control]):not(.input-group-btn){ width : 100% ;} 
			}
		</style>
	</head>
	<body>
		<nav class="navbar navbar-default" role="navigation">
				<div class="container">
					<!-- Navbar Header -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Toggle Navi</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="http://localhost/code-phoenix/code-phoenix.php">Code Rainbow</a>
					</div>
					<div class="navbar-collapse collapse">
						<!-- Left Nav -->
						<ul class="nav navbar-nav">   
							<li><select class="selectpicker" >
										<option>D1</option>
										<option>D2</option>
										<option>D3</option>
										<option>D4</option>
								    </select>
							</li>
							<li><select class="selectpicker">
										<option>10</option>
										<option>30</option>
										<option>60</option>
										<option>90</option>
								    </select>
							</li>							
						</ul>
					</div>
				</div>		
		</nav>
		<?php
			$ftp_server = "sokietech.com";
			$ftp_ebay_image_path = '/public_html/ebayimages/' ;
			$ebay_id = 'alvoturk9000';
			$ftp_image_path =  $ftp_ebay_image_path."alvoturk9000"."/";
			$ftp_user_name = "sokietec";
			$ftp_user_pass = "nq47P2Rk4k";
			
			// set up basic connection
			$conn_id = ftp_connect($ftp_server);
			if(!$conn_id){ exit("<h2>Connection to <b>".$ftp_server."</b> has failed!</h2>"); }
			
			// login with username and password
			$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
			if(!$login_result){ exit("<h2>Login to <b>".$ftp_server."</b> with <b>".$ftp_user_name."</b> has failed!</h2>"); }
			
			ftp_chdir($conn_id,$ftp_image_path ); 
			 
			// get contents of the current directory
			$contents = ftp_nlist($conn_id, ".");
			/*
			// output $contents
			foreach( $contents as $v){
				if(preg_match("/^[d]\d/",$v)){
					echo '<img style="width:400px;height:400px;" src="alvoturk9000/'.$v.'/1.jpg"></img>';
					echo $v."   ".ftp_size($conn_id,$v.'/1.jpg')."<br>";
				}
			}
			*/
			$v = "d1a1";
			$file = $v.'/1.jpg';
			echo	'<div class="entity">';
			echo	'<ul class="image-container">';
			echo	'<li><img class="cover-image" src="http://sokietech.com/ebayimages/alvoturk9000/'.$v.'/1.jpg" /></li>';
			echo	'<li><span class="image-attribute">'.$v.'</span><span class="image-attribute">'.round(ftp_size($conn_id,$file)/(1024)).'KB </span><span class="image-attribute">'.date("F d Y H:i:s",ftp_mdtm($conn_id, $file)).'</span></li>';
			echo	'</ul></div>';
		?>
	</body>
</html>