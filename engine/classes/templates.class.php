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
 File: templates.class.php
-----------------------------------------------------
 Use: Templates class
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class dle_template {
	
	public $dir = '';
	public $template = null;
	public $copy_template = null;
	public $desktop = true;
	public $smartphone = false;
	public $tablet = false;
	public $android = false;
	public $ios = false;
	public $data = array ();
	public $block_data = array ();
	public $user_data = array ();
	public $user_block_data = array ();
	public $user_loaded = false;
	public $result = array ('info' => '', 'vote' => '', 'speedbar' => '', 'content' => '' );
	public $allow_php_include = true;
	public $include_mode = 'tpl';
	public $category_tree = false;
	private $category_parents = null;
	public $news_mode = false;
	public $is_custom = false;
	public $if_array = array ();

	public $js_array = array ();
	public $css_array = array ();
	public $onload_scripts = array ();

	public $template_parse_time = 0;
	private $subloadcount = 0;

    function __construct(){

		$this->dir = ROOT_DIR . '/templates/';

		$mobile_detect = new \Detection\MobileDetect;

		if ( $mobile_detect->isMobile() ) {
			$this->smartphone = true;
			$this->desktop = false;
		}

		if ( $mobile_detect->isTablet() ) {
			$this->smartphone = false;
			$this->desktop = false;
			$this->tablet = true;
		}
		
		if( $mobile_detect->isiOS() ){
			$this->ios = true;
		}

		if( $mobile_detect->isAndroidOS() ){
			$this->android = true;
		}

	}
	
	function set($name, $var) {
		
		if( is_array( $var ) ) {
			if( count( $var ) ) {
				foreach ( $var as $key => $key_var ) {
					$this->set( $key, $key_var );
				}
			}
			return;
		}
		
		$var = str_replace(array("{", "["),array("_&#123;_", "_&#91;_"), (string)$var);
			
		$this->data[$name] = $var;
		
	}
	
	function set_block($name, $var) {
		
		if( is_array( $var ) ) {
			if( count( $var ) ) {
				foreach ( $var as $key => $key_var ) {
					$this->set_block( $key, $key_var );
				}
			}
			return;
		}
		
		$var = str_replace(array("{", "["),array("_&#123;_", "_&#91;_"), $var);
			
		$this->block_data[$name] = $var;
	}
	
	function compile_global_tags( $content ) {
		global $PHP_SELF, $category_id, $cat_info, $page_header_info, $config, $_CLOUDSTAG;

		if( $this->subloadcount > 200 ) {
			return 'Max 200 subtemplates files allowed';
		}

		if ( !is_string($content) ) {
			return '';
		}
		
		if (strpos ( $content, "{*" ) !== false) {
			$content = preg_replace("'\\{\\*(.*?)\\*\\}'si", '', $content);
		}

		$content = str_ireplace("{cache-id}", $config['cache_id'], $content);

		if (stripos ( $content, "page-title" ) !== false OR stripos( $content, "page-description" ) !== false) {
			
			if( !isset($page_header_info['title']) ) $page_header_info['title'] = "";
			if( !isset($page_header_info['description']) ) $page_header_info['description'] = "";
			
			$content = str_ireplace( array('{page-title}', '{page-description}'), array($page_header_info['title'], $page_header_info['description']), $content );
		
			if( $page_header_info['title'] ) {
				$content = preg_replace( "'\\[not-page-title\\](.*?)\\[/not-page-title\\]'is", "", $content );
				$content = str_ireplace( "[page-title]", "", $content );
				$content = str_ireplace( "[/page-title]", "", $content );
			} else {
				$content = preg_replace( "'\\[page-title\\](.*?)\\[/page-title\\]'is", "", $content );
				$content = str_ireplace( "[not-page-title]", "", $content );
				$content = str_ireplace( "[/not-page-title]", "", $content );
			}
			if( $page_header_info['description'] ) {
				$content = preg_replace( "'\\[not-page-description\\](.*?)\\[/not-page-description\\]'is", "", $content );
				$content = str_ireplace( "[page-description]", "", $content );
				$content = str_ireplace( "[/page-description]", "", $content );
			} else {
				$content = preg_replace( "'\\[page-description\\](.*?)\\[/page-description\\]'is", "", $content );
				$content = str_ireplace( "[not-page-description]", "", $content );
				$content = str_ireplace( "[/not-page-description]", "", $content );
			}
		}
		
		$content = $this->check_module($content);
		
		if (stripos ( $content, "[group=" ) !== false OR stripos ( $content, "[not-group=" ) !== false) {
			$content = $this->check_group($content);
		}
		
		if( defined( 'NEWS_ID' ) AND !$this->is_custom) $content = str_ireplace( "{news-id}", NEWS_ID, $content );
		
		if (stripos ( $content, "{cloudstag}" ) !== false) {

			if( $_CLOUDSTAG AND !$this->is_custom) $content = str_ireplace( "{cloudstag}", $_CLOUDSTAG, $content );
			else $content = str_ireplace( "{cloudstag}", '', $content );

		}
		
		$content = preg_replace_callback ( "#\\[script\\](.*?)\\[/script\\]#is", array( &$this, 'add_js_script'), $content );
		
		$page = isset($_GET['cstart']) ? intval($_GET['cstart']) : 1;

		if ( $page < 1 ) $page = 1;
		
		$content = str_ireplace( "{page-count}", $page, $content );

		if (stripos ( $content, "[page-count=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(page-count)=(.+?)\\](.*?)\\[/page-count\\]#is", array( &$this, 'check_page'), $content );
		}

		if (stripos ( $content, "[not-page-count=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-page-count)=(.+?)\\](.*?)\\[/not-page-count\\]#is", array( &$this, 'check_page'), $content );
		}

		if (stripos ( $content, "[tags=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(tags)=(.+?)\\](.*?)\\[/tags\\]#is", array( &$this, 'check_tag'), $content );
		}

		if (stripos ( $content, "[not-tags=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-tags)=(.+?)\\](.*?)\\[/not-tags\\]#is", array( &$this, 'check_tag'), $content );
		}

		if (stripos ( $content, "[news=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(news)=(.+?)\\](.*?)\\[/news\\]#is", array( &$this, 'check_tag'), $content );
		}

		if (stripos ( $content, "[not-news=" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-news)=(.+?)\\](.*?)\\[/not-news\\]#is", array( &$this, 'check_tag'), $content );
		}

		if (stripos ( $content, "[smartphone]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(smartphone)\\](.*?)\\[/smartphone\\]#is", array( &$this, 'check_device'), $content );
		}

		if (stripos ( $content, "[not-smartphone]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-smartphone)\\](.*?)\\[/not-smartphone\\]#is", array( &$this, 'check_device'), $content );
		}

		if (stripos ( $content, "[tablet]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(tablet)\\](.*?)\\[/tablet\\]#is", array( &$this, 'check_device'), $content );
		}

		if (stripos ( $content, "[not-tablet]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-tablet)\\](.*?)\\[/not-tablet\\]#is", array( &$this, 'check_device'), $content );
		}

		if (stripos ( $content, "[desktop]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(desktop)\\](.*?)\\[/desktop\\]#is", array( &$this, 'check_device'), $content );
		}

		if (stripos ( $content, "[not-desktop]" ) !== false) {
			$content = preg_replace_callback ( "#\\[(not-desktop)\\](.*?)\\[/not-desktop\\]#is", array( &$this, 'check_device'), $content );
		}
		
		if (stripos ( $content, "android]" ) !== false) {
			
			if($this->android) {
				
				$content = str_ireplace( '[android]', "", $content );
				$content = str_ireplace( '[/android]', "", $content );
				$content = preg_replace( "#\[not-android\](.+?)\[/not-android\]#is", "", $content );
				
			} else {
				
				$content = str_ireplace( '[not-android]', "", $content );
				$content = str_ireplace( '[/not-android]', "", $content );
				$content = preg_replace( "#\[android\](.+?)\[/android\]#is", "", $content );
				
			}
			
		}
		
		if (stripos ( $content, "ios]" ) !== false ) {
			
			if($this->ios) {
				
				$content = str_ireplace( '[ios]', "", $content );
				$content = str_ireplace( '[/ios]', "", $content );
				$content = preg_replace( "#\[not-ios\](.+?)\[/not-ios\]#is", "", $content );
				
			} else {
				
				$content = str_ireplace( '[not-ios]', "", $content );
				$content = str_ireplace( '[/not-ios]', "", $content );
				$content = preg_replace( "#\[ios\](.+?)\[/ios\]#is", "", $content );
				
			}
			
		}
	
		if (stripos ( $content, "category-" ) !== false) {
			
			$cat_id = intval($category_id);
			
			if( $cat_id ) {
				
				$content = str_ireplace( "{category-id}", $cat_id, $content );
				$content = str_ireplace( "{category-title}", $cat_info[$cat_id]['name'], $content );
				$content = str_ireplace( "{category-description}", $cat_info[$cat_id]['fulldescr'], $content );
				
				if( $cat_info[$cat_id]['fulldescr'] ) {

					$content = str_ireplace('[category-description]', "", $content);
					$content = str_ireplace('[/category-description]', "", $content);
					$content = preg_replace("#\[not-category-description\](.+?)\[/not-category-description\]#is", "", $content);

				} else {
					$content = str_ireplace('[not-category-description]', "", $content);
					$content = str_ireplace('[/not-category-description]', "", $content);
					$content = preg_replace("#\[category-description\](.+?)\[/category-description\]#is", "", $content);

				}

				if ( !$this->is_custom AND !$this->news_mode ) {
					if( $config['allow_alt_url'] ) $content = str_ireplace( "{category-url}", $config['http_home_url'] . get_url( $cat_id ) . "/", $content );
					else $content = str_ireplace( "{category-url}", "$PHP_SELF?do=cat&category={$cat_info[$cat_id]['alt_name']}", $content );
	
					if( $cat_info[$cat_id]['icon'] ) {
						
						$content = str_ireplace( "{category-icon}", $cat_info[$cat_id]['icon'], $content );
						$content = str_ireplace( '[category-icon]', "", $content );
						$content = str_ireplace( '[/category-icon]', "", $content );
						$content = preg_replace( "#\[not-category-icon\](.+?)\[/not-category-icon\]#is", "", $content );
				
						
					} else {
						
						$content = str_ireplace( "{category-icon}", '{THEME}/dleimages/no_icon.gif', $content );
						$content = str_ireplace( '[not-category-icon]', "", $content );
						$content= str_ireplace( '[/not-category-icon]', "", $content );
						$content = preg_replace( "#\[category-icon\](.+?)\[/category-icon\]#is", "", $content );
						
					}
				}


			} else {
				
				$content = str_ireplace( "{category-id}", '', $content );
				$content = str_ireplace( "{category-title}", '', $content );
				$content = str_ireplace( "{category-description}", '', $content );

				
			}

		}
		
		if (stripos ( $content, "{catmenu" ) !== false) {
			$content = preg_replace_callback ( "#\\{catmenu(.*?)\\}#is", array( &$this, 'build_cat_menu'), $content );
		}
		
		if (stripos ( $content, "{catnewscount" ) !== false) {
			$content = preg_replace_callback ( "#\\{catnewscount id=['\"](.+?)['\"]\\}#i", array( &$this, 'catnewscount'), $content );
		}
		
		if( stripos( $content, "{include file=" ) !== false ) {
			$this->include_mode = 'tpl';			
			$content = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#i", array( &$this, 'load_file'), $content );
		
		}
		
		return $content;
		
	}
	
	function load_template($tpl_name) {
		global $PHP_SELF, $category_id, $cat_info, $page_header_info, $config;
		
		$time_before = $this->get_real_time();
		$this->subloadcount = 0;

		if( !$this->user_loaded ) {
			$this->buld_user_data();
		}

		$tpl_name = str_replace(chr(0), '', (string)$tpl_name);

		$file_path = cleanpath(dirname($tpl_name));
		
		$url = parse_url ( $tpl_name );
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);
		$type = explode( ".", $tpl_name );
		$type = strtolower( end( $type ) );

		if ($type != "tpl") {
			$this->template = "Not Allowed Template Name: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";

		}

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		if( stripos ( $tpl_name, ".php" ) !== false ) {
			$this->template = "Not Allowed Template Name: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";
		}

		if( $tpl_name == '' || !file_exists( $this->dir . "/" . $tpl_name ) ) {
			$this->template = "Template not found: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";
		}
		
		$this->copy_template = $this->template = $this->compile_global_tags( file_get_contents( $this->dir . "/" . $tpl_name ) ) ;
		
		$this->template_parse_time += $this->get_real_time() - $time_before;
		
		return true;
	}

	function load_file( $matches=array() ) {
		global $db, $is_logged, $member_id, $cat_info, $config, $user_group, $category_id, $_TIME, $lang, $smartphone_detected, $dle_module;

		$name = trim($matches[1]);

		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '..', '', $name );
		$name = str_replace(array('/', '\\'), '/', $name);

		$url = @parse_url ($name);
		$type = explode( ".", $url['path'] );
		$type = strtolower( end( $type ) );
		
		if( $type == "js" ) {
			$this->js_array[] = $name;
			return '';
		}

		if( $type == "css" ) {
			$this->css_array[] = $name;
			return '';
		}
		
		if ($type == "tpl") {
			$this->subloadcount ++;
			return $this->sub_load_template( $name );

		}

		if ($this->include_mode == "php") {

			if ( !$this->allow_php_include ) return;

			if ($type != "php") return "To connect permitted only files with the extension: .tpl or .php";

			$file_path = ROOT_DIR."/".cleanpath(dirname($url['path']));	
			$url['path'] = clearfilepath( trim($url['path']) , array ("php") );
			
			if (substr ( $file_path, - 1, 1 ) == '/') $file_path = substr ( $file_path, 0, - 1 );

			$file_name = pathinfo($url['path']);
			$file_name = $file_name['basename'];
			$antivirus = new antivirus();

			if ( stristr ( php_uname( "s" ) , "windows" ) === false )
				$chmod_value = @decoct(@fileperms($file_path)) % 1000;
				
			if(!$file_name)
				return "Include files from root directory is denied";
			
			if(in_array("./" . $url['path'], $antivirus->good_files))
				return "Include standart DLE files is denied";
			
			if ( stristr ( dirname ($url['path']) , "uploads" ) !== false )
				return "Include files from directory /uploads/ is denied";

			if ( stristr ( dirname ($url['path']) , "templates" ) !== false )
				return "Include files from directory /templates/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/data" ) !== false )
				return "Include files from directory /engine/data/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/cache" ) !== false )
				return "Include files from directory /engine/cache/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/inc" ) !== false )
				return "Include files from directory /engine/inc/ is denied";
		
			if ($chmod_value == 777 ) return "File {$url['path']} is in the folder, which is available to write (CHMOD 777). For security purposes the connection files from these folders is impossible. Change the permissions on the folder that it had no rights to the write.";

			if ( !file_exists(DLEPlugins::Check($file_path."/".$file_name)) ) return "File {$url['path']} not found.";

			$url['query'] = str_ireplace(array("file_path","file_name", "dle_login_hash", "_GET","_FILES","_POST","_REQUEST","_SERVER","_COOKIE","_SESSION") ,"Filtered", $url['query'] );

			if( substr_count ($this->template, "{include file=") < substr_count ($this->copy_template, "{include file=")) return "Filtered";

			if ( isset($url['query']) AND $url['query'] ) {

				$module_params = array();

				parse_str( $url['query'], $module_params );

				extract($module_params, EXTR_SKIP);

				unset($module_params);
				

			}

			ob_start();
			
			$tpl = clone $this;
			$tpl->template = $tpl->copy_template = '';
			$tpl->block_data = $tpl->data = $tpl->result = [];
			
			include (DLEPlugins::Check($file_path."/".$file_name));
			return ob_get_clean();

		}

		return $matches[0];
	}
	
	function sub_load_template( $tpl_name ) {
		global $PHP_SELF, $category_id, $cat_info, $page_header_info, $config;

		$tpl_name = str_replace(chr(0), '', (string)$tpl_name);

		$file_path = cleanpath(dirname($tpl_name));
		
		if (strpos($tpl_name, '/templates/') === 0) $file_path = '/'.$file_path;
		
		$url = @parse_url($tpl_name);
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);
		$type = explode( ".", $tpl_name );
		$type = strtolower( end( $type ) );
		
		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		if ($type != "tpl") {

			return "Not Allowed Template Name: ". $tpl_name;

		}

		if (strpos($tpl_name, '/templates/') === 0) {

			$tpl_name = str_replace('/templates/','',$tpl_name);
			$templatefile = ROOT_DIR . '/templates/'.$tpl_name;

		} else $templatefile = $this->dir . "/" . $tpl_name;

		if( $tpl_name == '' || !file_exists( $templatefile ) ) {

			$templatefile = str_replace(ROOT_DIR,'',$templatefile);

			return "Template not found: " . $templatefile;

		}

		if( stripos ( $templatefile, ".php" ) !== false ) return "Not Allowed Template Name: ". $tpl_name;

		$template = $this->compile_global_tags( file_get_contents( $templatefile ) );
		
		return $template;
	}

	function check_module($matches) {
		global $dle_module;
		
		$regex = '/\[(aviable|available|not-aviable|not-available)=(.*?)\]((?>(?R)|.)*?)\[\/\1\]/is';

		if (is_array($matches)) {
			
			$aviable = $matches[2];
			$block = $matches[3];
			
			if ($matches[1] == "aviable" OR $matches[1] == "available") $action = true; else $action = false;
			
			$aviable = explode( '|', $aviable );
			
			if( $action ) {
				
				if( ! (in_array( $dle_module, $aviable )) and ($aviable[0] != "global") ) $matches = '';
				else $matches = $block;
			
			} else {
				
				if( (in_array( $dle_module, $aviable )) ) $matches = '';
				else $matches = $block;
			}		
	
		}
	
		return preg_replace_callback($regex, array( &$this, 'check_module'), $matches);
	}

	function check_group( $matches ) {
		global $member_id;

		$regex = '/\[(group|not-group)=(.*?)\]((?>(?R)|.)*?)\[\/\1\]/is';

		if (is_array($matches)) {

			$groups = $matches[2];
			$block = $matches[3];
	
			if ($matches[1] == "group") $action = true; else $action = false;
			
			$groups = explode( ',', $groups );
			
			if( $action ) {
				
				if( ! in_array( $member_id['user_group'], $groups ) ) $matches = ''; else $matches = $block;
			
			} else {
				
				if( in_array( $member_id['user_group'], $groups ) ) $matches = ''; else $matches = $block;
			
			}
		}
		
		return preg_replace_callback($regex, array( &$this, 'check_group'), $matches);
	
	}

	function check_device( $matches=array() ) {

		$block = $matches[2];
		$device = $this->desktop;

		if ($matches[1] == "smartphone" OR $matches[1] == "tablet" OR $matches[1] == "desktop") $action = true; else $action = false;
		if ($matches[1] == "smartphone" OR $matches[1] == "not-smartphone") $device = $this->smartphone;
		if ($matches[1] == "tablet" OR $matches[1] == "not-tablet") $device = $this->tablet;

		if( $action ) {
			
			if( !$device ) return "";
		
		} else {
			
			if( $device ) return "";
		
		}

		return $block;
	}

	function declination( $matches=array() ) {

		$matches[1] = strip_tags($matches[1] );
	    $matches[1] = str_replace(' ', '', $matches[1] );

		$matches[1] = intval($matches[1]);
		$words = explode('|', trim($matches[2]));
		$parts_word = array();

		switch ( count($words) ) {
			case 1:
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0];
				$parts_word[2] = $words[0];
				break;
			case 2:
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0].$words[1];
				$parts_word[2] = $words[0].$words[1];
				break;
			case 3: 
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0].$words[1];
				$parts_word[2] = $words[0].$words[2];
				break;
			case 4: 
				$parts_word[0] = $words[0].$words[1];
				$parts_word[1] = $words[0].$words[2];
				$parts_word[2] = $words[0].$words[3];
				break;
		}
	
		$word = $matches[1]%10==1&&$matches[1]%100!=11?$parts_word[0]:($matches[1]%10>=2&&$matches[1]%10<=4&&($matches[1]%100<10||$matches[1]%100>=20)?$parts_word[1]:$parts_word[2]);
	
		return $word;
	}

	function add_js_script( $matches=array() ) {
		$code = $matches[1];
		
		$code = preg_replace('#<script[^>]*>#i', '', $code);
		$code = trim(str_ireplace('</script>', '', $code));
		
		$this->onload_scripts[] = $code;
		
	}
	
	function check_page( $matches=array() ) {

		$pages = $matches[2];
		$block = $matches[3];

		if ($matches[1] == "page-count") $action = true; else $action = false;
	
		$pages = explode( ',', $pages );
		$page = intval($_GET['cstart']);

		if ( $page < 1 ) $page = 1;
		
		if( $action ) {
			
			if( !$this->_in_rangearray( $page, $pages ) ) return "";
		
		} else {
			
			if( $this->_in_rangearray( $page, $pages ) ) return "";
		
		}
		
		return $block;
	
	}

	function check_tag( $matches=array() ) {
		global $config, $_CLOUDSTAG;

		$params = $matches[2];
		$block = $matches[3];

		if ($matches[1] == "tags" OR $matches[1] == "news") $action = true; else $action = false;
		if ($matches[1] == "tags" OR $matches[1] == "not-tags") $tag = "tags";
		if ($matches[1] == "news" OR $matches[1] == "not-news") $tag = "news";
	
		$props = "";
		$params = trim($params);

		if ( $tag == "news" ) {

			if( defined( 'NEWS_ID' ) ) $props = NEWS_ID;
			$params = explode( ',', $params);
			
			if( $action ) {
				
				if( !$this->_in_rangearray( $props, $params ) ) return "";
			
			} else {
				
				if( $this->_in_rangearray( $props, $params ) ) return "";
			
			}
			
			return $block;
		
		} elseif ( $tag == "tags" ) {
		
			if( $_CLOUDSTAG ) {

				if( function_exists('mb_strtolower') ) {

					$params = mb_strtolower($params, $config['charset']);
					$props = trim(mb_strtolower($_CLOUDSTAG, $config['charset']));

				} else {

					$params = strtolower($params);
					$props = trim(strtolower($_CLOUDSTAG));

				}

			}

			$params = explode( ',', $params);

			if( $action ) {
				
				if( !in_array( $props, $params ) ) return "";
			
			} else {
				
				if( in_array( $props, $params ) ) return "";
			
			}
			
			return $block;
	
		} else return "";
	
	}
	
	function _in_rangearray($findvalue, $findarray) {
	
		$findvalue = trim($findvalue);
	
		foreach ($findarray as $value) {
			
			$value = trim($value);
			
			if( $value == $findvalue ) {
				
				return true;
			
			} elseif( count(explode('-', $value)) == 2 ) {
				
				list($min, $max) = explode('-', $value);
				
				$findvalue = intval($findvalue);
				$min = intval($min);
				$max = intval($max);
				
				if( $findvalue >= $min && $findvalue <= $max ) {
					return true;
				}
				
			}
		}
		
		return false;
	
	}
	
	function catnewscount( $matches=array() ) {
		global $cat_info;
		
		$id = intval($matches[1]);
		
		return intval($cat_info[$id]['newscount']);
	}

	function build_tree( $data ) {

		$tree = array();
		foreach ($data as $id=>&$node) {
			if ($node['parentid'] == 0) {
				$tree[$id] = &$node;
			} else {
				if (!isset($data[$node['parentid']]['children'])) $data[$node['parentid']]['children'] = array();
				$data[$node['parentid']]['children'][$id] = &$node;
			}
		}
		
		return $tree;

	}
	
	function recursive_array_search($needle, $haystack, $subcat = true, &$item = false) {
		
		if(!$item) $item = array();

		foreach($haystack as $key => $value) {

			if(in_array($key, $needle)) {
			
				if( $subcat === "only" ) {

					if(is_array( $value['children'] )) {
						
						foreach($value['children'] as $value2) {
							$item[$value2['id']] = $value2;
						}
						
					}
					
				} else $item[$key] = $value;
				
				if(!$subcat AND is_array( $value['children'] ) ) {
					unset($item[$key]['children']);
					$this->recursive_array_search($needle, $value['children'], $subcat, $item);
				}

			} elseif (is_array( $value['children'] ) ) {
				$this->recursive_array_search($needle, $value['children'], $subcat, $item);
			}
		}
		
		return $item;
	}

	function findParentCategories($categoryId){
		global $cat_info;

		$parentIds = array();

		while (isset($cat_info[$categoryId]) && $cat_info[$categoryId]['parentid']) {
			$parentId = $cat_info[$categoryId]['parentid'];
			$parentIds[] = $parentId;
			$categoryId = $parentId;
		}

		return $parentIds;
	}

	function build_cat_menu( $matches=array() ) {
		global $cat_info, $config;

		if(!count($cat_info)) return "";

		if( !is_array($this->category_tree) ) {
			
			$this->category_tree = $this->build_tree($cat_info);
			
		}
		
		if(!count($this->category_tree)) return "";
		
		$param_str = trim($matches[1]);
		$allow_cache = $config['allow_cache'];
		$config['allow_cache'] = false;
		$catlist = $this->category_tree;
		$cache_id = md5($param_str);
		
		if( $config['category_newscount'] ) $cache_prefix = "news"; else $cache_prefix = "catmenu";
		
		if( preg_match( "#cache=['\"](.+?)['\"]#i", $param_str, $match ) ) {
			if( $match[1] == "yes" ) $config['allow_cache'] = 1;
		}
		
		$content = dle_cache( $cache_prefix, $cache_id, true );
		
		if( $content !== false ) {
			
			$config['allow_cache'] = $allow_cache;
			return $content;
		
		} else {
			
			if( preg_match( "#subcat=['\"](.+?)['\"]#i", $param_str, $match ) ) {
				
				$match[1] = trim($match[1]);
				
				if($match[1] == "yes") $subcat = true; else $subcat = false;
				
				if($match[1] == "only") $subcat = "only";
	
			} else $subcat = true;
			
			if( preg_match( "#id=['\"](.+?)['\"]#i", $param_str, $match ) ) {
	
				$temp_array = array();
		
				$match[1] = explode (',', $match[1]);
		
				foreach ($match[1] as $value) {
		
					if( count(explode('-', $value)) == 2 ) $temp_array[] = get_mass_cats($value);
					else $temp_array[] = intval($value);
		
				}
		
				$temp_array = implode(',', $temp_array);
			
				$catlist= $this->recursive_array_search( explode(',', $temp_array), $catlist, $subcat);
				
				if(!count($catlist)) return "";
				
			}
			
			if( preg_match( "#template=['\"](.+?)['\"]#i", $param_str, $match ) ) {
				$template_name = trim($match[1]);
			} else $template_name = "categorymenu";
	
			$template = $this->sub_load_template( $template_name . '.tpl' );
	
			$template = str_replace( "[root]", "", $template );
			$template = str_replace( "[/root]", "", $template );
			
			if( preg_match( "'\\[sub-prefix\\](.+?)\\[/sub-prefix\\]'si", $template, $match ) ) {
				$prefix = trim($match[1]);
				$template = str_replace( $match[0], "", $template );
			}
			
			if( preg_match( "'\\[sub-suffix\\](.+?)\\[/sub-suffix\\]'si", $template, $match ) ) {
				$suffix = trim($match[1]);
				$template = str_replace( $match[0], "", $template );
			}
			
			if($config['allow_cache']) {
				$template = preg_replace( "'\\[active\\](.+?)\\[/active\\]'si", "", $template );
				$template = str_replace( "[not-active]", "", $template );
				$template = str_replace( "[/not-active]", "", $template );
			}
		
			if( preg_match( "'\\[item\\](.+?)\\[/item\\]'si", $template, $match ) ) {
				$item = trim($match[1]);
				$template = str_replace( $match[0], "{items}", $template );
				
				$template = str_replace( "{items}", $this->compile_menu($catlist, $prefix, $item, $suffix, false, 0), $template );
				
			}
			
			create_cache( $cache_prefix, $template, $cache_id, true);
			
			$config['allow_cache'] = $allow_cache;
			
			return $template;
		
		}

	}

	function compile_menu( $nodes, $prefix, $item_template, $suffix, $sublevelmarker = false ) {
		global $member_id, $user_group;
		
		$item = "";
		
		$allow_list = explode ( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
		
		foreach ($nodes as $node) {
			
			if( !$node['id'] ) continue;

			if ($allow_list[0] != "all") {
				if (!$user_group[$member_id['user_group']]['allow_short'] AND !in_array( $node['id'], $allow_list )) continue;
			}
			
			if ($not_allow_cats[0] != "") {
				if (!$user_group[$member_id['user_group']]['allow_short'] AND in_array( $node['id'], $not_allow_cats )) continue;
			}
			
			$item .= $this->compile_item($node, $item_template);
			
			if (isset($node['children'])) {
				if ( stripos ( $item_template, "{sub-item}" ) !== false ) {
					$item = str_replace( "{sub-item}", $this->compile_menu($node['children'], $prefix, $item_template, $suffix, true), $item );
				} else {
					$item .= $this->compile_menu($node['children'], $prefix, $item_template, $suffix, true);
				}
			}
			
		}
		
		if( $sublevelmarker ) {
			
			$item =  $prefix.$item.$suffix;
			
		}
			
		
		return $item;
	}
	
	function compile_item( $row,  $template) {
		global $config, $category_id;

		$category = intval($category_id);
		
		$template = str_replace( "{id}", $row['id'], $template );
		$template = str_replace( "{name}", $row['name'], $template );
		$template = str_replace( "{description}", $row['fulldescr'], $template );
		
		if( $row['fulldescr'] ) {
			
			$template = str_replace( '[description]', "", $template );
			$template = str_replace( '[/description]', "", $template );
			$template = preg_replace( "#\[not-description\](.+?)\[/not-description\]#is", "", $template );
			
		} else {

			$template = str_replace( '[not-description]', "", $template );
			$template = str_replace( '[/not-description]', "", $template );
			$template = preg_replace( "#\[description\](.+?)\[/description\]#is", "", $template );
			
		}
		
		if( $row['icon'] ) {
			
			$template = str_replace( "{icon}", $row['icon'], $template );
			$template = str_replace( '[category-icon]', "", $template );
			$template = str_replace( '[/category-icon]', "", $template );
			$template = preg_replace( "#\[not-category-icon\](.+?)\[/not-category-icon\]#is", "", $template );
			
		} else {
			$template = str_replace( "{icon}", "{THEME}/dleimages/no_icon.gif", $template );
			$template = str_replace( '[not-category-icon]', "", $template );
			$template = str_replace( '[/not-category-icon]', "", $template );
			$template = preg_replace( "#\[category-icon\](.+?)\[/category-icon\]#is", "", $template );
		}
		
		if( $config['allow_alt_url'] ) {
			$template = str_replace( "{url}", $config['http_home_url'] . get_url( $row['id'] ) . "/" , $template );
		} else {
			$template = str_replace( "{url}", $config['http_home_url'] . "index.php?do=cat&amp;category=".$row['alt_name'] , $template );
		}
		
		$row['newscount'] = isset($row['newscount']) ? intval($row['newscount']) : 0;
		
		$template = str_replace( "{news-count}", $row['newscount'], $template );
		
		if($category AND $this->category_parents === null )  $this->category_parents = $this->findParentCategories($category);

		if( $category AND ( $category == $row['id'] OR (is_array($this->category_parents) AND count($this->category_parents) AND in_array($row['id'], $this->category_parents) ) ) ) {
			$template = str_replace( "[active]", "", $template );
			$template = str_replace( "[/active]", "", $template );
			$template = preg_replace( "'\\[not-active\\](.+?)\\[/not-active\\]'si", "", $template );
		} else {
			$template = str_replace( "[not-active]", "", $template );
			$template = str_replace( "[/not-active]", "", $template );
			$template = preg_replace( "'\\[active\\](.+?)\\[/active\\]'si", "", $template );
		}
		
	    if(!isset($row['children'])) {
			
			$template = str_replace( "{sub-item}", "", $template );
			$template = str_replace( "[not-parent]", "", $template );
			$template = str_replace( "[/not-parent]", "", $template );
			$template = preg_replace( "'\\[isparent\\](.+?)\\[/isparent\\]'si", "", $template );
			
		} else {
			
			$template = str_replace( "[isparent]", "", $template );
			$template = str_replace( "[/isparent]", "", $template );
			$template = preg_replace( "'\\[not-parent\\](.+?)\\[/not-parent\\]'si", "", $template );
			
		}

	    if($row['parentid']) {
			
			$template = str_replace( "[is-children]", "", $template );
			$template = str_replace( "[/is-children]", "", $template );
			$template = preg_replace( "'\\[not-children\\](.+?)\\[/not-children\\]'si", "", $template );
			
		} else {
			
			$template = str_replace( "[not-children]", "", $template );
			$template = str_replace( "[/not-children]", "", $template );
			$template = preg_replace( "'\\[is-children\\](.+?)\\[/is-children\\]'si", "", $template );
			
		}
		
		return $template;
		
	}
	
	function _clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->if_array = array();
		$this->copy_template = $this->template;
	
	}
	
	function clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->copy_template = null;
		$this->template = null;
	
	}
	
	function global_clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->result = array ();
		$this->copy_template = null;
		$this->template = null;
	
	}
	
	function compile($tpl, $compile_if = false, $compile_user_data = true) {
		
		$time_before = $this->get_real_time();
		
		$find = $find_preg = $replace = $replace_preg = array();
		
		if( count( $this->block_data ) ) {
			
			foreach ( $this->block_data as $key_find => $key_replace ) {
				$find_preg[] = $key_find;
				$replace_preg[] = $key_replace;
			}
			
			if( $compile_user_data ) {
				
				foreach ( $this->user_block_data as $key_find => $key_replace ) {
					$find_preg[] = $key_find;
					$replace_preg[] = $key_replace;
				}
				
			}
			
			$this->copy_template = preg_replace( $find_preg, $replace_preg, $this->copy_template );
		}

		foreach ( $this->data as $key_find => $key_replace ) {
			$find[] = $key_find;
			$replace[] = $key_replace;
		}
		
		if( $compile_user_data ) {
			
			foreach ( $this->user_data as $key_find => $key_replace ) {
				$find[] = $key_find;
				$replace[] = $key_replace;
			}	
			
		}
		
		$find[] = "{category-url}";
		$replace[] = '';
		
		$this->copy_template = str_ireplace( $find, $replace, $this->copy_template );

		if (stripos ( $this->copy_template, "[declination=" ) !== false) {
			$this->copy_template = preg_replace_callback ( "#\\[declination=(.+?)\\](.+?)\\[/declination\\]#is", array( &$this, 'declination'), $this->copy_template );
		}
		
		if( stripos( $this->copy_template, "{customcomments" ) !== false ) {		
			$this->copy_template = preg_replace_callback( "#\\{customcomments(.+?)\\}#i", "custom_comments", $this->copy_template );
		
		}
		
		if( stripos( $this->copy_template, "[xfvalue_" ) !== false ) {
			
			$this->copy_template = preg_replace("#\[xfvalue_(.+?)]#i", '', $this->copy_template);
			
		}

		if( $compile_if AND stripos( $this->copy_template, "[if" ) !== false){
		
			$this->copy_template = $this->if_check($this->copy_template);
		}

		if( stripos( $this->copy_template, "{custom" ) !== false ) {		
			$this->copy_template = preg_replace_callback( "#\\{custom(.+?)\\}#i", "custom_print", $this->copy_template );
		
		}
		
		if( stripos( $this->copy_template, "{include file=" ) !== false ) {
			$this->include_mode = 'php';			
			$this->copy_template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#i", array( &$this, 'load_file'), $this->copy_template );
		
		}

		$this->copy_template = str_replace(array("_&#123;_", "_&#91;_"), array("{", "["), $this->copy_template);

		if( isset( $this->result[$tpl] ) ) $this->result[$tpl] .= $this->copy_template;
		else $this->result[$tpl] = $this->copy_template;
		
		$this->_clear();
		
		$this->template_parse_time += $this->get_real_time() - $time_before;
	}

	function buld_user_data() {
		global $PHP_SELF, $member_id, $config, $user_group, $lang, $_IP;

		$this->user_data['{ip}'] = $_IP;

		if( isset($member_id['user_group']) AND $member_id['user_group'] != 5 ) {
			
			if ( count(explode("@", $member_id['foto'])) == 2 ) {
		
				$this->user_data['{foto}'] = 'https://www.gravatar.com/avatar/' . md5(trim($member_id['foto'])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']);
			
			} else {
			
				if( $member_id['foto'] ) {
					
					if (strpos($member_id['foto'], "//") === 0) $avatar = "http:".$member_id['foto']; else $avatar = $member_id['foto'];
		
					$avatar = @parse_url ( $avatar );
		
					if( $avatar['host'] ) {
						
						$this->user_data['{foto}'] = $member_id['foto'];
						
					} else $this->user_data['{foto}'] = $config['http_home_url'] . "uploads/fotos/" . $member_id['foto'] ;
					
				} else $this->user_data['{foto}'] = "{THEME}/dleimages/noavatar.png" ;
		
			}
			
			$this->user_data['{profile-login}'] = stripslashes( $member_id['name'] );
			
			if( $member_id['fullname'] ) {
				
				$this->user_data['[fullname]'] = "";
				$this->user_data['[/fullname]'] = "";
				$this->user_data['{fullname}'] = stripslashes( $member_id['fullname'] );
				$this->user_block_data["'\\[not-fullname\\](.*?)\\[/not-fullname\\]'si"] = "";
			
			} else {
				
				$this->user_block_data["'\\[fullname\\](.*?)\\[/fullname\\]'si"] = "";
				$this->user_data['{fullname}'] = "";
				$this->user_data['[not-fullname]'] = "";
				$this->user_data['[/not-fullname]'] = "";
		
			}
			
			if( $member_id['land'] ) {
				
				$this->user_data['[land]'] =  "";
				$this->user_data['[/land]'] =  "";
				$this->user_data['{land}'] =  stripslashes( $member_id['land'] );
				$this->user_block_data["'\\[not-land\\](.*?)\\[/not-land\\]'si"] = "";
			
			} else {
				
				$this->user_block_data["'\\[land\\](.*?)\\[/land\\]'si"] = "";
				$this->user_data['{land}'] =  "";
				$this->user_data['[not-land]'] =  "";
				$this->user_data['[/not-land]'] =  "";
		
			}
			
			$this->user_data['{mail}'] =  stripslashes( $member_id['email'] );
			$this->user_data['{group}'] =  $user_group[$member_id['user_group']]['group_prefix'].$user_group[$member_id['user_group']]['group_name'].$user_group[$member_id['user_group']]['group_suffix'];
			$this->user_data['{registration}'] =  difflangdate("j F Y, H:i", $member_id['reg_date'] );
			$this->user_data['{lastdate}'] = difflangdate("j F Y, H:i", $member_id['lastdate'] );

			if( $user_group[$member_id['user_group']]['icon'] ) $this->user_data['{group-icon}'] = "<img src=\"" . $user_group[$member_id['user_group']]['icon'] . "\" alt=\"\">";
			else $this->user_data['{group-icon}'] =  "";

			if( $user_group[$member_id['user_group']]['time_limit'] ) {
				
				$this->user_block_data["'\\[time_limit\\](.*?)\\[/time_limit\\]'si"] = "\\1";
				
				if( $member_id['time_limit'] ) {
					
					$this->user_data['{time_limit}'] = langdate("j F Y, H:i", $member_id['time_limit'] );
				
				} else {
					
					$this->user_data['{time_limit}'] = $lang['no_limit'];
				
				}
			
			} else {
				
				$this->user_block_data["'\\[time_limit\\](.*?)\\[/time_limit\\]'si"] = "";
				$this->user_data['{time_limit}'] = "";
			
			}
			
			if( $member_id['comm_num'] ) {
				$this->user_data['[comm-num]'] = "";
				$this->user_data['[/comm-num]'] = "";
				$this->user_data['{comm-num}'] = number_format($member_id['comm_num'], 0, ',', ' ');
				$this->user_data['{comments}'] = "{$PHP_SELF}?do=lastcomments&amp;userid=" . $member_id['user_id'];
				$this->user_block_data["'\\[not-comm-num\\](.*?)\\[/not-comm-num\\]'si"] = "";

			} else {
				$this->user_data['{comments}'] = "";
				$this->user_data['{comm-num}'] = 0;
				$this->user_data['[not-comm-num]'] = "";
				$this->user_data['[/not-comm-num]'] = "";
				$this->user_block_data["'\\[comm-num\\](.*?)\\[/comm-num\\]'si"] = "";
				
			}
			
			if( $member_id['news_num'] ) {
				
				if( $config['allow_alt_url'] ) {
					$this->user_data['{news}'] = $config['http_home_url'] . "user/" . urlencode( $member_id['name'] ) . "/news/";
					$this->user_data['{rss}'] = $config['http_home_url'] . "user/" . urlencode( $member_id['name'] ) . "/rss.xml";
				} else {
					$this->user_data['{news}'] = $PHP_SELF . "?subaction=allnews&amp;user=" . urlencode( $member_id['name'] );
					$this->user_data['{rss}'] = $PHP_SELF . "?mod=rss&amp;subaction=allnews&amp;user=" . urlencode( $member_id['name'] );
				}
		
				$this->user_data['{news-num}'] = number_format($member_id['news_num'], 0, ',', ' ');
				$this->user_data['[news-num]'] = "";
				$this->user_data['[/news-num]'] = "";
				$this->user_block_data["'\\[not-news-num\\](.*?)\\[/not-news-num\\]'si"] = "";
		
			} else {
				
				$this->user_data['{news}'] = "";
				$this->user_data['{rss}'] = "";
				$this->user_data['{news-num}'] = 0;
				$this->user_data['[not-news-num]'] = "";
				$this->user_data['[/not-news-num]'] = "";
				$this->user_block_data["'\\[news-num\\](.*?)\\[/news-num\\]'si"] = "";
		
			}

			if ( $member_id['xfields'] ) {
			
				$xfields = xfieldsload( true );
				$xfieldsdata = xfieldsdataload( $member_id['xfields'] );
			
				foreach ( $xfields as $value ) {
					$preg_safe_name = preg_quote( $value[0], "'" );
					
					if( !isset($xfieldsdata[$value[0]]) ) $xfieldsdata[$value[0]] = "";
					
					if( empty( $xfieldsdata[$value[0]] ) ) {
			
						$this->user_block_data["'\\[profile_xfgiven_{$preg_safe_name}\\](.*?)\\[/profile_xfgiven_{$preg_safe_name}\\]'is"] = "";
						$this->user_data["[profile_xfnotgiven_{$value[0]}]"] = "";
						$this->user_data["[/profile_xfnotgiven_{$value[0]}]"] = "";
			
					} else {
						
						$this->user_block_data["'\\[profile_xfnotgiven_{$preg_safe_name}\\](.*?)\\[/profile_xfnotgiven_{$preg_safe_name}\\]'is"] = "";
						$this->user_data["[profile_xfgiven_{$value[0]}]"] = "";
						$this->user_data["[/profile_xfgiven_{$value[0]}]"] = "";
					}
					
					$this->user_data["[profile_xfvalue_{$value[0]}]"] = stripslashes( $xfieldsdata[$value[0]] );
			
				}
			
			} else {
				
				$this->user_block_data["'\\[profile_xfgiven_(.*?)\\](.*?)\\[/profile_xfgiven_(.*?)\\]'is"] = "";
				$this->user_block_data["'\\[profile_xfvalue_(.*?)\\]'i"] = "";
				$this->user_block_data["'\\[profile_xfnotgiven_(.*?)\\]'is"] = "";
				$this->user_block_data["'\\[/profile_xfnotgiven_(.*?)\\]'is"] = "";
			}

			$this->user_data['{new-pm}'] = $member_id['pm_unread'];
			$this->user_data['{all-pm}'] = $member_id['pm_all'];
			
			if( $member_id['pm_unread'] ) {
				$this->user_data['[new-pm]'] = "";
				$this->user_data['[/new-pm]'] = "";
			} else {
				$this->user_block_data["'\\[new-pm\\](.*?)\\[/new-pm\\]'si"] = "";
			}
			
			if ($member_id['favorites']) {
				$this->user_data['{favorite-count}'] = count(explode("," ,$member_id['favorites']));
			} else $this->user_data['{favorite-count}'] = 0;
	
			if ( $user_group[$member_id['user_group']]['allow_admin'] ) {
				$this->user_data['[admin-link]'] = "";
				$this->user_data['[/admin-link]'] = "";
				$this->user_data['{admin-link}'] = $config['http_home_url'] . $config['admin_path'] . "?mod=main";
			} else {
				$this->user_data['{admin-link}'] = "";
				$this->user_block_data["'\\[admin-link\\](.*?)\\[/admin-link\\]'si"] = "";
			}

			if ($config['allow_alt_url']) {
				$this->user_data['{profile-link}'] = $config['http_home_url'] . "user/" . urlencode ( $member_id['name'] ) . "/";
			} else {
				$this->user_data['{profile-link}'] = $PHP_SELF . "?subaction=userinfo&user=" . urlencode ( $member_id['name'] );
			}

		} else {
			
			$this->user_block_data["'\\[new-pm\\](.*?)\\[/new-pm\\]'si"] = "";
			$this->user_data['{profile-link}'] = "";
			$this->user_data['{admin-link}'] = "";
			$this->user_block_data["'\\[admin-link\\](.*?)\\[/admin-link\\]'si"] = "";
			$this->user_data['{favorite-count}'] = 0;
			$this->user_data['{new-pm}'] = '';
			$this->user_data['{all-pm}'] = '';	
			$this->user_block_data["'\\[profile_xfgiven_(.*?)\\](.*?)\\[/profile_xfgiven_(.*?)\\]'is"] = "";
			$this->user_block_data["'\\[profile_xfvalue_(.*?)\\]'i"] = "";
			$this->user_block_data["'\\[profile_xfnotgiven_(.*?)\\](.*?)\\[/profile_xfnotgiven_(.*?)\\]'is"] = "";
			$this->user_data['{news}'] = "";
			$this->user_data['{rss}'] = "";
			$this->user_data['{news-num}'] = 0;
			$this->user_data['[not-news-num]'] = "";
			$this->user_data['[/not-news-num]'] = "";
			$this->user_block_data["'\\[not-news-num\\](.*?)\\[/not-news-num\\]'si"] = "";
			$this->user_block_data["'\\[comm-num\\](.*?)\\[/comm-num\\]'si"] = "";
			$this->user_data['[not-comm-num]'] = "";
			$this->user_data['[/not-comm-num]'] = "";
			$this->user_data['{comments}'] = "";
			$this->user_data['{comm-num}'] = 0;	
			$this->user_block_data["'\\[time_limit\\](.*?)\\[/time_limit\\]'si"] = "";
			$this->user_data['{time_limit}'] = "";
			$this->user_data['{group-icon}'] =  "";
			$this->user_data['{registration}'] =  "";
			$this->user_data['{registration}'] =  "";
			$this->user_data['{group}'] =  "";
			$this->user_data['{mail}'] =  "";
			$this->user_data['{land}'] =  "";
			$this->user_data['[not-land]'] =  "";
			$this->user_data['[/not-land]'] =  "";
			$this->user_block_data["'\\[land\\](.*?)\\[/land\\]'si"] = "";
			$this->user_data['{profile-login}'] = '';
			$this->user_block_data["'\\[fullname\\](.*?)\\[/fullname\\]'si"] = "";
			$this->user_data['{fullname}'] = "";
			$this->user_data['[not-fullname]'] = "";
			$this->user_data['[/not-fullname]'] = "";
				
			$this->user_data['{foto}'] = "{THEME}/dleimages/noavatar.png";
			$this->user_data['{mail}'] =  "";
			
		}

		$this->user_loaded = true;
	}

	function if_check($matches){
		global $config, $row, $xfields;

		if( count($this->if_array) ) $row = $this->if_array;

		$regex = '/\[if (.+?)\]((?>(?R)|.)*?)\[\/if\]/is';
	
		if (is_array($matches)) {
			
			$matches[1] = trim(dle_strtolower($matches[1], $config['charset']));
			$find_type = true;
			$match_count = 0;
			
			if(stripos($matches[1], " or ")) {
				$find_type = false;
				$if_array = explode(" or ", $matches[1]);
			} else $if_array = explode(" and ", $matches[1]);
			
			foreach ($if_array as $if_str) {
				
				$if_str = trim($if_str);
				
				preg_match("#^(.+?)(!~|~|!=|=|>=|<=|<|>)\s*['\"]?(.*?)['\"]?$#is", $if_str, $m);
				
				$field = trim($m[1]);
				$operator = trim($m[2]);
				$value = trim($m[3]);
				
				$field = explode("xfield_",$field);
				$fieldvalue = '';
				$xf_p = false;

				if(isset($field[1]) AND $field[1]) {

					$fieldvalue = isset($row['xfields_array'][$field[1]]) ? $row['xfields_array'][$field[1]] : '';

					if(  isset($xfields) AND is_array($xfields) AND count($xfields) AND $fieldvalue ) {
						foreach ($xfields as $tmparr) {
							if( $tmparr[0] == $field[1]) {$xf_p = $tmparr; break;}
						}
					}

					if ( is_array($xf_p) ) {

						if ($xf_p[8] OR $xf_p[6] OR $xf_p[3] == "select" OR $xf_p[3] == "image" OR $xf_p[3] == "imagegalery" OR $xf_p[3] == "datetime") {
							$fieldvalue = str_replace(array("&amp;", "&#58;"), array("&", ":"), $fieldvalue);
						}

						if($xf_p[3] == "datetime" AND $xf_p[23] != "2" ) {

							$fieldvalue = strtotime($fieldvalue);

							if (strtotime($value) !== false) {
								$value = strtotime($value);
							}
						}
					}

				}elseif( $field[0]=='date' OR $field[0]=='editdate' OR $field[0]=='lastdate' OR $field[0]=='reg_date' OR $field[0]=='restricted_date') {
					
					$fieldvalue = strtotime( date( "Y-m-d H:i", $row[$field[0]]) );
					
					if( strtotime($value) !== false ) {
						$value = strtotime($value);
					}
					
				} elseif( $field[0]=='tags' AND is_array($row[$field[0]]) ) {
					
					$fieldvalue = array();
					
					foreach ( $row[$field[0]] as $temp_value ) {
						
						$fieldvalue[] = trim(dle_strtolower($temp_value, $config['charset']));
						
					}
					
				} elseif( $field[0]=='category' ) {
					
					$fieldvalue = $row['cats'];
					
				} elseif( isset( $row[$field[0]] ) ) $fieldvalue = $row[$field[0]];
				
				if( !is_array($fieldvalue) ) {
					$fieldvalue = trim(dle_strtolower($fieldvalue, $config['charset']));
				}
				
				switch( $operator ){
					case ">":
						
						if( is_array($fieldvalue) ) {
							
							$found_match = false;
							
							foreach ( $fieldvalue as $temp_value ) {
								
								$temp_value = floatval($temp_value);
								$value = floatval($value);
								
								if($temp_value > $value) {
									$found_match = true;
								}
								
							}
							
							if( $found_match ) $match_count ++;
							
						} else {
							
							$fieldvalue = floatval($fieldvalue);
							$value = floatval($value);
							if($fieldvalue > $value) $match_count ++;
							
						}
						
						break;
					case "<":
						
						if( is_array($fieldvalue) ) {
							
							$found_match = false;
							
							foreach ( $fieldvalue as $temp_value ) {
								
								$temp_value = floatval($temp_value);
								$value = floatval($value);
								
								if($temp_value < $value) {
									$found_match = true;
								}
								
							}
							
							if( $found_match ) $match_count ++;
							
						} else {
							
							$fieldvalue = floatval($fieldvalue);
							$value = floatval($value);
							if($fieldvalue < $value) $match_count ++;
							
						}
						
						break;
					case ">=":
	
						if( is_array($fieldvalue) ) {
							
							$found_match = false;
							
							foreach ( $fieldvalue as $temp_value ) {
								
								$temp_value = floatval($temp_value);
								$value = floatval($value);
								
								if($temp_value >= $value) {
									$found_match = true;
								}
								
							}
							
							if( $found_match ) $match_count ++;
							
						} else {
							
							$fieldvalue = floatval($fieldvalue);
							$value = floatval($value);
							if($fieldvalue >= $value) $match_count ++;
							
						}
						
						break;
					case "<=":
						
						if( is_array($fieldvalue) ) {
							
							$found_match = false;
							
							foreach ( $fieldvalue as $temp_value ) {
								
								$temp_value = floatval($temp_value);
								$value = floatval($value);
								
								if($temp_value <= $value) {
									$found_match = true;
								}
								
							}
							
							if( $found_match ) $match_count ++;
							
						} else {
							
							$fieldvalue = floatval($fieldvalue);
							$value = floatval($value);
							if($fieldvalue <= $value) $match_count ++;
							
						}
	
						break;
					case "!=":
						
						if( is_array($fieldvalue) ) {
							
							if ( !in_array($value, $fieldvalue)) {
								$match_count ++;
							}
							
						} else {
							
							if($fieldvalue != $value) $match_count ++;
							
						}
	
						break;
					
					case "~":
						
						if( is_array($fieldvalue) ) {
							
							foreach ( $fieldvalue as $temp_value ) {
							
								if(dle_strpos($temp_value,$value,$config['charset'])!==false) {
									$match_count ++;
									break;
								}
								
							}
							
						} else {
						
							if(dle_strpos($fieldvalue,$value,$config['charset'])!==false) $match_count ++;
							
						}
						
						break;
					case "!~":
						
						if( is_array($fieldvalue) ) {
							
							$found_count = 0;
							
							foreach ( $fieldvalue as $temp_value ) {
							
								if(dle_strpos($temp_value,$value,$config['charset'])===false) {
									$found_count ++;
								}
								
							}
							
							if( $found_count == count($fieldvalue) ) $match_count ++;
							
						} else {
							
							if(dle_strpos($fieldvalue,$value,$config['charset'])===false) $match_count ++;
							
						}
						
						break;
					default:
						
						if( is_array($fieldvalue) ) {
							
							if ( in_array($value, $fieldvalue)) {
								$match_count ++;
							}
							
						} else {
							
							if($fieldvalue == $value) $match_count ++;
							
						}
				}	
			}
			
			if($match_count AND $match_count == count($if_array) AND $find_type) {
				$matches = $matches[2];
			} elseif ($match_count AND !$find_type) {
				$matches = $matches[2];
			} else $matches = '';
		
		}
		
		return preg_replace_callback($regex, array( &$this, 'if_check'), $matches);
		
	}	
	
	function get_real_time() {
		list ( $seconds, $microSeconds ) = explode( ' ', microtime() );
		return (( float ) $seconds + ( float ) $microSeconds);
	}
}
