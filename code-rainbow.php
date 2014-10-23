<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css">
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>

		<script>
		$(document).ready(function(){
			
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
				$('.selectpicker').selectpicker('mobile');
				console.log("mobile mode");
			}
			else{
				$('.selectpicker').selectpicker();
				console.log("Desktop mode");
			}
			
			$('.selectpicker').selectpicker('setStyle', 'btn-lg', 'add');
		});
		</script>
		<style>
			body { }
			/* 
				#7FDED1	light green
				#6BD0BE green 
			*/
			.image-container{ float:left ; display:inline-block;} 
			.content-wrapper { margin-top : 50px; }
			.navbar-nav .bootstrap-select { margin-bottom : 0px ; margin-top : 8px ;} 
			.image-container {margin:0;padding:0;width : 20% ;}
			.image-attribute {margin : 0 10px; }
			.image-attribute-row  { background : #6BD0BE ;color : white;  font-size : 18px; display:block;}
			.cover-image { width:100% ; height : auto ; margin : 0 auto; border : 3px solid #6BD0BE;}
			@media screen and (max-width: 767px) {
				/* 如果使用者之視窗寬度 小於等於 768px，將會再載入這裡的 CSS。    */
				.image-container ,.content-wrapper {width : 100% ;}
				navbar .bootstrap-select.btn-group:not(.input-group-btn), .bootstrap-select.btn-group[class*=span], .bootstrap-select.btn-group[class*=col-] { margin : 0 auto;}
			}
		</style>
	</head>
	<body>
		<nav class="navbar navbar-fixed-top navbar-default" role="navigation">
			<div class="navbar-inner">
				<div class="container">
					<select class="selectpicker" data-width="20%">
						<option>D1</option>
						<option>D2</option>
						<option>D3</option>
						<option>D4</option>
					</select>
					<select class="selectpicker" data-width="20%">
						<option>10</option>
						<option>30</option>
						<option>60</option>
						<option>90</option>
					</select>
				</div>		
			</div>
		</nav>
		<div class="container content-wrapper">
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
			
			$limit = 7;
			// output $contents
			foreach( $contents as $v){
				$file = $v.'/1.jpg';
				if(preg_match("/^[d]\d/",$v) and ($limit>0)){
					echo	'<div class="image-container">';
					echo	'<img class="cover-image" src="http://sokietech.com/ebayimages/alvoturk9000/'.$v.'/1.jpg" />';
					echo	'<div class="image-attribute-row"><span class="image-attribute">'.strtoupper($v).'</span><span class="image-attribute">'.round(ftp_size($conn_id,$file)/(1024)).'KB </span><span class="image-attribute">'.date("F d Y H:i:s",ftp_mdtm($conn_id, $file)).'</span></div>';
					echo	'</div>';
					$limit --;
				}
			}
			/*
			$v = "d1a1";
			$file = $v.'/1.jpg';
			echo	'<div class="entity">';
			echo	'<ul class="image-container">';
			echo	'<li><img class="cover-image" src="http://sokietech.com/ebayimages/alvoturk9000/'.$v.'/1.jpg" /></li>';
			echo	'<li class="image-attribute-row"><span class="image-attribute">'.$v.'</span><span class="image-attribute">'.round(ftp_size($conn_id,$file)/(1024)).'KB </span><span class="image-attribute">'.date("F d Y H:i:s",ftp_mdtm($conn_id, $file)).'</span></li>';
			echo	'</ul></div>';
			*/
		?>
		</div>
	</body>
</html>