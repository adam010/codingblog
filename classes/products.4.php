<?php

class Products {

	private $db;
	private $text;
	private $language;
	private $regexProductCode = "/^[a-zA-Z0-9\-\.]{1,25}$/";
	private $productImageBasePath = "products/images/";
	private $merchandiseImageBasePath = "merchandise/images/";
	public $plID;
	public $plIdentifier;
	public $plName;
	
	public $debug;
	public $product = array();
	public $products = array();
	public $productsTotal = 0;
	public $productFilters = array();
	public $onelinerTranslations = array();
	public $descriptionTranslations = array();
	
	
	# start
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function __construct(Database $db,Text $text,$language=null,$plID=null){ //$category=null,$subcategory=null){
		// class connectors
		$this->db = $db->connect();
		$this->text = $text;
		
		// set private label ID
		$this->setPrivateLabel($plID);
		
		// set language
		if(preg_match("/^[a-zA-Z]{2}$/",$language)){
			$this->language = strtolower($language);
		}else{
			$this->language = "en";
		}
	}
	
	public function getPlproduct($product){
		
		
	}
	
	# set private label
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function setPrivateLabel($plID){
		if(preg_match("/^[0-9]{1,11}$/",$plID)){
			$pls = $this->db->prepare("SELECT ID, identifier, name FROM pl WHERE ID = :plID");
			if($pls->execute(array(":plID" => $plID))){
				$pls = $pls->fetchAll(PDO::FETCH_ASSOC);
				if(count($pls)==1){
					$this->plID = $pls[0]['ID'];
					$this->plIdentifier = $pls[0]['identifier'];
					$this->plName = $pls[0]['name'];
				}else{
					return "Could not retrieve private label data.";
				}
			}else{
				return "Could not retrieve private label data.";
			}
		}
		/*if(preg_match("/^[0-9]{1,11}$/",$plID)){
			$plsCheck = $this->db->prepare("SELECT users.username AS username,userXright.rightID AS rightID FROM users LEFT JOIN userXright ON userXright.userID = users.ID WHERE users.ID = :plID");
			$plsCheck->bindValue(':plID', $plID, PDO::PARAM_INT);
			$plsCheck->execute();
			if($plsCheck->rowCount()>0){
				$plsCheck = $plsCheck->fetchAll(PDO::FETCH_ASSOC);
				$plUsername = "";
				$plSetImagePath = false;
				foreach($plsCheck as $plCheck){
					if($plCheck['rightID']=="21"){ // is privatelabel
						$plUsername = $plCheck['username'];
						$this->plID = $plID;
					}
					if($plCheck['rightID']=="17"){ // are privatelabel product images available?
						$plSetImagePath = true;
					}
				}
				if($plSetImagePath){
					$this->productImageBasePath = "private/".$plUsername."/".$this->productImageBasePath;
				}
			}
		}*/
		
	}	
	
