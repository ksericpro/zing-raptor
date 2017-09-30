<?php
/**
 * SQL Server Class to exxecute SPs and CRUD
 * Author : Eric See
 * 2/11/2016
 */
 
require_once("sqlserver_init.php");
 
class SqlServer_Utils { 
    private $db;
	private $user;
	private $password;
	private $database;
	private $connection;
	private $connected = false;
	
	//constructor
	function __construct($a1,$a2,$a3,$a4) 
    { 
		if (DEBUG_PRINT)
        echo('__construct with 4 params called: '.$a1.','.$a2.','.$a3.','.$a4.PHP_EOL); 
		$this->db = $a1;
		$this->user = $a2;
		$this->password = $a3;
		$this->database = $a4;
		
		$this->connect();
    }
	
	function __destruct() {
       if (DEBUG_PRINT) print "Destroying Conection".PHP_EOL;
		$this->disconnect();
    }
	
	//Disconnect
	function disconnect()
	{
		// Close the link to MSSQL
		if($this->connection)
			mssql_close($this->connection);
	}
    
	//connect db 
	function connect()
	{
		if (DEBUG_PRINT) echo "Connecting..".$this->db." ".$this->database." ".$this->user."/".$this->password.PHP_EOL;
               
		$this->connection = mssql_connect($this->db, $this->user, $this->password);
		if($this->connection)
		{
			if (DEBUG_PRINT) echo "Database Connected.".PHP_EOL;
		}
		else
		{
			if (DEBUG_PRINT) echo "Database Connect Failed.".PHP_EOL; 
			//echo mssql_error();
			return;
		}

		if (!mssql_select_db($this->database, $this->connection)) {
			if (DEBUG_PRINT) echo "Unable to select database!".PHP_EOL;
			return;
		}
		
		$this->connected = true;
	}
	
	//Connected status
	function isConnected()
	{
		return $this->connected;
	}
    
	//Execute Open Table SP
    function execute_OpenTable_SP($posid, $operatorno, $table, $cover) { 
		if ($this->connected) {
			if (DEBUG_PRINT) echo "execute_OpenTable_SP()".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		if (DEBUG_PRINT) echo "Executing sp_HDS_OpenTable".PHP_EOL;
		$query = mssql_init("sp_HDS_OpenTable", $this->connection);

		//input parameters
		$p1 = $posid;
		$p2 = $operatorno;
		$p3 = $table;
		$p4 = 'eric';
		$p5 = '';
		$p6 = '';
		$p7 = '';
		$p8 = '';
		$p9 = $cover;

		//Bind Variables
		mssql_bind($query, "@POSID", $p1, SQLVARCHAR);
		mssql_bind($query, "@OperatorNo", $p2, SQLINT2); 
		mssql_bind($query, "@TableNo", $p3, SQLVARCHAR);
		mssql_bind($query, "@CustFirstname", $p4, SQLVARCHAR);
		mssql_bind($query, "@CustLastname", $p5, SQLVARCHAR);
		mssql_bind($query, "@CustAddress", $p6, SQLVARCHAR);
		mssql_bind($query, "@CustRemark", $p7, SQLVARCHAR);
		mssql_bind($query, "@OrderRemark", $p8, SQLVARCHAR);
		mssql_bind($query, "@Cover", $p9, SQLINT2);

		if (DEBUG_PRINT) echo "@POSID=$p1, @OperatorNo=$p2, @TableNo=$p3".PHP_EOL;
		$result = mssql_execute($query);
		$numProds = mssql_num_rows($result);
		if (DEBUG_PRINT) echo $numProds . " result" . ($numProds == 1 ? "" : "s") . " Found.".PHP_EOL;

		//output parameters
		$e = array(); 					
		while($row = mssql_fetch_row($result))
		{
			$e['ErrCode'] = $row[0];
			$e['ErrMsg'] = $row[1];
			$e['SalesNo'] = $row[2];
			$e['SplitNo'] = $row[3];
		}

		mssql_free_result($result);	
		
		return $e;
    }
	
	//Insert OrderItem
	function execute_OrderItem_SP($posid, $operatorno, $table, $salesno, $splitno, $plu, $quantity, $catid)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected - $this->db".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		if (DEBUG_PRINT) echo "Executing sp_HDS_OrderItem".PHP_EOL;
		 $sql = "exec sp_HDS_OrderItem @POSID='$posid', @OperatorNo=$operatorno,
@TableNo='$table', @SalesNo=$salesno,
@SplitNo=$splitno, @PLUNo='$plu', @Qty=$quantity,
@Itemremark='', @CtgryID=$catid";

		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		//print_r($row).PHP_EOL;
		$e = array();			
		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];
		$e['SalesRef'] = $row[2];
			
