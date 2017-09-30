<?php
/**
 * SQL Server function - Sample
 * Author : Eric See
 * 2/11/2016
 */
require_once("sqlserver_fns.php");

//Create Item arrays
$itemarray=array(); 

$e2 = new ItemOrdered('130000000000030', 1, 1); //4.50 dollar, quantity, catid
$topping1 = new PrepItem("130000000000147", 1); //4 dollar, quantity
$e2->addPrepItem($topping1);
array_push($itemarray, $e2);
echo 'Have Discount..'.PHP_EOL;
//$result_4 = insertPaidOrderMain('dev', SQLSERVER_ZINGMOBILE_TABLE, $itemarray, 14.45, "eric", YES_CODE_VOUCHER, 1);
//if (!$result_4) echo "Error. Insert Paid Order Failed".PHP_EOL;
//$result_4 = insertPaidOrderMain('dev', SQLSERVER_ZINGMOBILE_TABLE, $itemarray, 18.73, "eric", "90308495", YES_CODE_VOUCHER, 1);
//if (!$result_4) echo "Error. Insert Paid Order Failed".PHP_EOL;

echo 'No Discount..'.PHP_EOL;
$result_5 = insertPaidOrderMain('dev', SQLSERVER_ZINGMOBILE_TABLE, $itemarray, 19.80, "eric", "90308495", NO_CODE_VOUCHER);
if (!$result_5) echo "Error. Insert Paid Order Failed".PHP_EOL;
//insertTestFood('dev', 71994)
//insertOpenItem('dev', 71994, 'Eric xxTeo');
//insertDiscount('dev', 71997, 1.00);

//Insert ubpaid order - Book A Table
//$result_2 = insertUnpaidOrderMain('azure',POSID, OPERATOR_NO, "Eric",$itemarray);
//$result_2 = insertUnpaidOrderMain('azure', "Eric",$itemarray);
//if (!$result_2) echo "Error. Insert Unpaid Order Failed".PHP_EOL;

//insert paid order - Xpress
//$result_3 = insertPaidOrderMain('azure', POSID, OPERATOR_NO, SQLSERVER_ZINGMOBILE_TABLE,$itemarray, 108.07, DISCOUNT_CODE_VOUCHER, 20.0);
//$result_3 = insertPaidOrderMain('azure', SQLSERVER_ZINGMOBILE_TABLE,$itemarray, 108.07, "eric See", DISCOUNT_CODE_VOUCHER, 20.0);
//if (!$result_3) echo "Error. Insert Paid Order Failed".PHP_EOL;
//amount is trancated to 2 decimal
//$result_4 = insertPaidOrderMain('sp', SQLSERVER_ZINGMOBILE_TABLE,$itemarray, 15.52, "eric", NO_CODE_VOUCHER);
//if (!$result_4) echo "Error. Insert Paid Order Failed".PHP_EOL;

//insertTransaction('sp', 71930, 0);

//$it = createCustomerNamePrepItem("azure");
//echo $it->getPLU();

//Get All List
//$list = getPLUList('azure');
//print_r($list);


//$arr = array();
//array_push($arr, "PLU0000001");
//foreach ($arr as $item) {
 //   echo $item.PHP_EOL;
//}

//$e1->addPrepItem("PLU100000000000001");
//$e1->clearPrepItem();
//$e1->addPrepItem("PLU100000000000002");
//$e1->printAllPrepItem();

//$toppings = $e1->getPrepItemArray();
//foreach ($toppings as $item) {
//	echo $item.PHP_EOL;
//}
?>
