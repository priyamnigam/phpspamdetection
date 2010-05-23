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

    $gettokenquery = $this->dbh->prepare
      ('select `spamid`,`spamcount`,`hamcount` from spam 
        where token=? limit 0,1;');

    foreach($temparray as $token) {
      $gettokenquery->execute(array($token));
      $result = $gettokenquery->fetchAll();

      if(count($result) == 0) {
        if($isspam) {
          $query = $this->dbh->prepare
            ("insert into `spam` (`token`,`spamcount`,`hamcount`,
              `spamrating`) values (?,'1','0','1');");
          $insert = $query->execute(array($token));
        }
        else {
          $query = $this->dbh->prepare
            ("insert into `spam` (`token`,`spamcount`,`hamcount`,
              `spamrating`) values (?,'0','1','0');");
          $insert = $query->execute(array($token));
        }
      }
      else { // Already exists in the database
        $spamcount = $result[0]['spamcount'];
        $hamcount = $result[0]['hamcount'];

        if($isspam){
          $spamcount++;
        }
        else {
          $hamcount++;
        }

        $hamprob = 0;
        $spamprob = 0;
        if($totalham != 0){
          $hamprob = $hamcount/$totalham;
        }
        if($totalspam != 0) {
          $spamprob = $spamcount/$totalspam;
        }
        if($hamprob+$spamprob != 0) {
          $spamrating = $spamprob/($hamprob+$spamprob);
        }
        else {
          $spamrating = 0;
        }

        $query = $this->dbh->prepare
          ("update `spam` set `spamcount`=?, `hamcount`=?,
            `spamrating`=? where token=? limit 1;");
        $query->execute(array($spamcount,$hamcount,
                              $spamrating,$token));
      }
    }
  }

  function checkSpam($text) {
    if(is_null($this->dbh)) {
      throw new Exception("Database connection is null");
    }

    $text = preg_replace('/\W+/',' ',$text);
    $text = preg_replace('/\s\s+/',' ',$text);
    $text = strtolower($text);
    $temparray = explode(' ',$text);

    $gettokenquery = $this->dbh->prepare
      ('select `token`,`spamrating` from spam where token=? limit 0,1;');
    $spamratings = array();
    foreach($temparray as $token) {
      $gettokenquery->execute(array($token));
      $result = $gettokenquery->fetchAll();
      $spamrating = $result[0]['spamrating'];
      if($spamrating == ''){
        $spamrating = 0.4;
      }
      $spamratings[] = $spamrating;
    }

    $a = null;
    $b = null;

    foreach($spamratings as $rating) {
      $rating = max($rating,0.01);
      $a = is_null($a) ? (float)$rating : $a * $rating;
      $b = is_null($b) ? 1-(float)$rating : $b * (1-(float)$rating);
    }
    $spam = (float)0;
    if((float)((float)$a+(float)$b) != 0) {
      $spam = (float)$a/(float)((float)$a+(float)$b);
    }
    return $spam;
  }

  function resetFilter() {
    if(is_null($this->dbh)) {
      throw new Exception("Database connection is null");
    }
    $trun = $this->dbh->prepare('TRUNCATE TABLE `spam`;');
    $trun->execute();
    $trun = $this->dbh->prepare
              ('update totals set totalspam=0, totalham=0 limit 1;');
    $trun->execute();
  }
}
?>