<?php

class itemlabel{
    
   protected $data=[];   
   protected $uploadDirectory;
   protected $identifiers;
   protected $articleNumber;
   protected $productID;
   protected $plID;
   protected $fldlabeltype;
   protected $fldlang;
   protected $fldnote;	
   protected $filename;
   protected $version;	
   protected $currentdate; 
    
    public function __construct($postdata) {       
        $this->setValues($postdata);
    }
      
    public function plItemData(){
        $productItemQuery = "SELECT plp.plID AS plID,plp.ID AS productID, concat(plp.labelCode,'  ', concat(ROUND(pi.inhoud,2),' ',UPPER(pi.inhoudMaat)))AS productCode,pi.articleNumber AS articleNumber,plp.name 
                            FROM pl_products AS plp INNER JOIN sap_export_productItems AS pi ON pi.msdsCode = plp.labelCode  
                            where plp.labelCode=:productCode";
        echo $this->queryResult($productItemQuery);
    }
    
    public function itemData(){
        $productItemQuery = "SELECT 0 AS plID, p.id AS productID,concat(p.code,'      ', concat(ROUND(pi.inhoud,2),' ',UPPER(pi.inhoudMaat)))AS productCode,pi.articleNumber AS articleNumber,p.name
                             FROM products AS p INNER JOIN sap_export_productItems AS pi ON pi.msdsCode = p.code  
                             where p.code=:productCode";	
        echo $this->queryResult($productItemQuery);
    }
   
    public function newVersion(){
        echo json_encode($this->newLabelData());
    }    
 
    protected function queryResult($itemQuery){
        
        $productItems = $db->prepare($itemQuery);		
        $productItems->execute(array(":productCode"=>$productCode));
        $itemLabeldata = $productItems->fetchAll(PDO::FETCH_ASSOC);

        $data['languages']= $this->availableLanguages();			
	$data['itemLabeldata'] = $itemLabeldata;
	$data['success'] = true;
               
	return json_encode($data);
    }
    
    protected function newLabelData(){
        try {
            $insertLabeldata = $db->prepare("INSERT INTO itemlabels (plID,productID,articleNumber,type,note,language,filename,version,date) VALUES (:plID,:productID,:articleNumber,:fldlabeltype,:note,:language,:filename,:version,:currentdate)");
            if(!$insertLabeldata->execute(array(":plID" =>$plID ,
                                   ":productID" => $productID,
                                   ":articleNumber" =>$this->articleNumber,
                                   ":fldlabeltype" =>$this->fldlabeltype,
                                   ":note" =>$this->fldnote,
                                   ":language"=>$this->fldlang,
                                   ":filename" =>$this->filename,
                                   ":version" =>$this->version,
                                   ":currentdate" =>$this->currentdate)));	  			 
               throw new Exception('An error occurred while communcating with the database, please try again.');

            if(isset($_FILES)){
              $uploadfile = $this->uploaddir . basename($_FILES['fldupload']['name']);
                   if (!move_uploaded_file($_FILES['fldupload']['tmp_name'], $uploadfile))
                           throw new Exception('An error occurred while uploading the file, please try again.');
            }
           $data['success'] = true;
           $data['errors']="";
           return $data;
        }catch (Exception $e) {
           $data['success'] = false;
           $data['errors']="An error has occurred :". $e->getMessage();
           return $data;
        }       
    }
    
    protected function setValues($post){
        $this->identifiers= explode('_',$post['articleSelect']);
        $this->articleNumber=$identifiers[0];
        $this->productID=$identifiers[1];
        $this->plID=$identifiers[2]	;
        $this->fldlabeltype=$post['fldlabeltype'];
        $this->fldlang=$post['fldlang'];
        $this->fldnote=$post['fldnotes'];	
        $this->filename=$_FILES['fldupload']['name'];
        $this->version=versioning();
        $this->uploadDirectory=$_SERVER['DOCUMENT_ROOT']."upload/";;
        $this->currentdate=date('Ymd');
    }
    
    protected function versioning(){
        global $db ;
        $versionQuery="select count(articleNumber) as newversion from itemlabels where articleNumber=:articleNumber";
        $version = $db->prepare($versionQuery);		
        $version->execute(array(":articleNumber"=>$this->articleNumber));       
        $version = $version->fetchAll(PDO::FETCH_ASSOC);		
        if(is_null($version[0]['newversion'])) return 1;

        return $version[0]['newversion'] + 1;
    }
    
    protected function availableLanguages(){
        global $db ;
        $languagelistQuery= "SELECT concat( Code,' [',Name,']') as talen FROM languages";
        $languagelist= $db->prepare($languagelistQuery);
        $languagelist->execute();

        $availableLanguages['selectable']=$languagelist->fetchAll(PDO::FETCH_ASSOC);

        $languageDefaultListQuery= "SELECT * FROM itemlabellanguagedefaults";
        $languageDefaultList= $db->prepare($languageDefaultListQuery);	
        $languageDefaultList->execute();

        $availableLanguages['defaults']=$languageDefaultList->fetchAll(PDO::FETCH_ASSOC);

        return $availableLanguages;
    }
}