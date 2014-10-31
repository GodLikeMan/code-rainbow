<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css">
		<link rel="stylesheet" href="./lib/jquery.lazyloadxt.fadein.min.css">
		<link rel="stylesheet" href="./lib/jquery.lazyloadxt.spinner.min.css">
		<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
		<script src="./lib/jquery.cookie-1.4.1.min.js"></script>
		<script src="./lib/jquery.lazyloadxt.extra.min.js"></script>
		
		<script>
		$(document).ready(function(){
			/*init elements*/
			//select picker
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
				$('.selectpicker').selectpicker('mobile');
				console.log("mobile mode");
			}
			else{
				$('.selectpicker').selectpicker();
				console.log("Desktop mode");
			}
			
			$('.selectpicker').selectpicker('setStyle', 'btn-lg', 'add');
			
			//reset cookies
			$.removeCookie('displayLimit');
			$.removeCookie('term');
			$.removeCookie('selectedAccount');
			
			//save configs to cookie 
			$.cookie('displayLimit',$('#display-limit').val());
			console.log('init limit '+$.cookie('displayLimit'));
			$.cookie('term',$('#damper-select').val());
			console.log('init term '+$.cookie('term'));
			$.cookie('selectedAccount',$('#account-select').val());
			console.log('init account '+$.cookie('selectedAccount'));
			
			//init items
			refreshListAjax();
			
			//
			$("#damper-select").on("change",function(){
				$.cookie('term',$('#damper-select').val());
				refreshListAjax();
			});
			
			//	
			$("#display-limit").on("change",function(){
				$.cookie('displayLimit',$('#display-limit').val());
				refreshListAjax();
			});
			
			$("#account-select").on("change",function(){
			
				//reset
				$.removeCookie('term');
				$.removeCookie('searchTerm');
				$("#nav-search").trigger("reset");
				
				//Custom selecter process
				if($("#account-select").val() == "alvoturk9000"){
					$("#damper-select").selectpicker('show');
					$.cookie('term',$('#damper-select').val());
				}
				else{$("#damper-select").selectpicker('hide');}
				
				//save to cookie
				$.cookie('selectedAccount',$('#account-select').val());
				
				
				refreshListAjax();
				
			});
			
			$("#nav-search").on('submit',function() {
				event.preventDefault();
				$.cookie('searchTerm',$('#search-term').val());
				
				refreshListAjax();
				console.log($.cookie("searchTerm"));
			});
			
			/*temp*/
			$(window).on('ajaxComplete', function() {
				setTimeout(function() {
					$(window).lazyLoadXT();
				}, 50);
			});
			
			function refreshListAjax(){
				$.post("code-monkeys.php",{'selectedAccount':$.cookie('selectedAccount'),'displayLimit':$.cookie('displayLimit'),'term':$.cookie('term'),'searchTerm':$.cookie('searchTerm'),'query':'display_refresh'}
				).done(function(json){

					refreshList(json);
				});					
			}
			
			function refreshList(json){
				var	p = $.parseJSON(json);
				
				if(p['message']=='ERROR'){ 
					console.log(p['code']);
					proceedHtml = p['code'];
				}
				else {
					proceedHtml ="";
					for(var i = 0 ; i < p['refreshed_list'].length ; i++){
						file = p['refreshed_list'][i].account+'/'+p['refreshed_list'][i].sku+'/'+p['refreshed_list'][i].name;
					
						var fDate = formatDate(p['refreshed_list'][i].last_modify_date*1000);

						proceedHtml +=	'<div class="image-container">'+
									'<img class="cover-image lazy-loaded"  data-src="http://sokietech.com/ebayimages/'+file+'"/ >'+	
									'<div class="image-folder-name"><span class="image-attribute">'+p['refreshed_list'][i].sku+'</span></div>'+
									'<div class="image-attribute-row"><span class="image-attribute">'+Math.round(p['refreshed_list'][i].size/(1024))+' KB </span><span class="image-attribute">'+fDate+'</span></div>'+
									'</div>';	
					}
					console.log("refresh successed * "+"limit ="+$.cookie('displayLimit')+" term ="+$.cookie('term'));					
				}
				
				$(".content-wrapper").html(proceedHtml);
			}
			
			function formatDate(timestamp){
				var fDate = new Date(timestamp);
				months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
				var d = fDate.getDate();
				var m =  months[fDate.getMonth()];
				var y = fDate.getFullYear();
				
				return  m+' '+d+' '+y;
			}
			
		});
		</script>
		<style>
			/* 
				#7FDED1	light green
				#6BD0BE green 
			*/
			
			body {font-family: 'Roboto', sans-serif;} 
			#end-marker { clear : both ; float : left ;}
			nav .form-control:focus { border-color : #7FDED1 ;  }
			nav .form-control { color: #fff ; background-color: #6BD0BE;}
			nav .form-control::-moz-placeholder {color: rgba(255,255,255,0.8);}
			nav .form-control:-ms-input-placeholder {color: rgba(255,255,255,0.8);}
			nav .form-control::-webkit-input-placeholder {color: rgba(255,255,255,0.8);}
			
			/*
				button
			*/
			.btn-green {
				background-color: #6BD0BE;
				border-color: #6BD0BE;
				color:	#fff;
			}
			.btn-green:hover,
			.btn-green:focus,
			.btn-green:active,
			.btn-green.active {
				background-color: #58cab6;
				border-color: #44c4ad;
				color:	#fff;
			}
			.btn-green.disabled:hover,
			.btn-green.disabled:focus,
			.btn-green.disabled:active,
			.btn-green.disabled.active,
			.btn-green[disabled]:hover,
			.btn-green[disabled]:focus,
			.btn-green[disabled]:active,
			.btn-green[disabled].active,
			fieldset[disabled] .btn-green:hover,
			fieldset[disabled] .btn-green:focus,
			fieldset[disabled] .btn-green:active,
			fieldset[disabled] .btn-green.active {
				background-color: #6BD0BE;
				border-color: #6BD0BE;
			}
			
			.navbar-inner { padding : 5px 0; }
			.content-wrapper { margin-top : 65px; }
			.navbar-nav .bootstrap-select { margin-bottom : 0px ; margin-top : 8px ;} 
			.image-container {margin:9px 9px 0 9px;padding:0;width : 15% ;float:left ; display:inline-block;}
			.image-attribute {margin-left : 15px; }
			.image-folder-name , .image-attribute-row {background : #6BD0BE ;color : white;  display:block; text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
			.image-folder-name { font-size : 18px; text-transform: uppercase ;}
			.image-attribute-row  { font-size : 15px ; margin-bottom:15px;}
			.cover-image { width:100% ; height : 200px ; margin : 0 auto;}
			@media screen and (max-width: 767px) {
				/* 如果使用者之視窗寬度 小於等於 768px，將會再載入這裡的 CSS。    */
				.cover-image {height:auto;}
				.image-container ,.content-wrapper {width : 100% ;}
				.image-container { margin : 0;}
				navbar .bootstrap-select.btn-group:not(.input-group-btn), .bootstrap-select.btn-group[class*=span], .bootstrap-select.btn-group[class*=col-] { margin : 0 auto;}
			}
		</style>
	</head>
	<body>
		<nav class="navbar navbar-fixed-top navbar-default" role="navigation">
			<div class="navbar-inner">
				<div class="container">
					<select id="account-select" class="selectpicker" data-width="20%" data-style="btn-green">
						<option >alvoturk9000</option>
						<option>3amotor_com</option>
						<option>d2_sport</option>
					</select>
					<select id="display-limit" class="selectpicker" data-width="20%" data-style="btn-green">
						<option >10</option>
						<option>30</option>
						<option>60</option>
						<option>100</option>
					</select>
					<select id="damper-select" class="selectpicker" data-width="20%"  data-style="btn-green">
						<option data-subtext="Black Carbon Fiber" value="Black Carbon Fiber">D1</option>
						<option data-subtext="Black" value="Black">D2</option>
						<option data-subtext="Silver Carbon Fiber" value="Silver Carbon Fiber">D3</option>
						<option data-subtext="White" value="White">D4</option>
					</select>	

					<div class="col-sm-3 col-md-3 pull-right">
						<form id="nav-search" class="navbar-form" role="search">
							<div class="input-group">
								<input type="text" class="form-control" placeholder="Search" name="search-term" id="search-term">
								<div class="input-group-btn">
									<button class="btn btn-green" type="submit"><i class="glyphicon glyphicon-search"></i></button>
								</div>
							</div>
						</form>
					</div>
					
				</div>
				

		
			</div>
		</nav>
		<div class="container content-wrapper">
		<?php
			/*
			$limit = 10;
			$db = new SQLite3('code-rainbow.db');
			$stmt = $db->prepare('SELECT M.sku , P.name  , P.size , P.last_modify_date FROM Pictures as P INNER JOIN  Map_Picture_Product as M WHERE M.picture_id = P.id  AND P.name = "1.jpg" LIMIT :limit');
			$stmt->bindParam(':limit',$limit,SQLITE3_INTEGER);
			$result = $stmt->execute();
			
			while(($row = $result->fetchArray()) AND ($limit >0)){
				
				$file = $row[0].'/'.$row[1];

				echo	'<div class="image-container">';
				echo	'<img class="cover-image" src="http://sokietech.com/ebayimages/alvoturk9000/'.$row[0].'/'.$row[1].'" />';
				echo 	'<div class="image-folder-name"><span class="image-attribute">'.strtoupper($row[0]).'</span></div>';
				echo	'<div class="image-attribute-row"><span class="image-attribute">'.round($row[2]/(1024)).'KB </span><span class="image-attribute">'.date("M d Y",$row[3]).'</span></div>';
				echo	'</div>';
				$limit --;
			}

			$db->close();
		*/
		?>
		</div>
		<div id="end-marker"></div>
	</body>
</html>