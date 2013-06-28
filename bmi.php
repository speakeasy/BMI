<?php parse_str(implode('&', array_slice($argv, 1)), $_GET);
//
//  bmi.php
//
//  Created by Kevin Burress on 2013-06-26.
//  Copyright (c) 2013 Kevin Burress <mekevin1917@gmail.com>.
//
//  You may use this code, in original or modified form,
//  with written permission from the original author. If you would
//  like to use this code for any purpose other than private
//  educational and/or acedemic projects, you may contact the
//  author at the email address provided herein.
//
//  THIS SOFTWARE IS PROVIDED BY Kevin Burress ''AS IS'' AND ANY
//  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
//  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
//  DISCLAIMED. IN NO EVENT SHALL Kevin Burress BE LIABLE FOR ANY
//  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
//  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
//  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
//  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
//  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
//  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
//  All rights reserved.
//

class bmi {

    public $bmi = 0;
    public $weight = 0;
    public $height = "0'";
    public $bmiclass = Array (
		0 => "Undefined",
        1 => "Underweight",
        2 => "Normal",
        3 => "Overweight",
        4 => "Obese"
    );
    public $cli = FALSE;
	public $action = 0;
    public $output = "";


    public function __construct() {
        // init code here.
        try {
	        if (defined('STDIN')) {
		        $this->cli = TRUE;
				if (array_key_exists("rhts", $_GET)) {
					$_GET["rhts"] = TRUE;
				} else {
					$_GET["rhts"] = FALSE;
				}
				if (array_key_exists("rwts", $_GET)) {
					$_GET["rwts"] = TRUE;
				} else {
					$_GET["rwts"] = FALSE;
				}
				if (array_key_exists("bmi", $_GET)) {
					$this->bmi = $_GET["bmi"];
					$this->bmi += 0;
				}
				if (array_key_exists("w", $_GET)) {
					$this->weight = $_GET["w"];
		            $this->weight += 0;
				}
				if (array_key_exists("ft", $_GET)) {
					$this->height = $_GET["ft"] . "'";
				}
	            if (array_key_exists("in", $_GET)) {
					$this->height .= $_GET["in"] . "\"";
				} else {
					$this->height .= "0\"";
				}
				$this->height = $this->ftintoin($this->height);
				
				// bmi(1): calculate for BMI.
	            if (array_key_exists("w", $_GET)
	            && (array_key_exists("in", $_GET) || array_key_exists("ft", $_GET))
	            && $_GET["rhts"] == FALSE
	            && $_GET["rwts"] == FALSE) {
					$this->action = 1;
		
		        // bmi(2): calculate for weight.
	            } else if (
	            ($_GET["rwts"] || $this->bmi > 0)
	            && (array_key_exists("ft", $_GET) || array_key_exists("in", $_GET))
	            ) {
		            $this->action = 2;
		
		        // bmi(3): calculate for height.
	            } else if (
	            ($_GET["rhts"] || $this->bmi > 0)
	            && array_key_exists("w", $_GET)
				) {
		            $this->action = 3;
		
				// bmi(0): Display usage.
		        } else {
		            $this->action = 0;
	            }
	            return TRUE;
	        }
	        return FALSE;
	    } catch (Exception $ex) {
		    return $ex;
        }
    }

