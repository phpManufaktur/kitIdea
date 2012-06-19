<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

/*********************************************************************
 *
 * class for lcs  (longest common subsequence)
 *
 * (C) 2005-2006 by Heiko Richler  http://www.richler.de/
 *
 * Search for longest common subsequence problem (LCS) for details
 * aber the algorithmus.
 *
 * This Code is provided unter the GNU Lesser General Public License
 * http://www.gnu.org/licenses/lgpl.html
 *
 * No warranty at all!
 *
 * 2006-12-01 Heiko Richler
 *            Cleaned code and comments
 */

class lcs {
    /****************************************************************
     *
     * wordCompare(&$fir, &$sec, &$result)
     *
     * compares strings word by word not by character.
     *
     * $fir, $sec : Strings to compare
     * $result    : must be an object of the class report4lcs
     *              and will keep the result.
     *
     * returns a proportion or null in case of an error.
     */
    function wordCompare(&$fir, &$sec, &$result) {
        // Regular Expressen to split strings into words. In this
        // context a word may be a HTML-Tag, some white-spaces,
        // letters and figures or the other
        // characters. Letters inkludes german umlauts.
        $pattern = "(<[^>]*>*".              // HTML Tag
                   "|[ \t\n]+".                     // White Spaces
                   "|[a-zA-Z0-9äöüÄÖÜß]+".  // Letters
                   "|[^a-zA-Z0-9äöüÄÖÜß \t\n<]+".// anything else
                   ")";

        // doing the splitting and storing the product of the words in $power
        $i = preg_match_all($pattern, $fir,  $src_arr, PREG_PATTERN_ORDER);
        $j = preg_match_all($pattern, $sec, $dst_arr, PREG_PATTERN_ORDER);
        $power = $i * $j;
        // if $power is to large the skript may be terminated
        if ($power<200000) {
            return lcs::Compare($src_arr[0], $dst_arr[0],
                                equal, count, SubArray, $result);
        }
        if ($fir==$sec) {
            $result->put_eq($fir);
            return 1;
        }
        $result->put_diff($fir, $sec);
        return null;
    }

    /****************************************************************
     *
     * HTMLwordCompare(&$fir, &$sec, &$result)
     *
     * compares strings word by word not by character and respects
     * HTML-Encoding.
     *
     * $fir, $sec : Strings to compare
     * $result    : must be an object of the class report4lcs
     *              and will keep the result.
     *
     * returns a proportion or null in case of an error.
     */
    function HTMLwordCompare($fir, $sec, &$result) {
        // Regular Expressen to split strings into words. In this
        // context a word may be a HTML-Tag, some white-spaces,
        // letters and figures or the other
        // characters. Letters inkludes html-encoding and german umlauts.
        $pattern = "(<[^>]*>[ \t\n]*".              // HTML Tag
                   "|[ \t\n]+".                     // White Spaces
                   "|([a-zA-Z0-9äöüÄÖÜß]|&[a-zA-Z#0-9]+;)+[ \t\n]*".// Letters
                   "|[^a-zA-Z0-9äöüÄÖÜß \t\n<]+[ \t\n]*".// else
                   ")";
        // doing the splitting and storing the product of the words in $power
        $i = preg_match_all($pattern, $fir,  $src_arr, PREG_PATTERN_ORDER);
    $j = preg_match_all($pattern, $sec, $dst_arr, PREG_PATTERN_ORDER);
        $power = $i * $j;
        // if $power is to large the skript may be terminated
        if ($power<200000) {
            return lcs::Compare($src_arr[0], $dst_arr[0],
                                'trimmed_equal', 'count', 'SubArray', $result);
        }
        if ($fir==$sec) {
            $result->put_eq($fir);
            return 1;
        }
        $result->put_diff($fir, $sec);
        return null;
    }


    /****************************************************************
     *
     * charCompare(&$fir, &$sec, &$result)
     *
     * compares strings by characters
     *
     * $fir, $sec : Strings to compare
     * $result    : must be an object of the class report4lcs
     *              and will keep the result.
     *
     * returns a proportion or null in case of an error.
     */
    function charCompare(&$fir, &$sec, &$result) {
        return lcs::Compare($fir, $sec, equal, strlen, substr, $result);
    }

