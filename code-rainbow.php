<!DOCTYPE html>
<html>
	<head>
		<script src="http://code.jquery.com/jquery-2.1.1.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	</head>
	<body>
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

			// output $contents
			foreach( $contents as $v){
				if(preg_match("/^[d]\d/",$v)){
					echo '<img style="width:400px;height:400px;" src="alvoturk9000/'.$v.'/1.jpg"></img>';
					echo $v."<br>";
				}
			}

		?>
	</body>
</html>