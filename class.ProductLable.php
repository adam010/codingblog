<?php
# database: start connection
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once($_SERVER['DOCUMENT_ROOT']."classes/class.Database.php");
$database = new Database();
$db = $database->connect();
if(!$database->connected){
	header("HTTP/1.1 503 Service Unavailable");
	header("Location: http://www.mpmoil.nl/errors/503.php");
	die();
}

class  productLabel extends products{
	//r ID ;
	//r plID; //private label ID
	var $code;
	var $articleID;
	var $title;
	var $note;
	var $filename;
	var $version;
	var $currentdate;

	
	function __construct(Database $db,Text $text,$language=null,$plID=null) {
	
		parent::__construct($db,$text,$language,$plID);	
		
			
   }
	
	public function newLabel(request $request){
		$this->item = $this->getProduct($id);
		$this->filename = $request->filename;
		$this->version = $request->version;

	}
	public function newProductLabel($id){
		$this->insertProductLabel(findItem($id));
	}
	

	public function reviseLabel(){
		
	}
	public function lableList(){
		
	}
	public function findItem($id){
		$item = $this->getProduct($id);
		if($item){
			$code =      $this->item($id);
			$articleID = $this->item($id);
			$title =     $this->item($id);

			return [$code,$articleID,$title];

		}

	}
	public function updateProductLabel($item){
		if isset($this->)
		$updateProductStats = $this->db->prepare("UPDATE product_stats SET hits = :currentHits WHERE ID = :statID");
		$updateItemLabel = $this->db->prepare("Up INTO itemlabel (articleID,plID,code,title,note,version,date,) VALUES (:articleID,:plID,:code,:title,:note,:version,:date,'1')");
		$updateItemLabel->bindValue(":articleID",$item['articleID'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":articleID",$item['articleID'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":code",$item['code'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":title",$item['title'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":filename",$this->filename,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":note",$this->note,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":version",$this->version,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":date",date('Y-m-d H:i:s'),PDO::PARAM_INT);
		$updateItemLabel->execute();
	}
	public function insertProductLabel($item){
		if isset($this->)
		$addItemLabel = $this->db->prepare("INSERT INTO itemlabel (articleID,plID,code,title,note,version,date,) VALUES (:articleID,:plID,:code,:title,:note,:version,:date,'1')");
		$addItemLabel->bindValue(":articleID",$item['articleID'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":articleID",$item['articleID'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":code",$item['code'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":title",$item['title'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":filename",$this->filename,PDO::PARAM_INT);
		$addItemLabel->bindValue(":note",$this->note,PDO::PARAM_INT);
		$addItemLabel->bindValue(":version",$this->version,PDO::PARAM_INT);
		$addItemLabel->bindValue(":date",date('Y-m-d H:i:s'),PDO::PARAM_INT);
		$addItemLabel->execute();
	}

}
 /*$this->ID= 
		  $this->plID
		  $this->code
		  $this->articleNumber
		  $this->title
		  $this->note
		  $this->filename
		  $this->version
		  $this->currentdAE

		$this- =$product->Item($request->id);	
		}
		
		$this->*/
;?>