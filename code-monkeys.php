<?php	
	/*
		Pay banana get Monkeys  to work!
	*/
	class  CodeMonkeys
	{
		private $works;
		private $data;
		private $info = [ ];

		function __construct($post_data) {
			$this->works = $post_data['query'];
			$this->data = $post_data;
		}
		
		public function refreshProductList($account,$limit,$category,$searchTag){
			$filter = "";
			if(($category != "NULL")&&($category != "all")){
				$filter = ' AND MP.sku IN (SELECT Pro.sku FROM Products AS Pro WHERE Pro.category = "'.$category.'" COLLATE NOCASE) ';
			}
			$filter2 = "";
			if(($searchTag != "NULL")&&($searchTag != "")){
				$filter2 = ' AND MP.sku IN (SELECT MT.sku FROM Tags AS T INNER JOIN Map_Tag_Product AS MT 
									ON T.id = MT.tag_id AND T.data = "'.$searchTag.'" COLLATE NOCASE) ';
			}
			$finalFilter ="";
			if($limit!="all"){
				$finalFilter  = ' ORDER BY MP.sku ASC LIMIT '.$limit.' COLLATE NOCASE'; 
			}
			
			$query = 'SELECT MP.sku , MP.account , P.name  , P.size , P.last_modify_date FROM Pictures AS P
								INNER JOIN  Map_Picture_Product AS MP 
								ON MP.picture_id = P.id  
								AND P.name = "1.jpg" 
								AND MP.account = "'.$account.'" '
								.$filter.$filter2.$finalFilter;
								
			$this->searchDB($query,'refreshed_list',"No Matched Items");		
		}
		
		public function getCategoryFromAccount($account){
			$query = 'SELECT DISTINCT P.category FROM Products AS P INNER JOIN  Map_Picture_Product AS MP 
								ON P.sku = MP.sku AND MP.account = "'.$account.'" COLLATE NOCASE';
			$this->searchDB($query,'category_list','No Category');
		}
		
		
		public function getTagCloud($category){
			$ca ="";
			if($category!="all"){
				$ca = 'WHERE Pro.category = "'.$category.'"  COLLATE NOCASE';
			}
			$query ='SELECT DiSTINCT T.data,T.id FROM Products AS Pro ,Tags AS T '.$ca.' ORDER BY RANDOM() LIMIT 10 ';

			$this->searchDB($query,'tag_cloud','Not found any tags !');
		}
		
		public function saveToDB($query,$array_key,$error_msg){
			/*$link = $this->getDBLink();
			
			if($result = mysqli_query($link, $query)){
				$this->info[$array_key]  = 'success';
			}
			else{  if($error_msg!=false) die( json_encode(array('message' => 'ERROR', 'code' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣')));	}
			
			mysqli_close($link);	*/
		}
		
		public function searchDB($query,$array_key,$error_msg){
			
			$db = new SQLite3('code-rainbow.db');
			$result = $db->query($query);
			
			if($result->fetchArray()){
				$i = 0;
				$result->reset();
				while($row = $result->fetchArray()) {
					$this->info[$array_key][$i] =$row;
					$i++;
				}
			}
			else{ if($error_msg!=false) die( json_encode(array('message' => 'ERROR', 'code' =>$error_msg))); }
		}
		
		public function searchDBDebug($query,$array_key,$error_msg){
			
			$db = new SQLite3('code-rainbow.db');
			$result = $db->query($query);
			
			die( json_encode(array('message' => 'ERROR', 'code' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣'.$query." ".$error_msg)));
		}
		
		public function outputJSON(){
			echo json_encode($this->info);	
			unset($this->info); 
		}
		
		public function monkeyWorks(){
			if($this->works==='display_refresh'){
				if(!isset($_POST['category'])){$_POST['category']="NULL";}
				if(!isset($_POST['searchTag'])){$_POST['searchTag']="NULL";}
				
				$this->refreshProductList($_POST['selectedAccount'],$_POST['displayLimit'],$_POST['category'],$_POST['searchTag']);
			}
			else if($this->works==='get_category'){
				$this->getCategoryFromAccount($_POST['selectedAccount']);
			}
			else if($this->works === 'get_tag_cloud'){
				$this->getTagCloud($_POST['category']);
			}
			else{echo json_encode(array('message' => 'ERROR', 'code' => $_POST['query']));	}
			
			$this->outputJSON();
		}
	}	
	
	$cm = new CodeMonkeys($_POST);
	$cm->monkeyWorks();
?>