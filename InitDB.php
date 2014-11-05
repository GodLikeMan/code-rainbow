<!DOCTYPE html>
<html>
	<body>
<?php
	class RainbowDatabase {
		
		private $db;
		private $db_location;
		
		function __construct($location) {
			$this->connectDatabase($location);
			$this->db_location = $location;
		}
		
		public function connectDatabase($location){
			$this->db = new SQLite3($location);
		}
		
		public function deleteDatabase(){
			unlink($this->db_location);
		}
		
		public function deleteTables(){
			$this->db->exec('DROP TABLE IF EXISTS Products ');
			$this->db->exec('DROP TABLE IF EXISTS Pictures ');
			$this->db->exec('DROP TABLE IF EXISTS Map_Picture_Product ');
			$this->db->exec('DROP TABLE IF EXISTS Tags ');
			$this->db->exec('DROP TABLE IF EXISTS Map_Tag_Product ');			
		}
		
		public function initalTables(){
			$this->db->exec('CREATE TABLE  Products ( sku TEXT PRIMARY KEY , category TEXT , start_year INTEGER , end_year INTEGER ,title TEXT)');
			$this->db->exec('CREATE TABLE  Tags ( id INTEGER PRIMARY KEY AUTOINCREMENT , data TEXT , meta TEXT )');
			$this->db->exec('CREATE TABLE  Map_Tag_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , tag_id  INTEGER )');
			$this->db->exec('CREATE TABLE  Pictures ( id INTEGER PRIMARY KEY AUTOINCREMENT , name TEXT , size INTEGER , last_modify_date TEXT )');
			$this->db->exec('CREATE TABLE  Map_Picture_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , account TEXT , picture_id INTEGER )');			
		}
		
		public function resetDatabase(){
			$this->deleteTables();
			$this->initalTables();
		}
		
		public function scanPicturesToDatabase($accounts){
			
			$this->db->exec('BEGIN');
			for($i=0;$i<count($accounts);$i++){
		
				foreach (new DirectoryIterator('../ebayimages/'.$accounts[$i].'/') as $fileInfo) {
					if($fileInfo->isDot()) {continue;}
					if($fileInfo->isDir()) {
						foreach (new DirectoryIterator('../ebayimages/'.$accounts[$i].'/'.$fileInfo->getFilename()) as $f) {
							if($f->getExtension() == 'jpg') {
							
								//echo $f->getPathname()."<br>";	
								/*avoid warning*/
								$info[0] = $fileInfo->getFilename();
								$info[1] = $f->getFilename();
								$info[2] = $f->getSize();
								$info[3] = $f->getATime();
								
								$stmt = $this->db->prepare('INSERT INTO Pictures (name,size,last_modify_date) values (:name,:size,:lmd)');
								$stmt->bindParam(':name',$info[1],SQLITE3_TEXT);
								$stmt->bindParam(':size',$info[2],SQLITE3_INTEGER);
								$stmt->bindParam(':lmd',$info[3],SQLITE3_INTEGER);
								$stmt->execute();		
								$stmt->close();
								
								$lastInsertRowID = $this->db->lastInsertRowID();
								$stmt = $this->db->prepare('INSERT INTO Map_Picture_Product (sku,account,picture_id) values (:sku,:account,:pid)');
								$stmt->bindParam(':sku',$info[0],SQLITE3_TEXT);
								$stmt->bindParam(':account',$accounts[$i],SQLITE3_TEXT);
								$stmt->bindParam(':pid',$lastInsertRowID,SQLITE3_INTEGER);
								$stmt->execute();		
								$stmt->close();							
							}
						}
					}
				}
			}
			$this->db->exec('COMMIT');			
		}

		function importTags($tagData,$meta,$sku){
			
			$purifiedData = trim($tagData);
			$metaData = trim($meta);
								
			$result = $this->db->query('SELECT id FROM Tags WHERE "'.$purifiedData.'" = data');
								
			if($arr = $result->fetchArray()){
				$stmt = $this->db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
				$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
				$stmt->bindParam(':tid',$arr['id'],SQLITE3_INTEGER);
				$stmt->execute();		
				$stmt->close();
			}
			else {
							
				$stmt = $this->db->prepare('INSERT INTO Tags (data,meta) values (:data,:meta)');
				$stmt->bindParam(':data',$purifiedData,SQLITE3_TEXT);
				$stmt->bindParam(':meta',$metaData,SQLITE3_TEXT);
									
				$stmt->execute();	
				$lastInsertRowID = $this->db->lastInsertRowID();
				//echo $sku." -> ".$metaData."<br>";
				$stmt->reset();
									
				$stmt = $this->db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
				$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
				$stmt->bindParam(':tid',$lastInsertRowID,SQLITE3_INTEGER);
				$stmt->execute();		
									
				$stmt->close();
			}
		}
			
		function importTagsToDatabase($file_location){
			//will create a class for process the csv data 
			$headerLine = [];		
			$headerData = [];
			if (($handle = fopen($file_location, "r")) !== FALSE) {
				$this->db->exec('BEGIN');
					
				while(($data = fgetcsv($handle,2500,","))!== FALSE){
					/*
					*	0 SKU																	
					*	1 Category
					*	2	Brand 
					*	3	Model
					*	4	Year
					*	5	Surface Finish	
					*	6	Title
					*	
					*	4,6 IN Products
					*/
					if(count($headerLine)===0){
						for($i=0;$i<count($data);$i++){
							$headerLine[strtolower(trim($data[$i]))] = $i;
							$headerData[$i] = strtolower(trim($data[$i]));
						}
					
						print_r($headerData);
					}
					else{
						$sku = strtolower($data[$headerLine['sku']]);
						$year_arr = str_split(trim($data[$headerLine['year']]),3); 
						$category  = trim($data[$headerLine['category']]);
						
						if(intval($year_arr[0])<20){ $s_year = intval('20'.$year_arr[0]) ;}
						else{$s_year = intval('19'.$year_arr[0]);}
						if(intval($year_arr[count($year_arr)-1])<20){ $e_year = intval('20'.$year_arr[count($year_arr)-1]); }
						else{$e_year = intval('19'.$year_arr[count($year_arr)-1]);}
						
						$stmt = $this->db->prepare('INSERT INTO Products ( sku , category , start_year , end_year , title ) values ( :sku , :category , :s_year , :e_year , :title )');
						$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
						$stmt->bindParam(':category',$category,SQLITE3_TEXT);
						$stmt->bindParam(':s_year',$s_year,SQLITE3_INTEGER);
						$stmt->bindParam(':e_year',$e_year,SQLITE3_INTEGER);
						$stmt->bindParam(':title',$data[$headerLine['title']],SQLITE3_TEXT);
						
						$stmt->execute();		
						$stmt->close();
						
						for($i=0;$i<count($headerLine);$i++){
							if(($i!=0)&&($i!=1)&&($i!=4)&&($i!=6)){
								if ($i == 3){
									if(preg_match("/[,]/",$data[$i])){
										$temp = explode(',',$data[$i]);
										$model = array_map('trim',$temp);			
										
										for($f=0;$f<count($model);$f++){
											$this->importTags($model[$f],$headerData[$i],$sku);
										}
									}				
								}
								else {$this->importTags($data[$i],$headerData[$i],$sku);}
							}
						}
					}
				}
				$this->db->exec('COMMIT');
			}			
		}	
	}
			
	$acc = ['alvoturk9000','3amotor_com','d2_sport'];
	$rainbowDB = new RainbowDatabase('code-rainbow.db');
	$rainbowDB->resetDatabase();
	$rainbowDB->scanPicturesToDatabase($acc);
	$rainbowDB->importTagsToDatabase("DB_IMPORT.csv");
		
?>
	</body>
</html>