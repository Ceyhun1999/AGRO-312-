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
 File: plugins.class.php
-----------------------------------------------------
 Use: DLE Plugins Loader
=====================================================
*/

define('PLUGINS_READ_ONLY', false);

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

@include_once (ENGINE_DIR . '/data/config.php');

if ( !$config['version_id'] ) {

	if ( file_exists(ROOT_DIR . '/install.php') AND !file_exists(ENGINE_DIR . '/data/config.php') ) {

		header( "Location: ".str_replace(basename($_SERVER['PHP_SELF']),"install.php",$_SERVER['PHP_SELF']) );
		die ( "Datalife Engine not installed. Please run install.php" );

	} else {

		die ( "Datalife Engine not installed. Please run install.php" );
	}

}

@ini_set('pcre.recursion_limit', 10000000 );
@ini_set('pcre.backtrack_limit', 10000000 );
@ini_set('pcre.jit', '0');


if( isset($config['display_php_errors']) AND $config['display_php_errors'] ) {
	@ini_set('display_errors', '1');
	@ini_set('display_startup_errors', '1');
	@ini_set('html_errors', '0');
} else {
	@ini_set('display_errors', '0');
	@ini_set('display_startup_errors', '0');
}

require_once (ENGINE_DIR . '/classes/mysql.php');
require_once (ENGINE_DIR . '/data/dbconfig.php');

spl_autoload_register(function ($class_name) {
	
	switch ($class_name) {
		case 'DLEFiles':
		case 'thumbnail':
			include_once ENGINE_DIR . '/classes/composer/vendor/autoload.php';
			break;
	}

	switch ($class_name) {
		case 'DLESEO':	
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/seo.class.php'));
			break;
		case 'DLEFiles':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/filesystem.class.php'));
			break;
		case 'DLE_Comments':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/comments.class.php'));
			break;
		case 'thumbnail':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/thumb.class.php'));
			break;
		case 'dle_mail':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/mail.class.php'));
			break;
		case 'Detection\MobileDetect':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/mobiledetect.class.php'));
			break;
		case 'antivirus':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/antivirus.class.php'));
			break;
		case 'dle_template':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/templates.class.php'));
			break;
		case 'ParseFilter':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php'));
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
			break;
		case 'ReCaptcha':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/recaptcha.php'));
			break;
		case 'SocialAuth':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/social.class.php'));
			break;
		case 'StopSpam':
			include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/stopspam.class.php'));
			break;
	}
	
});

abstract class DLEPlugins {
	
	public static $protected_files = array("engine/classes/mysql.php", "engine/classes/plugins.class.php", "engine/ajax/controller.php", "engine/data/config.php", "engine/data/dbconfig.php", "engine/data/socialconfig.php", "engine/data/videoconfig.php");
	
	private static $min_dle_version = '13.0';
	private static $plugins 		= null;
	private static $root 			= null;

	
	public static function Check($source) {
		
		if( !is_array( self::$plugins ) ) {
			self::$root = ROOT_DIR.'/';
			self::pluginsstartup();
		}
		
		$check_file = str_ireplace(self::$root, '', (string)$source);

		if( DIRECTORY_SEPARATOR !== '/' ) {
			$check_file = str_replace(DIRECTORY_SEPARATOR, '/', $check_file);
		}

		if( isset(self::$plugins[$check_file]) ) {

			if( file_exists( ENGINE_DIR.'/cache/system/plugins/'.self::$plugins[$check_file] ) ) {
				
				return ENGINE_DIR.'/cache/system/plugins/'.self::$plugins[$check_file];
				
			} else return $source;
			
		} else return $source;
		
	}
	
	private static function pluginsstartup() {
		global $config;
		
		self::$plugins = array();

		if( version_compare($config['version_id'], self::$min_dle_version, '<') ) return;
		
		if( !$config['allow_plugins'] ) return;
		
		self::$plugins = self::getcache();
		
		if ( !is_array(self::$plugins) ) self::loadplugins();
		
	}
	
