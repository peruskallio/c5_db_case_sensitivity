<?php
class DbSchemaParser {
	
	private $_tableNames = array();
	
	private function _create_parser() {
		$xmlParser = xml_parser_create();
		xml_set_object( $xmlParser, $this );
		
		// Initialize the XML callback functions
		xml_set_element_handler( $xmlParser, '_tag_open', '_tag_close' );
		xml_set_character_data_handler( $xmlParser, '_tag_cdata' );
		
		return $xmlParser;
	}
	
	private function _tag_open( &$parser, $tag, $attributes ) {
		switch( strtoupper( $tag ) ) {
			case 'TABLE':
				$this->addTableName($attributes['NAME']);
			default:
				// Dummy
		};
	}
	
	private function _tag_close( &$parser, $tag ) {
		// Dummy
	}
	
	/**
	* XML Callback to process CDATA elements
	*
	* @access private
	*/
	private function _tag_cdata( &$parser, $cdata ) {
	}
	
	public function addTableName($name) {
		$this->_tableNames[] = $name;
	}
	
	public function getTableNames() {
		return $this->_tableNames;
	}
	
	public function parseSchema($xmlFile) {
		if ( !(file_exists($xmlFile)) ) {
			return false;
		}
		if( !($fp = @fopen( $xmlFile, 'r' )) ) {
			return false;
		}
		
		// Process the file
		$parser = $this->_create_parser();
		while( $data = fread( $fp, 4096 ) ) {
			if( !xml_parse( $parser, $data, feof( $fp ) ) ) {
				die( sprintf(
					"XML error: %s at line %d",
					xml_error_string( xml_get_error_code( $xmlParser) ),
					xml_get_current_line_number( $xmlParser)
				) );
			}
		}
		xml_parser_free( $parser );
		
		$this->_tableNames = array_unique($this->_tableNames);
	}
	
}
?>