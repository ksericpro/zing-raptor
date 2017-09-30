<?php
/**
 * SQL Server function to insert Paid and Unpaid Orders
 * Author : Eric See
 * 2/11/2016
 */
require_once("sqlserver_init.php");
require_once("sqlserver_class.php");

/*function insertPaidOrder($posid, $operatorno, $table, $itemarray, $amount, $discountcode, $disc_amount)
{
	return insertPaidOrderMain('azure', $table, $itemarray, $amount, $discountcode, $disc_amount);
}*/
function insertTransaction($branchcode, $salesno, $splitno)
{
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID;
	$posid = $POSID[$branchcode];
	$discountcode = $DISCOUNT_CODE[$branchcode];
	if (DEBUG_PRINT) echo "insertPaidOrder::connect DB".PHP_EOL;
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	$plu = '130000000000030';
	$quantity = 1;
	$catid = 1;
	$posid='WEB001';
	$table='PREORDER';
	if (DEBUG_PRINT) echo "insertPaidOrder::Open Table".PHP_EOL;
	$result_open = $o->execute_OpenTable_SP($posid, $operatorno, $table, 1);
	
	
	//$operatorno = 17;
	$table = 'PREORDER';
	if (DEBUG_PRINT) echo "inserting PLU>".$plu.", ",$quantity.", ".$catid.PHP_EOL;
	$result_insert = $o->execute_OrderItem_SP($posid, $operatorno, $table, $salesno, $splitno, $plu, $quantity, $catid);
	if (DEBUG_PRINT) print_r($result_insert);
	
}

