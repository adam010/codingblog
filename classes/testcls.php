<?php
require_once($_SERVER['DOCUMENT_ROOT']."classes/class.Database.php");
$database = new Database();
$db = $database->connect();
if(!$database->connected){
    header("HTTP/1.1 503 Service Unavailable");
    header("Location: http://www.mpmoil.nl/errors/503.php");
    die();
}

$action=$_POST['action'];

if (!postValidation(true)){
			$data['success'] = false;
			$data['errors']="OPERATION ABORTED :illegal data detected in Post !";
		    echo json_encode($data);die;			
		}
 

$label= new productItemData($_POST);

if ($action=='productItems')
     echo $label->plID==0? $label->plItemData : $label->itemData;
 else if($action=='insert')
     echo $label->newVersion();
 
 
  function postValidation(){
    $string = $_FILES['fldupload']['name'];
    if(!preg_match("/\b(\.jpg|\.JPG|\.png|\.PNG|\.psd|\.PSD|\.pdf|\.PDF|\.ai|\.AI)\b/", $string))return false;
    if(!preg_match("/\b(Frontlabel|Backlabel)\b/",$_POST['fldlabeltype']))return false;
    if(!preg_match("/^[a-zA-Z\/]{1,25}$/",$_POST['fldlang']))return false; // ^[0-9]{1,11}$
    if(!isset($_POST['newLabel']) && !preg_match("/\b(true)\b/",$_POST['newLabel']))return false;	

  return true;
}     