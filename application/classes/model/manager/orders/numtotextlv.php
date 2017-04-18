<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Orders_Numtotextlv {
	public $lang = false;
	public $step = 0;
	public $currency = 'EUR';
	public $currencies = array(	'LVL' => array( array('lati', 'lats'), array('santīmi', 'santīms') ),
								'USD' => array( array('dolāri', 'dolārs'), array('centi', 'cents') ),
								'EUR' => array( array('eiro', 'eiro'), array('centi', 'cents') ) );
	public $digits = array('', 'viens', 'divi', 'trīs', 'četri', 'pieci', 'seši', 'septiņi', 'astoņi', 'deviņi');
	public $suffix = array('', 'desmit ', 'simt ', 'padsmit ', );
	public $exp = array('', ' tūkstoši ', ' miljoni ', ' miljardi ');
	public $exp1 = array('', ' tūkstotis ', ' miljons ', ' miljards ');
	public $negative = 'mīnus';
    public $zero = 'nulle';
	
	public function PriceToText($int, $currency, $cents_as_number = false, $display_zero_cents = false) {
		$this->currency = $currency;
		
		$part_int = (int)abs($int);
		$part_decimal = (int)round(abs($int) * 100 - floor(abs($int)) * 100);

		return ($int < 0 ? $this->negative . ' ' : '')
			. $this->toWords($part_int)
			. " " . $this->getCurrencyString($part_int) .
			(($int == floor($int) and !$display_zero_cents)
				? ''
				:
					" " . ($cents_as_number ? $part_decimal : $this->toWords($part_decimal)) .
					" " . $this->getCurrencyString($part_decimal, true)
			);
	}
	
	public function getCurrencyString($int, $cent = false) {
		return  $this->currencies[$this->currency][(int)($cent > 0)][(int)($int % 100 == 1)];
    }
	
	public function toWords($int){
		$sign = $int < 0 ? $this->negative . ' ' : '';
		$int = abs($int);
		$this->step = 0;
		$return = $int == 0 ? $this->zero : '';
		while (($three = $int%1000) || ($int >= 1)) {
			$int /= 1000;
			$return = ($three >= 1
				? $this->threeDigitsToWord($three) .
					($three % 10 == 1 && ($three % 100 < 11 || $three % 100 > 19)
						? $this->exp1[$this->step]
						: $this->exp[$this->step]
					)
				: '') . $return;
			$this->step++;
		}
		return $sign . $return;
	}
	
	public function digitToWord($digit, $suf = 0){
		return $digit > 0
			? !($suf == 2  && $digit == 1)
				? ($suf == 0  || $digit == 3
					? $this->digits[$digit]
					: mb_substr($this->digits[$digit], 0, -1)
				) . $this->suffix[$suf]
				: $this->suffix[2]
			: '';
	}
	
	public function threeDigitsToWord($int){
		$div100 = $int / 100;
		$mod100 = $int % 100;
		return $this->digitToWord(floor($div100), 2) .
			($mod100 > 9 && $mod100 < 20
				// 10, 11, .. 19
				? $mod100 == 10
					? $this->suffix[1]
					: ($mod100 == 13
						? $this->digits[$mod100 % 10]
						: mb_substr($this->digits[$mod100 % 10], 0, -1)
				) . $this->suffix[3]
				//any other number
				: $this->digitToWord(floor($mod100 / 10), 1) . $this->digitToWord($int % 10));
	}    
}