//Insert Paid Order-Express Checkout
/*function insertPaidOrderMain2($branchcode, $table, $itemarray, $amount, $cust_name, $discountcode, $disc_amount = 0)
{
	//global $SQLSERVERS;
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID;
	$posid = $POSID[$branchcode];
	$discountcode = $DISCOUNT_CODE[$branchcode];
	if (DEBUG_PRINT) echo "insertPaidOrder::connect DB".PHP_EOL;
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	
	//Close Table
	if (DEBUG_PRINT) echo "insertUnpaidOrder::Force close on $table.".PHP_EOL;
	$o->close_Table($table);
	
	//Open Table
	if (DEBUG_PRINT) echo "insertPaidOrder::Open Table".PHP_EOL;
	$result_open = $o->execute_OpenTable_SP($posid, $operatorno, $table, 1);
	//if (DEBUG_PRINT) print_r($result_open);
	
	if ($result_open['ErrCode']==ERRCODE_FAILED)
	{
		if (DEBUG_PRINT) echo 'OpenTable Error..'.$table." ".$result_open['ErrMsg'].PHP_EOL;
		return false;		
	}
	else
	{
		if (DEBUG_PRINT) echo "insertPaidOrder::1..* Order Items".PHP_EOL;
		$salesno = $result_open['SalesNo'];
		$splitno = $result_open['SplitNo'];
		if (DEBUG_PRINT) echo "SalesNo=".$salesno.", SplitNo=".$splitno.PHP_EOL;
		
		//Print Customer Name as first PrepItem
		//$m_cust_name = massageCustomerName($cust_name);
		//$m_openprepitem = createCustomerNamePrepItem($branchcode);
		//$result_name_prepitem = $o->execute_AddOpenPrepItem_SP($posid, $operatorno, $table, $salesno, $splitno, $m_openprepitem->getPLU(), $m_openprepitem->getQuantity(), $salesno, $m_cust_name, 0.0);
		//if ($result_name_prepitem['ErrCode']==ERRCODE_FAILED)
		//{
			//if (DEBUG_PRINT) echo 'Name PrepItem Error..'.$table." ".$result_name_prepitem['ErrMsg'].PHP_EOL;
		//}
		//else {
		//	if (DEBUG_PRINT) echo 'PrepItem Successful for '.$m_cust_name.PHP_EOL;
		//}
		$salesref = 0;
		foreach ($itemarray as &$item) {
			$plu = $item->getPLU();
			$quantity = $item->getQuantity();
			$catid = $item->getCatId();
			if (DEBUG_PRINT) echo "inserting PLU>".$plu.", ",$quantity.", ".$catid.PHP_EOL;
			$result_insert = $o->execute_OrderItem_SP($posid, $operatorno, $table, $salesno, $splitno, $plu, $quantity, $catid);
			//if (DEBUG_PRINT) print_r($result_insert);
			if ($result_insert['ErrCode']==ERRCODE_FAILED)
			{
				//echo "dsds".PHP_EOL;
				if (DEBUG_PRINT) echo 'OrderItem Error..'.$result_insert['ErrMsg'].PHP_EOL;
			}
			else {
				$salesref = $result_insert['SalesRef'];
				if (DEBUG_PRINT) echo "Insert OrderItem Successfully ".$salesref.PHP_EOL;
				
				
				//insert toppings
				$toppings = $item->getPrepItemArray();
				foreach ($toppings as $item) {
					//echo "dsds".PHP_EOL;
					if (DEBUG_PRINT) echo $item->getPLU().', ('.$item->getQuantity().')'.PHP_EOL;
					//echo 'Toppings..'.$item.PHP_EOL;
					$result_prepitem = $o->execute_PrepItem_SP($posid, $operatorno, $table, $salesno, $splitno, $item->getPLU(), $item->getQuantity(), $salesref);
					if ($result_prepitem['ErrCode']==ERRCODE_FAILED)
					{
						if (DEBUG_PRINT) echo 'PrepItem Error..'.$result_prepitem['ErrMsg'].PHP_EOL;
					}
				}
				
			}
		}		
		
		
		if ($discountcode!=NO_CODE_VOUCHER)
		{
			if (DEBUG_PRINT) echo "insertPaidOrder::Discount on SalesRef = $salesref".PHP_EOL;
			$result_disc = $o->execute_Discount_SP($posid, $operatorno, $table, $salesno, $splitno, $salesref, $discountcode, $disc_amount);
			if ($result_disc['ErrCode']==ERRCODE_FAILED)
			{
				if (DEBUG_PRINT) echo 'Discount Error..'.$result_disc['ErrMsg'].PHP_EOL;
			}
		}
		
		//Hold Table
		if (DEBUG_PRINT) echo "insertUnpaidOrder::Hold Table".PHP_EOL;
		//HoldTable
		$result_hold = $o->execute_HoldTable_SP($posid, $operatorno, $table, $salesno, $splitno);
		if ($result_hold['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'HoldTable Error..'.$result_hold['ErrMsg'].PHP_EOL;
		}
		else {
			echo "HoldTable Successful for ".$table.PHP_EOL;
		}
		
		return true;
		
		if (DEBUG_PRINT) echo "insertPaidOrder::Payment".PHP_EOL;
		$result_payment = $o->execute_Payment_SP($posid, $operatorno, $table, $salesno, $splitno, $PAYMENT_TYPE[$branchcode], $amount);
		if (DEBUG_PRINT) print_r($result_payment);
		if ($result_payment['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'Payment Error..'.$result_payment['ErrMsg'].PHP_EOL;
		}
		else{
			if (DEBUG_PRINT) echo 'Payment Message..'.$result_payment['PaymentMsg'].", Amount=".$result_payment['Amount'].PHP_EOL;
			if ($result_payment['PaymentMsg']==PAYMENTMSG_PAID)
			{
				if (DEBUG_PRINT) echo "insertPaidOrder::Close Table".PHP_EOL;
				//Close Table
				$o->close_Table($table);
			}
		}
		
	}
	
	return true;
	
}*/

