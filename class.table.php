<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

/**
 * Class calcTable provide a excel like macro language for HTML tables for
 * easy calculation
 *
 * @author phpManufaktur - ralf.hertsch@phpManufaktur.de
 *
 */
class calcTable {

    private $cell_typo = 'abcdefghijklmnopqrstuvwxyz';
    private $process_commands = array();
    private $error = '';
    private $message = '';

	public function __construct() {

	} // __construct()

    /**
     * @return the $cell_typo
     */
    protected function getCell_typo ()
    {
        return $this->cell_typo;
    }

	/**
     * @param string $cell_typo
     */
    protected function setCell_typo ($cell_typo)
    {
        $this->cell_typo = $cell_typo;
    }

	/**
      * Set $this->error to $error
      *
      * @param string $error
      */
    public function setError($error) {
     	$this->error = $error;
    } // setError()

    /**
      * Get Error from $this->error;
      *
      * @return string $this->error
      */
    public function getError() {
        return $this->error;
    } // getError()

    /**
      * Check if $this->error is empty
      *
      * @return boolean
      */
    public function isError() {
        return (bool) !empty($this->error);
    } // isError

    /** Set $this->message to $message
      *
      * @param string $message
      */
    public function setMessage($message) {
        $this->message = $message;
    } // setMessage()

    /**
      * Get Message from $this->message;
      *
      * @return string $this->message
      */
    public function getMessage() {
        return $this->message;
    } // getMessage()

    /**
      * Check if $this->message is empty
      *
      * @return bool
      */
    public function isMessage() {
        return (bool) !empty($this->message);
    } // isMessage

    /**
     * Parse $content for HTML tables and within the tables parse the cells
     * for calculation commands and write cells into an array for further
     * process.
     *
     * If the function find any calculation command it process these commands
     * and return the processed table in $content.
     *
     * @param string reference $content
     * @return boolean - true on success, false on error and set self::error
     */
	public function parseTables(&$content) {
		$tables = array();
		$cell_array = array();
		$commands = array();
		if (preg_match_all('%<table[^>]*>(.*?)<\/table>%si', $content,
		    $tables) > 0) {
			// check table for calculating commands
			$table = $tables[1][0]; // limit parsing to the first table!
			if (preg_match_all('%<tr[^>]*>(.*?)<\/tr>%si', $table, $rows) > 0) {
				$r=1;
				foreach ($rows[1] as $row) {
					// step through the rows...
					if (($count = preg_match_all('%<td[^>]*>(.*?)<\/td>%si', $row, $cells)) > 0) {
						// parse cells
						for ($c=0; $c < $count; $c++) {
							// add cell to cell_array
							$cell_array[$this->cell_typo[$c].$r] =
							    strip_tags($cells[1][$c]);
							if (preg_match('/{=(.*?)\)}/si', $cells[1][$c], $command) > 0) {
								$commands[$this->cell_typo[$c].$r] = $command[0];
							}
						}
						$r++;
					}
				}
			}
			if (!empty($commands)) {
			    // process commands
			    return $this->processCommands($commands, $cell_array, $content);
			}
	  }
	  return true;
	} // processTable