		mssql_free_result($result);
		//print_r($e);
		
		return $e;
	}
	
	//Execute Holdtable
	function execute_HoldTable_SP($posid, $operatorno, $table, $salesno, $splitno)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_HoldTable".PHP_EOL;
		$sql = "exec sp_HDS_HoldTable @POSID='$posid', @TableNo='$table', @operatorNo=$operatorno, @SalesNo=$salesno, @SplitNo=$splitno";
	
		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		//output parameters
		$e = array();	

		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];

		mssql_free_result($result);	
		
		return $e;
		
	}
	
	//Execute Payment/Will automatic close table
	function execute_Payment_SP($posid, $operatorno, $table, $salesno, $splitno, $paymenttype, $paidamt)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_Payment".PHP_EOL;
		$sql="exec sp_HDS_Payment @POSID='$posid', @OperatorNo=$operatorno, @TableNo='$table', @SalesNo=$salesno, @SplitNo=$splitno,
@PaymentType=$paymenttype, @PaidAmount=$paidamt, @CustomerID=''";
		
		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		//output parameters
		$e = array();	

		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];
		$e['PaymentMsg'] = $row[2];
		$e['Amount'] = $row[3];

		mssql_free_result($result);	
		
		return $e;
	}
	
	//Execute Discount
	function execute_Discount_SP($posid, $operatorno, $table, $salesno, $splitno, $disccode, $disc_amount, $salesref = 1)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_Disc".PHP_EOL;
		$sql = "exec sp_HDS_Disc @POSID='$posid', @operatorNo=$operatorno, @TableNo='$table', @SalesNo=$salesno, @SplitNo=$splitno,  @SalesRef=$salesref, @DiscCode=$disccode, 
@DiscRemarks='', @DiscAmountOpen=$disc_amount";
		
		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		
		//output parameters
		$e = array();	
		
		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];

		mssql_free_result($result);	
		
		return $e;		
	}
	
	//Execute Prep Item
	function execute_PrepItem_SP($posid, $operatorno, $table, $salesno, $splitno, $pluno, $qty, $salesref)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_PrepItem".PHP_EOL;
		
		$sql = "exec sp_HDS_PrepItem @POSID='$posid', @OperatorNo=$operatorno,
@TableNo='$table', @SalesNo=$salesno,
@SplitNo=$splitno, @PLUNo='$pluno', @Qty=$qty, @PLUSalesRef=$salesref";
		
		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		//output parameters
		$e = array();	
		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];

		mssql_free_result($result);	
		
		return $e;	
	}
	
	//Execute Add Open Item
	//exec sp_HDS_OpenItem  @POSID='WEB001', @OperatorNo=17,
	//@TableNo='PREORDER1', @SalesNo=71986,
	//@SplitNo=0, @PLUNo='139999999999999', @Qty=1,
	//@PLUName='Eric Teo', @Amount=0.00;
	function execute_AddOpenItem_SP($posid, $operatorno, $table, $salesno, $splitno, $pluno, $qty, $pluname, $amount = 0.00)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_OpenItem".PHP_EOL;
	
		$sql = "exec sp_HDS_OpenItem  @POSID='$posid', @OperatorNo=$operatorno,
