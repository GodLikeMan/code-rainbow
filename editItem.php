
<div class="container">
	<button title="Close (Esc)" type="button" class="mfp-close">×</button>
	<div class="jumbotron">
		<div class="row"><h2 class="text-uppercase"><i class="fa fa-plus-circle"></i> <?php echo $_GET['sku']; ?></h2></div>
		<div id = "#itemTags" class="row">
			<span class="edit-tag btn btn-default"><i class="fa fa-pencil-square-o"></i> Edit Tags</span>
			<?php
				$query = 'SELECT data FROM Tags AS T join  Map_Tag_Product AS MT
									ON T.id = MT.tag_id
									AND  MT.sku = "'.$_GET['sku'].'"  COLLATE NOCASE';
				$db = new SQLite3('code-rainbow.db');
				$result = $db->query($query);
				while($row = $result->fetchArray()) {
					echo '<span class="product-tag"><i class="fa fa-link"></i> '.$row['data'].'</span>';
				}
			?>
		</div>
		<div id = "DisplayPicture" class="row">
			<div class="row">
				<?php 
					for($i=1;$i<=3;$i++){
						echo '<img class="col-xs-6  col-sm-3 col-md-2 product-picture" src="http://sokietech.com/ebayimages/'.$_GET['account']."/".$_GET['sku']."/".$i.'.jpg">';
					}
				?>
			</div>
		</div>
		<?php echo '<div class="row">Debug Info : '.$_GET['account'].' / '.$_GET['category'].' / '.$_GET['searchTag'].'</div>'; ?>
		
	</div>
</div>