	/**
	 * Split a complete command string into the real command and the values.
	 * Sets a message if the operation fail.
	 *
	 * @param string $command_string - the complete command string
	 * @param string reference $command - the separated command
	 * @param string reference $values - the values to process by the command
	 * @return boolean true on success
	 */
	private function splitCommand($command_string, &$command, &$values) {
	    $command = '';
	    $values = '';
	    if (preg_match_all('/{=([a-z]*)\(/si', $command_string, $cmd) > 0) {
	        if (isset($cmd[1][0])) {
	            // got command string
	            $command = strtolower($cmd[1][0]);
	            if (preg_match_all('/\((.*?)\)/si', $command_string, $vals) > 0) {
	                if (isset($vals[1][0])) {
	                    // got value string
	                    $values = strip_tags(strtolower($vals[1][0]));
	                    return true;
	                }
	            }
	        }
	    }
	    $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cmd_split_fail, $command_string)));
	    return false;
	} // splitCommand()

	/**
	 * Process the detected calculation commands within the HTML table
	 *
	 * @param array $commands - the detected commands to process
	 * @param array $cell_array - the array with all cells of the table
	 * @param string reference $content - the complete content
	 * @return boolean true on success
	 */
	public function processCommands($commands, $cell_array, &$content) {
	    $this->process_commands = $commands;
	    foreach ($commands as $cell_ID => $command) {
	    		$calculateCommand = '';
	    		$calculateValues = '';
	        if ($this->splitCommand($command, $calculateCommand, $calculateValues)) {
	        		$result = '';
	            if (!$this->execCalculation($cell_ID, $calculateCommand, $calculateValues, $content, $cell_array, $command, $result)) {
	                return false;
	            }
	        }
	    }
	    return true;
	} // processCommands()

	/**
	 * Exec the desired $calculateCommand for the $cell_ID
	 *
	 * @param string $cell_ID - identifier of the cell
	 * @param string $calculateCommand - the calculate command
	 * @param string $values - the values within the calculate command
	 * @param string reference $content - the complete content to process
	 * @param array $cell_array - the cell array
	 * @param string $command - the complete unseparated command
	 * @param float reference $result - the result of the executed calculation
	 * @return boolean true on success
	 */
	private function execCalculation($cell_ID, $calculateCommand, $values, &$content, $cell_array, $command, &$result) {
	    switch ($calculateCommand) {
	        case 'sum':
	            // calculate sum
	            return $this->calculateSum($cell_ID, $values, &$content, $cell_array, $command, $result);
	        case 'mul':
	            // calculate mutliplication
	            return $this->calculateMul($cell_ID, $values, &$content, $cell_array, $command, $result);
	        default:
	            // unknown command
	            $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cmd_unknown_cmd, $calculateCommand)));
	            return false;
	    }
	} // execCalculation()

	/**
	 * Return the ROW number from the Cell ID
	 *
	 * @param string $cell_ID
	 * @return mixed $row - integer on success, boolean false on error
	 */
	private function getCellIDRow($cell_ID) {
	    if (preg_match_all('/([0-9]{1,3})/si', $cell_ID, $row)) {
	        return $row[1][0];
	    }
	    $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cell_id_row_invalid, $cell_ID)));
	    return false;
	} // getCellIDRow()

	/**
	 * Return the COLUMN character from the Cell ID
	 *
	 * @param string $cell_ID
	 * @return mixed $col - character on success, boolean false on error
	 */
	private function getCellIDColumn($cell_ID) {
	    if (preg_match_all('/([a-z]{1,2})/si', $cell_ID, $col)) {
	        return $col[1][0];
	    }
	    $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cell_id_col_invalid, $cell_ID)));
	    return false;
	} // getCellIDColumn()

	/**
	 * Return the COLUMN position as integer, starting with 1
	 *
	 * @param string $column_char
	 * @return mixed $position as integer or boolean false on error
	 */
	private function getColumnNumber($column_char) {
	    if (false !== ($x = stripos($this->cell_typo, $column_char))) {
	        return $x+1;
	    }
	    $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_col_char_invalid, $column_char)));
	    return false;
	} // getColumnNumber()

	/**
	 * Calculate a SUM in the table.
	 * Possible commands:
	 *   {=sum(a1:a4)} - adds cell a1+a2+a3+a4
	 *   {=sum(a1;a3)} - adds cell a1+a3
	 *   {=sum(col)}   - adds all cells in the column
	 *   {=sum(row)}   - adds all cells in the row
	 * The additional param dec:x allows to specify the number of decimals:
	 *   {=sum(a1:a4;dec:2)} - adds cell a1+a2+a3+4 and format it as float with 2 decimals
	 *
	 * @param string $cell_ID - cell identifier (a1, c3 ...)
	 * @param string $values - cell values
	 * @param string reference $content - the complete WYSIWYG content
	 * @param array $cell_array - array of all cells
	 * @param string $command - command to execute
	 * @param mixed reference $result - result of the calculation
	 */
	private function calculateSum($cell_ID, $values, &$content, $cell_array, $command, &$result) {
	    // explode $values
	    $value_array = explode(';', $values);
	    $decimals = 0; // setting decimals to zero == integer
	    $id = '';
	    if (count($value_array) > 1) {
	        // check for formatting commands
	        foreach ($value_array as $key => $cell) {
	            if (preg_match_all('/dec:([0-9]{1,2})/si', $cell, $formatter) > 0) {
	                // set decimal value
	                $decimals = trim($formatter[1][0]);
	                unset($value_array[$key]);
	            }
	            elseif (preg_match_all('/id:([a-z,0-9,_,-]{1,32})/si', $cell, $ids) > 0) {
	                // unique ID i.e. for 'row' or 'cell' command - don't process!
	                $id = trim($ids[1][0]);
	                unset($value_array[$key]);
	            }
	        }
	    }
	    $result = 0;
	    foreach ($value_array as $cell) {
            if (isset($cell_array[$cell])) {
                // ok - access to a cell
                if (key_exists($cell, $this->process_commands)) {
                    // this cell contains a calculation command!
                    $cmd = '';
                    $vals = '';
                    if (!$this->splitCommand($this->process_commands[$cell], $cmd, $vals)) {
                        return false;
                    }
                    $res = '';
                    if (!$this->execCalculation($cell, $cmd, $vals, $content, $cell_array, $this->process_commands[$cell], $res)) {
                        return false;
                    }
                    $result += $res;
                }
                else {
                    // add value of a cell to the $result
                    $val = ($cell_array[$cell]);
                    $val = str_replace(' ', '', $val);
                    $val = str_replace(idea_cfg_thousand_separator, '', $val);
                    $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                    $result += (float) $val;
                }
            }
            elseif (in_array($cell, array('col', 'row'))) {
                // check for column and row commands
                switch ($cell) {
                    case 'col':
                        // adding all cells above
                        $c = $this->getCellIDColumn($cell_ID);
                        $r = $this->getCellIDRow($cell_ID);
                        for ($i = 1; $i < $r; $i++) {
                            $ce = $c.$i;
                            if (key_exists($ce, $this->process_commands)) {
                                // this cell contains a calculation command!
                                if (!$this->splitCommand($this->process_commands[$ce], $cmd, $vals)) {
                                    return false;
                                }
                                if (!$this->execCalculation($ce, $cmd, $vals, $content, $cell_array, $this->process_commands[$ce], $res)) {
                                    return false;
                                }
                                $result += $res;
                            }
                            else {
                                // add cell value to $result
                                $val = ($cell_array[$ce]);
                                $val = str_replace(' ', '', $val);
                                $val = str_replace(idea_cfg_thousand_separator, '', $val);
                                $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                                $result += (float) $val;
                            }
                        }
                        break;
                    case 'row':
                        // adding all cells to the left
                        $c = $this->getCellIDColumn($cell_ID);
                        $cn = $this->getColumnNumber($c);
                        $r = $this->getCellIDRow($cell_ID);
                        for ($i = 1; $i < $cn; $i++) {
                            $ce = $this->cell_typo[($i-1)].$r;
                            if (key_exists($ce, $this->process_commands)) {
                                // this cell contains a calculation command!
                                if (!$this->splitCommand($this->process_commands[$ce], $cmd, $vals)) {
                                    return false;
                                }
                                if (!$this->execCalculation($ce, $cmd, $vals, $content, $cell_array, $this->process_commands[$ce], $res)) {
                                    return false;
                                }
                                $result += $res;                            }
                            else {
                                // add cell value to $result
                                $val = ($cell_array[$ce]);
                                $val = str_replace(' ', '', $val);
                                $val = str_replace(idea_cfg_thousand_separator, '', $val);
                                $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                                $result += (float) $val;
                            }
                        }
                        break;
                    default:
                        $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cmd_unknown_cmd, $values)));
                        return false;
                }
            }
            elseif (strpos($cell, ':')) {
                // area command span row or column
                list($from, $to) = explode(':', $cell, 2);
                if (false == ($from_row = $this->getCellIDRow($from))) return false;
                if (false == ($from_col = $this->getCellIDColumn($from))) return false;
                if (false == ($from_col_nr = $this->getColumnNumber($from_col))) return false;
                if (false == ($to_row = $this->getCellIDRow($to))) return false;
                if (false == ($to_col = $this->getCellIDColumn($to))) return false;
                if (false == ($to_col_nr = $this->getColumnNumber($to_col))) return false;

                if ($from_row == $to_row) {
                    // calculate row
                    for ($i = $from_col_nr; $i < $to_col_nr+1; $i++) {
                        $ce = $this->cell_typo[($i-1)].$from_row;
                        if (key_exists($ce, $this->process_commands)) {
                            // this cell contains a calculation command!
                            if (!$this->splitCommand($this->process_commands[$ce], $cmd, $vals)) {
                                return false;
                            }
                            if (!$this->execCalculation($ce, $cmd, $vals, $content, $cell_array, $this->process_commands[$ce], $res)) {
                                return false;
                            }
                            $result += $res;
                        }
                        else {
                            // add cell value to $result
                            $val = ($cell_array[$ce]);
                            $val = str_replace(' ', '', $val);
                            $val = str_replace(idea_cfg_thousand_separator, '', $val);
                            $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                            $result += (float) $val;
                        }
                    }
                }
                elseif ($from_col == $to_col) {
                    // calculate column
                    for ($i = $from_row; $i < $to_row+1; $i++) {
                        //$ce = $this->cell_typo[($i-1)].$from_row;
                        $ce = $from_col.$i;
                        if (key_exists($ce, $this->process_commands)) {
                            // this cell contains a calculation command!
                            if (!$this->splitCommand($this->process_commands[$ce], $cmd, $vals)) {
                                return false;
                            }
                            if (!$this->execCalculation($ce, $cmd, $vals, $content, $cell_array, $this->process_commands[$ce], $res)) {
                                return false;
                            }
                            $result += $res;
                        }
                        else {
                            // add cell value to $result
                            $val = ($cell_array[$ce]);
                            $val = str_replace(' ', '', $val);
                            $val = str_replace(idea_cfg_thousand_separator, '', $val);
                            $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                            $result += (float) $val;
                        }
                    }
                }
                else {
                    // invalid identifiers ...
                    $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_cell_area_invalid, $cell)));
                    return false;
                }
            }
            else {
                /*
                $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_calc_values_invalid, $cell)));
                return false;
                */
                // assume that the value is numeric
                $val = str_replace(' ', '', $cell);
                $val = str_replace(idea_cfg_thousand_separator, '', $val);
                $val = str_replace(idea_cfg_decimal_separator, '.', $val);
                $result += (float) $val;
            }
        } // foreach
        $content = str_replace($command, number_format($result, $decimals, idea_cfg_decimal_separator, idea_cfg_thousand_separator), $content);
        return true;
	} // calculateSum

	/**
	 * Calculate a MULTIPLICATION in the table
	 * Possible commands:
	 *   {=mul(a1;a3)} - multiplicate cell a1*a2
	 *   {=mul(a1;1,19)} - multiplicate cell a1*1.19
	 * The additional param dec:x allows to specify the number of decimals:
	 *   {=mul(a1;1,19;dec:2)} - format the result as float width two decimals
	 *
	 * @param string $cell_ID - cell identifier (a1, c3 ...)
	 * @param string $values - cell values
	 * @param string reference $content - the complete WYSIWYG content
	 * @param array $cell_array - array of all cells
	 * @param string $command - command to execute
	 * @param mixed reference $result - result of the calculation
	 */
	private function calculateMul($cell_ID, $values, &$content, $cell_array, $command, &$result) {
	    // explode $values
	    $value_array = explode(';', $values);
	    $decimals = 0; // setting decimals to zero == integer
	    $id = '';
	    if (count($value_array) > 1) {
	        // check for formatting commands
	        foreach ($value_array as $key => $cell) {
	            if (preg_match_all('/dec:([0-9]{1,2})/si', $cell, $formatter) > 0) {
	                // set decimal value
	                $decimals = trim($formatter[1][0]);
	                unset($value_array[$key]);
	            }
	            elseif (preg_match_all('/id:([a-z,0-9,_,-]{1,32})/si', $cell, $ids) > 0) {
	                // unique ID i.e. for 'row' or 'cell' command - don't process!
	                $id = trim($ids[1][0]);
	                unset($value_array[$key]);
	            }
	        }
	    }
	    $result = 1;

	    foreach ($value_array as $cell) {
	        if (isset($cell_array[$cell])) {
	            // ok - access to a cell
	            if (key_exists($cell, $this->process_commands)) {
	                // this cell contains a calculation command!
	                $cmd = '';
	                $vals = '';
	                if (!$this->splitCommand($this->process_commands[$cell], $cmd, $vals)) {
	                    return false;
	                }
	                $res = '';
	                if (!$this->execCalculation($cell, $cmd, $vals, $content, $cell_array, $this->process_commands[$cell], $res)) {
	                    return false;
	                }
	                $result *= $res;
	            }
	            else {
	                // add value of a cell to the $result
	                $val = ($cell_array[$cell]);
	                $val = str_replace(' ', '', $val);
	                $val = str_replace(idea_cfg_thousand_separator, '', $val);
	                $val = str_replace(idea_cfg_decimal_separator, '.', $val);
	                $result *= (float) $val;
	            }
	        }
	        else {
	            // assume that the value is numeric
	            $val = str_replace(' ', '', $cell);
	            $val = str_replace(idea_cfg_thousand_separator, '', $val);
	            $val = str_replace(idea_cfg_decimal_separator, '.', $val);
	            $result *= (float) $val;
	        }
	    } // foreach
	    $content = str_replace($command, number_format($result, $decimals, idea_cfg_decimal_separator, idea_cfg_thousand_separator), $content);
        return true;
	} // calculateMul()

} // calcTable
