<?php
include_once("sqlserver_init.php");
$branch = "dev";  
//$connection = mssql_connect($SQLSERVERS_NAME['azure'],SQLSERVER_USER,SQLSERVER_PASSWORD);
$connection = mssql_connect($SQLSERVERS_NAME[$branch],SQLSERVER_USER,SQLSERVER_PASSWORD);
if($connection)
{
  echo "Database  Connected.".PHP_EOL;
}
else
{
  echo "Database Connect Failed.".PHP_EOL; 
  echo mssql_error();
}

if (!mssql_select_db($SQLSERVER_DB[$branch], $connection)) {
  die('Unable to select database!');
}

$result = mssql_query('SELECT TOP 1 * FROM HeldTables');

while ($row = mssql_fetch_array($result)) {
 // var_dump($row);
 print_r($row);
}

mssql_free_result($result);

return;
//Insert Order Item

$sql = "exec sp_HDS_OrderItem @POSID='WEB001', @OperatorNo=17,
@TableNo='PREORDER1', @SalesNo=71995,
@SplitNo=0, @PLUNo='130000000000030', @Qty=1,
@Itemremark='', @CtgryID=1";

echo "executing $sql".PHP_EOL;
$result = mssql_query($sql);
$row = mssql_fetch_array($result);
print_r($row).PHP_EOL;
$e = array();				
$e['ErrCode'] = $row[0];
$e['ErrMsg'] = $row[1];
$e['SalesRef'] = $row[2];

mssql_free_result($result);
print_r($e);

//Insert Open Item
$sql = "exec sp_HDS_OpenItem  @POSID='WEB001', @OperatorNo=17,
@TableNo='PREORDER1', @SalesNo=71995,
@SplitNo=0, @PLUNo='139999999999999', @Qty=1,
@PLUName='Eric Teo', @Amount=0.00;";
echo "executing $sql".PHP_EOL;
$result = mssql_query($sql);
$row = mssql_fetch_array($result);
print_r($row).PHP_EOL;
$e = array();				
$e['ErrCode'] = $row[0];
$e['ErrMsg'] = $row[1];
//$e['SalesRef'] = $row[2];

mssql_free_result($result);
print_r($e);
return;


?>
