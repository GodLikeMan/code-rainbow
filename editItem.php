<div class="container">
	<div class="jumbotron">
		<h2 class="text-uppercase"><i class="fa fa-plus-circle"></i> <?php echo $_GET['sku']; ?></h2>
		<div id = "#itemTags">
			<span ><i class="fa fa-pencil-square-o"></i> tag</span>
			<?php
				
			?>
		</div>
		<div id = "DisplayPicture">
			<div class="row">
				<?php 
					for($i=1;$i<=3;$i++){
						echo '<img class="col-xs-12  col-sm-3 col-md-2" src="http://sokietech.com/ebayimages/'.$_GET['account']."/".$_GET['sku']."/".$i.'.jpg">';
					}
				?>
			</div>
		<?php echo "Debug Info : ".$_GET['account'].' / '.$_GET['category'].' / '.$_GET['searchTag'] ?>
			
		</div>
	</div>
</div>