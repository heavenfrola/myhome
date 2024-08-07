<?php
class Image_Barcode_code128 extends PEAR {
    var $_type = 'code128';
    var $_barcodeheight = 25;
    var $_font = 10;
    var $_barwidth = 1;
    var $code;
	var $_ttf = '_manage/image/font/ARIAL.TTF';

    function &draw($text, $imgtype = 'png') {
		global $engine_dir;

        $startcode= $this->getStartCode();
        $checksum = 104;
        $allbars = $startcode;

        $bars = '';
        for ($i=0; $i < strlen($text); ++$i) {
            $char = $text[$i];
            $val = $this->getCharNumber($char);

            $checksum += ($val * ($i + 1));

            $bars = $this->getCharCode($char);
            $allbars = $allbars . $bars;
        }

        $checkdigit = $checksum % 103;
        $bars = $this->getNumCode($checkdigit);

        $stopcode = $this->getStopCode();
        $allbars = $allbars . $bars . $stopcode;

        $barcodewidth = 0;

        for ($i=0; $i < strlen($allbars); ++$i) {
            $nval = $allbars[$i];
            $barcodewidth += ($nval * $this->_barwidth);
        }
        $barcodelongheight = (int) (imagefontheight($this->_font) / 2) + $this->_barcodeheight;

        $img = ImageCreate($barcodewidth, $barcodelongheight+ imagefontheight($this->_font)+1);
        $black = ImageColorAllocate($img, 0, 0, 0);
        $white = ImageColorAllocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

		$fontsize = imageftbbox ($this->_font, 0, $engine_dir.'/'.$this->_ttf, $text);

		imagefttext(
			$img,
			$this->_font,
			0,
			$barcodewidth / 2 - ((abs($fontsize[0])+abs($fontsize[2]))/2),
			$this->_barcodeheight + (abs($fontsize[7])+abs($fontsize[1])) + 10,
			$black,
			$engine_dir.'/'.$this->_ttf,
			$text
		);

        $xpos = 0;

        $bar = 1;
        for ($i=0; $i < strlen($allbars); ++$i) {
            $nval = $allbars[$i];
            $width = $nval * $this->_barwidth;

            if ($bar==1) {
                imagefilledrectangle($img, $xpos, 0, $xpos + $width-1, $barcodelongheight, $black);
                $xpos += $width;
                $bar = 0;
            } else {
                $xpos += $width;
                $bar = 1;
            }
        }

        return $img;
    } // function draw()


