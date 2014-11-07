<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css">
		<link rel="stylesheet" href="./lib/jquery.lazyloadxt.fadein.min.css">
		<link rel="stylesheet" href="./lib/jquery.lazyloadxt.spinner.min.css">
		<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
		<script src="./lib/jquery.cookie-1.4.1.min.js"></script>
		<script src="./lib/jquery.lazyloadxt.extra.min.js"></script>
		
		<script>
		$(document).ready(function(){
		
			/*init elements*/
			function initializer(){
			
				//reset cookies
				$.removeCookie('displayLimit');
				$.removeCookie('category');
				$.removeCookie('searchTag');
				$.removeCookie('selectedAccount');
				
				//save configs to cookie 
				$.cookie('displayLimit',$('#display-limit').val());
				$.cookie('selectedAccount',$('#account-select').val());
				
				
				//init items
				refreshCategorySelector();
				refreshListAjax();
				
				/*temp*/
				$(window).on('ajaxComplete', function() {
					setTimeout(function() {
						$(window).lazyLoadXT();
					}, 50);
				});
			}
			
			function refreshCategorySelector(){
			
				$('#category-select').empty();
				$.ajax({	type :	'POST',
								url	:	"code-monkeys.php",
								async : false,
								data : {'selectedAccount':$.cookie('selectedAccount'),'query':'get_category'}
				}).done(function(json){
					var	p = $.parseJSON(json);
					
					if(p['message']=='ERROR'){ 
						$('#category-select').selectpicker('hide');
						console.log(p['code']);
					}
					else {
						$('#category-select').append('<option value="all">All Category</option>');
						
						if($("#account-select").val() == "alvoturk9000"){
								$('#category-select').append('<option data-subtext="Black Carbon Fiber" value="Black Carbon Fiber">D1</option>'+
																						'<option data-subtext="Black" value="Black">D2</option>'+
																						'<option data-subtext="Silver Carbon Fiber" value="Silver Carbon Fiber">D3</option>'+
																						'<option data-subtext="White" value="White">D4</option>');
						}
						else{
							for(var i = 0;i<p['category_list'].length;i++){
								console.log(p['category_list'][i][0]);
								$('#category-select').append('<option>'+p['category_list'][i][0]+'</option>');
							}							
						}
						
						$('#category-select').selectpicker('refresh');
						$('#category-select').selectpicker('show');
						
						if($("#account-select").val() == "alvoturk9000"){
							$.cookie('category',"sokie tech damper");
						}
						else{
							$.cookie('category',$('#category-select').val());
						}
					}
				});				
				
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
			}
			
			//
			$("#category-select").on("change",function(){
				if($.cookie('selectedAccount') == "alvoturk9000"){
					
					$.cookie('category','sokie tech damper');
					
					if($("#category-select").val()!="all"){
						$.cookie('searchTag',$("#category-select").val());
					}
					else{ $.cookie('searchTag',""); }
				}
				else{
					$.cookie('category',$('#category-select').val());
					console.log($.cookie('category'));
				}
				
				refreshListAjax();
			});
			
			//	
			$("#display-limit").on("change",function(){
				$.cookie('displayLimit',$('#display-limit').val());
				refreshListAjax();
			});
			
			$("#account-select").on("change",function(){
			
				//reset
				$.removeCookie('category');
				$.removeCookie('searchTag');
				$("#nav-search").trigger("reset");
				
				//save to cookie
				$.cookie('selectedAccount',$('#account-select').val());
				$.cookie('category',"all");
				
				
				refreshCategorySelector();
				refreshListAjax();
			});
			
			$("#nav-search").on('submit',function(event) {
				event.preventDefault();
				$.cookie('searchTag',$('#search-tag').val());
				
				refreshListAjax();
				console.log($.cookie("searchTag"));
				return false;
	
			});
			
			function initTagCloudAjax(){
				$.ajax({	type :	'POST',
								url	:	"code-monkeys.php",
								async : false,
								data : {'selectedAccount':$.cookie('selectedAccount'),'category':$.cookie('category'),'query':'get_tag_cloud'},
				}).done(function (json) {
						//console.log(json);
						createTagCloud(json);
					});
			}
			
			function createTagCloud(json){
				
				var	p = $.parseJSON(json);
				var  tagCloudHTML ="";
				
				if(p['message']=='ERROR'){ 
					console.log(p['code']);
					tagCloudHTML = '<h1 class="warning">'+p['code']+'</h1>';
				}
				else {
					tagCloudHTML = '<div id ="tag-cloud">';
					
					for(var i = 0;i<p['tag_cloud'].length;i++){
						tagCloudHTML += '<h4><span class="label label-info" data-tag-id="'+p['tag_cloud'][i].id+'">'+ p['tag_cloud'][i].data+'</span></h4>' ;
					}
					
					tagCloudHTML += '</div>';
				}	
				$(".content-wrapper").append(tagCloudHTML);
				
				$("#tag-cloud .label").on('click',function(){
					$("#search-tag").val($(this).html()).submit();
					console.log($(this).html());
				});
			}
				
			
			function refreshListAjax(){
				console.log("refreshListAjax   : "+"/selectedAccount/-> "+$.cookie('selectedAccount')+" /category/-> "+$.cookie('category')+" /searchTag/->"+$.cookie("searchTag")+" /limit/->"+$.cookie("displayLimit"));
				$.ajax({	type :	'POST',
								url	:	"code-monkeys.php",
								async : false,
								data : {'selectedAccount':$.cookie('selectedAccount'),'displayLimit':$.cookie('displayLimit'),'category':$.cookie('category'),'searchTag':$.cookie('searchTag'),'query':'display_refresh'}
				}).done(function(json){
					refreshList(json);
				});					
			}
			
			function refreshList(json){
				//console.log(json);
				var	p = $.parseJSON(json);
				proceedHtml ="";
				$(".content-wrapper").empty();
				
				if(p['message']=='ERROR'){ 
					console.log(p['code']);
					proceedHtml = '<h1 class="warning">'+p['code']+'</h1>';
				}
				else {
					console.log(p['refreshed_list'].length+" items");
					
					initTagCloudAjax();
					
					for(var i = 0 ; i < p['refreshed_list'].length ; i++){
						file = p['refreshed_list'][i].account+'/'+p['refreshed_list'][i].sku+'/'+p['refreshed_list'][i].name;
					
						var fDate = formatDate(p['refreshed_list'][i].last_modify_date*1000);

						proceedHtml +=	'<div class="image-container">'+
									'<img class="cover-image lazy-loaded"  data-src="http://sokietech.com/ebayimages/'+file+'"/ >'+	
									'<div class="image-folder-name"><span class="image-attribute">'+p['refreshed_list'][i].sku+'</span></div>'+
									'<div class="image-attribute-row"><span class="image-attribute">'+Math.round(p['refreshed_list'][i].size/(1024))+' KB </span><span class="image-attribute">'+fDate+'</span></div>'+
									'</div>';	
					}
					console.log("Refresh successed : "+"/selectedAccount/-> "+$.cookie('selectedAccount')+" /category/-> "+$.cookie('category')+" /searchTag/->"+$.cookie("searchTag")+" /limit/->"+$.cookie("displayLimit"));					
				}
				
				$(".content-wrapper").append(proceedHtml);
			}
			
			function formatDate(timestamp){
				var fDate = new Date(timestamp);
				months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
				var d = fDate.getDate();
				var m =  months[fDate.getMonth()];
				var y = fDate.getFullYear();
				
				return  m+' '+d+' '+y;
			}
			
			initializer();
			
		});
		</script>
		<style>
			/* 
				#7FDED1	light green
				#6BD0BE green 
			*/
			
			body { font-family : 'Roboto', sans-serif ; background : #ebebeb ;} 
			#end-marker { clear : both ; float : left ;}
			.warning { text-align : center ;}
			nav .form-control:focus { border-color : #7FDED1 ;  }
			nav .form-control { color: #fff ; background-color: #6BD0BE;}
			nav .form-control::-moz-placeholder {color: rgba(255,255,255,0.8);}
			nav .form-control:-ms-input-placeholder {color: rgba(255,255,255,0.8);}
			nav .form-control::-webkit-input-placeholder {color: rgba(255,255,255,0.8);}
			#nav-search  input { display : inline-block ;} 
			#tag-cloud {  text-transform : capitalize ;}
			#tag-cloud h4 {  display : inline-block ; margin-right : 5px ;}
			
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
			.image-container {margin:10px 10px 5px 0px;padding:0;width : 19% ;float:left ; display:inline-block;}
			.image-attribute {margin-left : 15px; }
			.image-folder-name , .image-attribute-row {background : #6BD0BE ;color : white;  display:block; text-overflow: ellipsis;overflow: hidden;white-space: nowrap;}
			.image-folder-name { font-size : 18px; text-transform: uppercase ;}
			.image-attribute-row  { font-size : 15px ; }
			.cover-image { width:100% ; height : 200px ; margin : 0 auto;}
			@media screen and (max-width: 767px) {
				/* 如果使用者之視窗寬度 小於等於 768px，將會再載入這裡的 CSS。    */
				.cover-image {height:auto;}
				.image-container ,.content-wrapper {width : 100% ;}
				.content-wrapper { margin-top : 132px ;}
				.image-container { margin : 0 ;}
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
						<option value="all">Unlimited</option>
					</select>
					<select id="category-select" class="selectpicker" data-width="20%"  data-style="btn-green"></select>	
					<form id="nav-search" class="navbar-form navbar-right" role="search">
						<div class="form-group">
							<input type="text" class="form-control" placeholder="Search" name="search-tag" id="search-tag" required>
						</div>
						<button class="btn btn-green hidden-xs" type="submit"><i class="glyphicon glyphicon-search"></i></button>
					</form>
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