    /****************************************************************
     *
     * Compare($fir, $sec, $equal, $sizeop, $partop, &$result)
     *
     * compares strings by characters
     *
     * $fir, $sec : Strings to compare or Array of Symbols
     * $equal     : bool-function for equality
     * $sizeop    : a function to calculate a strings len
     * $partop    : a function to get a substring
     * $result    : must be an object of the class report4lcs
     *              and will get the result.
     *
     * returns a proportion
     */
    function Compare($fir, $sec, $equal, $sizeop, $partop, &$result) {
        $lenFir  = $sizeop($fir);
        $lenSec = $sizeop($sec);
        $table = array(array()); // of  int  [$lenFir, $lenSec]

    //lcs:
        for ($i=0; $i<$lenFir; $i++) {
            for ($j=0; $j<$lenSec; $j++) {
                $table[$i][$j] = -1;
            }
        }
        for ($i = $lenFir; $i >= 0; $i--) {
            for ($j = $lenSec; $j >= 0; $j--) {
                //if ($equal($fir[$i], $sec[$j])) {

            	if (isset($fir[$i]) && isset($sec[$j]) && call_user_func($equal, $fir[$i], $sec[$j])) {

                if (isset($table[$i+1][$j+1])) $table[$i][$j] = 1 + $table[$i + 1][$j + 1];
                }
                else {
                //    $table[$i][$j] = max($table[$i + 1][$j], $table[$i][$j + 1]);
                    if (isset($table[$i + 1][$j]) && isset($table[$i][$j + 1])) $table[$i][$j] = max($table[$i + 1][$j], $table[$i][$j + 1]);
                }
            }
        }
        $equality = $table[0][0]-1; // max($lenFir, $lenSec);

        // lets get results

        $leftPos  = 0; $x=0;
        $rightPos = 0; $y=0;
        while ($leftPos < $lenFir || $rightPos < $lenSec) {
            //if ($x < $lenFir && $y < $lenSec && $equal($fir[$leftPos],$sec[$rightPos])) {
            if ($x < $lenFir && $y < $lenSec && call_user_func($equal, $fir[$leftPos], $sec[$rightPos])) {
                // common part
                $len = 0;
                $x=$leftPos;
                $y=$rightPos;
                //while ($x < $lenFir && $y < $lenSec && $equal($fir[$x], $sec[$y])) {
                while ($x < $lenFir && $y < $lenSec && call_user_func($equal, $fir[$x], $sec[$y])) {
                    $x++;
                    $y++;
                    $len++;
                }
                $result->put_eq($partop($fir, $leftPos, $len));
                $leftPos=$x;
                $rightPos=$y;
            }
            else {
                // unequal
                $leftLen = 0;
                $rightLen = 0;
                $x=$leftPos;
                $y=$rightPos;
                //while ($x < $lenFir && $y < $lenSec && !$equal($fir[$x], $sec[$y])) {
                while ($x < $lenFir && $y < $lenSec && !call_user_func($equal, $fir[$x], $sec[$y])) {
                    if ((isset($table[$x+1][$y]) && isset($table[$x][$y+1])) && ($table[$x+1][$y] > $table[$x][$y+1])) {
                        // included into left side
                        $x++;
                        $leftLen++;
                    }
                    elseif ((isset($table[$x+1][$y]) && isset($table[$x][$y+1])) && ($table[$x+1][$y] < $table[$x][$y+1])) {
                        // included into right side
                        $y++;
                        $rightLen++;
                    }
                    else {
                        //different
                        $x++;
                        $leftLen++;
                        $y++;
                        $rightLen++;
                    }
                }
                if ($x >= $lenFir || $y >= $lenSec) {
                    // done
                    $result->put_diff($partop($fir, $leftPos), $partop($sec, $rightPos));
                    $leftPos=$lenFir;
                    $rightPos=$lenSec;
                }
                else {
                    // there is more to come
                    $result->put_diff($partop($fir, $leftPos, $leftLen), $partop($sec, $rightPos, $rightLen));
                    $leftPos=$x;
                    $rightPos=$y;
                }
            }
        }

        if (max($lenFir, $lenSec)>0) {
            return (($equality * 100) / max($lenFir, $lenSec));
        }
        else {
            return null;
        }
    }
}



/****************************************************************************
* report4lcs
*
* Reporter (printout) class for the class lcs. The Method show in
* lcs uses the Methods from report4lcs to print out an given Result
*/
class report4lcs {
    /****************************************************************
     *
     * put_eq($value)
     *
     * Function to print an equal Part.
     */
    function put_eq($value) {
        echo nl2br(htmlentities($value));
    }

    /****************************************************************
     *
     * put_diff($fir, $sec)
     *
     * Function to print the subsets that are different
     */
    function put_diff($fir, $sec) {
        echo '<span style="background:#ffa;color:red;">';
        echo nl2br(htmlentities($fir));
        echo '</span>';
        echo '<span style="background:#fcf;color:red;">';
        echo nl2br(htmlentities($sec));
        echo '</span>';
    }
}



/****************************************************************************
* reportstorage4lcs
*
* Reporter class for the class lcs. The Method stores and shows the
* results given by lcs.
* This class is build to show not HTML-Text.
*/
class reportstorage4lcs extends report4lcs {
    var $data = array();

