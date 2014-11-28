function refreshListAjax(){
	console.log("refreshListAjax   : "+"/selectedAccount/-> "+$.cookie('selectedAccount')+" /category/-> "+$.cookie('category')+" /searchTag/->"+$.cookie("searchTag")+" /limit/->"+$.cookie("displayLimit"));
	$.ajax({	type :	'POST',
					url	:	"code-monkeys.php",
					async : true,
					data : {'selectedAccount':$.cookie('selectedAccount'),'displayLimit':$.cookie('displayLimit'),'category':$.cookie('category'),'searchTag':$.cookie('searchTag'),'query':'display_refresh'}
	}).done(function(json){
		refreshList(json);
	});					
}

function refreshList(json){
	console.log(json);
	var	p = $.parseJSON(json);
	proceedHtml ="";
	$("#item-display").empty();
	
	if(p['code']=='ERROR'){ 
		console.log(p['message']);
		proceedHtml = '<h1 class="warning">'+p['code']+'</h1>';
	}
	else {
		console.log(p['refreshed_list'].length+" items");
		
		initTagCloudAjax();
		
		for(var i = 0 ; i < p['refreshed_list'].length ; i++){
			file = p['refreshed_list'][i].account+'/'+p['refreshed_list'][i].sku+'/'+p['refreshed_list'][i].name;
		
			var fDate = formatDate(p['refreshed_list'][i].last_modify_date*1000);
			var editItemHref = 'editItem.php?test=ok&sku='+p['refreshed_list'][i].sku+'&account='+$.cookie("selectedAccount")+'&category='+$.cookie('category')+'&searchTag='+$.cookie('searchTag');
			proceedHtml +=	'<div class="image-container col-xs-12  col-sm-3 col-md-2"><a class="editItemAjax" href="'+editItemHref+'">'+
						'<img class="cover-image lazy"  data-original="http://sokietech.com/ebayimages/'+file+'"/ ></a>'+	
						'<div class="image-folder-name"><span class="image-attribute">'+p['refreshed_list'][i].sku+'</span></div>'+
						'<div class="image-attribute-row"><span class="image-attribute">'+Math.round(p['refreshed_list'][i].size/(1024))+' KB </span><span class="image-attribute">'+fDate+'</span></div>'+
						'</div>';	
		}
		console.log("Refresh successed : "+"/selectedAccount/-> "+$.cookie('selectedAccount')+" /category/-> "+$.cookie('category')+" /searchTag/->"+$.cookie("searchTag")+" /limit/->"+$.cookie("displayLimit"));					
	}
	
	$("#item-display").html(proceedHtml);
	$("img.lazy").lazyload({
		effect: "fadeIn",
		event: "scrollstop"
	});
	
	$(".editItemAjax").magnificPopup({
		type:'ajax',
		enableEscapeKey:false,
		closeOnBgClick:false,
		showCloseBtn:true,
		closeBtnInside:true
	});
}	

function initTagCloudAjax(){
	if(!$.cookie('category')){$.cookie('category','all');}
	$.ajax({	type :	'POST',
					url	:	"code-monkeys.php",
					async : true,
					data : {'selectedAccount':$.cookie('selectedAccount'),'category':$.cookie('category'),'query':'get_tag_cloud'},
	}).done(function (json) {
			//console.log(json);
			createTagCloud(json);
		});
}

function createTagCloud(json){
	
	var	p = $.parseJSON(json);
	var  tagCloudHTML= "";
	
	if(p['code']=='ERROR'){ 
		console.log(p['message']);
		tagCloudHTML = '';
	}
	else {
		for(var i = 0;i<p['tag_cloud'].length;i++){
			tagCloudHTML += '<h4><span class="label label-info" data-tag-id="'+p['tag_cloud'][i].id+'">'+ p['tag_cloud'][i].data+'</span></h4>' ;
		}
		
	}	
	$("#tag-cloud").html(tagCloudHTML);
	
	$("#tag-cloud .label").on('click',function(){
		$("#search-tag").val($(this).html()).submit();
		console.log($(this).html());
	});
}

function refreshCategorySelector(){

	$('#category-select').empty();
	$.ajax({	type :	'POST',
					url	:	"code-monkeys.php",
					async : true,
					data : {'selectedAccount':$.cookie('selectedAccount'),'query':'get_category'}
	}).done(function(json){
		var	p = $.parseJSON(json);
		
		if(p['code']=='ERROR'){ 
			$('#category-select').selectpicker('hide');
			console.log(p['message']);
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
	//$('.selectpicker').selectpicker('setStyle', 'btn-lg', 'add');
	//$('.selectpicker').selectpicker('setStyle', 'hidden', 'add');
}

function formatDate(timestamp){
	var fDate = new Date(timestamp);
	months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	var d = fDate.getDate();
	var m =  months[fDate.getMonth()];
	var y = fDate.getFullYear();
	
	return  m+' '+d+' '+y;
}

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
}

function checkExecutionStatus(json){
	if(json.exec.status === "FAILED" ){
		console.log(json.exec.message);
		return false;
	}
	else if(json.exec.status === "SUCCESS" ){
		return true;
	}
}

function outputInfo(json){
	if (json.info !== undefined){
		for(var i = 1 ; i <= Object.keys(json.info).length ; i++){
			console.log("code="+json.info[i].code+"  message="+json.info[i].message);
		}
	}
}

$(document).ready(function(){
	
	//dropzone test
	$("#drop-zone").on("dragover",function(event){
		event.preventDefault();
        $("#drop-zone").addClass('drop');
	});
	
	$("#drop-zone").on("dragleave",function(event){
		event.preventDefault();
        $("#drop-zone").removeClass('drop');
	});		

	$("#drop-zone").on("drop",function(event){
		event.preventDefault();
        $("#drop-zone").removeClass('drop');
		console.log(event.originalEvent.dataTransfer.files);
	});	
	
	
	/* Buggy as fuck*/
	$(".upload-drop-zone").on("click",function(){
		console.log("???");
        $("#file-uploader").trigger('click');
	});
	
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
});