    public function bmimain($action = 0) {
		if ($_GET["rhts"] || $_GET["rwts"]) {
			$rbmis = Array(
				(float)(rand(1000,1849)*0.01),
				(float)(rand(1850,2499)*0.01),
				(float)(rand(2500,2999)*0.01),
				(float)(rand(3000,3599)*0.01)
			);
		}
		switch ($action) {

				// Height and weight provided, calculate for BMI.
			case 1 : {
				if (FALSE == $this->setbmi($this->height, $this->weight)) throw new Exception("Error calculating BMI.\n");
				if (FALSE == $this->wtclass($this->bmi)) throw new Exception("Error calculating weight status.\n");
				break;
			}

				// BMI and height provided, calculate for weight.
			case 2 : {
				if ($_GET["rwts"]) {
					$this->output .= "\n";
					foreach($rbmis as $k => $abmi) {
						$this->bmi = $abmi;
						if (FALSE == $this->wtclass($this->bmi)) throw new Exception("Error calculating weight status.\n");
						
						$fl = $this->setweight($this->bmi, $this->height);
						if (FALSE == $fl) throw new Exception("Error calculating weights.\n");
						
						$tmpout = $this->catbmiout($this->height, $this->weight, $abmi);
						if (FALSE == $tmpout) throw new Exception("Error concatinating BMI statistics.\n");
						$this->output .= $tmpout;
					}
					$this->output .= "\nDone.\n";
					return $this->output;
				} else if (FALSE == $this->setweight($this->bmi, $this->height)) {
					throw new Exception("Error calculating weight.\n");
					break;
				}
				break;
			}

				// BMI and weight provided, calculate for height.
			case 3 : {
				if ($_GET["rhts"] ) {
					$this->output .= "\n";
					foreach($rbmis as $k => $abmi) {
						$this->bmi = $abmi;
						if (FALSE == $this->wtclass($this->bmi)) throw new Exception("Error calculating weight status.\n");
						
						$fl = $this->setheight($this->bmi, $this->weight);
						if (FALSE == $fl) throw new Exception("Error calculating heights.\n");
						
						$tmpout = $this->catbmiout($this->height, $this->weight, $abmi);
						if (FALSE == $tmpout) throw new Exception("Error concatinating BMI statistics.\n");
						$this->output .= $tmpout;
					}
					$this->output .= "\nDone.\n";
					return $this->output;
				} else if (FALSE == $this->setheight($this->bmi, $this->weight)) {
					throw new Exception("Error calculating height.\n");
					break;
				}
				break;
			}

			default : {
		        $this->output .= <<<USAGE

Invalid arguments. Please specify height and weight.

Usage: bmi.php [ft=feet] [in=inches] [w=weight] [bmi=bmi] [( rhts=true || rwts=true )]

    ft=feet        Height feet.

    in=inches      Height inches.

    w=weight       Weight in pounds.

    bmi=bmi        Calculated Body Mass Index.

    rhts=true      Calculate height for random BMI of each weight class given a weight.

    rwts=true      Calculate weight for random BMI of each weight class given a height.


USAGE;
		        return $this->output;
			}
	    }
		$tmpout = $this->catbmiout($this->height, $this->weight, $this->bmi);
		if ($tmpout == FALSE) throw new Exception("Error concatinating BMI statistics:\n");
		$this->output .= $tmpout . "\nDone.\n";
		return $this->output;
    }

	public function catbmiout($height = 0, $weight = 0, $bmi = 0) {
		$output = "\n";
		if ( is_int($height) && is_int($weight) && (is_int($bmi) || is_float($bmi)) ) {
			$wtclass = $this->wtclass($bmi);
			$output .= "\nWeight Class: " . $this->bmiclass[$wtclass] . ":";
			$output .= "\n      Height: " . $height . " inches.";
			$output .= "\n      Weight: " . $weight . " pounds.";
			$output .= "\n         BMI: " . $bmi . ".\n";
			return $output;
		}
		return FALSE;
	}

	public function setbmi($height = 0, $weight = 0) {
		if (is_int($height) && is_int($weight) && $height > 0 && $weight > 0) {
			$this->bmi = (703 * $weight) / ($height * $height);
			return TRUE;
		}
		return FALSE;
	}

	public function setheight($bmi = 0, $weight = 0){
		if ( (is_int($bmi) || is_float($bmi)) && is_int($weight) && $bmi > 0 && $weight > 0) {
			$this->height = (int)sqrt((float)((703 * $weight) / $bmi));
			return TRUE;
		}
		return FALSE;
	}

	public function setweight($bmi = 0, $height = 0){
		if ( (is_int($bmi) || is_float($bmi)) && is_int($height) && $bmi > 0 && $height > 0) {
			$this->weight = (int)((703 * $bmi) / $height);
			return TRUE;
		}
		return NULL;
	}

    public function wtclass($bmi = 0) {
	    switch((float)$bmi) {
		    case ( (float)$bmi < (float)18.5 ) : { return 1; }
		    case ( ((float)$bmi >= (float)18.5) && ((float)$bmi < (float)25) ) : { return 2; }
		    case ( ((float)$bmi >= (float)25) && ((float)$bmi < (float)30) ) : { return 3; }
		    case ( (float)$bmi >= (float)30 ) : { return 4; }
	    }
	    return FALSE;
    }

    public function ftintoin($length = "0'0\"") {
	    $arrstr = explode("'", $length);
	    if (sizeof($arrstr) == 2) {
		    str_replace("\"", "", $arrstr[1]);
		    $arrstr[0] += 0;
		    $arrstr[1] += 0;
		    return (($arrstr[0] * 12) + $arrstr[1]);
	    }
	    return FALSE;
    }

}
$mybmi = new bmi();
echo $mybmi->bmimain($mybmi->action);
?>
