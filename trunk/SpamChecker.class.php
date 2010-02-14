<?php
class SpamChecker {
  public $dbh = null;

  function trainFilter($text,$isspam=true) {
    if(is_null($this->dbh)) {
      throw new Exception("Database connection is null");
    }
    $query = $this->dbh->prepare
      ('select totalsid,totalspam,totalham FROM totals;');
    $query->execute();
    $result = $query->fetchAll();
    $totalsid = $result[0]['totalsid'];
    $totalspam = $result[0]['totalspam'];
    $totalham = $result[0]['totalham'];

    if($isspam){
      $totalspam++;
      $query = $this->dbh->prepare
        ('update `totals` set totalspam=? where totalsid=1 limit 1;');
      $insert = $query->execute(array($totalspam));
    }
    else {
      $totalham++;
      $query = $this->dbh->prepare
        ('update `totals` set totalham=? where totalsid=1 limit 1;');
      $insert = $query->execute(array($totalham));
    }
    
    $text = preg_replace('/\W+/',' ',$text);
    $text = preg_replace('/\s\s+/',' ',$text);
    $text = strtolower($text);
    $temparray = explode(' ',$text);
    foreach($temparray as $token) {
      $query = $this->dbh->prepare
        ('select `spamid` from spam where token=? limit 0,1;');
      $query->execute(array($token));
      $result = $query->fetchAll();
      if(count($result) == 0) {
        if($isspam) {
          $query = $this->dbh->prepare
            ("insert into `spam` (`token`,`spamcount`,`hamcount`,
              `spamrating`) values (?,'1','0','1');");
          $insert = $query->execute(array($token));
        }
        else {
        }
      }
      else {
      }
    }
  }

  function checkSpam($text) {
    if(is_null($this->dbh)) {
      throw new Exception("Database connection is null");
    }
  }

  function resetFilter() {
    if(is_null($this->dbh)) {
      throw new Exception("Database connection is null");
    }
    $trun = $this->dbh->prepare('TRUNCATE TABLE `spam`;');
    $trun->execute();
  }
}

try {
  $dbh = new PDO('mysql:host=localhost;dbname=spam','root','dascene123');
}
catch(PDOException $ex) {
  echo 'Connection Error : '.$ex->getMessage();
  exit();
}

$sf = new spamchecker();
$sf->dbh = $dbh;
$sf->resetFilter();
$sf->trainFilter('this is some spam <html> 4299u09u89(*&$%(*&$%     sdf');
//$sf->trainFilter('this is some spam',false);
?>