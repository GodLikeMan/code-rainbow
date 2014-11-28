<?php
	function selectTagsFromProduct(){
		$query = 'SELECT T.data ,T.meta , T.id FROM Tags AS T join  Map_Tag_Product AS MT
							ON T.id = MT.tag_id
							AND  MT.sku = "'.$_GET['sku'].'"  COLLATE NOCASE';
		$db = new SQLite3('code-rainbow.db');
		$result = $db->query($query);	
		return $result;
	}
	
?>

<div class="container">
	<button title="Close (Esc)" type="button" class="mfp-close">×</button>
	<div class="jumbotron">
		<div class="row"><h2 class="text-uppercase col-xs-12 product-page-title"><i class="fa fa-coffee"></i> <?php echo $_GET['sku']; ?></h2></div>
		<div  class="row">
			<div id = "itemTags" class="col-xs-12">
				<div id="edit-area" class="clearfix">
				<?php 
					$result = selectTagsFromProduct();
					$tagNum = 0;
					$tagListHtml = "";
					$tagType ="";
					echo '<input id="editSku" type="hidden" value="'.$_GET['sku'].'" >';
					while($row = $result->fetchArray()) {
						if($row['meta']=='tag'||$row['meta']==""){
							$tagType = 'label-info';
						}
						else{$tagType = 'label-primary'; }
						$tagListHtml .= '<li class="tag-list-item col-xs-12"><span class="label '.$tagType.'">'.$row['data'].'</span><button class="tag-list-item-control pull-right" type="button" data-tag-id="'.$row["id"].'" data-tag-data="'.$row["data"].'" ><i class="fa fa-times"></i></button></li>';
						$tagNum++;	
					}
					echo '<form id="edit-tag-form">';
					echo '<ul class="row"><li class="col-xs-12"><h4>Tag Capacity</h4></li>';
					echo '<li class="col-xs-12"><div class="progress"><div id="tagCapacityBar" class="progress-bar  progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="'.($tagNum*10).'" aria-valuemin="0" aria-valuemax="100" style="width: '.($tagNum*10).'%;">'.($tagNum*10).'%</div></div></li>';
					echo '<li id="tag-list-header" class="col-xs-12"><h4>Product Tags</h4></li>';
					echo $tagListHtml;
					
					if($tagNum < 10){
						echo '<li class="tagInputSet col-xs-12"><div class="input-group input-group-sm"> <span class="input-group-btn"><button class="btn btn-default" type="submit"><i class="fa fa-check"></i></button></span><input  id="tagDataInput" class="form-control" type="text" maxlength="30" required placeholder="Add new tag"></div></li>';
					}
					echo '<li class="col-xs-12"><button id="end-edit-tag" class="btn btn-default pull-right" type="button"><i class="fa fa-undo"></i>  End Edit Tag</button></li>';
					echo "</ul></form>";		
				?>
				</div>
				<div id="tag-display-area">
				<span class="edit-tag btn btn-default"><i class="fa fa-pencil-square-o"></i> Edit Tags </span>
				<?php
					$result = selectTagsFromProduct();
					while($row = $result->fetchArray()) {
						echo '<span class="product-tag"><i class="fa fa-link"></i> '.$row['data'].'</span>';
					}
				?>
				</div>
			</div>
		</div>
		<div id = "product-pictures" class="row">
			<?php 
				for($i=1;$i<=3;$i++){
					echo '<div class="col-xs-12 col-sm-4"><img class="lazy product-picture" src="http://sokietech.com/ebayimages/'.$_GET['account']."/".$_GET['sku']."/".$i.'.jpg">';
					echo '<select  class="selectpicker picture-number"  data-style="btn-green" data-width="100%">
					<option >1</option><option>2</option><option>3</option><option>Delete</option>
					</select>';
					echo '</div>';
				}
			?>
		</div>
		<?php //echo '<div class="text-center">Debug Info : '.$_GET['account'].' / '.$_GET['category'].' / '.$_GET['searchTag'].'</div>'; ?>
		<?php echo '<div class="text-center"><button id="exit" class="btn btn-default" type="button"><i class="fa fa-caret-square-o-up"></i> Exit</button></div>'; ?>
	</div>
	<div id="edit-iem-message"></div>
</div>
<?php echo '<script src="editItem.js"></script>'; ?>