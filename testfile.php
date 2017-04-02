<?php
namespace classes;
require_once("./dblink.php");
include_once("./Collection.php");

$results = $mysqli->query("SELECT
products.name,
products.code,
product_items.id,
product_items.productID,
product_items.packageID,
product_items.code,
product_items.art_no,
product_items.eanCode,
product_items.capacity,
product_items.capacity_type,
product_items.items_per_box,
product_items.items_per_pallet,
product_items.`date`,
product_items.updatedByUserID
FROM
products
Inner Join product_items ON products.id = product_items.productID
WHERE
products.code =  '01000'
");

$num_rows = mysqli_num_rows($results);
//var_dump($results);
//items=array();
$itemsCollection = new Collection();

while($item = mysqli_fetch_array($results)){

    $itemsCollection->add($item['id'],$item)
    /*
     * echo "<td>". $item['name'] . "</td>";
    echo "<td>". $item['code'] . "</td>";
    echo "<td>". $item['capacity'] . "</td>";
    echo "<td>". $item['capacity_type'] . "</td>";
   echo " </tr><tr>";*/
;}   
echo $itemsCollection->numItems;
//var_dump($itemsCollection);

;?>
<div class="">
    
<form action="action">
    <table>
        <tr></td>
            <td><select name="code">
    <option>dasdfsdafsdafasd</option>
    <option>dasdfasdfadsf</option>
    <option>dfsadfdasfasd</option>
    <option>dfdfasdfadfadsd</option>
</select>
</td>
            <td><div style="width: 103px;display:inline-block;float:left;"><input type="text" name="" style="
    width: 100px;
"></div><div style="width: 61px;display:inline-block;float:left;"><input type="text" name=""style="
    width: 60px;
"></div>
            <td><input type="text" name=""></td>
            <td><input type="text" name=""></td>
            <td><select name="code">
    <option></option>
    <option></option>
    <option></option>
    <option></option>
</select></td>
<td><input type="text" name=""></td>
        </tr>
    </table>
    
    </form>
</div>    