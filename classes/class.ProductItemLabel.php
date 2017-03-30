<?php
# database: start connection
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once($_SERVER['DOCUMENT_ROOT']."classes/class.Database.php");
require_once($_SERVER['DOCUMENT_ROOT']."classes/Text.4.php");
include_once('classes/products.4.php');
include_once('classes/class.ProductItemLabel.php');
class  productItemLabel extends products{
	
		
	var $ID;
	var $plID;
	var $productID=2;
	var $itemID=3010;
	var $title;
	var $note;
	var $filename;
	var $version;
	var $date;
	private $db;
	private $text;	

	
	function __construct(Database $db,Text $text,$language=null,$plID=null) {
		parent::__construct($db,$text,$language,$plID);	
				 
		
   }
	
    public function labelData($product,$item=null){
		$this->getItembyId($product, $item);
		/*$this->item = $this->getProduct($id);
		$this->filename = $request->filename;
		$this->version = $request->version;*/
            
		echo '<pre>';
  var_dump($this->product);
  echo '</pre>';
	}
/*		public function newProductLabel($id){
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
		$updateItemLabel = $this->db->prepare("UPDATE product_itemLabels SET plID =:plID,productID =:productID,itemID=:itemID,title=:title,note=:note,filename=:filename,version=:version,date=:date)";		
		$updateItemLabel->bindValue(":plID",$item['plID'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":productID",$item['productID'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":itemID",$item['itemID'],PDO::PARAM_INT);
		$updateItemLabel->bindValue(":title",$item['title'],PDO::PARAM_INT);		
		$updateItemLabel->bindValue(":note",$this->note,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":filename",$this->filename,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":version",$this->version,PDO::PARAM_INT);
		$updateItemLabel->bindValue(":date",date('Y-m-d H:i:s'),PDO::PARAM_INT);
		$updateItemLabel->execute();
	}
	public function insertProductLabel($item){
		if isset($this->)
		$addItemLabel = $this->db->prepare("INSERT INTO product_itemlabels (plID,productID,itemID,title,note,filename,version,date) VALUES (:plID,:productID,:itemID,:title,:note,:filename,:version,:date,'1')");
		$addItemLabel->bindValue(":plID",$item['plID'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":productID",$item['productID'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":itemID",$item['itemID'],PDO::PARAM_INT);
		$addItemLabel->bindValue(":title",$item['title'],PDO::PARAM_INT);		
		$addItemLabel->bindValue(":note",$this->note,PDO::PARAM_INT);
		$addItemLabel->bindValue(":filename",$this->filename,PDO::PARAM_INT);
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
		ID
plID
productID
itemID
title
note
filename
version
date

		}
		
		$this->*/
}
;?>