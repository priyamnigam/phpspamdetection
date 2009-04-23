<?php
/*

  **NB** This is still in active development and is not ready for use.

  © Copyright 2007 Ben Boyter (http://www.boyter.org)

  This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/


/**
 * vectorspace.php
 * Created 22 April 2009 
 */
class vectorspace {

  function magnitude($concordance) {
	  $total = 0;
	  foreach ($concordance as $i => $value) {
	    $total += $value * $value;
	  }
	  return sqrt($total);
  }

  function relation($concordance1,$concordance2) {
	  $relevance = 0;
	  $topvalue = 0;
	  foreach ($concordance1 as $i => $value) {
		if(array_key_exists($i,$concordance2)){
		  $topvalue += $value * $concordance2[$i];
		}
	  }
	  return $topvalue / ($this->magnitude($concordance1) * $this->magnitude($concordance2));
  }

  function concordance($text) {
	$text = preg_replace('/\W+/',' ',$text);
    $temparray = explode(' ',strtolower($text));
	
	$returnarray = array();
	
	foreach($temparray as $i => $value) {
	  if(array_key_exists($value,$returnarray)){
	    $returnarray[$value]+=1;
	  }
	  else{
	    $returnarray[$value] = 1;
	  }
	}
	return $returnarray;
  }
  
  
  /**
   * Takes the given text, cleans it up and stores it using the provided category.
   * This is the store that everything will be checked against. The larger it gets the slower
   * that checking will be.
   * @param text the text which will be cleaned and added
   * @param category the category that the text will be added under
   */
  function addtostore($text,$category) {
	  $clean_text = preg_replace('/\W+/',' ',$text);
      $clean_text = strtolower($clean_text);
	  mysql_query("INSERT INTO `store` ( `text` , `category` ) VALUES ( '".mysql_real_escape_string($clean_text)."','".mysql_real_escape_string($category)."')");
  }
  
  /**
   * Purges the store of all data.
   */
  function cleanstore() {
    mysql_query("TRUNCATE TABLE `store`;");
  }
  
  
  function checktype($text) {
	  $rows = mysql_query('SELECT text, category FROM store');
	  $results = array();
	  
	  $text_concordance = $this->concordance($text);
	  
	  while ($line = mysql_fetch_array($rows)) {
		$relation = $this->relation($text_concordance,$this->concordance($line[0]));
	    //echo $line[1].' '..'<br>';
		if(array_key_exists($line[1],$results)){
		  $results[$line[1]] = ($results[$line[1]] + $relation) / 2;
		}
		else{
		  $results[$line[1]] = $relation;
		}
	  }
	  
	  return $results;
  }
}
?>