function insertPaidOrderMain($branchcode, $table, $itemarray, $amount, $cust_name, $cust_contact, $discountcode, $disc_amount = 0)
{
	//global $SQLSERVERS;
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID, $DISCOUNT_CODE;
	$posid = $POSID[$branchcode];
	$operatorno = $OPERATOR_NO[$branchcode];
	$discountcode = $DISCOUNT_CODE[$branchcode];
	if (DEBUG_PRINT) echo "insertPaidOrder::connect DB".PHP_EOL;
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	
	//Close Table
	if (DEBUG_PRINT) echo "insertPaidOrder::Force close on $table.".PHP_EOL;
	$o->close_Table($table);

	//Open Table 
	if (DEBUG_PRINT) echo "insertPaidOrder::Open Table".PHP_EOL;
	$result_open = $o->execute_OpenTable_SP($posid, $operatorno, $table, 1);
	if (DEBUG_PRINT) print_r($result_open);
	
	if ($result_open['ErrCode']==ERRCODE_FAILED)
	{
		if (DEBUG_PRINT) echo 'OpenTable Error..'.$table." ".$result_open['ErrMsg'].PHP_EOL;
		return false;		
	}
	else
	{
		$salesno = $result_open['SalesNo'];
		$splitno = $result_open['SplitNo'];
		if (DEBUG_PRINT) echo "SalesNo=".$salesno.", SplitNo=".$splitno.PHP_EOL;
		if (DEBUG_PRINT) echo "insertPaidOrder::1..* Order Items".PHP_EOL;
		
		//OrderItems
		$salesref = 0;
		foreach ($itemarray as &$item) {
			$plu = $item->getPLU();
			$quantity = $item->getQuantity();
			$catid = $item->getCatId();
			if (DEBUG_PRINT) echo "inserting PLU>".$plu.", ",$quantity.", ".$catid.PHP_EOL;
			$result_insert = $o->execute_OrderItem_SP($posid, $operatorno, $table, $salesno, $splitno, $plu, $quantity, $catid);
			if (DEBUG_PRINT) print_r($result_insert);
			if ($result_insert['ErrCode']==ERRCODE_FAILED)
			{
				if (DEBUG_PRINT) echo 'OrderItem Error..'.$result_insert['ErrMsg'].PHP_EOL;
			}
			else {
				$salesref = $result_insert['SalesRef'];
				if (DEBUG_PRINT) echo "Insert OrderItem Successfully - ".$salesref.PHP_EOL;
							
				//insert toppings
				$toppings = $item->getPrepItemArray();
				foreach ($toppings as $item) {
					if (DEBUG_PRINT) echo $item->getPLU().', ('.$item->getQuantity().')'.PHP_EOL;
					$result_prepitem = $o->execute_PrepItem_SP($posid, $operatorno, $table, $salesno, $splitno, $item->getPLU(), $item->getQuantity(), $salesref);
					if ($result_prepitem['ErrCode']==ERRCODE_FAILED)
					{
						if (DEBUG_PRINT) echo 'PrepItem Error..'.$result_prepitem['ErrMsg'].PHP_EOL;
					}
				}
				
			}
		}
		
		//OpenItem - Print Name/Contact
		if (DEBUG_PRINT) echo "insertPaidOrder::OpenItem Table".PHP_EOL;
		//name
		$m_cust_name = massageCustomerName($cust_name);
		$m_openprepitem = createCustomerNamePrepItem($branchcode);
		$result_name_prepitem = $o->execute_AddOpenItem_SP($posid, $operatorno, $table, $salesno, $splitno, $m_openprepitem->getPLU(), $m_openprepitem->getQuantity(), $m_cust_name, 0.0);
		if ($result_name_prepitem['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'Name OpenItem Error..'.$table." ".$result_name_prepitem['ErrMsg'].PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo 'Name OpenItem Successful for '.$m_cust_name.PHP_EOL;
		}
		//contact
		$m_cust_contact = massageCustomerName($cust_contact);
		$m_openprepitem_contact = createCustomerNamePrepItem($branchcode);
		$result_name_prepitem_contact = $o->execute_AddOpenItem_SP($posid, $operatorno, $table, $salesno, $splitno, $m_openprepitem_contact->getPLU(), $m_openprepitem_contact->getQuantity(), $m_cust_contact, 0.0);
		if ($result_name_prepitem_contact['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'Contact OpenItem Error..'.$table." ".$result_name_prepitem_contact['ErrMsg'].PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo 'Contact OpenItem Successful for '.$m_cust_contact.PHP_EOL;
		}
		
		//HoldTable
		//exec sp_HDS_HoldTable @POSID='WEB001', @TableNo='PREORDER1', @operatorNo=17, @SalesNo=71986, @SplitNo=0;
		if (DEBUG_PRINT) echo "insertPaidOrder::Hold Table".PHP_EOL;
		$result_hold = $o->execute_HoldTable_SP($posid, $operatorno, $table, $salesno, $splitno);
		if ($result_hold['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'HoldTable Error..'.$result_hold['ErrMsg'].PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "HoldTable Successful for ".$table.PHP_EOL;
		}
		
		//Discount
		if ($discountcode!=NO_CODE_VOUCHER)
		{
			if (DEBUG_PRINT) echo "insertPaidOrder::Discount Code $discountcode for $disc_amount".PHP_EOL;
			$result_disc = $o->execute_Discount_SP($posid, $operatorno, $table, $salesno, 0, $discountcode, $disc_amount);
			if ($result_disc['ErrCode']==ERRCODE_FAILED)
			{
				if (DEBUG_PRINT) echo 'Discount Error..'.$result_disc['ErrMsg'].PHP_EOL;
			}
		}
		
		//Payment
		if (DEBUG_PRINT) echo "insertPaidOrder::Payment".PHP_EOL;
		$result_payment = $o->execute_Payment_SP($posid, $operatorno, $table, $salesno, $splitno, $PAYMENT_TYPE[$branchcode], $amount);
		if (DEBUG_PRINT) print_r($result_payment);
		if ($result_payment['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'Payment Error..'.$result_payment['ErrMsg'].PHP_EOL;
		}
		else{
			if (DEBUG_PRINT) echo 'Payment Message..'.$result_payment['PaymentMsg'].", Amount=".$result_payment['Amount'].PHP_EOL;
			if ($result_payment['PaymentMsg']==PAYMENTMSG_PAID)
			{
				if (DEBUG_PRINT) echo "insertPaidOrder::Close Table".PHP_EOL;
				//Close Table
				$o->close_Table($table);
			}
		}
		
	}
	
	return true;
	
}

/*function insertUnpaidOrder($branchcode, $posid, $operatorno, $table, $itemarray)
{
	return insertUnpaidOrderMain('azure', $posid, $operatorno, $table, $itemarray);
}*/

//Insert Unpaid Order-Book A Table
function insertUnpaidOrderMain($branchcode, $table, $itemarray)
{
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID, $DISCOUNT_CODE;
	$posid = $POSID[$branchcode];
	$operatorno = $OPERATOR_NO[$branchcode];
	$discountcode = $OPERATOR_NO[$branchcode];
	
	//echo 'xxxxxx-'.$operatorno.PHP_EOL;
	
	if (DEBUG_PRINT) echo "insertUnpaidOrder::connect DB".PHP_EOL;
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	
	//Close Table
	if (DEBUG_PRINT) echo "insertUnpaidOrder::Force close on $table.".PHP_EOL;
	$o->close_Table($table);
				
	if (DEBUG_PRINT) echo "insertUnpaidOrder::Open Table".PHP_EOL;
	$result_open = $o->execute_OpenTable_SP($posid, $operatorno, $table, 1);
	if (DEBUG_PRINT) print_r($result_open);
	
	if ($result_open['ErrCode']==ERRCODE_FAILED)
	{
		if (DEBUG_PRINT) echo 'OpenTable Error..'.$table." ".$result_open['ErrMsg'].PHP_EOL;
		return false;		
	}
	else
	{
		if (DEBUG_PRINT) echo "insertUnpaidOrder::1..* Order Items".PHP_EOL;
		$salesno = $result_open['SalesNo'];
		$splitno = $result_open['SplitNo'];
		if (DEBUG_PRINT) echo "SalesNo=".$salesno.", SplitNo=".$splitno.PHP_EOL;
		foreach ($itemarray as &$item) {
			$plu = $item->getPLU();
			$quantity = $item->getQuantity();
			$catid = $item->getCatId();
			if (DEBUG_PRINT) echo $plu.", ",$quantity.", ".$catid.PHP_EOL;
			$result_insert = $o->execute_OrderItem_SP($posid, $operatorno, $table, $salesno, $splitno, $plu, $quantity, $catid);
			if (DEBUG_PRINT) print_r($result_insert);
			if ($result_insert['ErrCode']==ERRCODE_FAILED)
			{
				if (DEBUG_PRINT) echo 'OrderItem Error..'.$result_insert['ErrMsg'].PHP_EOL;
			}
			else
			{
				$salesref = $result_insert['SalesRef'];
				
				//insert toppings
				$toppings = $item->getPrepItemArray();
				foreach ($toppings as $item) {
					if (DEBUG_PRINT) echo $item->getPLU().', ('.$item->getQuantity().')'.PHP_EOL;
					//echo 'Toppings..'.$item.PHP_EOL;
					$result_prepitem = $o->execute_PrepItem_SP($posid, $operatorno, $table, $salesno, $splitno, $item->getPLU(), $item->getQuantity(), $salesref);
					if ($result_prepitem['ErrCode']==ERRCODE_FAILED)
					{
						if (DEBUG_PRINT) echo 'PrepItem Error..'.$result_prepitem['ErrMsg'].PHP_EOL;
					}
				}
			}
		}
		
		if (DEBUG_PRINT) echo "insertUnpaidOrder::Hold Table".PHP_EOL;
		//HoldTable
		$result_hold = $o->execute_HoldTable_SP($posid, $operatorno, $table, $salesno, $splitno);
	
		if (DEBUG_PRINT) print_r($result_hold);
		if ($result_hold['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'HoldTable Error..'.$result_hold['ErrMsg'].PHP_EOL;
		}
		
	}
	
	return true;
}

//Get All the PLU
function getPLUList($branchcode, $scope = 1) //1 - Active, 2 - All, 0 - Inactive
{
	global $SQLSERVERS_NAME, $SQLSERVER_DB;
	if (DEBUG_PRINT) echo "insertUnpaidOrder::connect DB".PHP_EOL;
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	
	$result_insert = $o->execute_PLUList_SP($scope);
	if (DEBUG_PRINT) print_r($result_insert);
	return $result_insert;
}

//create Customer Name PrepItem
function createCustomerNamePrepItem($branchcode)
{
	//echo 'dsds'.substr($name, 0, MAX_PRINT_NAME).PHP_EOL;
	global $OPEN_PREPITEM;
	return new PrepItem($OPEN_PREPITEM[$branchcode], 1);
}

function massageCustomerName($name)
{
	return substr($name, 0, MAX_PRINT_NAME);
}

function insertTestFood($branchcode, $saleno)
{
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID;
	$posid = $POSID[$branchcode];
	$operatorno = $OPERATOR_NO[$branchcode];
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	$result_insert = $o->execute_OrderItem_SP($posid, $operatorno, SQLSERVER_ZINGMOBILE_TABLE, $saleno, 0, '130000000000030', 1, 1);
	print_r($result_insert);
}

function insertOpenItem($branchcode, $salesno, $cust_name)
{
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID;
	$posid = $POSID[$branchcode];
	$operatorno = $OPERATOR_NO[$branchcode];
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	$m_cust_name = massageCustomerName($cust_name);
	$m_openprepitem = createCustomerNamePrepItem($branchcode);

	$result_name_prepitem = $o->execute_AddOpenItem_SP($posid, $operatorno, SQLSERVER_ZINGMOBILE_TABLE, $salesno, 0, $m_openprepitem->getPLU(), $m_openprepitem->getQuantity(), $m_cust_name, 0.0);
	if ($result_name_prepitem['ErrCode']==ERRCODE_FAILED)
	{
		if (DEBUG_PRINT) echo 'Name OpenItem Error..'.SQLSERVER_ZINGMOBILE_TABLE." ".$result_name_prepitem['ErrMsg'].PHP_EOL;
	}
	else {
		if (DEBUG_PRINT) echo 'OpenItem Successful for '.$m_cust_name.PHP_EOL;
	}
}


function insertDiscount($branchcode, $salesno, $disc_amount)
{ 
	global $SQLSERVERS_NAME, $SQLSERVER_DB, $OPERATOR_NO, $PAYMENT_TYPE, $POSID, $DISCOUNT_CODE;
	$posid = $POSID[$branchcode];
	$operatorno = $OPERATOR_NO[$branchcode];
	$discountcode = $OPERATOR_NO[$branchcode];
	$o = new SqlServer_Utils($SQLSERVERS_NAME[$branchcode], SQLSERVER_USER, SQLSERVER_PASSWORD, $SQLSERVER_DB[$branchcode]); 
	if (!$o->isConnected())
	{
		if (DEBUG_PRINT) echo "Database not available.".PHP_EOL;
		return false;
	}
	if ($discountcode!=NO_CODE_VOUCHER)
	{
		if (DEBUG_PRINT) echo "insertPaidOrder::Discount on SalesRef = $salesref".PHP_EOL;
		$result_disc = $o->execute_Discount_SP($posid, $operatorno, SQLSERVER_ZINGMOBILE_TABLE, $salesno, 0, $discountcode, $disc_amount);
		if ($result_disc['ErrCode']==ERRCODE_FAILED)
		{
			if (DEBUG_PRINT) echo 'Discount Error..'.$result_disc['ErrMsg'].PHP_EOL;
		}
	}
}
?>
