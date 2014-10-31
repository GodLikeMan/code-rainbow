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
		
		public function refreshProductList($account,$limit,$term,$searchTerm){
			$filter = "";
			if($term != "NULL"){
				$filter = ' AND M.sku IN (SELECT MT.sku FROM Tags as T INNER JOIN Map_Tag_Product as MT 
												ON T.id = MT.tag_id AND T.data = "'.$term.'" COLLATE NOCASE) ';
			}
			$filter2 = "";
			if($searchTerm != "NULL"){
				$filter2 = ' AND M.sku IN (SELECT MT.sku FROM Tags as T INNER JOIN Map_Tag_Product as MT 
												ON T.id = MT.tag_id AND T.data = "'.$searchTerm.'" COLLATE NOCASE) ';
			}
			$query = 'SELECT M.sku , M.account , P.name  , P.size , P.last_modify_date FROM Pictures as P
								INNER JOIN  Map_Picture_Product as M 
								ON M.picture_id = P.id  
								AND P.name = "1.jpg" 
								AND M.account = "'.$account.'" '
								.$filter.$filter2.
								' ORDER BY M.sku ASC LIMIT '.$limit.' COLLATE NOCASE'; 
			$this->searchDB($query,'refreshed_list','No Item Matches!');		
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
				while($row = $result->fetchArray()){
					$this->info[$array_key][$i] =$row;
					$i++;
				}
			}
			//else{ if($error_msg!=false) die( json_encode(array('message' => 'ERROR', 'code' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣'.$error_msg))); }
			else{ if($error_msg!=false) die( json_encode(array('message' => 'ERROR', 'code' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣'.$query))); }
			
		}
		
		public function outputJSON(){
			echo json_encode($this->info);	
			unset($this->info); 
		}
		
		public function monkeyWorks(){
			if($this->works==='display_refresh'){
				if(!isset($_POST['term'])){$_POST['term']="NULL";}
				if(!isset($_POST['searchTerm'])){$_POST['searchTerm']="NULL";}
				
				$this->refreshProductList($_POST['selectedAccount'],$_POST['displayLimit'],$_POST['term'],$_POST['searchTerm']);
			}
			else{echo json_encode(array('message' => 'ERROR', 'code' => $_POST['query']));	}
			
			$this->outputJSON();
		}
	}	
	
	$cm = new CodeMonkeys($_POST);
	$cm->monkeyWorks();
?>