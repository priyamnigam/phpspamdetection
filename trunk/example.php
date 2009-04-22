<form action="" method="POST">
<textarea name="check" rows="10" cols="40"><?php echo $_REQUEST['check'] ?></textarea> - Check Spam Rating<br>
<textarea name="spam" rows="10" cols="40"><?php echo $_REQUEST['spam'] ?></textarea> - Train Spam<br>
<textarea name="ham" rows="10" cols="40"><?php echo $_REQUEST['ham'] ?></textarea> - Train Ham<br>
<input type="submit" /><input type="reset" />
</form>

<form action="" method="POST">
<input type="hidden" name="reset" />
<input type="submit" value="Reset Filter"/>
</form>

<form action="" method="POST">
<input type="hidden" name="findham" />
<input type="submit" value="Find Ham"/>
</form>

<form action="" method="POST">
<input type="hidden" name="trainonspam" />
<input type="submit" value="Train On Spam"/>
</form>

<form action="" method="POST">
<input type="hidden" name="trainonham" />
<input type="submit" value="Train On Ham"/>
</form>

<form action="" method="POST">
<input type="hidden" name="test" />
<input type="submit" value="Test"/>
</form>



<?php
set_time_limit(0);
/*
  This is just an example of how to use the spamchecker class. It demonstrates how
  to add both spam and ham examples to the class, and check new values for the spamicity.
*/


$link = mysql_connect('localhost', 'root', '') or die('Could not connect: ' . mysql_error());
mysql_select_db('spam') or die('Could not select database');

include_once('spamchecker.php');


$spamchecker = new spamchecker();

if(isset($_REQUEST['check']) && strlen($_REQUEST['check']) != 0) {
  echo "Spam Rating = ".$spamchecker->checkSpam($_REQUEST['check'])."<br />";
}

if(isset($_REQUEST['spam']) && strlen($_REQUEST['spam']) != 0) {
  $spamchecker->train($_REQUEST['spam'],true);
  echo "Spam Added = ".$spamchecker->checkSpam($_REQUEST['spam'])."<br />";
}

if(isset($_REQUEST['ham']) && strlen($_REQUEST['ham']) != 0) {
  $spamchecker->train($_REQUEST['ham'],false);
  echo "Ham Added = ".$spamchecker->checkSpam($_REQUEST['ham'])."<br />";
}

if(isset($_REQUEST['reset'])) {
  $spamchecker->resetSpam();
  echo "Filter Reset<br />";
}

if(isset($_REQUEST['findham'])) {
  $result = mysql_query("SELECT content FROM foundspam;");
  echo "Found Spam<br />";
  echo "<dl>";
  while ($line = mysql_fetch_array($result)) {
	  $spamrating = $spamchecker->checkSpam($line[0]);
	  if($spamrating < 0.95) {
	    echo "<li>".$spamrating." - ".$line[0]."</li>";
	  }
  }
  echo "</dl>";
  //echo "Filter Reset<br />";
}

if(isset($_REQUEST['trainonspam'])) {
  $result = mysql_query("SELECT name,content FROM foundspam;");
  
  echo "Trained On Spam<br />";
  while ($line = mysql_fetch_array($result)) {
    $spamchecker->train($line[0].' '.$line[1],true);
  }  
}

if(isset($_REQUEST['trainonham'])) {
  $result = mysql_query("SELECT name,comment FROM comment;");
  
  echo "Trained On Ham<br />";
  while ($line = mysql_fetch_array($result)) {
    $spamchecker->train($line[0].' '.$line[1],false);
  }  
}

if(isset($_REQUEST['test'])) {
  $result = mysql_query("SELECT name,comment FROM testham;");
  
  $foundspam = 0;
  $totalspam = 0;
  echo "<h1>Checking Ham</h1><br />";
  while ($line = mysql_fetch_array($result)) {
	$spamrating = $spamchecker->checkSpam($line[0].' '.$line[1]);
	echo "<dl>";
    if($spamrating > 0.97) {
	    echo "<li>".$spamrating." - ".substr(strip_tags($line[0].' '.$line[1]),0,60)."</li>";
		$foundspam++;
	}
	$totalspam++;
	echo "</dl>";
  }
  echo "<br>".$totalspam.' '.$foundspam;
  echo "<br><b>Percentage</b> - ".($foundspam/$totalspam)*100;
  
  $result = mysql_query("SELECT name, content FROM testspam;");
  
  $foundspam = 0;
  $totalspam = 0;
  echo "<h1>Checking Spam</h1><br />";
  while ($line = mysql_fetch_array($result)) {
	$spamrating = $spamchecker->checkSpam($line[0].' '.$line[1]);
	echo "<dl>";
    if($spamrating < 0.97) {
	    echo "<li>".$spamrating." - ".substr(strip_tags($line[0].' '.$line[1]),0,60)."</li>";
		$foundspam++;
	}
	$totalspam++;
	echo "</dl>";
  }  
  echo "<br>".$totalspam.' '.$foundspam;
  echo "<br><b>Percentage</b> - ".($foundspam/$totalspam)*100;
  
}




?>