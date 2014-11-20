$("img.lazy").lazyload({
	effect: "fadeIn",
	event: "scrollstop"
});
	
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

$(".tag-list-item").on("click",function(){
	refreshProgressBar("tagCapacityBar",-10);
});

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

//Add new Tags on Product
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
		//console.log(json);
		var	json = $.parseJSON(jsonString);
		if( checkExecutionStatus(json)){
			refreshProductTags(json);
			refreshProgressBar("tagCapacityBar",10);
			$("#edit-tag-form").trigger("reset");
			outputInfo(json);
		}

	});
});

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

/* Rewrite json comunications */
function refreshProductTags(json){
	proceedHtml ="";
	tagNum=0;

	console.log(json);
	console.log(json.info);
	console.log(json.exec);


	for(var i = 0 ; i <Object.keys(json['get_tags_by_sku']).length;i++){
		proceedHtml += '<li class="tag-list-item tag-id-'+json["get_tags_by_sku"][i].id+'"><h4><span class="label label-primary"><i class="fa fa-arrows"></i> '+ json["get_tags_by_sku"][i].data+'</span></h4></li>';
		tagNum++;	
	}
	
	if(tagNum === json.tagCapacity.value ){
		console.log(tagNum);
		console.log(json.tagCapacity.value);
		$(".tagInputSet").hide();
		$(".tagInputSet input , .tagInputSet button").attr('disabled','disabled');
	}
	
	$(".tag-list-item").remove();
	$("#tag-list-header").after(proceedHtml);
	
}

$("#exit").on('click',function(){
	var magnificPopup = $.magnificPopup.instance;
	magnificPopup.close();
});

