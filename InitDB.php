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
			$db->exec('CREATE TABLE  Products ( sku TEXT PRIMARY KEY , category TEXT , start_year INTEGER , end_year INTEGER )');
			
			$db->exec('CREATE TABLE  Tags ( id INTEGER PRIMARY KEY AUTOINCREMENT , meta TEXT , data TEXT )');
			$db->exec('CREATE TABLE  Map_Tag_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , tag_id  INTEGER )');
			
			$db->exec('CREATE TABLE  Pictures ( id INTEGER PRIMARY KEY AUTOINCREMENT , name TEXT , size INTEGER , last_modify_date TEXT , account )');
			$db->exec('CREATE TABLE  Map_Picture_Product ( id INTEGER PRIMARY KEY AUTOINCREMENT , sku TEXT , account TEXT , picture_id INTEGER )');
			
			$accounts = ['alvoturk9000','3amotor_com','d2_sport'];
			
			$db->exec('BEGIN');
			for($i=0;$i<count($accounts);$i++){
		
				foreach (new DirectoryIterator('../ebayimages/'.$accounts[$i].'/') as $fileInfo) {
					if($fileInfo->isDot()) {continue;}
					if($fileInfo->isDir()) {
						foreach (new DirectoryIterator('../ebayimages/'.$accounts[$i].'/'.$fileInfo->getFilename()) as $f) {
							if($f->getExtension() == 'jpg') {
							
								echo $f->getPathname()."<br>";	
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
	/*		
	$headerLine = [];		
	if (($handle = fopen("test.csv", "r")) !== FALSE) {
		$db->exec('BEGIN');
		while(($data = fgetcsv($handle,2500,","))!== FALSE){
			/*
			*	0 sku
			*	6 Category
			*	7	ShortDescription
			*	9	Brand 
			*	10	Model
			*	13	Year
			*	16	Surface Finish	
			*	17	Placement
			*/
	/*
			$index = [0,6,9,10,13,17,18];
			if(count($headerLine)===0){
				for($i=0;$i<count($index);$i++){
					$headerLine[$i] = trim($data[$index[$i]]);
				}
				print_r($headerLine);
			}
			else{
			
				$stmt = $db->prepare('INSERT INTO Tags (meta,data) values (:meta,:data)');
				$stmt->bindParam(':meta',      ,SQLITE3_TEXT);
				$stmt->bindParam(':data',      ,SQLITE3_TEXT);
				$stmt->execute();		
				$stmt->close();
								
				echo '<p>'.trim($data[0]).' / '.trim($data[6]).' / '.trim($data[9]).' / '.trim($data[10]).' / '.trim($data[13]).' / '.trim($data[17]).' / '.trim($data[18]).' / '.'</p>'; 
				$year = str_split(trim($data[13]),3);				
			}
		}
		$db->exec('COMMIT');
	}*/
?>
	</body>
</html>