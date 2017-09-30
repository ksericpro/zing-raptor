<?php
/**
 * Init SQL Server
 * Author : Eric See
 * 2/11/2016
 */
$SQLSERVER_DB = array(
"sp"=>"raptor",  
"ecp"=>"raptor", 
"pg"=>"raptor",
"pr"=>"raptor",
"sl"=>"raptor",
"azure"=>"siglap",
"dev"=>"GeorgesSP" 
); 

$SQLSERVERS_NAME = array(
"sp"=>"raptor_sp", 
"ecp"=>"raptor_ecp", 
"pg"=>"raptor_pg",
"pr"=>"raptor_pr",
"sl"=>"raptor_sl",
"azure"=>"raptor",
"dev"=>"raptor_dev"
);


/*1.  georges.hrsdns1.com  - siglap

2.  georgesbeachclub.hrsdns1.com - ecp

3.  georgesbythebay.hrsdns1.com - punggol

4.  georgesmember.hrsdns1.com

5.  georgespr.hrsdns1.com - pasir ris

6.  georgesseletar.hrsdns1.com - seletar

7. others
*/


//georges.hrsdns1.com - siglap
//hrs/R8pt0r5792
//define('SQLSERVER','raptor');
//define('SQLSERVER_USER','sa');
//define('SQLSEREVER_PASSWORD', 'ultraman');
define('SQLSERVER_USER','hrs');
define('SQLSERVER_PASSWORD', 'R8pt0r5792');
//define('SQLSERVER_DB','siglap');
define('ERRCODE_FAILED','00');
define('ERRCODE_PASSED','01');
define('SQLSERVER_ZINGMOBILE_TABLE','PREORDER'); //For Paid Order
//define('PAYMENT_TYPE', 40);//99
$PAYMENT_TYPE = array(
"sp"=>99, 
"ecp"=>99, 
"pg"=>99,
"pr"=>99,
"sl"=>99,
"azure"=>40,
"dev"=>99
);
define('PAYMENTMSG_PAID','Paid');
//define('DISCOUNT_CODE_BAR_CREDIT', 405);
//define('DISCOUNT_CODE_VOUCHER', 408);
$DISCOUNT_CODE = array(
"sp"=>"396", 
"ecp"=>"396", 
"pg"=>"396",
"pr"=>"396",
"sl"=>"396",
"azure"=>"396",
"dev"=>"396"
);

define('NO_CODE_VOUCHER', -1);
define('YES_CODE_VOUCHER', 99);
define('DEBUG_PRINT', false);
//define('POSID', 'POS001');
define('MAX_PRINT_NAME', 15);
$POSID = array(
"sp"=>"WEB001", 
"ecp"=>"WEB001", 
"pg"=>"WEB001",
"pr"=>"WEB001",
"sl"=>"WEB001",
"azure"=>"POS001",
"dev"=>"WEB001"
);
//define('OPERATOR_NO', 1);
$OPERATOR_NO = array(
"sp"=>17, 
"ecp"=>1, 
"pg"=>1,
"pr"=>1,
"sl"=>1,
"azure"=>1,
"dev"=>17
); 

$OPEN_PREPITEM = array(
"sp"=>'139999999999999', 
"ecp"=>'000000000000459', 
"pg"=>'000000000000459',
"pr"=>'000000000000459',
"sl"=>'000000000000459',
"azure"=>'000000000000459',
"dev"=>'139999999999999'
);
?>
