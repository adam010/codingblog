<?php
require_once($_SERVER['DOCUMENT_ROOT']."classes/database.php");
$database = new Database();
$db = $database->connect();

$data = array();
$data['success'] = false;
$uploadDirectory=$_SERVER['DOCUMENT_ROOT']."upload/";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	

	if(isset($_POST['newLabel'])){
		if (!postValidation(true)){
			$data['success'] = false;
			$data['errors']="OPERATION ABORTED :illegal data detected in Post !";
		    echo json_encode($data);die;			
		}
		$identifiers= explode('_',$_POST['articleSelect']);
		$articleNumber=$identifiers[0];
		$productID=$identifiers[1];
		$plID=$identifiers[2]	;
		$fldlabeltype=$_POST['fldlabeltype'];
		$fldlang=$_POST['fldlang'];
		$fldnote=$_POST['fldnotes'];	
		$filename=$_FILES['fldupload']['name'];
		$version=newVersion($articleNumber);	
		$currentdate=date('Ymd');
	}

  	if(isset($_POST['productCode'])){
		$productCode=$_POST['productCode'];
		if(isset($plID)&& $plID >0){
			$productItemQuery = "SELECT plp.plID AS plID,plp.ID AS productID, concat(plp.labelCode,'  ', concat(ROUND(pi.inhoud,2),' ',UPPER(pi.inhoudMaat)))AS productCode,pi.articleNumber AS articleNumber,plp.name 
								FROM pl_products AS plp INNER JOIN sap_export_productItems AS pi ON pi.msdsCode = plp.labelCode  
								where plp.labelCode=:productCode";
		}else {
			$productItemQuery = "SELECT 0 AS plID, p.id AS productID,concat(p.code,'      ', concat(ROUND(pi.inhoud,2),' ',UPPER(pi.inhoudMaat)))AS productCode,pi.articleNumber AS articleNumber,p.name
								FROM products AS p INNER JOIN sap_export_productItems AS pi ON pi.msdsCode = p.code  
								where p.code=:productCode";			

		}
		
		
		$productItems = $db->prepare($productItemQuery);		
		$productItems->execute(array(":productCode"=>$productCode));
		$itemLabeldata = $productItems->fetchAll(PDO::FETCH_ASSOC);
		
		/*$countrylistQuery= "SELECT concat( Code2 ,' [',Name,']') as taal FROM countries";
		$countrylist= $db->prepare($countrylistQuery);
		$countrylist->execute();
		$countries=$countries->fetchAll(PDO::FETCH_ASSOC);*/
		
		$data[itemLabeldata] = $itemLabeldata;
		$data['success'] = true;	  
		echo json_encode($data)	;	
	}
	else if(isset($_POST['newLabel'])){
	   try {

			$insertLabeldata = $db->prepare("INSERT INTO itemlabels (plID,productID,articleNumber,type,note,language,filename,version,date) VALUES (:plID,:productID,:articleNumber,:fldlabeltype,:note,:language,:filename,:version,:currentdate)");
			 if(!$insertLabeldata->execute(array(":plID" =>$plID ,
													":productID" => $productID,
													":articleNumber" =>$articleNumber,
													":fldlabeltype" =>$fldlabeltype,
													":note" =>$fldnote,
													":language"=>$fldlang,
													":filename" =>$filename,
													":version" =>$version,
													":currentdate" =>$currentdate)));	  			 
				throw new Exception('Er is een fout opgetreden tijdens de communicaite met de (database)server');
				

		   if(isset($_FILES)){
			   $uploadfile = $uploaddir . basename($_FILES['fldupload']['name']);
				if (!move_uploaded_file($_FILES['fldupload']['tmp_name'], $uploadfile))
					throw new Exception('Er is een fout opgetreden tijdens het uploaden van het bestand.');
		   }
			
		}catch (Exception $e) {
		   $data['success'] = false;
		   $data['errors']="Er heeft zich het volgende probleem voorgedaan  :". $e->getMessage();
	    }	
	   		
	}
	 
	function postValidation(){

		$string = $_FILES['fldupload']['name'];
		if(!preg_match("/\b(\.jpg|\.JPG|\.png|\.PNG|\.psd|\.PSD|\.pdf|\.PDF|\.ai|\.AI)\b/", $string))return false;
		if(!preg_match("/\b(Frontlabel|Backlabel)\b/",$_POST['fldlabeltype']))return false;
		if(!preg_match("/^[a-zA-Z\/]{1,25}$/",$_POST['fldlang']))return false; // ^[0-9]{1,11}$
		if(!isset($_POST['newLabel']) && !preg_match("/\b(true)\b/",$_POST['newLabel']))return false;	
		
		return true;
	}



    function newVersion($articleNumber){
		global $db ;
		$versionQuery="select count(articleNumber) as newversion from itemlabels where articleNumber=:articleNumber";
		$version = $db->prepare($versionQuery);		
		$version->execute(array(":articleNumber"=>$articleNumber));       
		$version = $version->fetchAll(PDO::FETCH_ASSOC);		
		if(is_null($version[0]['newversion'])) return 1;
                
		 return $version[0]['newversion'] + 1;
    }
;?>