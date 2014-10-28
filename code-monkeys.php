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
		
		public function refreshProductList($limit,$term){
			
			$query = 'SELECT M.sku , P.name  , P.size , P.last_modify_date FROM Pictures as P INNER JOIN  Map_Picture_Product as M WHERE M.picture_id = P.id  AND P.name = "1.jpg" LIMIT '.$limit; 
			$this->searchDB($query,'refreshed_list','This Sku not found on DB');		
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
			
			if($result = $db->query($query)){
				$i = 0;
				while($row = $result->fetchArray()){
					$this->info[$array_key][$i] =$row;
					$i++;
				}
			}
			else{ if($error_msg!=false) die( json_encode(array('message' => 'ERROR', 'code' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣'))); }
			
		}
		
		public function outputJSON(){
			echo json_encode($this->info);	
			unset($this->info); 
		}
		
		public function monkeyWorks(){
			if($this->works==='display_refresh'){
				$this->refreshProductList($_POST['displayLimit'],$_POST['term']);
			}
			else{echo json_encode(array('message' => 'ERROR', 'code' => $_POST['query']));	}
			
			$this->outputJSON();
		}
	}	
	
	$cm = new CodeMonkeys($_POST);
	$cm->monkeyWorks();
?>