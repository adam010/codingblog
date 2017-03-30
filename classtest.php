<?php
require_once($_SERVER['DOCUMENT_ROOT']."classes/class.Database.php");
include_once('classes/class.ProductItemLabel.php');

$database = new Database();
$db = $database->connect();
$text = new Text($database,'en');
if(!$database->connected){
	header("HTTP/1.1 503 Service Unavailable");
	header("Location: mpmoil.local/errors/503.php");
	die();
}
$productID=2;
 $itemID=3010;
$productitem= new productItemLabel($database, $text);
return $productitem->labelData($productID,$itemID);

//Database $db,Text $text,$language=nkull,$plID=null