	private static function loadplugins() {
		global $db, $config;
		
		self::$plugins = array();
		$files = $bad_plugins = $found_plugins = $first_sort = $second_sort = array();

		$db->query( "DELETE FROM " . PREFIX . "_plugins_logs WHERE type = 'file'", false );
		
		if( !is_dir( ENGINE_DIR . "/cache/system/plugins" ) ) {
				
			@mkdir( ENGINE_DIR . "/cache/system/plugins", 0777 );
			@chmod( ENGINE_DIR . "/cache/system/plugins", 0777 );
	
		}
	
		if( !is_dir( ENGINE_DIR . "/cache/system/plugins") OR !is_writable( ENGINE_DIR . "/cache/system/plugins" )) {
			
			$db->query( "INSERT INTO " . PREFIX . "_plugins_logs (plugin_id, area, error, type) values ('0', 'Problem with folder /engine/cache/system/plugins/', 'Unable to save plugins to /engine/cache/system/plugins/. Please check CHMOD, and set CHMOD 777 to folders /engine/cache/system/ and  /engine/cache/system/plugins/', 'file')", false );
			return;
		}
		
		if( version_compare($config['version_id'], '13.3', '<') ) {
			$db->query( "SELECT id FROM " . PREFIX . "_plugins ORDER BY id ASC", false );
		} else {
			$db->query( "SELECT id, needplugin FROM " . PREFIX . "_plugins ORDER BY posi DESC, id ASC", false );
		}

		
		while ( $row = $db->get_row() ) {
			$found_plugins[] = $row['id'];
			if(!$row['needplugin']) $first_sort[] = $row['id']; else $second_sort[] = $row['id'];
		}
		
		if( count($found_plugins) > 1 ) {
			
			$sort = implode( ",", array_merge($first_sort, $second_sort) );
			$sort = "FIND_IN_SET(plugin_id, '".$sort."'), ";
			
		} else $sort = "";

		
		$db->free();

		if( count($found_plugins) ) {
			
			$db->query( "SELECT * FROM " . PREFIX . "_plugins_files WHERE active='1' ORDER BY {$sort}id ASC", false );
				
			while ( $row = $db->get_row() ) {
				
				if ( !in_array( $row['plugin_id'], $found_plugins ) ) {
					$bad_plugins[] = $row['id'];
					continue;
				}
				
				if( !$row['filedisable'] ) {
					continue;
				}
				
				if( $row['filedleversion'] AND $row['fileversioncompare']) {
					if( !version_compare($config['version_id'], $row['filedleversion'], $row['fileversioncompare']) ) continue;
				}
				
				$files[$row['file']][] = array('id'=> $row['plugin_id'], 'action_id'=> $row['id'], 'action' => $row['action'], 'searchcode' => $row['searchcode'], 'replacecode' => $row['replacecode'], 'searchcount' => intval($row['searchcount']), 'replacecount' => intval($row['replacecount']) );
			}
			
			$db->free();
	
			if ( count($bad_plugins) ) {
				$db->query( "DELETE FROM " . PREFIX . "_plugins_files WHERE id IN ('" . implode("','", $bad_plugins) . "')");
			}
			
		}
		
		if( count($db->query_errors_list) ) {
			$db->query_errors_list = array();
		}
		
		if( count($files) ) {
			
			foreach($files as $filename => $mods) {
				
				if( count($mods) ) {
					
					if( file_exists( self::$root.$filename ) ) {
						$content = file_get_contents( self::$root.$filename );
					} else $content = '';
				
					foreach($mods as $mod) {
						$content = self::applymod($filename, $content, $mod);
					}
					
					if($content) {
						
						$store_key = md5(SECURE_AUTH_KEY.$filename).'.php';
						@file_put_contents (ENGINE_DIR . "/cache/system/plugins/" . $store_key, $content, LOCK_EX);
						@chmod( ENGINE_DIR . "/cache/system/plugins/" . $store_key, 0666 );
						
						self::$plugins[$filename] = $store_key;
					}
				}
				
			}
			
		}
		
		self::setcache(self::$plugins);
	}
	
