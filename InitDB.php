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
			$this->db->exec('DROP TABLE IF EXISTS Accounts ');
			$this->db->exec('DROP TABLE IF EXISTS Map_Account_Product ');
			$this->db->exec('DROP TABLE IF EXISTS Pictures ');
			$this->db->exec('DROP TABLE IF EXISTS Map_Picture_Product ');
			$this->db->exec('DROP TABLE IF EXISTS Tags ');
			$this->db->exec('DROP TABLE IF EXISTS Map_Tag_Product ');		
			
		}
		
		public function initalTables(){
			$this->db->exec('CREATE TABLE  Products ( sku TEXT PRIMARY KEY , category TEXT , start_year INTEGER , end_year INTEGER )');
			$this->db->exec('CREATE TABLE  Accounts ( id INTEGER PRIMARY KEY  , account TEXT )');
			$this->db->exec('CREATE TABLE  Map_Account_Product ( id INTEGER PRIMARY KEY  , account TEXT , sku TEXT ,title  TEXT, UNIQUE ( account , sku ))');
			$this->db->exec('CREATE TABLE  Tags ( id INTEGER PRIMARY KEY AUTOINCREMENT , data TEXT , meta TEXT )');
			$this->db->exec('CREATE TABLE  Map_Tag_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , tag_id  INTEGER  , sku TEXT )');
			$this->db->exec('CREATE TABLE  Pictures ( id INTEGER PRIMARY KEY AUTOINCREMENT , name TEXT , size INTEGER , last_modify_date TEXT )');
			$this->db->exec('CREATE TABLE  Map_Picture_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , account_id INTEGER , picture_id INTEGER )');			
		}
		
		public function resetDatabase(){
			$this->deleteTables();
			$this->initalTables();
		}
		

		
		public function importPicture($fileName,$fileSize,$fileModifyDate){
			$stmt = $this->db->prepare('INSERT INTO Pictures (name,size,last_modify_date) values (:name,:size,:lmd)');
			$stmt->bindParam(':name',$fileName,SQLITE3_TEXT);
			$stmt->bindParam(':size',$fileSize,SQLITE3_INTEGER);
			$stmt->bindParam(':lmd',$fileModifyDate,SQLITE3_INTEGER);
			$stmt->execute();		
			$stmt->close();
								
			return $this->db->lastInsertRowID();
		}
		
		public function importAccounts($accounts){
			$this->db->exec('BEGIN');
			for($i = 0;$i<count($accounts);$i++){
				$purifiedData = trim($accounts[$i]);
				$result = $this->db->querySingle('SELECT id FROM Accounts WHERE "'.$purifiedData.'" = account');
					
				if(!isset($result)){
						$stmt = $this->db->prepare('INSERT INTO Accounts (account) values (:acc)');
						$stmt->bindParam(':acc',$purifiedData,SQLITE3_TEXT);
						$stmt->execute();		
						$stmt->close();			
				}	
			}
			$this->db->exec('COMMIT');	
		}
		
		public function importTag($tagData,$meta,$sku){
			
			$purifiedData = trim($tagData);
			$metaData = trim($meta);
								
			$result = $this->db->querySingle('SELECT id FROM Tags WHERE "'.$purifiedData.'" = data');
								
			if(isset($result)){
				$this->importMapTagProduct($sku,$result);
			}
			else {
							
				$stmt = $this->db->prepare('INSERT INTO Tags (data,meta) values (:data,:meta)');
				$stmt->bindParam(':data',$purifiedData,SQLITE3_TEXT);
				$stmt->bindParam(':meta',$metaData,SQLITE3_TEXT);								
				$stmt->execute();	
				$lastInsertRowID = $this->db->lastInsertRowID();
				//echo $sku." -> ".$metaData."<br>";
				$this->importMapTagProduct($sku,$lastInsertRowID);
			}
		}
		
		public function importMapTagProduct($sku,$tagID){
			$stmt = $this->db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
			$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
			$stmt->bindParam(':tid',$tagID,SQLITE3_INTEGER);
			$stmt->execute();			
			$stmt->close();			
			
			return $this->db->lastInsertRowID();
		}
		
		public function importProduct($sku,$category,$start_year,$end_year){
			$stmt = $this->db->prepare('INSERT INTO Products ( sku , category , start_year , end_year ) values ( :sku , :category , :start_year , :end_year )');
			$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
			$stmt->bindParam(':category',$category,SQLITE3_TEXT);
			$stmt->bindParam(':start_year',$start_year,SQLITE3_INTEGER);
			$stmt->bindParam(':end_year',$end_year,SQLITE3_INTEGER);		
			$stmt->execute();		
			$stmt->close();		
			return $this->db->lastInsertRowID();
		}
		
		public function importMapPictureProduct($sku,$accountID,$lastInsertRowID){
			$stmt = $this->db->prepare('INSERT INTO Map_Picture_Product (sku,account_id,picture_id) values (:sku,:aid,:pid)');
			$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
			$stmt->bindParam(':aid',$accountID,SQLITE3_INTEGER);
			$stmt->bindParam(':pid',$lastInsertRowID,SQLITE3_INTEGER);
			$stmt->execute();		
			$stmt->close();
			return $this->db->lastInsertRowID();
		}
		
		public function importCSVToDatabase($file_location){
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
					*	7	Accounts
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
						/*Process CSV data for import to table Product */
						$sku = strtolower($data[$headerLine['sku']]);
						$year_arr = str_split(trim($data[$headerLine['year']]),3); 
						$category  = trim($data[$headerLine['category']]);
						
						if(intval($year_arr[0])<20){ $start_year = intval('20'.$year_arr[0]) ;}
						else{$start_year = intval('19'.$year_arr[0]);}
						if(intval($year_arr[count($year_arr)-1])<20){ $end_year = intval('20'.$year_arr[count($year_arr)-1]); }
						else{$end_year = intval('19'.$year_arr[count($year_arr)-1]);}
						
						$this->importProduct($sku,$category,$start_year,$end_year);
						
						/*Process CSV data for import to table Tag */
						for($i=0;$i<count($headerLine);$i++){
							if(($i!=0)&&($i!=1)&&($i!=4)&&($i!=6)){
								if ($i == 3){
									if(preg_match("/[,]/",$data[$i])){
										$temp = explode(',',$data[$i]);
										$models = array_map('trim',$temp);			
										
										for($f=0;$f<count($models);$f++){
											$this->importTag($models[$f],$headerData[$i],$sku);
										}
									}				
								}
								else if($i==7){
									if(preg_match("/[,]/",$data[$i])){
										$temp = explode(',',$data[$i]);
										$accounts = array_map('trim',$temp);			
										
										for($f=0;$f<count($accounts);$f++){
											
										}
									}		
								}
								else {$this->importTag($data[$i],$headerData[$i],$sku);}
							}
						}
					}
				}
				$this->db->exec('COMMIT');
			}			
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
								$sku = $fileInfo->getFilename();
								$info[1] = $f->getFilename();
								$info[2] = $f->getSize();
								$info[3] = $f->getATime();
								
								$lastInsertRowID = $this->importPicture($info[1],$info[2],$info[3]);
								
								$accountID  = $this->db->querySingle('SELECT id FROM Accounts WHERE account = "'.$accounts[$i].'"');
								
								$this->importMapPictureProduct($sku,$accountID,$lastInsertRowID);
							}
						}
					}
				}
			}
			$this->db->exec('COMMIT');			
		}
	
	}
	
	echo "----------------------- DB INIT START -------------------------<br/>";
	$start = (float) array_sum(explode(' ',microtime()));
	
	$acc = ['alvoturk9000','3amotor_com','d2_sport'];
	$rainbowDB = new RainbowDatabase('code-rainbow.db');
	$rainbowDB->resetDatabase();
	$rainbowDB->importAccounts($acc);
	$rainbowDB->scanPicturesToDatabase($acc);
	$rainbowDB->importCSVToDatabase("DB_IMPORT.csv");
	
	$end = (float) array_sum(explode(' ',microtime()));
	echo "<br/>-------------------  COMPLETED in:". sprintf("%.4f", ($end-$start))." seconds ------------------<br/>";	
?>