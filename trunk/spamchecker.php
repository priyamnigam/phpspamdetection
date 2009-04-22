<?php
/*

  © Copyright 2007 Ben Boyter (http://www.boyter.org)

  This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/


/**
 * spamchecker
 * Updated 5 April 2009 
 * Updated to fix a variety of small niggling bugs which caused incorrect
 * classification of spam.
 */
class spamchecker {

  /**
   * Trains the spam checker on the given text. It allows you to train on either spam or ham.
   * @param text the test to train the filter on
   * @param boolen value, true if the text is spam, false if it is ham
   */
  function train($text,$spam) {
  
    $result = mysql_query('SELECT totalsid, totalspam, totalham FROM totals');
      
    while ($line = mysql_fetch_array($result)) {
      $totalsid  = $line[0];
      $totalspam = $line[1];
      $totalham  = $line[2];
    }
      
    if($spam) {
      $totalspam++;
      mysql_query("UPDATE `totals` SET `totalspam` = '".$totalspam."' WHERE `totalsid` =1 LIMIT 1");
    }
    else {
      $totalham++;
      mysql_query("UPDATE `totals` SET `totalham` = '".$totalham."' WHERE `totalsid` =1 LIMIT 1");
    }
      
    $text = preg_replace('/\W+/',' ',$text);
    $temparray = explode(' ',strtolower($text));
      
    foreach ($temparray as $token) {
      $token = trim($token);
      $result = mysql_query('SELECT spamid FROM spam WHERE token="'.$token.'"');
        
      while ($line = mysql_fetch_array($result)) {
        $spamid = $line[0];
      }
        
      if(mysql_num_rows($result) == 1) {
        if($spam) {
          $result2 = mysql_query('SELECT token,spamcount,hamcount FROM spam WHERE spamid='.$spamid);
          while ($line = mysql_fetch_array($result2)) {
            $token = $line[0];
            $spamcount = $line[1];
            $hamcount = $line[2];
              
            $spamcount++;
            $spamrating = 0.4;
              
            // Work out spam rating
            if($totalham != 0 && $totalspam !=0) {
              $hamprob = $hamcount/$totalham;
              $spamprob = $spamcount/$totalspam;
		  
              $spamrating = $spamprob/($hamprob+$spamprob);

              if($hamcount == 0){
                $spamrating = 1;
              }
                
            }
			if($spamcount != 0 && $hamcount == 0) {
				$spamrating = 1;
			}
            mysql_query("UPDATE `spam` SET `spamcount` = '".$spamcount."', `spamrating`='".$spamrating."' WHERE `spamid` =".$spamid." LIMIT 1");
          }
        }
        else {
          $result2 = mysql_query('SELECT token,spamcount,hamcount FROM spam WHERE spamid='.$spamid.' LIMIT 0,1');
          while ($line = mysql_fetch_array($result2)) {
            $token = $line[0];
            $spamcount = $line[1];
            $hamcount = $line[2];
              
            $hamcount++;
              
            //work out spam rating
            if($totalham != 0 && $totalspam !=0) {
              $hamprob = $hamcount/$totalham;
              $spamprob = $spamcount/$totalspam;
              
              $spamrating = $spamprob/($hamprob+$spamprob);
                
              if($spamcount == 0){
                $spamrating = 0;
              }
            }
			if($hamcount != 0 && $spamcount == 0) {
				$spamrating = 0;
			}
            mysql_query("UPDATE `spam` SET `hamcount` = '".$hamcount."', `spamrating`='".$spamrating."' WHERE `spamid` =".$spamid." LIMIT 1");
          }
        }
      }
      else { // not in the database
        if($spam) {
          mysql_query("INSERT INTO `spam` ( `spamid` , `token` , `spamcount` , `hamcount` , `spamrating` ) VALUES ( NULL , '".$token."', '1', '0', '1')");
        }
        else {
          mysql_query("INSERT INTO `spam` ( `spamid` , `token` , `spamcount` , `hamcount` , `spamrating` ) VALUES ( NULL , '".$token."', '0', '1', '0')");
        }
      }
    }
    
  }

  
  /**
   * Checks the given text and returns a value from 0 to 1 indicating spamicty level.
   * @param text to test if it is spam or not
   * @return float from 0 to 1
   */
  function checkSpam($text) {
    $text = preg_replace('/\W+/',' ',$text);
    $temparray = explode(' ',strtolower($text));
    
    $spamratings = array();
    $count = 0;    
    
    foreach ($temparray as $token) {
      $result = mysql_query('SELECT spamrating FROM spam WHERE token="'.$token.'"');
      while ($line = mysql_fetch_array($result)) {
        $spamratings[$count]=$line[0];
      }

      if(mysql_num_rows($result) == 0) {
		// This number should probably be a contstant or something.
		// It is essentially just the default value for anything which we dont know
		// Which means we assume everything is spam unless we know about it
        $spamratings[$count]='0.9';
      }    
      $count++;
    }
    
    $a = null;
    $b = null;
    
    foreach ($spamratings as $token) {
      if($a == null)
        $a = (float)$token;
      else
        $a = $a * $token;  
      
      if($b == null)
        $b = 1-(float)$token;
      else
        $b = $b * (1-(float)$token);      
      
    }

	$spam = (float)0;
    $spam = (float)$a/(float)((float)$a+(float)$b);

    return $spam;  
  }
  
  /**
   * Resets the spam filter. This will then require a full retrain to identify spam.
   */
  function resetSpam() {
    mysql_query("UPDATE `totals` SET `totalspam` = '0',`totalham` = '0' WHERE `totalsid` =1 LIMIT 1;");
    mysql_query("TRUNCATE TABLE `spam`;");
  }

}
  
?>