<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2023 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: download.class.php
-----------------------------------------------------
 Use: Download files
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class download {
	
	var $properties = array ();
	
	var $range = 0;
	
	function __construct($path, $name, $driver) {

		DLEFiles::init();
			
		if ( !DLEFiles::FileExists( $path, $driver ) ) {
			header( "HTTP/1.1 403 Forbidden" );
			die ( "The file was not found on the server" );
		}
		
		$size = DLEFiles::Size( $path, $driver );
		$type = DLEFiles::MimeType( $path, $driver );

		if ( DLEFiles::$error ){
			header( "HTTP/1.1 403 Forbidden" );
			echo DLEFiles::$error;
			die ();
		}
		
		$this->properties = array ('path' => $path, 'name' => $name, 'disk' => $driver, 'type' => $type, 'size' => $size);
	
	}
	
	function download_file() {

		header( $_SERVER['SERVER_PROTOCOL'] . " 200 OK" );
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header( "Cache-Control: private", false);

		if( $this->properties['type'] ) {
			header( "Content-Type: " . $this->properties['type'] );
		} else {
			header( "Content-Type: application/octet-stream" );
		}

		header( 'Content-Disposition: attachment; filename="' . $this->properties['name'] . '"' );
		header( "Content-Transfer-Encoding: binary" );	
		header( "Content-Length: " . $this->properties['size'] );
		header('Accept-Ranges: bytes');
		header("Connection: close");
 		
		@ini_set( 'max_execution_time', 0 );
		@set_time_limit(0);
		
		$this->_download();
	}
	
	function _download() {

		@ob_end_clean();
		
		$handle = DLEFiles::ReadStream( $this->properties['path'], $this->properties['disk']);
	
		if ( DLEFiles::$error ){
			header( "HTTP/1.1 403 Forbidden" );
			echo DLEFiles::$error;
			die ();
		}
		
		if (is_resource($handle)) {
		
			while ( !feof( $handle ) ) {
				print( fread( $handle, 8192 ) );
				ob_flush();
				flush();
			}
			
			fclose( $handle );
		}
	}

}
