if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
	$('.selectpicker').selectpicker('mobile');
}
else{
	$('.selectpicker').selectpicker();

}


$("img.lazy").lazyload({
	effect: "fadeIn",
	event: "scrollstop"
});

//temp 
$(".tag-list-item-control").on("click",deleteTag);
	
$(".edit-tag").on("click",function(){
	console.log("hello world!");
	$("#edit-area").toggle("slow");
	$("#product-pictures").toggle();
	$("#tag-display-area").toggle();
});

$("#end-edit-tag").on('click',function(){
	$("#edit-tag-form").trigger("reset");
	console.log("good bye world!");
	$("#edit-area").toggle("slow");
	$("#product-pictures").toggle("slow");
	$("#tag-display-area").toggle();	
});

function deleteTag(){
	$.cookie('prevDelTag',$(this).data("tagData"));
	$.ajax({	type :	'POST',
					url	:	"code-monkeys.php",
					async : true,
					data : {'sku':$("#editSku").val(),'tagId': $(this).data("tagId"),'query':'delete_tag'},
					beforeSend:function() {
						console.log("loading");
					}				
				}
	).done(function(jsonString){
		console.log(jsonString);
		var	json = $.parseJSON(jsonString);
		if( checkExecutionStatus(json)){
			$("#edit-iem-message").html(alertInfo('success','<strong>'+$.cookie('prevDelTag')+'</strong> tag delete success !'));
			refreshProductTags(json);
			refreshProgressBar("tagCapacityBar",-10);			
		}
		else {
			$("#edit-iem-message").html(alertInfo('danger','<strong>'+$.cookie('prevDelTag')+'</strong> tag delete failed !'));
		}
	});		
}

function refreshProgressBar( targetId ,  value ) {
	var id = '#'+targetId;
	var barMax = parseInt($(id).attr('aria-valuemax'));
	var barMin = parseInt($(id).attr('aria-valuemin'));
	var barNow = parseInt($(id).attr('aria-valuenow'));
	var finalValue = barNow+value;
	
	if ( finalValue >= barMin &&  finalValue <= barMax ) {
		$(id).html(finalValue +"%");
		$(id).css('width', finalValue +'%').attr('aria-valuenow', finalValue );						
	}
}

//Add new Tags on Product  //have to rewrite
$("#edit-tag-form").on('submit',function(event){
	event.preventDefault();
	
	//check tag quota
	
	$.ajax({	type :	'POST',
					url	:	"code-monkeys.php",
					async : true,
					data : {'sku':$("#editSku").val(),'tagData':$("#tagDataInput").val(),'query':'add_tag'},
					beforeSend:function() {
						console.log("loading");
					}				
				}
	).done(function(jsonString){
		
		var	json = $.parseJSON(jsonString);
		if( checkExecutionStatus(json)){
		
			$("#edit-iem-message").html(alertInfo('success','<strong>'+$("#tagDataInput").val()+'</strong> tag add success !'));
			refreshProductTags(json);
			refreshProgressBar("tagCapacityBar",10);
			$("#edit-tag-form").trigger("reset");
			
			
			outputInfo(json);
		}

	});
});

/* Rewrite json comunications */
function refreshProductTags(json){
	console.log(json);
	proceedHtml ="";
	tagNum=0;
	tagType ="";
	
	for(var i = 0 ; i <Object.keys(json['get_tags_by_sku']).length;i++){
		if(json['get_tags_by_sku'][i].meta === 'tag' || json['get_tags_by_sku'][i].meta === undefined ){
			tagType = 'label-info';
		}
		else { tagType = 'label-primary'; }
		proceedHtml += '<li class="tag-list-item col-xs-12"><span class="label '+tagType+'">'+ json["get_tags_by_sku"][i].data+'</span><button class="tag-list-item-control pull-right" type="button" data-tag-id="'+ json["get_tags_by_sku"][i].id+'" data-tag-data="'+ json["get_tags_by_sku"][i].data+'" ><i class="fa fa-times"></i></button></li>';
		tagNum++;	
	}
	
	if(tagNum >= json.tagCapacity.value ){
		console.log(tagNum);
		console.log(json.tagCapacity.value);
		$(".tagInputSet").hide();
		$(".tagInputSet input , .tagInputSet button").attr('disabled','disabled');
	}
	else{
		$(".tagInputSet").show();
		$(".tagInputSet input , .tagInputSet button").removeAttr("disabled"); 
	}
	
	$(".tag-list-item").remove();
	$("#tag-list-header").after(proceedHtml);
	$(".tag-list-item-control").on("click",deleteTag);
}

$("#exit").on('click',function(){
	var magnificPopup = $.magnificPopup.instance;
	magnificPopup.close();
});

function alertInfo(type,message){
	alert = '<div class="alert alert-'+type+' alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>'+message+'</div>';
	return alert;
}