	# get product filters
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductFilters($filterByFilters=null,$merchandise=false){
		if(preg_match("/^[0-9\,]{1,150}$/",$filterByFilters)){
			$filterByFilterIDs = explode(",",$filterByFilters);
		}
		
		if($merchandise){
			$productFilters = $this->db->query("SELECT	merchandise_filtergroups.ID AS filtergroupID,
															merchandise_filtergroups.identifier AS filtergroup,
															merchandise_filters.ID AS filterID,
															merchandise_filters.identifier AS filter,
															merchandise_filters.name AS filtername,
															merchandise_subfilters.ID AS subfilterID,
															merchandise_subfilters.identifier AS subfilter,
															merchandise_subfilters.name AS subfiltername,
															merchandise_subsubfilters.ID AS subsubfilterID,
															merchandise_subsubfilters.identifier AS subsubfilter,
															merchandise_subsubfilters.name AS subsubfiltername
															FROM merchandise_filtergroups
															LEFT JOIN merchandise_filtergroupXfilter ON merchandise_filtergroupXfilter.filtergroupID = merchandise_filtergroups.ID
															LEFT JOIN merchandise_filters ON merchandise_filters.ID = merchandise_filtergroupXfilter.filterID
															LEFT JOIN merchandise_filterXfilter AS merchandise_filterXsubfilter ON merchandise_filterXsubfilter.filterID = merchandise_filters.ID
															LEFT JOIN merchandise_filters AS merchandise_subfilters ON merchandise_subfilters.ID = merchandise_filterXsubfilter.subFilterID
															LEFT JOIN merchandise_filterXfilter AS merchandise_subfilterXsubsubfilter ON merchandise_subfilterXsubsubfilter.filterID = merchandise_subfilters.ID
															LEFT JOIN merchandise_filters AS merchandise_subsubfilters ON merchandise_subsubfilters.ID = merchandise_subfilterXsubsubfilter.subFilterID
															ORDER BY 	merchandise_filtergroups.ordering ASC,
																		merchandise_filters.ordering ASC,
																		merchandise_subfilters.ordering ASC,
																		merchandise_subsubfilters.ordering ASC
															");
		}else{
			$productFilters = $this->db->query("SELECT	product_filtergroups.ID AS filtergroupID,
															product_filtergroups.identifier AS filtergroup,
															product_filters.ID AS filterID,
															product_filters.identifier AS filter,
															product_filters.name AS filtername,
															product_subfilters.ID AS subfilterID,
															product_subfilters.identifier AS subfilter,
															product_subfilters.name AS subfiltername,
															product_subsubfilters.ID AS subsubfilterID,
															product_subsubfilters.identifier AS subsubfilter,
															product_subsubfilters.name AS subsubfiltername
															FROM product_filtergroups
															LEFT JOIN product_filtergroupXfilter ON product_filtergroupXfilter.filtergroupID = product_filtergroups.ID
															LEFT JOIN product_filters ON product_filters.ID = product_filtergroupXfilter.filterID
															LEFT JOIN product_filterXfilter AS product_filterXsubfilter ON product_filterXsubfilter.filterID = product_filters.ID
															LEFT JOIN product_filters AS product_subfilters ON product_subfilters.ID = product_filterXsubfilter.subFilterID
															LEFT JOIN product_filterXfilter AS product_subfilterXsubsubfilter ON product_subfilterXsubsubfilter.filterID = product_subfilters.ID
															LEFT JOIN product_filters AS product_subsubfilters ON product_subsubfilters.ID = product_subfilterXsubsubfilter.subFilterID
															ORDER BY 	product_filtergroups.ordering ASC,
																		product_filters.ordering ASC,
																		product_subfilters.ordering ASC,
																		product_subsubfilters.ordering ASC
															");
		}
		if($productFilters->rowCount()>0){
			foreach($productFilters->fetchAll(PDO::FETCH_ASSOC) as $productFilter){
				// add group
				$filtersWithoutGroup = array();
				if(isset($filterByFilterIDs)){
					$filtersWithoutGroup = $filterByFilterIDs;
					if($merchandise){
						$checkFilters = $this->db->prepare("	SELECT merchandise_filters.ID AS filterID,
																merchandise_subfilters.ID AS subfilterID,
																merchandise_subsubfilters.ID AS subsubfilterID
																FROM merchandise_filtergroups
																LEFT JOIN merchandise_filtergroupXfilter ON merchandise_filtergroupXfilter.filtergroupID = merchandise_filtergroups.ID
																LEFT JOIN merchandise_filters ON merchandise_filters.ID = merchandise_filtergroupXfilter.filterID
																LEFT JOIN merchandise_filterXfilter AS merchandise_filterXsubfilter ON merchandise_filterXsubfilter.filterID = merchandise_filters.ID
																LEFT JOIN merchandise_filters AS merchandise_subfilters ON merchandise_subfilters.ID = merchandise_filterXsubfilter.subFilterID
																LEFT JOIN merchandise_filterXfilter AS merchandise_subfilterXsubsubfilter ON merchandise_subfilterXsubsubfilter.filterID = merchandise_subfilters.ID
																LEFT JOIN merchandise_filters AS merchandise_subsubfilters ON merchandise_subsubfilters.ID = merchandise_subfilterXsubsubfilter.subFilterID
																WHERE merchandise_filtergroups.ID = :filterGroupID
																");
					}else{
						$checkFilters = $this->db->prepare("	SELECT product_filters.ID AS filterID,
																product_subfilters.ID AS subfilterID,
																product_subsubfilters.ID AS subsubfilterID
																FROM product_filtergroups
																LEFT JOIN product_filtergroupXfilter ON product_filtergroupXfilter.filtergroupID = product_filtergroups.ID
																LEFT JOIN product_filters ON product_filters.ID = product_filtergroupXfilter.filterID
																LEFT JOIN product_filterXfilter AS product_filterXsubfilter ON product_filterXsubfilter.filterID = product_filters.ID
																LEFT JOIN product_filters AS product_subfilters ON product_subfilters.ID = product_filterXsubfilter.subFilterID
																LEFT JOIN product_filterXfilter AS product_subfilterXsubsubfilter ON product_subfilterXsubsubfilter.filterID = product_subfilters.ID
																LEFT JOIN product_filters AS product_subsubfilters ON product_subsubfilters.ID = product_subfilterXsubsubfilter.subFilterID
																WHERE product_filtergroups.ID = :filterGroupID
																");
					}
					$checkFilters->bindValue(':filterGroupID', $productFilter['filtergroupID'], PDO::PARAM_INT);
					$checkFilters->execute();
					
					if($checkFilters->rowCount()>0){
						foreach($checkFilters->fetchAll(PDO::FETCH_ASSOC) as $checkFilter){
							if(($key = array_search($checkFilter['filterID'], $filtersWithoutGroup)) !== false) {
								unset($filtersWithoutGroup[$key]);
							}
							if(($key = array_search($checkFilter['subfilterID'], $filtersWithoutGroup)) !== false) {
								unset($filtersWithoutGroup[$key]);
							}
							if(($key = array_search($checkFilter['subsubfilterID'], $filtersWithoutGroup)) !== false) {
								unset($filtersWithoutGroup[$key]);
							}
						}
					}					
				}
				if(!empty($productFilter['filtergroupID']) && !array_key_exists($productFilter['filtergroupID'],$this->productFilters)){
					$this->productFilters[$productFilter['filtergroupID']] = array(	"identifier" => $productFilter['filtergroup'],
																							"show" => "normal",
																					"filtersWithoutGroup" => $filtersWithoutGroup
																					);
				}
				// show group true/false
				if(isset($filterByFilterIDs)){
					if(in_array($productFilter['filterID'],$filterByFilterIDs) || in_array($productFilter['subfilterID'],$filterByFilterIDs) || in_array($productFilter['subsubfilterID'],$filterByFilterIDs)){
						$this->productFilters[$productFilter['filtergroupID']]['show'] = "filtered";
					}elseif($this->productFilters[$productFilter['filtergroupID']]['show']!="filtered"){
						$this->productFilters[$productFilter['filtergroupID']]['show'] = "hide";						
					}
				}else{
					$this->productFilters[$productFilter['filtergroupID']]['show'] = "normal";
				}
				// add filters
				if(!empty($productFilter['filtergroupID']) && !empty($productFilter['filterID'])){
					if(!array_key_exists("filters",$this->productFilters[$productFilter['filtergroupID']])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'] = array();
					}
					if(!array_key_exists($productFilter['filterID'],$this->productFilters[$productFilter['filtergroupID']]['filters'])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']] = array(	"identifier" => $productFilter['filter'],
																																"name" => $productFilter['filtername'],
																																"show" => "normal"
																																);
						if(isset($filterByFilterIDs) && !in_array($productFilter['filterID'],$filterByFilterIDs) || ($this->language!="nl" && $productFilter['filtername']=="Petronas") || ($this->language!="nl" && $this->language!="en" && $productFilter['filtername']=="Arexons")){
							$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['show'] = "hide";
						}
					}					
				}
				// add subfilters
				if(!empty($productFilter['filtergroupID']) && !empty($productFilter['filterID']) && !empty($productFilter['subfilterID'])){
					if(!array_key_exists("filters",$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'] = array();
					}
					if(!array_key_exists($productFilter['subfilterID'],$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']] = array(	"identifier" => $productFilter['subfilter'],
																																											"name" => $productFilter['subfiltername'],
																																											"show" => "normal"
																																											);
						if((isset($filterByFilterIDs) && !in_array($productFilter['filterID'],$filterByFilterIDs)) || !isset($filterByFilterIDs)){	// also contains petronas on certain language filter
							$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']]['show'] = "hide";
						}
					}
				}
				// add subsubfilters
				if(!empty($productFilter['filtergroupID']) && !empty($productFilter['filterID']) && !empty($productFilter['subfilterID']) && !empty($productFilter['subsubfilterID'])){
					if(!array_key_exists("filters",$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']]['filters'] = array();
					}
					if(!array_key_exists($productFilter['subsubfilterID'],$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']]['filters'])){
						$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']]['filters'][$productFilter['subsubfilterID']] = array(	"identifier" => $productFilter['subsubfilter'],
																																																						"name" => $productFilter['subsubfiltername'],
																																																						"show" => "normal"
																																																						);
						if((isset($filterByFilterIDs) && (!in_array($productFilter['filterID'],$filterByFilterIDs) || !in_array($productFilter['subfilterID'],$filterByFilterIDs))) || !isset($filterByFilterIDs)){
							$this->productFilters[$productFilter['filtergroupID']]['filters'][$productFilter['filterID']]['filters'][$productFilter['subfilterID']]['filters'][$productFilter['subsubfilterID']]['show'] = "hide";
						}
					}
				}
			}
		}
		
		if(isset($filterByFilterIDs)){
			// show unfiltered groups
			if(count($this->products)>0){
				$filterIDs = array();
				$filterWhere = "";
				$filterWhereValues = array();
				$iFilter = 1;
				foreach($this->products as $productID => $product){
					if(empty($filterWhere)){
						$filterWhere .= "WHERE ";
					}else{
						$filterWhere .= " OR ";
					}
					$filterWhere .= "productID = :productID_".$iFilter;
					array_push($filterWhereValues,$productID);
					$iFilter++;
				}
				$filters->prepare("SELECT filterID FROM productXfilter ".$filterWhere." GROUP BY filterID");
				$filters->execute($filterWhereValues);
				
				if($filters->rowCount()>0){
					foreach($filters->fetchAll(PDO::FETCH_ASSOC) as $filterID){
						array_push($filterIDs,$filterID['filterID']);
					}
				}
			}
				
			foreach($this->productFilters as $filterGroupID => $filterGroup){
				if(isset($filterGroup['filters']) && $filterGroup['show']=="hide"){
					$iFilterGroupShow = 0;
					foreach($filterGroup['filters'] as $filterID => $filter){
						if(isset($filterIDs) && in_array($filterID,$filterIDs)){
							$this->productFilters[$filterGroupID]['filters'][$filterID]['show'] = "normal";
							$iFilterGroupShow++;
						}
					}
					if($iFilterGroupShow>1){
						$this->productFilters[$filterGroupID]['show'] = "normal";
					}
				}
			}
			
			// for filtered groups, set proper display status per filter + create exclude filter link
			foreach($this->productFilters as $filterGroupID => $filterGroup){
				if($filterGroup['show']=="filtered"){
					foreach($filterGroup['filters'] as $lvl1filterID => $lvl1filter){
						if(in_array($lvl1filterID,$filterByFilterIDs)){
							$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['show'] = "current";
							$lvl2found = false;
							if(isset($lvl1filter['filters'])){
								foreach($lvl1filter['filters'] as $lvl2filterID => $lvl2filter){
									if(in_array($lvl2filterID,$filterByFilterIDs)){
										$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['show'] = "stepto";
										$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['show'] = "current";
										$lvl3found = false;
										if(isset($lvl2filter['filters'])){
											foreach($lvl2filter['filters'] as $lvl3filterID => $lvl3filter){
												if(in_array($lvl3filterID,$filterByFilterIDs)){
													$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['show'] = "stepto";
													$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['filters'][$lvl3filterID]['show'] = "current";
													$lvl3found = true;
												}else{
													$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['filters'][$lvl3filterID]['show'] = "off";
												}
											}
											if(!$lvl3found){
												foreach($lvl2filter['filters'] as $lvl3filterID => $lvl3filter){
													$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['filters'][$lvl3filterID]['show'] = "normal";
												}
											}
										}
										$lvl2found = true;
									}else{
										$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['show'] = "off";
									}
								}
								if(!$lvl2found){
									foreach($lvl1filter['filters'] as $lvl2filterID => $lvl2filter){
										$this->productFilters[$filterGroupID]['filters'][$lvl1filterID]['filters'][$lvl2filterID]['show'] = "normal";
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	# get products
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProducts($filters=null,$offset=null,$limit=null,$visibleFilter=true,$eolFilter=true,$orderBy=null,$order=null,$merchandise=false){
		$this->products = array();
		
		if(preg_match("/^[0-9]{1,11}$/",$limit)){
			if(preg_match("/^[0-9]{1,11}$/",$offset)){
				$limit = " LIMIT ".$offset.",".$limit;
			}else{
				$limit = " LIMIT ".$limit;
				$offset = null;
			}
		}else{
			$limit = null;
		}
		
		// display & eol
		$showWhereClause = "";		
		if($visibleFilter){
			$visibleFilter = true;
			$showWhereClause = " AND products.display = '1'";
		}else{
			$visibleFilter = false;
		}
		if($eolFilter){
			$eolFilter = true;
			if($visibleFilter){
				$showWhereClause .= " AND products.eol = '0'";
			}else{
				$showWhereClause = " AND products.eol = '1'";
			}
		}else{
			$eolFilter = false;
		}
		
		// ordering
		if(preg_match("/\b(asc|desc|ASC|DESC)\b/",$order)){
			$order = strtoupper($order);
		}elseif(!empty($_SESSION['sortProductsOrder'])){
			$order = $_SESSION['sortProductsOrder'];
		}elseif(empty($_SESSION['sortProductsOrder'])){
			$_SESSION['sortProductsOrder'] = "ASC";
			$_SESSION['sortProductsOrderReverse'] = "DESC";
		}
		switch($order){
			case "DESC":
				$sortProductsOrder = "DESC";
				$sortProductsOrderReverse = "ASC";
				break;
			case "ASC":
			default:
				$sortProductsOrder = "ASC";
				$sortProductsOrderReverse = "DESC";
				break;	
		}
		if(preg_match("/\b(popular|name|productcode)\b/",$orderBy)){
			switch($orderBy){
				case "popular":
					$orderBy = "popular";
					break;
				case "name":
					$orderBy = "name";
					break;
				case "productcode":
					$orderBy = "productcode";
					break;
			}
		}elseif(!empty($_SESSION['sortProductsBy'])){
			switch($_SESSION['sortProductsBy']){
				case "popular":
					$orderBy = "popular";
					break;
				case "name":
					$orderBy = "name";
					break;
				case "productcode":
					$orderBy = "productcode";
					break;
			}
		}
		if($orderBy=="popular"){
			$queryOrderBy = "ORDER BY find_in_set(p3.make, 'MPM Oil,Petronas,Arexons'), hits ".$sortProductsOrderReverse.", p3.code ".$sortProductsOrder.", p3.name ".$sortProductsOrder;
		}elseif($orderBy=="name"){
			$queryOrderBy = "ORDER BY find_in_set(p3.make, 'MPM Oil,Petronas,Arexons'), p3.name ".$sortProductsOrder.", p3.code ".$sortProductsOrder.", p3.name ".$sortProductsOrder;
		}elseif($orderBy=="productcode"){
			$queryOrderBy = "ORDER BY find_in_set(p3.make, 'MPM Oil,Petronas,Arexons'), p3.code ".$sortProductsOrder.", p3.name ".$sortProductsOrder;
		}else{
			$queryOrderBy = "ORDER BY p3.productXfilterOrder ".$sortProductsOrderReverse.", find_in_set(p3.make, 'MPM Oil,Petronas,Arexons'), p3.filterOrder ".$sortProductsOrder.", p3.code ".$sortProductsOrder;
		}
		
		// filters
		$makeWhereClause = "";
		if($this->language!="nl" && !$merchandise){ // petronas & arexons on certain language filter
			$makeWhereClause .= " AND products.make <> 'Petronas'";
			if($this->language!="en"){
				$makeWhereClause .= " AND products.make <> 'Arexons'";
			}
		}
		if(preg_match("/^[0-9a-zA-Z\_\-\,\ ]{1,255}$/",$filters)){
			$filterArray = explode(",",$filters);
		}
		
		$productsQueryValues = array();
		if(!empty($filters)){
			$productsQuery = "( SELECT	products.id AS ID,
											products.make AS make,
											products.code AS code,
											products.name AS name,
											products.noImageType AS noImageType,
											products.date AS date,
											productXfilter.ordering AS productXfilterOrder
											FROM product_filters
											LEFT JOIN productXfilter ON productXfilter.filterID = product_filters.ID
											LEFT JOIN products ON products.id = productXfilter.productID
											WHERE (";
											$findID = false;
											if(preg_match("/^[0-9,]{1,150}$/",$filters)){
												$findID = true;
											}
											$iFilter = 1;
											foreach($filterArray as $filter){
												if($iFilter>1){
													$productsQuery .= " OR ";
												}
												if($findID){
													$productsQuery .= "product_filters.ID = :filterID_".$iFilter;
													$productsQueryValues['filterID_'.$iFilter] = array($filter,PDO::PARAM_INT);
												}else{
													$productsQuery .= "product_filters.identifier = :filterIdentifier_".$iFilter;	
													$productsQueryValues['filterIdentifier_'.$iFilter] = array($filter,PDO::PARAM_STR);
												}
												$iFilter++;
											}
				$productsQuery .=			")".$makeWhereClause.$showWhereClause."
											GROUP BY products.id
											HAVING COUNT(productXfilter.productID) = ".count($filterArray)."
											)";
			$p1ProductXfilterSelection = "p1.productXfilterOrder AS productXfilterOrder";
			if(!in_array('21',$filterArray) && !in_array('equipment',$filterArray)){
				$p1WhereClause = "WHERE p1.ID NOT IN (SELECT productID FROM productXfilter WHERE filterID = '21')";
			}
		}else{
			$productsQuery = "products";
			$p1ProductXfilterSelection = "'0' AS productXfilterOrder";
			$p1WhereClause = "WHERE p1.ID NOT IN (SELECT productID FROM productXfilter WHERE filterID = '21')".str_replace("products.","p1.",$makeWhereClause).str_replace("products.","p1.",$showWhereClause);
		}
				
		$getProductsQuery = "SELECT	p3.ID AS ID,
									p3.make AS make,
									p3.code AS code,
									p3.name AS name,
									p3.noImageType AS noImageType,
									p3.lastUpdate AS lastUpdate,
									p3.productXfilterOrder AS productXfilterOrder,
									p3.filterOrder AS filterOrder,
									";
		if(!$merchandise){
			$getProductsQuery .= 	"product_oneliners_".$this->language.".text AS oneliner,
									";
		}
		$getProductsQuery .=		"(SELECT sum(hits) FROM product_stats WHERE productID = p3.ID) AS hits
									FROM( SELECT	*
													FROM( SELECT	p1.ID AS ID,
																	p1.make AS make,
																	p1.code AS code,
																	p1.name AS name,
																	p1.noImageType AS noImageType,
																	p1.date AS lastUpdate,
																	".$p1ProductXfilterSelection.",
																	product_filters.ordering AS filterOrder
																	FROM ".$productsQuery." AS p1
																	LEFT JOIN productXfilter ON productXfilter.productID = p1.ID
																	LEFT JOIN product_filters ON product_filters.ID = productXfilter.filterID
																	".$p1WhereClause."
																	ORDER BY product_filters.ordering ASC
													) AS p2
													GROUP BY ID
									) AS p3
									";
		if(!$merchandise){
			$getProductsQuery .=	"LEFT JOIN product_oneliners_".$this->language." ON product_oneliners_".$this->language.".code = p3.code
									";
		}
		$getProductsQuery .=		"GROUP BY p3.ID
									".$queryOrderBy;
		
		//echo $getProductsQuery;
									
		if($merchandise){
			$getProductsQuery = str_replace(array("products","product"),array("merchandise","merchandise"),$getProductsQuery);
		}
		$products = $this->db->prepare($getProductsQuery);
		foreach($productsQueryValues as $queryKey => $queryValue){
			$products->bindValue($queryKey,$queryValue[0],$queryValue[1]);
		}
		$products->execute();

		// put in products array
		$this->productsTotal = $products->rowCount();
		if($this->productsTotal>0){
			foreach($products->fetchAll(PDO::FETCH_ASSOC) as $product){
				$pushProduct = array();
				if(!array_key_exists($product['ID'],$this->products)){
					$pushProduct = array();
					
					if(isset($this->plID)){
						$plProducts = $this->db->prepare("SELECT pl_products.ID AS ID,pl_products.labelCode AS code,pl_products.name AS name,users.name AS make FROM pl_products LEFT JOIN users ON users.ID = pl_products.userID WHERE userID = :userID AND code = :code");
						$plProducts->bindValue(":userID",$this->plID,PDO::PARAM_INT);
						$plProducts->bindValue(":code",$product['code'],PDO::PARAM_STR);
						$plProducts->execute();
						
						if($plProducts->rowCount()>0){
							$plProducts = $plProducts->fetchAll(PDO::FETCH_ASSOC);
							foreach($plProducts as $plProduct){
								$plProductSpecifications = $this->getProductSpecs($plProduct['code']);
								if(is_array($plProductSpecifications) && count($plProductSpecifications)>0){
									$plProductSpecifications = implode(" &bull; ",$plProductSpecifications);
								}
								array_push($this->products,array(	"ID" => $plProduct['ID'],
																	"make" => $plProduct['make'],
																	"code" => $plProduct['code'],
																	"name" => $plProduct['name'],
																	"oneliner" => "",
																	"specifications" => $plProductSpecifications,
																	"productXfilterOrder" => $product['productXfilterOrder'],
																	"filterOrder" => $product['filterOrder'],
																	"images" => $this->getProductImages($plProduct['code'],1,$merchandise),
																	"lastUpdate" => $product['lastUpdate']
																	)
																);
							}
						}
					}elseif(!empty($product['code'])){
						if(!$merchandise){
							$productSpecifications = $this->getProductSpecs($product['code']);
							if(is_array($productSpecifications) && count($productSpecifications)>0){
								$productSpecifications = implode(" &bull; ",$productSpecifications);
							}
							$this->products[$product['ID']] = array(	"ID" => $product['ID'],
																		"make" => $product['make'],
																		"code" => $product['code'],
																		"name" => $product['name'],
																		"oneliner" => $product['oneliner'],
																		"specifications" => $productSpecifications,
																		"productXfilterOrder" => $product['productXfilterOrder'],
																		"filterOrder" => $product['filterOrder'],
																		"images" => $this->getProductImages($product['code'],1,$merchandise),
																		"lastUpdate" => $product['lastUpdate']
																		);
						}else{
							$this->products[$product['ID']] = array(	"ID" => $product['ID'],
																		"make" => $product['make'],
																		"code" => $product['code'],
																		"name" => $product['name'],
																		"oneliner" => $product['oneliner'],
																		"productXfilterOrder" => $product['productXfilterOrder'],
																		"filterOrder" => $product['filterOrder'],
																		"images" => $this->getProductImages($product['code'],1,$merchandise),
																		"lastUpdate" => $product['lastUpdate']
																		);
						}
					}
				}
			}
		}
	}
	
 /*** product by id/////////////////////////////*/
 
 public function getItembyId($productID, $itemID=null){
		//if(preg_match($this->regexProductCode,$productCode)){
			if(isset($this->plID)){
				// check if pl description language exists
				/*$descriptionTables = $this->db->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='mpmoil' AND TABLE_NAME = :tableName");
				$descriptionTables->bindValue(':tableName', "pl_productDescriptions_".$this->language, PDO::PARAM_STR);
				$descriptionTables->execute();
				$descriptionTable = "pl_productDescriptions_en";
				if($descriptionTables->rowCount()>0){
					$descriptionTable = "pl_productDescriptions_".$this->language;
				}*/
				$productQuery = "SELECT	pl_products.ID AS ID,
										pl.name AS make,
										pl_products.labelCode AS code,
										pl_products.name AS name,
										products.typeID AS typeID,
										product_types.type AS type,
										products.viscosityID AS viscosityID,
										product_viscosities.viscosity AS viscosity,
										products.eol AS eol,
										products.display AS display,
										products.date AS date,
										products.initiatedByUserID AS initiatedByUserID,
										products.initiateDate AS initiateDate,
										products.authedByUserID AS authedByUserID,
										products.authDate AS authDate,
										products.recommendation_show_at_max_alternatives AS recommendationShowAtMaxAlternatives,
										product_oneliners_".$this->language.".text AS oneliner,
										".$descriptionTable.".text AS description
										FROM pl_products
										LEFT JOIN pl ON pl.ID = pl_products.plID
										LEFT JOIN products ON products.code = pl_products.code
										LEFT JOIN product_types ON product_types.ID = products.typeID
										LEFT JOIN product_viscosities ON product_viscosities.ID = products.viscosityID
										LEFT JOIN users ON users.ID = pl_products.userID
										LEFT JOIN product_oneliners_".$this->language." ON product_oneliners_".$this->language.".code = pl_products.labelCode
										LEFT JOIN ".$descriptionTable." ON ".$descriptionTable.".plProductID = pl_products.ID
										WHERE pl_products.plID = :plID	LIMIT 1	";
			
			}else {
				$productQuery = "SELECT	products.id AS ID,
											products.make AS make,
											products.code AS code,
											products.name AS name,
											products.typeID AS typeID,
											product_types.type AS type,
											products.viscosityID AS viscosityID,
											product_viscosities.viscosity AS viscosity,
											products.eol AS eol,
											products.display AS display,
											products.date AS date,
											products.initiatedByUserID AS initiatedByUserID,
											products.initiateDate AS initiateDate,
											products.authedByUserID AS authedByUserID,
											products.authDate AS authDate,
											products.recommendation_show_at_max_alternatives AS recommendationShowAtMaxAlternatives,
											product_oneliners_".$this->language.".text AS oneliner,
											product_descriptions_".$this->language.".text AS description
											FROM products
											LEFT JOIN product_types ON product_types.ID = products.typeID
											LEFT JOIN product_viscosities ON product_viscosities.ID = products.viscosityID
											LEFT JOIN product_oneliners_".$this->language." ON product_oneliners_".$this->language.".code = products.code
											LEFT JOIN product_descriptions_".$this->language." ON product_descriptions_".$this->language.".code = products.code
											WHERE products.id = :productCode LIMIT 1	";
			}
			
			$products = $this->db->prepare($productQuery);
			if(isset($this->plID)){
				$products->bindValue(":plID",$this->plID,PDO::PARAM_INT);
			}
			$products->bindValue(":productCode",$productID,PDO::PARAM_STR);
			$products->execute();
			if(isset($itemID)){
					  $this->product['items'] = $this->getProductItemByID($itemID);
			}else{
				if($products->rowCount()>0){
					foreach($products->fetchAll(PDO::FETCH_ASSOC) as $product){
						$this->product['ID'] = $product['ID'];
						$this->product['make'] = $product['make'];
						$this->product['code'] = $product['code'];
						$this->product['name'] = $product['name'];
						$this->product['typeID'] = $product['typeID'];
						$this->product['type'] = $product['type'];
						$this->product['viscosityID'] = $product['viscosityID'];
						$this->product['viscosity'] = $product['viscosity'];
						$this->product['eol'] = $product['eol'];
						$this->product['display'] = $product['display'];
						$this->product['description'] = $product['description'];						
						//$this->product['items'] = $this->getProductItems($productID);		
						$this->product['items'] = $this->getProductItems($product['code'],null,false);
					}					
					$this->product['lastUpdate'] = $product['date'];		

				}
				
		  }
	}

       
 /*/////////////////////////////////////////////////////////////////////*/
	# get product 
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProduct($productCode=null,$returnImages=false,$imageLimit=1,$merchandise=false){
		if(preg_match($this->regexProductCode,$productCode)){
			if(isset($this->plID)){
				// check if pl description language exists
				$descriptionTables = $this->db->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='mpmoil' AND TABLE_NAME = :tableName");
				$descriptionTables->bindValue(':tableName', "pl_productDescriptions_".$this->language, PDO::PARAM_STR);
				$descriptionTables->execute();
				$descriptionTable = "pl_productDescriptions_en";
				if($descriptionTables->rowCount()>0){
					$descriptionTable = "pl_productDescriptions_".$this->language;
				}
				$productQuery = "SELECT	pl_products.ID AS ID,
										pl.name AS make,
										pl_products.labelCode AS code,
										pl_products.name AS name,
										products.typeID AS typeID,
										product_types.type AS type,
										products.viscosityID AS viscosityID,
										product_viscosities.viscosity AS viscosity,
										products.eol AS eol,
										products.display AS display,
										products.date AS date,
										products.initiatedByUserID AS initiatedByUserID,
										products.initiateDate AS initiateDate,
										products.authedByUserID AS authedByUserID,
										products.authDate AS authDate,
										products.recommendation_show_at_max_alternatives AS recommendationShowAtMaxAlternatives,
										product_oneliners_".$this->language.".text AS oneliner,
										".$descriptionTable.".text AS description
										FROM pl_products
										LEFT JOIN pl ON pl.ID = pl_products.plID
										LEFT JOIN products ON products.code = pl_products.code
										LEFT JOIN product_types ON product_types.ID = products.typeID
										LEFT JOIN product_viscosities ON product_viscosities.ID = products.viscosityID
										LEFT JOIN users ON users.ID = pl_products.userID
										LEFT JOIN product_oneliners_".$this->language." ON product_oneliners_".$this->language.".code = pl_products.labelCode
										LEFT JOIN ".$descriptionTable." ON ".$descriptionTable.".plProductID = pl_products.ID
										WHERE pl_products.plID = :plID AND pl_products.labelCode = :productCode
										LIMIT 1
										";
			}elseif($merchandise){
				$productQuery = "SELECT	merchandise.id AS ID,
											merchandise.make AS make,
											merchandise.code AS code,
											merchandise.name AS name,
											merchandise.eol AS eol,
											merchandise.display AS display,
											merchandise.date AS date,
											merchandise_descriptions.".strtoupper($this->language)." AS description
											FROM merchandise
											LEFT JOIN merchandise_descriptions ON merchandise_descriptions.code = merchandise.code
											WHERE merchandise.code = :productCode
											LIMIT 1
										";
			}else{
				$productQuery = "SELECT	products.id AS ID,
											products.make AS make,
											products.code AS code,
											products.name AS name,
											products.typeID AS typeID,
											product_types.type AS type,
											products.viscosityID AS viscosityID,
											product_viscosities.viscosity AS viscosity,
											products.eol AS eol,
											products.display AS display,
											products.date AS date,
											products.initiatedByUserID AS initiatedByUserID,
											products.initiateDate AS initiateDate,
											products.authedByUserID AS authedByUserID,
											products.authDate AS authDate,
											products.recommendation_show_at_max_alternatives AS recommendationShowAtMaxAlternatives,
											product_oneliners_".$this->language.".text AS oneliner,
											product_descriptions_".$this->language.".text AS description
											FROM products
											LEFT JOIN product_types ON product_types.ID = products.typeID
											LEFT JOIN product_viscosities ON product_viscosities.ID = products.viscosityID
											LEFT JOIN product_oneliners_".$this->language." ON product_oneliners_".$this->language.".code = products.code
											LEFT JOIN product_descriptions_".$this->language." ON product_descriptions_".$this->language.".code = products.code
											WHERE products.code = :productCode
											LIMIT 1
										";
			}
			
			$products = $this->db->prepare($productQuery);
			if(isset($this->plID)){
				$products->bindValue(":plID",$this->plID,PDO::PARAM_INT);
			}
			$products->bindValue(":productCode",$productCode,PDO::PARAM_STR);
			$products->execute();
			
			if($products->rowCount()>0){
				foreach($products->fetchAll(PDO::FETCH_ASSOC) as $product){
					$this->product['ID'] = $product['ID'];
					if(!$merchandise){
						$this->product['oneliner'] = $product['oneliner'];
						$this->product['recommendationShowAtMaxAlternatives'] = $product['recommendationShowAtMaxAlternatives'];
						$this->product['specifications'] = $this->getProductSpecs($productCode);
						$this->product['standardAnalyses'] = $this->getProductStandardAnalyses($product['code']);
					}
					$this->product['make'] = $product['make'];
					$this->product['code'] = $product['code'];
					$this->product['name'] = $product['name'];
					$this->product['typeID'] = $product['typeID'];
					$this->product['type'] = $product['type'];
					$this->product['viscosityID'] = $product['viscosityID'];
					$this->product['viscosity'] = $product['viscosity'];
					$this->product['eol'] = $product['eol'];
					$this->product['display'] = $product['display'];
					$this->product['description'] = $product['description'];
					if($returnImages){
						$this->product['items'] = $this->getProductItems($productCode,true,$merchandise);
					}else{
						$this->product['items'] = $this->getProductItems($productCode,null,$merchandise);
					}
					$this->product['images'] = $this->getProductImages($product['code'],null,$merchandise);
					$this->product['lastUpdate'] = $product['date'];
					$currentMonth = date("n");
					$currentYear = date("Y");
					$this->product['initiatedByUserID'] = $product['initiatedByUserID'];
					$this->product['initiateDate'] = $product['initiateDate'];
					$this->product['authedByUserID'] = $product['authedByUserID'];
					$this->product['authDate'] = $product['authDate'];
					if(!isset($this->plID) && $merchandise){
						$checkMerchandiseStats = $this->db->prepare("SELECT ID,hits FROM merchandise_stats WHERE merchandiseID = :merchandiseID AND year = :currentYear AND month = :currentMonth LIMIT 1");
						$checkMerchandiseStats->bindValue(":merchandiseID",$product['ID'],PDO::PARAM_INT);
						$checkMerchandiseStats->bindValue(":currentYear",$currentYear,PDO::PARAM_INT);
						$checkMerchandiseStats->bindValue(":currentMonth",$currentMonth,PDO::PARAM_INT);
						$checkMerchandiseStats->execute();
						
						if($checkMerchandiseStats->rowCount()>0){
							foreach($currentMerchandiseStats->fetchAll(PDO::FETCH_ASSOC) as $currentMerchandiseStat){
								$currentMerchandiseStat['hits']++;
								$updateMerchandiseStats = $this->db->prepare("UPDATE merchandise_stats SET hits = :currentHits WHERE ID = :currentID");
								$updateMerchandiseStats->bindValue(":currentHits",$currentMerchandiseStat['hits'],PDO::PARAM_INT);
								$updateMerchandiseStats->bindValue(":currentID",$currentMerchandiseStat['ID'],PDO::PARAM_INT);
								$updateMerchandiseStats->execute();
							}
						}else{
							$updateMerchandiseStats = $this->db->prepare("INSERT INTO merchandise_stats (merchandiseID,year,month,hits) VALUES (:merchandiseID,:year,:month,'1')");
							$updateMerchandiseStats->bindValue(":merchandiseID",$product['ID'],PDO::PARAM_INT);
							$updateMerchandiseStats->bindValue(":year",$currentYear,PDO::PARAM_INT);
							$updateMerchandiseStats->bindValue(":month",$currentMonth,PDO::PARAM_INT);
							$updateMerchandiseStats->execute();
						}
					}elseif(!isset($this->plID)){
						$checkProductStats = $this->db->prepare("SELECT ID,hits FROM product_stats WHERE productID = :productID AND year = :year AND month = :month LIMIT 1");
						$checkProductStats->bindValue(":productID",$product['ID'],PDO::PARAM_INT);
						$checkProductStats->bindValue(":year",$currentYear,PDO::PARAM_INT);
						$checkProductStats->bindValue(":month",$currentMonth,PDO::PARAM_INT);
						$checkProductStats->execute();
						
						if($checkProductStats->rowCount()>0){
							foreach($checkProductStats->fetchAll(PDO::FETCH_ASSOC) as $checkProductStat){
								$currentProductStat['hits']++;
								$updateProductStats = $this->db->prepare("UPDATE product_stats SET hits = :currentHits WHERE ID = :statID");
								$updateProductStats->bindValue(":currentHits",$currentProductStat['hits'],PDO::PARAM_INT);
								$updateProductStats->bindValue(":statID",$currentProductStat['ID'],PDO::PARAM_INT);
								$updateProductStats->execute();
							}
						}else{
							$updateProductStats = $this->db->prepare("INSERT INTO product_stats (productID,year,month,hits) VALUES (:productID,:currentYear,:currentMonth,'1')");
							$updateProductStats->bindValue(":productID",$product['ID'],PDO::PARAM_INT);
							$updateProductStats->bindValue(":currentYear",$currentYear,PDO::PARAM_INT);
							$updateProductStats->bindValue(":currentMonth",$currentMonth,PDO::PARAM_INT);
							$updateProductStats->execute();
						}
					}
				}
			}
				
		}
	}
	
	
	# get filters by product
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getFiltersByProduct($productCode=null,$productID=null){
		if(preg_match($this->regexProductCode,$productCode)){
			$getProductID = $this->db->prepare("SELECT id AS ID FROM products WHERE code = :productCode LIMIT 1");
			if($getProductID->execute(array(":productCode" => $productCode))){
				$getProductID = $getProductID->fetchAll(PDO::FETCH_ASSOC);
				if(count($getProductID)>0){
					$productID = $getProductID[0]['ID'];
				}
			}
		}
		
		if(preg_match("/^[a-zA-Z0-9.]{1,11}$/",$productID)){
			$filters = array();
			
			$productFilters = $this->db->prepare("SELECT product_filters.* FROM productXfilter LEFT JOIN product_filters ON product_filters.ID = productXfilter.filterID WHERE productXfilter.productID = :productID");
			if($productFilters->execute(array(":productID" => $productID))){
				$productFilters = $productFilters->fetchAll(PDO::FETCH_ASSOC);
				if(count($productFilters)>0){
					$filterIDs = array();
					foreach($productFilters as $productFilter){
						array_push($filterIDs,$productFilter['ID']);
					}
					
					$this->getProductFilters();
					$productAllFilters = $this->productFilters;
					$productAllFilters = $this->passFilters($filterIDs,$productAllFilters,true);
					return $productAllFilters;
				}else{
					return "No filters found for this product.";
				}
			}else{
				return "Invalid product code.";
			}
		}else{
			return "Invalid product code.";
		}		
	}
	private function passFilters($filterIDs=null,$filters=null,$isGroup=false){
		if(is_array($filterIDs) && is_array($filters)){
			$return = array();
			foreach($filters as $filterKey => $filter){
				$foundFilter = false;
				if($isGroup){
					foreach($filterIDs as $filterID){
						if(is_array($filter['filters']) && array_key_exists($filterID,$filter['filters'])){
							$foundFilter = true;
						}
					}
				}elseif(in_array($filterKey,$filterIDs)){
					$foundFilter = true;
				}
				if($foundFilter){
					switch($filter['identifier']){
						case "appliance":
							$filterName = $this->text->translate("application");
							break;
						case "category":
						case "make":
							$filterName = $this->text->translate($filter['identifier']);
							break;
						case "feature":
							$filterName = $this->text->translate($filter['features']);
							break;
						default:
							$filterName = $this->text->translate($filter['name']);
							if(empty($filterName)){
								$filterName = $filter['name'];
							}
					}
					if(is_array($filter['filters'])){
						$return[$filterKey] = array("identifier" => $filter['identifier'],
													"name" => $filterName,
													"filters" => $this->passFilters($filterIDs,$filter['filters'])
													);
					}else{
						$return[$filterKey] = array("identifier" => $filter['identifier'],
													"name" => $filterName
													);
					}
				}
			}
			return $return;
		}else{
			return "Unable to pass filters.";
		}
	}
	
	
	# get product image(s) in array
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductImages($productCode=null,$limit=null,$merchandise=false){
		if(!preg_match("/^[a-zA-Z0-9.]{1,25}$/",$limit)){
			$limit = null;
		}
		
		if($merchandise){
			$basePath = $this->merchandiseImageBasePath;	
		}else{
			$basePath = $this->productImageBasePath;
		}
		
		if(preg_match($this->regexProductCode,$productCode)){
			$productImageBasePathLength = strlen($basePath); //16
			$productImages = array();
			if($this->product['code']==$productCode && count($this->product['items'])>0){
				$productItems = $this->product['items'];
			}else{
				$productItems = $this->getProductItems($productCode,null,$merchandise);
			}
			$iProductImage = 0;
			
			// 1st check 3D
			if($limit==null){
				
			}
			
			// 2nd check images/products/
			$productImageSubPath = "products/";
			$productImageSubPathLength = 9;
			$imagesProducts = glob($_SERVER['DOCUMENT_ROOT'].$basePath.$productImageSubPath.$productCode.".*");
			if(!empty($imagesProducts)){
				$imagesProducts = array_values(preg_grep('/^((?!48x48).)*$/',$imagesProducts));
				$imagesProducts = array_values(preg_grep('/^((?!120x120).)*$/',$imagesProducts));
				$imagesProducts = array_values(preg_grep('/^((?!340x340).)*$/',$imagesProducts));
				foreach($imagesProducts as $imagesProductsKey => $imagesProduct){
					$imagesProducts[$imagesProductsKey] = substr($imagesProduct,strpos($imagesProduct,$basePath)+$productImageBasePathLength+$productImageSubPathLength); // remove path
					$imagesProducts[$imagesProductsKey] = substr($imagesProducts[$imagesProductsKey],0,strrpos($imagesProducts[$imagesProductsKey],".")); // remove extension
					if(!array_key_exists('products',$productImages)){
						$productImages['products'] = array();
					}
					if($limit==null || $iProductImage<$limit){
						array_push($productImages['products'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath.$productImageSubPath, "filename" => $imagesProducts[$imagesProductsKey]));
						$iProductImage++;
					}
				}
			}
			
			// 3rd check images/items/
			if(($limit==null || $iProductImage<$limit) && count($productItems)>0){
				$productImageSubPath = "items/";
				$productImageSubPathLength = 6;
				foreach($productItems as $productItem){
					if($limit==null || $iProductImage<$limit){
						$imagesItems = glob($_SERVER['DOCUMENT_ROOT'].$basePath.$productImageSubPath.$productItem['articleNumber'].".*");
						if(!empty($imagesItems)){
							$imagesItems = array_values(preg_grep('/^((?!48x48).)*$/',$imagesItems));
							$imagesItems = array_values(preg_grep('/^((?!120x120).)*$/',$imagesItems));
							$imagesItems = array_values(preg_grep('/^((?!340x340).)*$/',$imagesItems));
							foreach($imagesItems as $imagesItemsKey => $imagesItem){
								$imagesItems[$imagesItemsKey] = substr($imagesItem,strpos($imagesItem,$basePath)+$productImageBasePathLength+$productImageSubPathLength); // remove path
								$imagesItems[$imagesItemsKey] = substr($imagesItems[$imagesItemsKey],0,strrpos($imagesItems[$imagesItemsKey],".")); // remove extension
								if(!array_key_exists('items',$productImages)){
									$productImages['items'] = array();
								}
								if($limit==null || $iProductImage<$limit){
									array_push($productImages['items'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath.$productImageSubPath, "filename" => $imagesItems[$imagesItemsKey]));
									$iProductImage++;
								}
							}
						}
					}
				}
			}
			
			// 4th give image holder when no image was found
			if($iProductImage==0){
				$placeHolderTypes = $this->db->prepare("SELECT noImageType FROM products WHERE code = :productCode LIMIT 1");
				$placeHolderTypes->bindValue(":productCode",$productCode,PDO::PARAM_STR);
				$placeHolderTypes->execute();
				
				if($placeHolderTypes->rowCount()>0){
					foreach($placeHolderTypes->fetchAll(PDO::FETCH_ASSOC) as $placeHolderType){
						if($placeholderType['noImageType']=="barrel" || $placeholderType['noImageType']=="barrell" || $placeholderType['noImageType']=="budgetbarrel"){
							if($placeholderType['noImageType']=="budgetbarrel"){
								$budget	= "-budget";
							}							
							$show60L = false;
							$show205L = false;
							foreach($productItems as $productItem){
								if($productItem['capacity']=="60" && $productItem['capacityType']=="L"){
									$show60L = true;
								}elseif($productItem['capacity']=="205" && $productItem['capacityType']=="L"){
									$show205L = true;
								}
							}
							if($show205L && $show60L){
								if(!array_key_exists('products',$productImages)){
									$productImages['products'] = array();
								}
								array_push($productImages['products'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."products/", "filename" => "60L+205L".$budget.".1"));
							}elseif($show205L){
								if(!array_key_exists('items',$productImages)){
									$productImages['items'] = array();
								}
								array_push($productImages['items'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."items/", "filename" => "205L".$budget.".1"));
							}elseif($show60L){
								if(!array_key_exists('items',$productImages)){
									$productImages['items'] = array();
								}
								array_push($productImages['items'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."items/", "filename" => "60L".$budget.".1"));
							}
						}elseif($placeholderType['noImageType']=="pail"){
							$show18kg = false;
							$show50kg = false;
							foreach($productItems as $productItem){
								if($productItem['capacity']=="18" && $productItem['capacityType']=="kg"){
									$show18kg = true;
								}elseif($productItem['capacity']=="50" && $productItem['capacityType']=="kg"){
									$show50kg = true;
								}
							}
							if($show50kg && $show18kg){
								if(!array_key_exists('products',$productImages)){
									$productImages['products'] = array();
								}
								array_push($productImages['products'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."products/", "filename" => "18kg+50kg.1"));
							}elseif($show50kg){
								if(!array_key_exists('items',$productImages)){
									$productImages['items'] = array();
								}
								array_push($productImages['items'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."items/", "filename" => "50kg.1"));
							}elseif($show18kg){
								if(!array_key_exists('items',$productImages)){
									$productImages['items'] = array();
								}
								array_push($productImages['items'],array("basepath" => "http://www.mpmoil.nl/", "path" => $basePath."items/", "filename" => "18kg.1"));
							}
						}
					}
				}
			}			
		}
		
		return $productImages;
	}
	
	
	# get product superlatives
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductSuperlatives($productCode=null,$productID=null){
		$return = array();
		
		if(!preg_match("/^[0-9]{1,11}$/",$productID) && preg_match($this->regexProductCode,$productCode)){
			$productIDs = $this->db->prepare("SELECT id AS ID FROM products WHERE code = :productCode LIMIT 1");
			if($productIDs->execute(array(":productCode" => $productCode))){
				$productIDs = $productIDs->fetchAll(PDO::FETCH_ASSOC);
				if(count($productIDs)>0){
					$productID = $productIDs[0]['ID'];
				}
			}
		}
			
		if(preg_match("/^[0-9]{1,11}$/",$productID)){
			$superlatives = $this->db->prepare("SELECT productXsuperlative.superlativeProductID AS ID,products.code AS code,products.name AS name FROM productXsuperlative LEFT JOIN products ON products.id = productXsuperlative.superlativeProductID WHERE productXsuperlative.productID = :productID");
			if($superlatives->execute(array(":productID" => $productID))){
				$superlatives = $superlatives->fetchAll(PDO::FETCH_ASSOC);
				if(count($superlatives)>0){
					foreach($superlatives as $superlative){
						$return[$superlative['ID']] = array("code" => $superlative['code'],
															"name" => $superlative['name']
															);
					}
				}
			}	
		}
		return $return;
	}
	
	
	# get related products
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductsRelated($productCode=null,$productID=null,$pl=false){
		$return = array('products' => array(),'plProducts' => array());
		
		if(preg_match("/^[0-9]{1,11}$/",$productID) && !preg_match($this->regexProductCode,$productCode)){
			$products = $this->db->prepare("SELECT code FROM products WHERE id = :productID LIMIT 1");
			if($products->execute(array(":productID" => $productID))){
				$products = $products->fetchAll(PDO::FETCH_ASSOC);
				if(count($products)>0){
					$productCode = $products[0]['ID'];
				}
			}
		}
			
		if(preg_match($this->regexProductCode,$productCode)){
			if($pl){
				$plproducts = $this->db->prepare("SELECT pl_products.ID AS plProductID, pl_products.plID AS plID, pl.identifier AS plIdentifier, pl.name AS pl, pl_products.labelCode AS plCode, pl_products.name AS plName FROM pl_products LEFT JOIN pl ON pl.ID = pl_products.plID WHERE pl_products.code = :productCode");
				if($plproducts->execute(array(":productCode" => $productCode))){
					$plproducts = $plproducts->fetchAll(PDO::FETCH_ASSOC);
					if(count($plproducts)>0){
						foreach($plproducts as $plproduct){
							$return['plProducts'][$plproduct['ID']] = array("plID" => $plproduct['plID'],
																			"plIdentifier" => $plproduct['plIdentifier'],
																			"pl" => $plproduct['pl'],
																			"plProductID" => $plproduct['plProductID'],
																			"plCode" => $plproduct['plCode'],
																			"plName" => $plproduct['plName']
																			);
						}
					}
				}
			}
		}
		return $return;
	}
	
	
	# get product oneliners
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductOneliners($productCode=null,$returnConcept=false){
		if(preg_match($this->regexProductCode,$productCode)){
			// get productID
			$productIDs = $this->db->prepare("SELECT id AS ID FROM products WHERE code = :productCode LIMIT 1");
			if($productIDs->execute(array(":productCode" => $productCode))){
				$productIDs = $productIDs->fetchAll(PDO::FETCH_ASSOC);
				if(count($productIDs)>0){
					$productID = $productIDs[0]['ID'];
				}
			}else{
				return "Could not get product ID.";
			}
			
			$oneliners = $this->db->prepare("	SELECT TABLE_NAME as tableName
												FROM information_schema.TABLES
												WHERE TABLES.TABLE_NAME LIKE 'product_oneliners_%'
												");
			if($oneliners->execute()){
				$oneliners = $oneliners->fetchAll(PDO::FETCH_ASSOC);
				if(count($oneliners)>0){
					$return = array();
					foreach($oneliners as $oneliner){
						$language = str_replace("product_oneliners_","",$oneliner['tableName']);
						if($returnConcept){
						
							$translations = $this->db->prepare("SELECT text FROM pc_productOneliners_".$language." WHERE productID = :productID LIMIT 1");
							if($translations->execute(array(":productID" => $productID))){
								$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
								if(count($translations)>0){
									foreach($translations as $translation){
										$return[$language] = $translation['text'];
									}
								}
							}
							
						}else{
						
							$translations = $this->db->prepare("SELECT text FROM product_oneliners_".$language." WHERE code = :productCode LIMIT 1");
							if($translations->execute(array(":productCode" => $productCode))){
								$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
								if(count($translations)>0){
									foreach($translations as $translation){
										$return[$language] = $translation['text'];
									}
								}
							}
							
						}
						
					}
					return $return;
				}
			}
		}else{
			return "Invalid product code.";
		}
	}
	
	
	# get product descriptions
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductDescriptions($productCode=null,$returnConcept=false,$pl=false){
		if(preg_match($this->regexProductCode,$productCode)){
			// get productID
			if($pl){
				$productIDs = $this->db->prepare("SELECT ID FROM pl_products WHERE labelCode = :productCode LIMIT 1");
			}else{
				$productIDs = $this->db->prepare("SELECT id AS ID FROM products WHERE code = :productCode LIMIT 1");
			}
			if($productIDs->execute(array(":productCode" => $productCode))){
				$productIDs = $productIDs->fetchAll(PDO::FETCH_ASSOC);
				if(count($productIDs)>0){
					$productID = $productIDs[0]['ID'];
				}
			}else{
				return "Could not get product ID.";
			}
			
			if($pl){
				$descriptions = $this->db->prepare("SELECT TABLE_NAME as tableName
													FROM information_schema.TABLES
													WHERE TABLES.TABLE_NAME LIKE 'pl_productDescriptions_%'
													");
			}else{
				$descriptions = $this->db->prepare("SELECT TABLE_NAME as tableName
													FROM information_schema.TABLES
													WHERE TABLES.TABLE_NAME LIKE 'product_descriptions_%'
													");
			}
			if($descriptions->execute()){
				$descriptions = $descriptions->fetchAll(PDO::FETCH_ASSOC);
				if(count($descriptions)>0){
					$return = array();
					foreach($descriptions as $description){
						if($pl){
							$language = str_replace("pl_productDescriptions_","",$description['tableName']);
						}else{
							$language = str_replace("product_descriptions_","",$description['tableName']);
						}
						if($returnConcept){
							
							if($pl){
								$translations = $this->db->prepare("SELECT text FROM pl_pc_productDescriptions_".$language." WHERE plProductID = :productID LIMIT 1");
							}else{
								$translations = $this->db->prepare("SELECT text FROM pc_productDescriptions_".$language." WHERE productID = :productID LIMIT 1");
							}
							if($translations->execute(array(":productID" => $productID))){
								$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
								if(count($translations)>0){
									foreach($translations as $translation){
										$return[$language] = htmlspecialchars($translation['text'], ENT_QUOTES);
									}
								}
							}
							
						}else{
							
							if($pl){
								$queryVariable = $productID;
								$translations = $this->db->prepare("SELECT text FROM pl_productDescriptions_".$language." WHERE plProductID = :queryVariable LIMIT 1");
							}else{
								$queryVariable = $productCode;
								$translations = $this->db->prepare("SELECT text FROM product_descriptions_".$language." WHERE code = :queryVariable LIMIT 1");
							}
							if($translations->execute(array(":queryVariable" => $queryVariable))){
								$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
								if(count($translations)>0){
									foreach($translations as $translation){
										$return[$language] = htmlspecialchars($translation['text'], ENT_QUOTES);
									}
								}
							}
						
						}
					}
					return $return;
				}
			}
		}else{
			return "Invalid product code.";
		}
	}
	
	
	# get product specs
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductSpecs($productCode=null,$returnConcept=false,$combineSpecifications=true,$returnSpecificationAsArray=false){
		if(preg_match($this->regexProductCode,$productCode)){
			
			$specDivider = " / ";
			if(isset($this->plID)){
				$plPrefix = "pl_";
				$columnNameProductID = "plProductID";
				$parameterProductsID = "ID";
			}else{
				$plPrefix = "";
				$columnNameProductID = "productID";
				$parameterProductsID = "id";
			}
			if($returnConcept){
				$returnConceptPrefix = "pc_";
			}else{
				$returnConceptPrefix = "";
			}
			
			if($combineSpecifications){
				$specsQuery = "	SELECT	product_specifications.ID AS ID,
										product_specificationTypes.type AS type,
										product_specificationTypes.ordering AS typeOrder,
										product_specifications.standard AS standard,
										GROUP_CONCAT(product_specifications.specification ORDER BY product_specifications.specification SEPARATOR '".$specDivider."') AS specification
								FROM ".$plPrefix."products
								LEFT JOIN ".$plPrefix.$returnConceptPrefix."productXspecification ON ".$plPrefix.$returnConceptPrefix."productXspecification.".$columnNameProductID." = ".$plPrefix."products.".$parameterProductsID."
								LEFT JOIN product_specifications ON product_specifications.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationID
								LEFT JOIN product_specificationTypes ON product_specificationTypes.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationTypeID
								WHERE ";
				if(isset($this->plID)){
					$specsQuery .= $plPrefix."products.plID = :plID1 AND ".$plPrefix."products.labelCode = :productCode1 AND standard <> ''";
				}else{
					$specsQuery .= "products.code = :productCode1 AND standard <> ''";
				}
				$specsQuery .= " GROUP BY	product_specificationTypes.type,
											product_specifications.standard
								UNION ALL
								SELECT	product_specifications.ID AS ID,
										product_specificationTypes.type AS type,
										product_specificationTypes.ordering AS typeOrder,
										product_specifications.standard AS standard,
										product_specifications.specification AS specification
								FROM ".$plPrefix."products
								LEFT JOIN ".$plPrefix.$returnConceptPrefix."productXspecification ON ".$plPrefix.$returnConceptPrefix."productXspecification.".$columnNameProductID." = ".$plPrefix."products.".$parameterProductsID."
								LEFT JOIN product_specifications ON product_specifications.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationID
								LEFT JOIN product_specificationTypes ON product_specificationTypes.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationTypeID
								WHERE ";
				if(isset($this->plID)){
					$specsQuery .= $plPrefix."products.plID = :plID2 AND ".$plPrefix."products.labelCode = :productCode2 AND product_specifications.standard = ''";
				}else{
					$specsQuery .= "products.code = :productCode2 AND product_specifications.standard = ''";
				}
				$specsQuery .= " ORDER BY typeOrder ASC, standard ASC, specification ASC";
			}else{
				$specsQuery = "	SELECT	product_specifications.ID AS ID,
										product_specificationTypes.type AS type,
										product_specificationTypes.ordering AS typeOrder,
										product_specifications.standard AS standard,
										product_specifications.specification AS specification
								FROM ".$plPrefix."products
								LEFT JOIN ".$plPrefix.$returnConceptPrefix."productXspecification ON ".$plPrefix.$returnConceptPrefix."productXspecification.".$columnNameProductID." = ".$plPrefix."products.".$parameterProductsID."
								LEFT JOIN product_specifications ON product_specifications.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationID
								LEFT JOIN product_specificationTypes ON product_specificationTypes.ID = ".$plPrefix.$returnConceptPrefix."productXspecification.specificationTypeID
								WHERE ";
				if(isset($this->plID)){
					$specsQuery .= $plPrefix."products.plID = :plID1 AND ".$plPrefix."products.labelCode = :productCode1";
				}else{
					$specsQuery .= "products.code = :productCode1";
				}
				$specsQuery .= " ORDER BY typeOrder ASC, standard ASC, specification ASC";
			}
			
			$specs = $this->db->prepare($specsQuery);
			
			$tmpSpecs = array();
			
			if(	(
					$combineSpecifications &&
					(
						(
							isset($this->plID) &&
							$specs->execute(array(	":plID1" => $this->plID,
													":plID2" => $this->plID,
													":productCode1" => $productCode,
													":productCode2" => $productCode
													))
						) || (
							$specs->execute(array(	":productCode1" => $productCode,
													":productCode2" => $productCode
													))
						)
					)
				) || (
					isset($this->plID) &&
					$specs->execute(array(	":plID1" => $this->plID,
											":productCode1" => $productCode
											))
				) || (
					$specs->execute(array(	":productCode1" => $productCode
											))
				)
			){
				$specs = $specs->fetchAll(PDO::FETCH_ASSOC);
				if(count($specs)>0){
					foreach($specs as $spec){
						if(!empty($spec['standard']) || !empty($spec['specification'])){
							if(!is_array($tmpSpecs[$spec['type']])){
								$tmpSpecs[$spec['type']] = array(	"name" => $this->text->translate($spec['type']),
																	"specifications" => array()
																	);
							}
							if($returnSpecificationAsArray){
								$tmpSpecs[$spec['type']]['specifications'][$spec['ID']] = array("standard" => $spec['standard'],
																								"specification" => $spec['specification']
																								);
							}else{
								$tmpSpecs[$spec['type']]['specifications'][$spec['ID']] = trim($spec['standard']." ".$spec['specification']);
							}
						}
					}
				}
			}
			
			foreach($tmpSpecs as $tmpSpecsTypeID => $tmpSpecsType){
				natcasesort($tmpSpecs[$tmpSpecsTypeID]['specifications']);
			}
	
			// $tmpSpecs);
			return $tmpSpecs;
		}
	}
	
	
	# get specifications
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getSpecifications(){
		$specifications = $this->db->prepare("SELECT * FROM product_specifications ORDER BY standard ASC, specification ASC, brandSpecific DESC");
		if($specifications->execute()){
			$specifications = $specifications->fetchAll(PDO::FETCH_ASSOC);
		}
		return $specifications;
	}
	
	
	# get specificationTypes
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getSpecificationTypes(){
		$specificationTypes = array();
		$getSpecificationTypes = $this->db->prepare("SELECT ID,type FROM product_specificationTypes ORDER BY ordering ASC");
		if($getSpecificationTypes->execute()){
			$getSpecificationTypes = $getSpecificationTypes->fetchAll(PDO::FETCH_ASSOC);
			if(count($getSpecificationTypes)>0){
				foreach($getSpecificationTypes as $specificationType){
					$specificationTypes[$specificationType['ID']] = array(	"identifier" => $specificationType['type'],
																			"name" => $this->text->translate($specificationType['type'])
																			);
				}
			}
		}
		return $specificationTypes;
	}
	
	
	# get specifications (by new specification ID)
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductsBySpecification($specificationID=null,$showPl=false,$showProductStatus=false,$showConcepts=false){
		$return = array();
		
		// mpm products
		$products = array();
		
		$getProducts = $this->db->prepare("SELECT 	products.id AS ID,
													products.code AS code
											FROM productXspecification
											LEFT JOIN products ON products.id = productXspecification.productID
											WHERE productXspecification.specificationID = :specificationID
											ORDER BY products.code ASC
											");
		if($getProducts->execute(array(":specificationID" => $specificationID))){
			$getProducts = $getProducts->fetchAll(PDO::FETCH_ASSOC);
			if(count($getProducts)>0){
				foreach($getProducts as $product){
					$products['mpmoil_'.$product['ID']] = $product;
					if($showProductStatus){
						$products['mpmoil_'.$product['ID']]['conceptExists'] = false;
						$products['mpmoil_'.$product['ID']]['lockedExists'] = false;	
						$checkProductStatus = $this->db->prepare("SELECT locked FROM pc_products WHERE productID = :productID");
						if($checkProductStatus->execute(array(":productID" => $product['ID']))){
							$checkProductStatus = $checkProductStatus->fetchAll(PDO::FETCH_ASSOC);
							if(count($checkProductStatus)>0){
								if($checkProductStatus[0]['locked']){
									$products['mpmoil_'.$product['ID']]['lockedExists'] = true;
								}else{
									$products['mpmoil_'.$product['ID']]['conceptExists'] = true;
								}
							}
						}
					}
					if($showConcepts){
						$products['mpmoil_'.$product['ID']]['concept'] = false;
						$products['mpmoil_'.$product['ID']]['locked'] = false;
					}
				}
			}
		}
		
		if($showConcepts){
			$getConceptProducts = $this->db->prepare("SELECT 	products.id AS ID,
																products.code AS code,
																pc_products.locked AS locked
														FROM pc_productXspecification
														LEFT JOIN products ON products.id = pc_productXspecification.productID
														LEFT JOIN pc_products ON pc_products.productID = pc_productXspecification.productID
														WHERE pc_productXspecification.specificationID = :specificationID
														ORDER BY products.code ASC
														");
			if($getConceptProducts->execute(array(":specificationID" => $specificationID))){
				$getConceptProducts = $getConceptProducts->fetchAll(PDO::FETCH_ASSOC);
				if(count($getConceptProducts)>0){
					foreach($getConceptProducts as $product){
						if(!is_array($products['mpmoil_'.$product['ID']])){
							$products['mpmoil_'.$product['ID']] = $product;	
						}
						if($product['locked']){
							$products['mpmoil_'.$product['ID']]['locked'] = true;
						}else{
							$products['mpmoil_'.$product['ID']]['concept'] = true;
						}
					}
				}
			}
		}
		
		foreach($products as $product){
			if(!empty($product['ID'])){
				array_push($return,array(	"ID" => $product['ID'],
											"code" => $product['code'],
											"label" => "mpmoil",
											"labelName" => "MPM Oil",
											"locked" => $product['locked'],
											"concept" => $product['concept'],
											"lockedExists" => $product['lockedExists'],
											"conceptExists" => $product['conceptExists']
											));
			}
		}
		
		// private label products
		if($showPl){
		
			$plProducts = array();
			
			$getProducts = $this->db->prepare("SELECT	pl_products.ID AS ID,
													pl_products.labelCode AS code,
													pl.ID AS plID,
													pl.identifier AS label,
													pl.name AS labelName
											FROM pl_productXspecification
											LEFT JOIN pl_products ON pl_products.ID = pl_productXspecification.plProductID
											LEFT JOIN pl ON pl.ID = pl_products.plID
											WHERE pl_productXspecification.specificationID = :specificationID
											ORDER BY pl_products.labelCode ASC, pl_products.code ASC
											");
			if($getProducts->execute(array(":specificationID" => $specificationID))){
				$getProducts = $getProducts->fetchAll(PDO::FETCH_ASSOC);
				if(count($getProducts)>0){
					foreach($getProducts as $product){
						$plProducts[$product['label'].'_'.$product['ID']] = $product;
						if($showProductStatus){
							$plProducts[$product['label'].'_'.$product['ID']]['conceptExists'] = false;
							$plProducts[$product['label'].'_'.$product['ID']]['lockedExists'] = false;	
							$checkProductStatus = $this->db->prepare("SELECT locked FROM pl_pc_products WHERE plProductID = :plProductID");
							if($checkProductStatus->execute(array(":plProductID" => $product['ID']))){
								$checkProductStatus = $checkProductStatus->fetchAll(PDO::FETCH_ASSOC);
								if(count($checkProductStatus)>0){
									if($checkProductStatus[0]['locked']){
										$plProducts[$product['label'].'_'.$product['ID']]['lockedExists'] = true;
									}else{
										$plProducts[$product['label'].'_'.$product['ID']]['conceptExists'] = true;
									}
								}
							}
						}
						if($showConcepts){
							$plProducts[$product['label'].'_'.$product['ID']]['concept'] = false;
							$plProducts[$product['label'].'_'.$product['ID']]['locked'] = false;
						}
					}
				}
			}	
			
			if($showConcepts){
				$getConceptProducts = $this->db->prepare("SELECT 	pl_products.ID AS ID,
																	pl_products.labelCode AS code,
																	pl_products.plID AS plID,
																	pl.identifier AS label,
																	pl.name AS labelName,
																	pl_pc_products.locked AS locked
															FROM pl_pc_productXspecification
															LEFT JOIN pl_products ON pl_products.ID = pl_pc_productXspecification.plProductID
															LEFT JOIN pl ON pl.ID = pl_products.plID
															LEFT JOIN pl_pc_products ON pl_pc_products.plProductID = pl_pc_productXspecification.plProductID
															WHERE pl_pc_productXspecification.specificationID = :specificationID
															ORDER BY pl_products.code ASC
															");
				if($getConceptProducts->execute(array(":specificationID" => $specificationID))){
					$getConceptProducts = $getConceptProducts->fetchAll(PDO::FETCH_ASSOC);
					if(count($getConceptProducts)>0){
						foreach($getConceptProducts as $product){
							if(!is_array($plProducts[$product['label'].'_'.$product['ID']])){
								$plProducts[$product['label'].'_'.$product['ID']] = $product;	
							}
							if($product['locked']){
								$plProducts[$product['label'].'_'.$product['ID']]['locked'] = true;
							}else{
								$plProducts[$product['label'].'_'.$product['ID']]['concept'] = true;
							}
						}
					}
				}
			}
			
			foreach($plProducts as $product){
				if(!empty($product['ID'])){
					array_push($return,array(	"ID" => $product['ID'],
												"plID" => $product['plID'],
												"code" => $product['code'],
												"label" => $product['label'],
												"labelName" => $product['labelName'],
												"locked" => $product['locked'],
												"concept" => $product['concept'],
												"lockedExists" => $product['lockedExists'],
												"conceptExists" => $product['conceptExists']
												));
				}
			}
		
		}
		
		return $return;
	}
	
	
	# get product items
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductItems($productCode=null,$returnImages=false,$merchandise=false){
		if(preg_match($this->regexProductCode,$productCode)){
			if(isset($this->plID)){
				$items = $this->db->prepare("SELECT pl_productItems.ID as ID,
													pl_productItems.articleNumber AS art_no,
													pl_productPackages.capacity AS sort1,
													pl_productPackages.capacity AS sort2,
													pl_productPackages.capacity AS capacity,
													pl_productPackages.capacityType AS capacity_type,
													pl_productPackages.itemsPerBox AS items_per_box,
													pl_productPackages.itemsPerPallet AS items_per_pallet
													FROM pl_products
													LEFT JOIN pl_productItems ON pl_productItems.productID = pl_products.ID
													LEFT JOIN pl_productPackages ON pl_productPackages.ID = pl_productItems.packageID
													WHERE pl_products.labelCode = :productCode
													");
				$returnImages = false;
			}elseif($merchandise){
				$items = $this->db->prepare("SELECT id AS ID, art_no, capacity AS sort1, capacity AS sort2, capacity, capacity_type, items_per_box, items_per_pallet FROM merchandise_items WHERE code = :productCode"); // ORDER BY ABS(capacity) ASC, SUBSTRING(sort,1,1) DESC. sort2 ASC
			}else{
				$items = $this->db->prepare("SELECT id AS ID, art_no, capacity AS sort1, capacity AS sort2, capacity, capacity_type, items_per_box, items_per_pallet FROM product_items WHERE code = :productCode"); // ORDER BY ABS(capacity) ASC, SUBSTRING(sort,1,1) DESC. sort2 ASC
			}
			$items->bindValue(":productCode",$productCode,PDO::PARAM_STR);
			$items->execute();
			
			if($items->rowCount()>0){
				$tmpItems = array();
				$items = $items->fetchAll(PDO::FETCH_ASSOC);
				foreach($items as $item){
					$tmpItem = array();
					$tmpItem['ID'] = $item['ID'];
					$tmpItem['articleNumber'] = $item['art_no'];
					$tmpItem['capacity'] = $item['capacity'];
					$tmpItem['capacityType'] = $item['capacity_type'];
					if(!empty($item['items_per_box'])){
						$tmpItem['itemsPerBox'] = $item['items_per_box'];
					}else{
						$tmpItem['itemsPerBox'] = 0;
					}
					if(!empty($item['items_per_pallet'])){
						$tmpItem['itemsPerPallet'] = $item['items_per_pallet'];
					}else{
						$tmpItem['itemsPerPallet'] = 0;
					}
					if(empty($tmpItems) || !preg_match("/^[0-9,]{1,10}$/",$item['capacity'])){
						array_push($tmpItems,$tmpItem);
					}else{
						$tmpFound = false;
						$i = 0;
						while($tmpFound!=true && $i<count($items)){
							if($i==0 && $item['capacity']<$tmpItems[$i]['capacity']){
								array_unshift($tmpItems,$tmpItem);
								$tmpFound = true;
							}elseif($i==count($tmpItems)){
								array_push($tmpItems,$tmpItem);
								$tmpFound = true;
							}elseif($item['capacity']>$tmpItems[($i-1)]['capacity'] && $item['capacity']<$tmpItems[$i]['capacity']){
								$ir = count($tmpItems)-1;
								while($ir>=$i){
									$tmpItems[$ir+1] = $tmpItems[$ir];
									$ir--;
								}
								$tmpItems[$i] = $tmpItem;								
								ksort($tmpItems);
								$tmpFound = true;
							}
							$i++;						
						}
					}
				}
					
				// get item images
				if($returnImages){
					foreach($tmpItems as $itemKey => $item){
						$imagesItems = glob($_SERVER['DOCUMENT_ROOT'].$this->productImageBasePath."items/".$item['articleNumber'].".*");
						if(!empty($imagesItems)){
							$imagesItems = array_values(preg_grep('/^((?!48x48).)*$/',$imagesItems));
							$imagesItems = array_values(preg_grep('/^((?!120x120).)*$/',$imagesItems));
							$imagesItems = array_values(preg_grep('/^((?!340x340).)*$/',$imagesItems));
							foreach($imagesItems as $imagesItemsKey => $imagesItem){
								$imagesItems[$imagesItemsKey] = substr($imagesItem,strpos($imagesItem,$this->productImageBasePath)+strlen($this->productImageBasePath)+6); // remove path
								$imagesItems[$imagesItemsKey] = substr($imagesItems[$imagesItemsKey],0,strrpos($imagesItems[$imagesItemsKey],".")); // remove extension
								if(!array_key_exists('images',$tmpItems)){
									$tmpItems[$itemKey]['images'] = array();
								}
								array_push($tmpItems[$itemKey]['images'],array(	"size_original" => "http://www.mpmoil.nl/".$this->productImageBasePath."items/".$imagesItems[$imagesItemsKey].".png",
																				"size_340x340" => "http://www.mpmoil.nl/".$this->productImageBasePath."items/".$imagesItems[$imagesItemsKey].".340x340.png",
																				"size_120x120" => "http://www.mpmoil.nl/".$this->productImageBasePath."items/".$imagesItems[$imagesItemsKey].".120x120.png",
																				"size_48x48" => "http://www.mpmoil.nl/".$this->productImageBasePath."items/".$imagesItems[$imagesItemsKey].".48x48.png"
																				)
																			);
							}
						}
					}
				}
				
				return $tmpItems;
			}
		}
	}
	
	
	# get product item (not for merchandise)
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductItemByID($itemID=null,$plID=null){
		
		if(isset($this->plID)){
				$items = $this->db->prepare("SELECT pl_productItems.ID as ID,	
													pl_products.name,
													pl_productItems.articleNumber AS art_no,
													pl_productPackages.capacity AS sort1,
													pl_productPackages.capacity AS sort2,
													pl_productPackages.capacity AS capacity,
													pl_productPackages.capacityType AS capacity_type,
													pl_productPackages.itemsPerBox AS items_per_box,
													pl_productPackages.itemsPerPallet AS items_per_pallet
													FROM pl_products
													LEFT JOIN pl_productItems ON pl_productItems.productID = pl_products.ID
													LEFT JOIN pl_productPackages ON pl_productPackages.ID = pl_productItems.packageID
													WHERE pl_productsItems.plID = :itemID
													");
				$returnImages = false;
			}elseif($merchandise){
				$items = $this->db->prepare("SELECT id AS ID, art_no, capacity AS sort1, capacity AS sort2, capacity, capacity_type, items_per_box, items_per_pallet FROM merchandise_items WHERE code = :itemID"); // ORDER BY ABS(capacity) ASC, SUBSTRING(sort,1,1) DESC. sort2 ASC
			}else{
				$items = $this->db->prepare("SELECT id AS ID, art_no, capacity AS sort1, capacity AS sort2, capacity, capacity_type, items_per_box, items_per_pallet FROM product_items WHERE id = :itemID"); // ORDER BY ABS(capacity) ASC, SUBSTRING(sort,1,1) DESC. sort2 ASC
			}
			$items->bindValue(":itemID",$itemID,PDO::PARAM_STR);
			$items->execute();
		return $items;
	}
	
	
	# get product standard analyses
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getProductStandardAnalyses($productCode){
		if(preg_match($this->regexProductCode,$productCode)){
			$analyses = array();
			if(isset($this->plID)){
				$standardAnalyses = $this->db->prepare("SELECT	product_standards.code AS code,
																product_standards.standard AS standard,
																product_standards.value AS value,
																product_typicals.name AS name,
																product_typicals.name_translate AS name_translate,
																product_typicals.name_variable1 AS name_variable1,
																product_typicals.name_variable2 AS name_variable2,
																product_typicals.method AS method,
																product_typicals.unit AS unit,
																product_typicals.unit_translate AS unit_translate
																FROM pl_products
																LEFT JOIN product_standards ON product_standards.code = pl_products.code
																LEFT JOIN product_typicals ON product_typicals.standard = product_standards.standard
																WHERE pl_products.labelCode = :productCode
																ORDER BY product_typicals.ordering
																");
			}else{
				$standardAnalyses = $this->db->prepare("SELECT	product_standards.code AS code,
																product_standards.standard AS standard,
																product_standards.value AS value,
																product_typicals.name AS name,
																product_typicals.name_translate AS name_translate,
																product_typicals.name_variable1 AS name_variable1,
																product_typicals.name_variable2 AS name_variable2,
																product_typicals.method AS method,
																product_typicals.unit AS unit,
																product_typicals.unit_translate AS unit_translate
																FROM product_standards
																LEFT JOIN product_typicals ON product_typicals.standard = product_standards.standard
																WHERE product_standards.code = :productCode
																ORDER BY product_typicals.ordering
																");
			}
			$standardAnalyses->bindValue(":productCode",$productCode,PDO::PARAM_STR);
			if($standardAnalyses->execute()){
				$standardAnalyses = $standardAnalyses->fetchAll(PDO::FETCH_ASSOC);
				if(count($standardAnalyses)>0){
					foreach($standardAnalyses as $standardAnalysis){
						$standard = array();
						if($standardAnalysis['name_translate']==1){
							$standard['test'] = $this->text->translate($standardAnalysis['name'],$this->language);
						}else{
							$standard['test'] = $standardAnalysis['name'];
						}
						if($standardAnalysis['unit_translate']==1){
							$standardAnalysis['value'] = $this->text->translate($standardAnalysis['value'],$this->language);
						}
						$standard['typical'] = $standardAnalysis['value'];
						if($standardAnalysis['unit_translate']==1){
							$standard['unit'] = $this->text->translate($standardAnalysis['unit'],$this->language);
						}else{
							$standard['unit'] = $standardAnalysis['unit'];
						}
						$standard['test'] = ucfirst(str_replace('[variable]',$standardAnalysis['name_variable1'],$standard['test']));
						$standard['test'] = ucfirst(str_replace('[variable1]',$standardAnalysis['name_variable1'],$standard['test']));
						$standard['test'] = ucfirst(str_replace('[variable2]',$standardAnalysis['name_variable2'],$standard['test']));
						if(!empty($standardAnalysis['method'])){
							$standard['method'] = $standardAnalysis['method'];
						}
						
						array_push($analyses,$standard);
					}
				}
			}
			return $analyses;
		}
	}
	

	# get product oneliner translations
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getOnelinerTranslations($pl=false){
		$return = array();
		
		$translations = $this->db->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE :tableName");
		if($translations->execute(array(":tableName" => "product_oneliners_%"))){
			$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
			if(count($translations)){
				foreach($translations as $translation){
					array_push($return,substr($translation['TABLE_NAME'],-2));
				}
			}
		}
		sort($return);
		$this->onelinerTranslations = $return;
		return $this->onelinerTranslations;
	}
	
	
	# get product description translations
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getDescriptionTranslations($pl=false){
		$return = array();
		
		if($pl || $this->plID){
			$tableName = "pl_productDescriptions_%";
		}else{
			$tableName = "product_descriptions_%";	
		}
		
		$translations = $this->db->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE :tableName");
		if($translations->execute(array(":tableName" => $tableName))){
			$translations = $translations->fetchAll(PDO::FETCH_ASSOC);
			if(count($translations)){
				foreach($translations as $translation){
					array_push($return,substr($translation['TABLE_NAME'],-2));
				}
			}
		}
		sort($return);
		$this->descriptionTranslations = $return;
		return $this->descriptionTranslations;
	}
	
	
	# price (round by 5 cents)
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function price($price,$multiply=null,$round=false){
		$price = str_replace(",",".",$price);
		if(!empty($multiply)){
			$price = round($multiply*$price,2);
		}
		$tmpPrice = explode(".",$price);
		$tmpPrice[1] = $tmpPrice[1][0].$tmpPrice[1][1];
		
		// no decimal, or 0, or 00
		if(empty($tmpPrice[1]) || $tmpPrice[1]=="0" || $tmpPrice[1]=="00"){
			$tmpPrice[1] = "-";
		
		// 1 decimal
		}elseif(strlen($tmpPrice[1])<2){
			$tmpPrice[1] = $tmpPrice[1][0]."0";
		
		// last decimal smaller than 3
		}elseif($tmpPrice[1][1]<3 && $round){
			if($tmpPrice[1][0]=="0"){
				$tmpPrice[1] = "-";
			}else{
				$tmpPrice[1] = $tmpPrice[1][0]."0";
			}
		
		// last decimal larger or equal to 3, but smaller than 8
		}elseif($tmpPrice[1][1]>=3 && $tmpPrice[1][1]<8 && $round){
			$tmpPrice[1] = $tmpPrice[1][0]."5";
		
		// last decimal larger or equal to 8
		}elseif($tmpPrice[1][1]>=8 && $round){
			$price = round(($tmpPrice[0].".".$tmpPrice[1]),1);
			$tmpPrice = explode(".",$price);
			if(strlen($tmpPrice[1])<1 || $tmpPrice[1][0]=="0"){
				$tmpPrice[1] = "-";
			}else{
				$tmpPrice[1] = $tmpPrice[1]."0";
			}
		}
		
		$price = $tmpPrice[0].",".$tmpPrice[1];
					
		return $price;
	}
}

?>