    /**
    * @internal
    * In the Image_Barcode_code128 constructor, we initialize
    * the $code array, containing the bar and space pattern
    * for the Code128 B character set.
    */
    function __construct()
    {
        $this->code[0] = "212222";  // " "
        $this->code[1] = "222122";  // "!"
        $this->code[2] = "222221";  // "{QUOTE}"
        $this->code[3] = "121223";  // "#"
        $this->code[4] = "121322";  // "$"
        $this->code[5] = "131222";  // "%"
        $this->code[6] = "122213";  // "&"
        $this->code[7] = "122312";  // "'"
        $this->code[8] = "132212";  // "("
        $this->code[9] = "221213";  // ")"
        $this->code[10] = "221312"; // "*"
        $this->code[11] = "231212"; // "+"
        $this->code[12] = "112232"; // ","
        $this->code[13] = "122132"; // "-"
        $this->code[14] = "122231"; // "."
        $this->code[15] = "113222"; // "/"
        $this->code[16] = "123122"; // "0"
        $this->code[17] = "123221"; // "1"
        $this->code[18] = "223211"; // "2"
        $this->code[19] = "221132"; // "3"
        $this->code[20] = "221231"; // "4"
        $this->code[21] = "213212"; // "5"
        $this->code[22] = "223112"; // "6"
        $this->code[23] = "312131"; // "7"
        $this->code[24] = "311222"; // "8"
        $this->code[25] = "321122"; // "9"
        $this->code[26] = "321221"; // ":"
        $this->code[27] = "312212"; // ";"
        $this->code[28] = "322112"; // "<"
        $this->code[29] = "322211"; // "="
        $this->code[30] = "212123"; // ">"
        $this->code[31] = "212321"; // "?"
        $this->code[32] = "232121"; // "@"
        $this->code[33] = "111323"; // "A"
        $this->code[34] = "131123"; // "B"
        $this->code[35] = "131321"; // "C"
        $this->code[36] = "112313"; // "D"
        $this->code[37] = "132113"; // "E"
        $this->code[38] = "132311"; // "F"
        $this->code[39] = "211313"; // "G"
        $this->code[40] = "231113"; // "H"
        $this->code[41] = "231311"; // "I"
        $this->code[42] = "112133"; // "J"
        $this->code[43] = "112331"; // "K"
        $this->code[44] = "132131"; // "L"
        $this->code[45] = "113123"; // "M"
        $this->code[46] = "113321"; // "N"
        $this->code[47] = "133121"; // "O"
        $this->code[48] = "313121"; // "P"
        $this->code[49] = "211331"; // "Q"
        $this->code[50] = "231131"; // "R"
        $this->code[51] = "213113"; // "S"
        $this->code[52] = "213311"; // "T"
        $this->code[53] = "213131"; // "U"
        $this->code[54] = "311123"; // "V"
        $this->code[55] = "311321"; // "W"
        $this->code[56] = "331121"; // "X"
        $this->code[57] = "312113"; // "Y"
        $this->code[58] = "312311"; // "Z"
        $this->code[59] = "332111"; // "["
        $this->code[60] = "314111"; // "\"
        $this->code[61] = "221411"; // "]"
        $this->code[62] = "431111"; // "^"
        $this->code[63] = "111224"; // "_"
        $this->code[64] = "111422"; // "`"
        $this->code[65] = "121124"; // "a"
        $this->code[66] = "121421"; // "b"
        $this->code[67] = "141122"; // "c"
        $this->code[68] = "141221"; // "d"
        $this->code[69] = "112214"; // "e"
        $this->code[70] = "112412"; // "f"
        $this->code[71] = "122114"; // "g"
        $this->code[72] = "122411"; // "h"
        $this->code[73] = "142112"; // "i"
        $this->code[74] = "142211"; // "j"
        $this->code[75] = "241211"; // "k"
        $this->code[76] = "221114"; // "l"
        $this->code[77] = "413111"; // "m"
        $this->code[78] = "241112"; // "n"
        $this->code[79] = "134111"; // "o"
        $this->code[80] = "111242"; // "p"
        $this->code[81] = "121142"; // "q"
        $this->code[82] = "121241"; // "r"
        $this->code[83] = "114212"; // "s"
        $this->code[84] = "124112"; // "t"
        $this->code[85] = "124211"; // "u"
        $this->code[86] = "411212"; // "v"
        $this->code[87] = "421112"; // "w"
        $this->code[88] = "421211"; // "x"
        $this->code[89] = "212141"; // "y"
        $this->code[90] = "214121"; // "z"
        $this->code[91] = "412121"; // "{"
        $this->code[92] = "111143"; // "|"
        $this->code[93] = "111341"; // "}"
        $this->code[94] = "131141"; // "~"
        $this->code[95] = "114113"; // 95
        $this->code[96] = "114311"; // 96
        $this->code[97] = "411113"; // 97
        $this->code[98] = "411311"; // 98
        $this->code[99] = "113141"; // 99
        $this->code[100] = "114131"; // 100
        $this->code[101] = "311141"; // 101
        $this->code[102] = "411131"; // 102
    }

    /**
    * Return the Code128 code for a character
    */
    function getCharCode($c) {
        $retval = $this->code[ord($c) - 32];
        return $retval;
    }

    /**
    * Return the Start Code for Code128
    */
    function getStartCode() {
        return '211214';
    }

    /**
    * Return the Stop Code for Code128
    */
    function getStopCode() {
        return '2331112';
    }

    /**
    * Return the Code128 code equivalent of a character number
    */
    function getNumCode($index) {
        $retval = $this->code[$index];
        return $retval;
    }

    /**
    * Return the Code128 numerical equivalent of a character.
    */
    function getCharNumber($c) {
        $retval = ord($c) - 32;
        return $retval;
    }

} // class
?>