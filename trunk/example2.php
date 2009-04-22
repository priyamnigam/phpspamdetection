<form action="" method="POST">
<textarea name="check" rows="10" cols="40"><?php echo $_REQUEST['check'] ?></textarea> - Check Ratings<br>
<br>
<input type="text" name="category" value="<?php echo $_REQUEST['category'] ?>" /> Category<br />
<textarea name="text" rows="10" cols="40"><?php echo $_REQUEST['text'] ?></textarea> - Text<br>
<input type="submit" /><input type="reset" />
</form>

<form action="" method="POST">
<input type="hidden" name="reset" />
<input type="submit" value="Reset Store"/>
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
  **NB** This is still in active development and is not ready for use.
*/


$link = mysql_connect('localhost', 'root', '') or die('Could not connect: ' . mysql_error());
mysql_select_db('spam') or die('Could not select database');

include_once('vectorspace.php');
$vectorspace = new vectorspace();

if(isset($_REQUEST['check']) && strlen($_REQUEST['check']) != 0) {
  // should display all of the scores
  echo $vectorspace->checktype($_REQUEST['check'])."<br>";
}


if(isset($_REQUEST['text']) && strlen($_REQUEST['text']) != 0 && isset($_REQUEST['category']) && strlen($_REQUEST['category']) != 0) {
  $vectorspace->addtostore($_REQUEST['text'],$_REQUEST['category']);
  echo "Added to Store<br />";
}

if(isset($_REQUEST['reset'])) {
  $vectorspace->cleanstore();
  echo "Store Cleaned<br />";
}


if(isset($_REQUEST['trainonspam'])) {
  $result = mysql_query("SELECT name,content FROM foundspam;");
  
  echo "Trained On Spam<br />";
  while ($line = mysql_fetch_array($result)) {
	$vectorspace->addtostore($line[0].' '.$line[1],'spam');
  }
}

if(isset($_REQUEST['trainonham'])) {
  $result = mysql_query("SELECT name,comment FROM comment;");
  
  echo "Trained On Spam<br />";
  while ($line = mysql_fetch_array($result)) {
	$vectorspace->addtostore($line[0].' '.$line[1],'ham');
  }
}

if(isset($_REQUEST['test'])) {
	/*
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
  */
}


?>