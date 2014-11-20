<?php	
	/*
		Pay banana get Monkeys  to work!
	*/
	
	class  CodeMonkeys
	{
		private $works;
		private $data;
		private $info = [ ];
		private $messageCounter = 0;
		private $tagCapacity = 10;

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
			
			$query = 'SELECT MP.sku , MP.account_id , A.account , P.name  , P.size , P.last_modify_date
								FROM Pictures AS P 
								INNER JOIN Map_Picture_Product AS MP 
								ON MP.picture_id = P.id
								INNER JOIN Accounts AS A
								ON MP.account_id = (select id from Accounts where account = "'.$account.'") 
								AND MP.account_id = A.id
								AND P.name = "1.jpg" '
								.$filter.$filter2.$finalFilter;
								
			$this->searchDB($query,'refreshed_list',"No Matched Items");		
		}
		
		private function getAccountId($account){
			$query = 'SELECT id FROM Accounts WHERE account = "'.$account.'"';
			$db = new SQLite3('code-rainbow.db');
			$result = $db->querySingle($query);
			return $result;
		}
		
		public function getCategoryByAccount($account){
			$query = 'SELECT DISTINCT P.category FROM Products AS P INNER JOIN  Map_Picture_Product AS MP 
								ON P.sku = MP.sku AND MP.account_id in  (SELECT id FROM Accounts WHERE "'.$account.'" = account COLLATE NOCASE ) ';
			$this->searchDB($query,'category_list','No Category');
		}
		
		public function getTagCapacity(){
			$this->info['tagCapacity'] = array('value' => $this->tagCapacity);
			return $this->tagCapacity;
		} 
		
		public function getTagQuantity($sku){
			$query = 'SELECT COUNT(MTP.tag_id) AS counter FROM Map_Tag_Product AS MTP INNER JOIN Products AS P  ON 
								MTP.sku  = P.sku AND MTP.sku = "'.$sku.'" COLLATE NOCASE'; 
			
			$db = new SQLite3('code-rainbow.db');
			$result = $db->querySingle($query);
			return $result;			
		}
		
		public function addTag($sku,$tagData){
			$tq = $this->getTagQuantity($sku);
			$tc =  $this->getTagCapacity();
			$result = ($tq+1 <= $tc);
			
			$this->info['debug'] = array('tq' => $tq , 'tc' => $tc , 'result' => $result );
			
			if($this->getTagQuantity($sku)+1 <= $this->getTagCapacity() ){
				$db = new SQLite3('code-rainbow.db');
				$result = $db->querySingle('SELECT id FROM Tags WHERE "'.$tagData.'" = data');
				$msg;
				
				if(isset($result)){
					$this->insertMapTagProduct($sku,$result);
				}
				else {
					$tagMeta = "tag";
					$stmt = $db->prepare('INSERT INTO Tags (data,meta) values (:data,:meta)');
					$stmt->bindParam(':data',$tagData,SQLITE3_TEXT);
					$stmt->bindParam(':meta',$tagMeta,SQLITE3_TEXT);								
					$stmt->execute();	
					$lastInsertRowID = $db->lastInsertRowID();
					$msg = $db->lastErrorMsg();

					$this->info['info'][++$this->messageCounter] = array('code' => 'SUCCESS', 'message' =>'Insert Tag "'.$tagData.'" Successed !');
					
					/*$this->info['add_tag'][0] = array('data' => 'test-hello', 'id' =>7788);
					$this->info['add_tag'][1] = array('data' => 'test-wtf', 'id' =>5566);*/
					$this->insertMapTagProduct($sku,$lastInsertRowID);
				}				
				
				if($msg !== "not an error"){
					$this->info['exec'] = array('status' => 'FAILED' , 'message' => $db->lastErrorMsg() );
				}
				else {$this->info['exec'] = array('status' => 'SUCCESS' , 'message' => "Seems okay bro !" ); }
			}
			else { $this->info['exec'] = array('status' => 'FAILED' , 'message' => "Tag Capacity is Full !!!" ); }
		}
		
		public function insertMapTagProduct($sku,$tagID){
			$db = new SQLite3('code-rainbow.db');
			$stmt = $db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
			$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
			$stmt->bindParam(':tid',$tagID,SQLITE3_INTEGER);
			$stmt->execute();			
			$stmt->close();			
			
			/*if($db->changes()>0){echo json_encode(array('code' => 'SUCCESS', 'message' =>"Map Tag Successed !"));}
			else{echo json_encode(array('code' => 'ERROR', 'message' =>"Map Tag Error!!!"));}*/
			$this->info['info'][++$this->messageCounter] = array('code' => 'SUCCESS', 'message' =>'Map "'.$sku.'" Successed !');
			//return $db->lastInsertRowID();
		}
		
		public function getTagsBySku($sku){
			$query = 'SELECT T.data , T.id  FROM Tags AS T join  Map_Tag_Product AS MTP
								ON T.id = MTP.tag_id
								AND  MTP.sku = "'.$sku.'"  COLLATE NOCASE';
								
			$this->searchDB($query,'get_tags_by_sku','Not found any tags from the product!');		
		}
		
		public function getTagCloud($category,$account){
			$ca ="";
			$aid = $this->getAccountId($account);
			
			if($category!="all"){
				$ca = 'AND Pro.category = "'.$category.'"  COLLATE NOCASE';
			}
			$query ='SELECT DiSTINCT T.data,T.id 
			FROM Map_Tag_Product AS MT 
			join Tags AS T on MT.tag_id = T.id 
			join Products AS Pro on MT.sku = Pro.sku 
			join Map_Picture_Product as MP ON
			MP.sku = Pro.sku AND MP.account_id = "'.$aid.'"		
			'.$ca.' ORDER BY RANDOM() LIMIT 10 ';

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
			else{ if($error_msg!=false) die( json_encode(array('code' => 'ERROR', 'message' =>$error_msg))); }
		}
		
		public function searchDBDebug($query,$array_key,$error_msg){
			
			$db = new SQLite3('code-rainbow.db');
			$result = $db->query($query);
			
			die( json_encode(array('code' => 'ERROR', 'message' => '◢▆▅崩▄▃▂╰(〒皿〒)╯▂▃▄潰▅▇◣'.$query." ".$error_msg)));
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
				$this->getCategoryByAccount($_POST['selectedAccount']);
			}
			else if($this->works === 'get_tag_cloud'){
				$this->getTagCloud($_POST['category'],$_POST['selectedAccount']);
			}
			else if($this->works === 'add_tag' ){
				$this->addTag($_POST['sku'],$_POST['tagData']);
				if($this->info['exec']['status'] !== "FAILED" ){
					$this->getTagsBySku($_POST['sku']);
				}
				
			}
			else if($this->works === 'get_tags_by_sku' ) {
				$this->getTagsBySku($_POST['sku']);
			}
			else if($this->works === 'get_tag_quota_info'){
				$this->getTagQuotaInfo($_POST['sku']);
			}
			
			$this->outputJSON();
		}
	}	
	
	$cm = new CodeMonkeys($_POST);
	$cm->monkeyWorks();
?>