    /****************************************************************
     *
     * put_eq($value)
     *
     * Stores an equal Part.
     */
    function put_eq($value) {
        $this->data[] = $value;
    }

    /****************************************************************
     *
     * put_diff($fir, $sec)
     *
     * Stores different subsets
     */
    function put_diff($fir, $sec) {
        $this->data[] = array($fir, $sec);
    }

    /****************************************************************
     *
     * x_nl2br($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes NewLines inkluding a given Symbol
     */
    function x_nl2br($value, $deco, $enddeco, $nl) {
        return str_replace("\n","$nl<br>\n", $value);
    }

    /****************************************************************
     *
     * nl2br($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes NewLines
     */
    function nl2br($value, $deco, $enddeco, $nl) {
        return nl2br($value);
    }

    /****************************************************************
     *
     * encode($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes the given string
     */
    function encode($value, $deco, $enddeco, $nl) {
        return htmlentities($value);
    }

    /****************************************************************
     *
     * getHTML($pos, $deco, $enddeco, $nl)
     *
     * $pos     : 0 for first, 1 for second
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * Returns the
     */
    function getHTML($pos, $deco, $enddeco, $nl) {
        $result='';
        foreach($this->data as $value) {
            if (is_array($value)) {
                $puffer = $this->x_nl2br($this->encode($value[$pos],
                                         $deco, $enddeco, $nl),
                                         $deco, $enddeco, $nl);
                if ($puffer!='') {
                    $result .= $deco;
                    $result .= $puffer;
                    $result .= $enddeco;
                }
            }
            else {
                $result .= $this->nl2br($this->encode($value,
                                        $deco, $enddeco, $nl),
                                        $deco, $enddeco, $nl);
            }
        }
        return $result;
    }

    /****************************************************************
     *
     * Show($deco, $enddeco, $nl, $class)
     *
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     * $class   : CSS-Class to use for the HTML-table
     *
     * Returns the
     */
    function Show($deco = '<span style="background-color:#ffcccc;">',
                  $enddeco='</span>', $nl='&crarr;', $class = '')
    {
        echo "<table class='$class'>";
        echo '<tr><td>';
        echo $this->getHTML(0, $deco, $enddeco, $nl);
        echo '</td><td>';
        echo $this->getHTML(1, $deco, $enddeco, $nl);
        echo '</td></tr>';
        echo '</table>';
    }
}

/****************************************************************************
* reportstorage4lcs
*
* Reporter class for the class lcs. The Method stores and shows the
* results given by lcs.
* This class is build to show HTML-Text.
*/
class reportstorageHTML4lcs extends reportstorage4lcs {
    /****************************************************************
     *
     * x_nl2br($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes NewLines inkluding a given Symbol
     */
    function x_nl2br($value, $deco, $enddeco, $nl) {
        return str_replace("<br>","$nl<br>",
                           str_replace("<br/>","$nl<br/>", $value));
    }

    /****************************************************************
     *
     * nl2br($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes NewLines
     */
    function nl2br($value, $deco, $enddeco, $nl) {
        return $value;
    }

    /****************************************************************
     *
     * encode($value, $deco, $enddeco, $nl)
     *
     * $value   : the value to encode
     * $deco    : code to add decoration for different code
     * $enddeco : code to terminate decoration
     * $nl      : Symbol to make an NewLine visible
     *
     * HTML-Encodes the given string
     */
    function encode($value, $deco, $enddeco, $nl) {
        return $value;
    }
}



/****************************************************************
*
* SubArray($arr, $start, $len)
*
* concatenates a sequence of array-elements to one string
*/
function SubArray($arr, $start, $len = null) {
    if ($len === 0) {
        return '';
    }
    //$puffer = $arr[$start];
    $puffer = '';
    if (isset($arr[$start])) $puffer = $arr[$start];
    if ($len < 0) {
        $ende = $start+$len;
        if ($ende < 0) {
            $ende = 0;
        }
        for ($i = $start - 1; $i > 0; $i--) {
            $puffer .= $arr[$i];
        }
    }
    else {
        if (is_null($len)) {
            $ende = count($arr);
        }
        elseif ($len > 0) {
            $ende = $start + $len;
            if ($ende > count($arr)) {
                $ende = count($arr);
            }
        }
        for ($i = $start + 1; $i < $ende; $i++) {
            $puffer .= $arr[$i];
        }
    }
    return $puffer;
}



/****************************************************************
*
* equal($lvalue, $rvalue)
*
* return $lvalue == $rvalue
*/
function equal($lvalue, $rvalue) {
    return $lvalue == $rvalue;
}



/****************************************************************
*
* equal($lvalue, $rvalue)
*
* return true if the trimmed strings are equal
*/
function trimmed_equal($lvalue, $rvalue) {
    return trim($lvalue) == trim($rvalue);
}

?>
