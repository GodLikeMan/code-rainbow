<!DOCTYPE html>
<html>
	<body>
<?php
			$db = new SQLite3('code-rainbow.db');
			$db->exec('DROP TABLE IF EXISTS Products ');
			$db->exec('DROP TABLE IF EXISTS Pictures ');
			$db->exec('DROP TABLE IF EXISTS Map_Picture_Product ');
			$db->exec('DROP TABLE IF EXISTS Tags ');
			$db->exec('DROP TABLE IF EXISTS Map_Tag_Product ');
			$db->exec('CREATE TABLE  Products ( sku TEXT PRIMARY KEY , category TEXT , start_year INTEGER , end_year INTEGER ,title TEXT)');
			
			$db->exec('CREATE TABLE  Tags ( id INTEGER PRIMARY KEY AUTOINCREMENT , data TEXT , meta TEXT )');
			$db->exec('CREATE TABLE  Map_Tag_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , tag_id  INTEGER )');
			
			$db->exec('CREATE TABLE  Pictures ( id INTEGER PRIMARY KEY AUTOINCREMENT , name TEXT , size INTEGER , last_modify_date TEXT )');
			$db->exec('CREATE TABLE  Map_Picture_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , account TEXT , picture_id INTEGER )');
			
			$accounts = ['alvoturk9000','3amotor_com','d2_sport'];
			
			$db->exec('BEGIN');
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
								
								$stmt = $db->prepare('INSERT INTO Pictures (name,size,last_modify_date) values (:name,:size,:lmd)');
								$stmt->bindParam(':name',$info[1],SQLITE3_TEXT);
								$stmt->bindParam(':size',$info[2],SQLITE3_INTEGER);
								$stmt->bindParam(':lmd',$info[3],SQLITE3_INTEGER);
								$stmt->execute();		
								$stmt->close();
								
								$lastInsertRowID = $db->lastInsertRowID();
								$stmt = $db->prepare('INSERT INTO Map_Picture_Product (sku,account,picture_id) values (:sku,:account,:pid)');
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
			$db->exec('COMMIT');
	

	//will create a class for process the csv data 
	$headerLine = [];		
	$headerData = [];
	if (($handle = fopen("DB_IMPORT.csv", "r")) !== FALSE) {
		$db->exec('BEGIN');
		
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
				
				$stmt = $db->prepare('INSERT INTO Products ( sku , category , start_year , end_year , title ) values ( :sku , :category , :s_year , :e_year , :title )');
				$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
				$stmt->bindParam(':category',$category,SQLITE3_TEXT);
				$stmt->bindParam(':s_year',$s_year,SQLITE3_INTEGER);
				$stmt->bindParam(':e_year',$e_year,SQLITE3_INTEGER);
				$stmt->bindParam(':title',$data[$headerLine['title']],SQLITE3_TEXT);
				
				$stmt->execute();		
				$stmt->close();
				
				for($i=0;$i<count($headerLine);$i++){
					if(($i!=0)&&($i!=1)&&($i!=4)&&($i!=6)){
					
						$result = $db->query('SELECT id FROM Tags WHERE "'.trim($data[$i]).'" = data');
						
						if($arr = $result->fetchArray()){
							$stmt = $db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
							$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
							$stmt->bindParam(':tid',$arr['id'],SQLITE3_INTEGER);
							$stmt->execute();		
							$stmt->close();
						}
						else {
							$purifiedData = trim($data[$i]);
							$metaData = $headerData[$i];
							$stmt = $db->prepare('INSERT INTO Tags (data,meta) values (:data,:meta)');
							$stmt->bindParam(':data',$purifiedData,SQLITE3_TEXT);
							$stmt->bindParam(':meta',$metaData,SQLITE3_TEXT);
							
							$stmt->execute();	
							$lastInsertRowID = $db->lastInsertRowID();
							echo $sku." -> ".$metaData."<br>";
							$stmt->reset();
							
							$stmt = $db->prepare('INSERT INTO Map_Tag_Product (sku  , tag_id) values (:sku,:tid)');
							$stmt->bindParam(':sku',$sku,SQLITE3_TEXT);
							$stmt->bindParam(':tid',$lastInsertRowID,SQLITE3_INTEGER);
							$stmt->execute();		
							
							$stmt->close();
						}
					}
				}
			}
		}
		$db->exec('COMMIT');
	}
?>
	</body>
</html>