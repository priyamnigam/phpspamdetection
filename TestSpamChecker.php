<?php

try {
  $dbh = new PDO('mysql:host=localhost;dbname=spam','root','dascene123');
}
catch(PDOException $ex) {
  echo 'Connection Error : '.$ex->getMessage();
  exit();
}

$sf = new spamchecker();
$sf->dbh = $dbh;
//$sf->resetFilter();
//$sf->trainFilter('this is some spam <html> 4299u09u89(*&$%(*&$%     sdf');
//$sf->trainFilter('this is some spam');
//echo $sf->checkSpam('this is some spam');


//echo $sf->checkSpam('this is not spam because it is by me');
?>