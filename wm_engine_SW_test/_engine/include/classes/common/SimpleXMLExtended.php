<?PHP

	namespace Wing\common;

	class SimpleXMLExtended extends \SimpleXMLElement {
		public function addCData($cdata_text) {
			$node = dom_import_simplexml($this);
			$no = $node->ownerDocument;
			$node->appendChild($no->createCDATASection($cdata_text));
		}

		public function val($name) {
			return (is_object($this->{$name}) == true) ? $this->{$name}->__toString() : false;
		}

		public function attr($name) {
			return (is_object($this->attributes()->{$name}) == true) ? $this->attributes()->{$name}->__toString() : false;
		}
	}

?>