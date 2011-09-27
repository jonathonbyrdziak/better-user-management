<?php 
/**
 * @Author	Nicholas Horlocker
 * @link http://www.5twentystudios.com
 * @Package Wordpress
 * @SubPackage Better User Management
 * @Since 1.0.0
 * @copyright  Copyright (C) 2011 5Twenty Studios
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");

class xmlDataManagement {
	
	/**
	 * 
	 * Constructor class
	 * @param string $name
	 */
	function __construct( $name )
	{
		$this->name = $name;
		$this->file_name = dirname(__file__).DS.'xml-data'.DS.$this->name.'.xml';
	}
	
	/**
	 * 
	 * Convert array to XML
	 * @param array $xml
	 * @param unknown_type $child
	 */
	function array_to_xml( $xml = array(), $child = false )
	{
		$xml->asXML( $this->file_name );
    	chmod( $this->file_name, 0777 );
    	return true;
	}
	
	function load_file()
	{
		//if the file exists return it otherwise create an empty xml and return that
		if( file_exists( $this->file_name ) )
		{
			$return = simplexml_load_file( $this->file_name );
		}
		else
		{
			$xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<data>
</data>
XML;
			$return = simplexml_load_string( $xml );
		}
		
		return $return;
	}
	
}
?>