	private static function applymod($filename, $content, $mod) {
		global $config, $db;

		switch ( $mod['action'] ) {
			
			case "replace":
				
				$search = self::prepare_search($mod['searchcode']);

				if( preg_match($search, $content) ) {

					$counter = 0;
					$rep_counter = 0;

					$content = preg_replace_callback($search,
						function ($matches) use ($mod, &$counter, &$rep_counter) {
							
							$counter ++;
							
							if ($mod['replacecount'] AND $counter < $mod['replacecount']) {
								
								return $matches[0];
							
							} else {
								
								$rep_counter ++;
								
								if(!$mod['searchcount'] OR $rep_counter <= $mod['searchcount'] ) {
									
									return $mod['replacecode'];
								
								} else return $matches[0];
							}

						} ,$content);
					
				} else {
					
					$db->query( "INSERT INTO " . PREFIX . "_plugins_logs (plugin_id, area, error, type, action_id) values ('{$mod['id']}', '".$db->safesql( $filename )."', '".$db->safesql( htmlspecialchars( $mod['searchcode'], ENT_QUOTES, $config['charset'] ), false)."', 'file', '{$mod['action_id']}')" );

				}
				
			break;
		
			case "before":
				
				$search = self::prepare_search($mod['searchcode']);
				
				if( preg_match($search, $content) ) {

					$counter = 0;
					$rep_counter = 0;

					$content = preg_replace_callback($search,
						function ($matches) use ($mod, &$counter, &$rep_counter) {
							
							$counter ++;
							
							if ($mod['replacecount'] AND $counter < $mod['replacecount']) {
								
								return $matches[0];
							
							} else {
								
								$rep_counter ++;
								
								if(!$mod['searchcount'] OR $rep_counter <= $mod['searchcount'] ) {
									
									return $mod['replacecode']."\n".$matches[0];
								
								} else return $matches[0];
							}

						} ,$content);
					
				} else {
					
					$db->query( "INSERT INTO " . PREFIX . "_plugins_logs (plugin_id, area, error, type, action_id) values ('{$mod['id']}', '".$db->safesql( $filename )."', '".$db->safesql( htmlspecialchars( $mod['searchcode'], ENT_QUOTES, $config['charset'] ), false )."', 'file', '{$mod['action_id']}')" );

				}

			break;

			case "after":
				
				$search = self::prepare_search($mod['searchcode']);
				
				if( preg_match($search, $content) ) {

					$counter = 0;
					$rep_counter = 0;

					$content = preg_replace_callback($search,
						function ($matches) use ($mod, &$counter, &$rep_counter) {
							
							$counter ++;
							
							if ($mod['replacecount'] AND $counter < $mod['replacecount']) {
								
								return $matches[0];
							
							} else {
								
								$rep_counter ++;
								
								if(!$mod['searchcount'] OR $rep_counter <= $mod['searchcount'] ) {
									
									return $matches[0]."\n".$mod['replacecode'];
								
								} else return $matches[0];
							}

						} ,$content);
					
				} else {
					
					$db->query( "INSERT INTO " . PREFIX . "_plugins_logs (plugin_id, area, error, type, action_id) values ('{$mod['id']}', '".$db->safesql( $filename )."', '".$db->safesql( htmlspecialchars( $mod['searchcode'], ENT_QUOTES, $config['charset'] ), false )."', 'file', '{$mod['action_id']}')" );

				}
				
			break;
		
			case "replaceall":
			case "create":
				$content = $mod['replacecode'];
			break;
		
		}
		
		return $content;
	}
	
	private static function prepare_search( $code ) {
		
		$safe_code = array();
		$codes = explode("\n", trim($code));
		
		foreach($codes as $code) {
			if( trim($code) ) {
				$safe_code[] = preg_replace( "/\s+/u", "\s*", preg_quote( trim($code), '#') );
			}
		}
		
		$safe_code = "#".implode("\s*", $safe_code)."#siu";

		return $safe_code;
	}
	
	private static function getcache() {
		
		if( file_exists(  ENGINE_DIR . '/cache/system/plugins.php' ) ) {
			
			$data = file_get_contents( ENGINE_DIR . '/cache/system/plugins.php' );
			
		} else return false;
	
		if ( $data ) {
	
			$data = json_decode( $data, true );
			if ( is_array($data) ) return $data;
	
		} 

		return false;
	
	}
	
	private static function setcache( $data ) {
		
		if ( is_array($data) ) {
			
			@file_put_contents(ENGINE_DIR . '/cache/system/plugins.php', json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), LOCK_EX);
			@chmod( ENGINE_DIR . '/cache/system/plugins.php', 0666 );
			
		}
	
	}
	
}
