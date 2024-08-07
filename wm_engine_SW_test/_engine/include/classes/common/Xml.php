<?PHP

	namespace Wing\common;

	class Xml {
        var $data;
        var $encoding;
        var $arr;
        var $pointer;
        var $index;

		function __construct($data = null,$encoding = "UTF-8") {
			if($data) $this->xmlData($data, $encoding);
		}

        function xmlData($data,$encoding = "") {
            if (strpos($data, 'encoding="EUC-KR"')) {
                $data = mb_convert_encoding($data, 'utf8', 'euckr');
                $data = str_replace('encoding="EUC-KR"', 'encoding="UTF-8"', $data);
            }

			$this->data = $data;
			$this->encoding = $encoding;
			$this->index = 0;
			$this->pointer[] = &$this->arr;

			$xml_parser = xml_parser_create($this->encoding);

			xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
			xml_set_object($xml_parser, $this);
			xml_set_element_handler($xml_parser, "startElement", "endElement");
			xml_set_character_data_handler($xml_parser, "characterData");

			xml_parse($xml_parser, $this->data, true);

			xml_parser_free($xml_parser);
        }

		function startElement($parser, $tag, $attribute) {
			foreach($attribute as $name => $value)	{
				$value = $this->cleanString($value);
				//$object->$name = $value;
			}

            settype($this->pointer[$this->index], 'object');
			$this->pointer[$this->index]->{$tag}[] = $object;
			$size = count($this->pointer[$this->index]->{$tag});
			$this->pointer[] = &$this->pointer[$this->index]->{$tag}[$size-1];

			$this->index++;
		}

		function endElement($parser, $tag) {
			array_pop($this->pointer);
			$this->index--;
		}

		function characterData($parser, $data) {
			$data = trim($data);
			if($data) {
				$encoding = strtolower(mb_detect_encoding(trim($data), 'ascii, utf-8, euckr', true));
				if($encoding != _BASE_CHARSET_) {
					$data = mb_convert_encoding($data, _BASE_CHARSET_, $encoding);
				}
			}
			if(trim($data) != '') $this->pointer[$this->index] .= $data;
		}

		function cleanString($string) {
			$string = trim($string);
			if($string) {
				$encoding = strtolower(mb_detect_encoding(trim($string), 'ascii, utf-8, euckr', true));
				if($encoding != _BASE_CHARSET_) {
					$string = mb_convert_encoding($string, _BASE_CHARSET_, $encoding);
				}
			}
			return $string;
		}
	}

?>