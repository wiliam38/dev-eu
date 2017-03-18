<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_Orders_Numtotexten {
	public $lang = false;
	public $step = 0;
	public $currency = 'EUR';
	public $currencies = array(	'LVL' => array( array('lats', 'lat'), array('centimes', 'centime') ),
								'USD' => array( array('dollars', 'dollar'), array('cents', 'cent') ),
								'EUR' => array( array('euros', 'euro'), array('cents', 'cent') ) );
	public $digits = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'one', 'one');
	public $tens = array('', 'ten ', 'twenty ', 'thirty ', 'forty ', 'fifty ', 'sixty ', 'seventy ', 'eighty ', 'ninety ');
	public $teens = array('ten ', 'eleven ', 'twelve ', 'thirteen ', 'fourteen ', 'fifteen ', 'sixteen ', ' seventeen ', 'eighteen ', 'nineteen ');
    public $exp = array('', ' thousand ', ' million ', ' billion ');
	public $negative = 'minus';
    public $zero = 'zero';
	
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
        if (!isset($this->hundreds)) {
            $this->hundreds = $this->digits;
            array_walk($this->hundreds, function(&$val, $key) {
                $val .= ' hundred ';
            });
        }
        $sign = $int < 0 ? $this->negative . ' ' : '';
        $int = abs($int);
        $this->step = 0;
        $return = $int == 0 ? $this->zero : '';
        while (($three = $int % 1000) || ($int >= 1)) {
            $int /= 1000;
            $return = ($three >= 1
                ? $this->threeDigitsToWord($three) . $this->exp[$this->step]
                : '') . $return;
            $this->step++;
        }
        return $sign.$return;
    }
	
	public function digitToWord($digit, $suf = 0){
        return $digit > 0
        ? $suf == 2
            ? $this->hundreds[$digit]
            : ($suf == 1
                ? $this->tens[$digit]
                : $this->digits[$digit]
            )
        : '';
    }
	
	public function threeDigitsToWord($int){
        $div100 = $int / 100;
        $mod100 = $int % 100;
        return 
            $this->digitToWord(floor($div100), 2) .
            ($mod100 > 9 && $mod100 < 20
                // 10, 11, .. 19
                ? $this->teens[$mod100 - 10]
                //any other number
                : $this->digitToWord(floor($mod100 / 10), 1) . $this->digitToWord($int % 10));
    }
}