@TableNo='$table', @SalesNo=$salesno,
@SplitNo=$splitno, @PLUNo='$pluno', @Qty=$qty,
@PLUName='$pluname', @Amount=$amount";

		if (DEBUG_PRINT) echo "Executing $sql".PHP_EOL;
		$result = mssql_query($sql);
		$row = mssql_fetch_array($result);
		
		//output parameters
		$e = array();	

		$e['ErrCode'] = $row[0];
		$e['ErrMsg'] = $row[1];

		mssql_free_result($result);	
		
		return $e;
		
	}
	
	//Get All PLU
	function execute_PLUList_SP($scope, $language = 0)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statement
		if (DEBUG_PRINT) echo "Executing sp_HDS_PLUList".PHP_EOL;
		$query = mssql_init("sp_HDS_PLUList", $this->connection);
		
		//input parameters
		$p1 = $scope;
		$p2 = $language;
		
		//Bind Variables
		mssql_bind($query, "@PLUActive", $p1, SQLINT2);
		mssql_bind($query, "@Language", $p2, SQLINT2); 
		
		$result = mssql_execute($query);
		$numProds = mssql_num_rows($result);
		if (DEBUG_PRINT) echo $numProds . " result" . ($numProds == 1 ? "" : "s") . " Found.".PHP_EOL;

		//output parameters
		$e = array();
		$ct =0;
		while($row = mssql_fetch_row($result))
		{
			//echo $ct.PHP_EOL;
			$e[$ct]['PLUNumber'] = $row[0];
			$e[$ct]['PLUName'] = $row[1];
			$e[$ct]['Price'] = $row[2];
			$ct++;
		}

		mssql_free_result($result);	
		
		return $e;
		
	}
	
	//close table
	function close_Table($table)
	{
		if ($this->connected) {
			if (DEBUG_PRINT) echo "Database connected.".PHP_EOL;
		}
		else {
			if (DEBUG_PRINT) echo "No Database connection.".PHP_EOL;
			return;
		}
		
		// Create a new statementto delete HeldTables	
		$query="delete from HeldTables where TableNo = '$table'";
		
		if (DEBUG_PRINT) echo $query.PHP_EOL;
		
		$result = mssql_query($query, $this->connection);
		
		if (!$result) {
			if (DEBUG_PRINT) print("SQL statement failed with error:\n");
			print("   ".mssql_get_last_message()."\n");
		} else {
			$number_of_rows = mssql_rows_affected($this->connection);
			if (DEBUG_PRINT) print("$number_of_rows HeldTables rows deleted.\n");
		}
		
		//echo "Close Table-Start ".PHP_EOL;
		// Create a new statementto delete HeldItems	
		/*$query="delete from HeldItems where TableNo = '$table'";
		
		if (DEBUG_PRINT) echo $query.PHP_EOL;
		
		$result = mssql_query($query, $this->connection);
		
		if (!$result) {
			if (DEBUG_PRINT) {
				print("SQL statement failed with error:\n");
				print("   ".mssql_get_last_message()."\n");
			}
		} else {
			$number_of_rows = mssql_rows_affected($this->connection);
			if (DEBUG_PRINT) print("$number_of_rows HeldItems rows deleted.\n");
		}*/
		
		//echo "Close Table-End ".PHP_EOL;
	}	
}

//Item Ordered
class ItemOrdered {
	private $plu;
	private $quantity;
	private $catid;
	private $prepitem_arr = array();

	//constructor
	function __construct($a1,$a2,$a3) 
    { 
        if (DEBUG_PRINT) echo('__construct with 3 params called: '.$a1.','.$a2.','.$a3.PHP_EOL); 
		$this->plu = $a1;
		$this->quantity = $a2;
		$this->catid = $a3;
    }
	
	public function setPLU($plu){
		$this->plu=$plu;
	}

	public function setQuantity($quantity){
		$this->quantity=$quantity;
	}
	
	public function setCatId($catid){
		$this->catid=$catid;
	}

	public function getPLU(){
		return $this->plu;
	}

	public function getQuantity(){
		return $this->quantity;
	}
	
	public function getCatId(){
		return $this->catid;
	}
	
	public function addPrepItem($plu)
	{
		if (!isset($this->prepitem_arr)) $this->prepitem_arr = array();
		array_push($this->prepitem_arr, $plu);
	}
	
	public function clearPrepItem()
	{
		unset($this->prepitem_arr);
	}
	
	public function printAllPrepItem()
	{
		echo "Printing All Prep Items for $this->plu".PHP_EOL;
		if (isset($this->prepitem_arr))
		{
			foreach ($this->prepitem_arr as $item) {
				echo $item->getPLU().', '.$item->getQuantity().PHP_EOL;
			}
		}
	}
	
	public function getPrepItemArray()
	{
		return $this->prepitem_arr;
	}
}

class PrepItem {
	private $plu;
	private $quantity;

	//constructor
	function __construct($a1,$a2) 
    { 
        if (DEBUG_PRINT) echo('__construct with 2 params called: '.$a1.','.$a2.PHP_EOL); 
		$this->plu = $a1;
		$this->quantity = $a2;
    }
	
	public function setPLU($plu){
		$this->plu=$plu;
	}

	public function setQuantity($quantity){
		$this->quantity=$quantity;
	}

	public function getPLU(){
		return $this->plu;
	}

	public function getQuantity(){
		return $this->quantity;
	}
}

?>
