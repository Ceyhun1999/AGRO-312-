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
 File: rss.php
-----------------------------------------------------
 Use: News import
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if(!$is_logged OR !$user_group[$member_id['user_group']]['admin_rss']) {die ("error");}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	die ("error");
	
}

function get_content ($scheme, $host, $path, $query, $others=''){

	if ($scheme != "http" AND $scheme != "https") {
		return false;
	}

	if (function_exists('curl_init')) {

		if ( $query ) $query = "?".$query;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $scheme."://".$host.$path.$query);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_REFERER, $scheme."://".$host.$path.$query);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt($ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($others != '') curl_setopt($ch, CURLOPT_COOKIE, $others);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data) return $data; else return false;

	} else {

	   if (!empty($others)) $others = "Cookie: ".$others."\r\n";
	   else $others = "";

	   $post="GET $path HTTP/1.1\r\nHost: $host\r\nContent-type: application/x-www-form-urlencoded\r\n{$others}User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\nContent-length: ".strlen($query)."\r\nConnection: close\r\n\r\n$query";
	
	   $h=@fsockopen($host,80, $errno, $errstr, 30);

		if (!$h) return false;
	    else {
			fwrite($h,$post);
	    
	         for($a=0,$r='';!$a;){
	            $b=fread($h,8192);
	            $r.=$b;
	            $a=(($b=='')?1:0);
	         }
	
	         fclose($h);
	    }

		return $r;
	}

}

function convert ( $from, $to, $string ) {


	if( function_exists( 'mb_convert_encoding' ) ) {

		return mb_convert_encoding( $string, $to, $from );

	} elseif( function_exists( 'iconv' ) ) {
	
		return @iconv($from, $to, $string);
	
	}

	return $string;
}

	$news_id = intval($_REQUEST['news_id']);
	$rss_id = intval($_REQUEST['rss_id']);
	$link = parse_url(urldecode($_REQUEST['link']));
	$parse = new ParseFilter();
	$parse->leech_mode = true;

	if ($config['allow_admin_wysiwyg']) $parse->allow_code = false;

	$rss = $db->super_query("SELECT * FROM " . PREFIX . "_rss WHERE id='$rss_id'");

	if( !$rss['id'] ){ die ("error"); }

	$rss['cookie'] = str_replace("\n", "; ", str_replace("\r", "", stripslashes(rtrim($rss['cookie']))));

	$content = get_content ($link['scheme'], $link['host'], $link['path'], $link['query'], $rss['cookie']);

	$rss['search'] = addcslashes(stripslashes($rss['search']), "[]!-.?*\\()|");
	$rss['search'] = str_replace("{get}", "(.*)", $rss['search']);
	$rss['search'] = str_replace("{skip}", ".*", $rss['search']);
	$rss['search'] = preg_replace("![\n\r\t]!s", "", $rss['search']);
	$rss['search'] = preg_replace("!>[ ]{1,}<!s", "><", $rss['search']);

	if ($rss['search'] != "" && preg_match("!".$rss['search']."!Us", $content, $found)) {

       $temp = array();
       for($i=1; $i < sizeof($found); $i++) {
            $temp[] = $found[$i];
       }

       $content = implode("", $temp);

		$content = str_replace('src="//', "src=\"{$link['scheme']}://", $content);
		$content = str_replace("src='//", "src=\"{$link['scheme']}://", $content);
		$content = str_replace('srcset="//', "srcset=\"{$link['scheme']}://", $content);
		$content = str_replace("srcset='//", "srcset=\"{$link['scheme']}://", $content);

		$content = str_replace('src="/', "src=\"{$link['scheme']}://{$link['host']}/", $content);
		$content = str_replace("src='/", "src=\"{$link['scheme']}://{$link['host']}/", $content);
		$content = str_replace('srcset="/', "srcset=\"{$link['scheme']}://{$link['host']}/", $content);
		$content = str_replace("srcset='/", "srcset=\"{$link['scheme']}://{$link['host']}/", $content);

		if ($_POST['rss_charset'] != strtolower($config['charset']) AND $content != "") $content = convert($_POST['rss_charset'], strtolower($config['charset']), $content);
		
		$parse->edit_mode = false;

		if ($rss['text_type'] AND !$config['allow_admin_wysiwyg']) {

			$content = $parse->decodeBBCodes($content, false);
			$content = stripslashes($parse->process($content));

			$parse->edit_mode = true;
			$content = $parse->decodeBBCodes($content, false);

		} else {

			$content = $parse->decodeBBCodes($content, true, $config['allow_admin_wysiwyg']);
			$content = stripslashes($parse->process($content));

			$parse->edit_mode = true;
			$content = $parse->decodeBBCodes($content, true, $config['allow_admin_wysiwyg']);
		}

		if ($content != "") {

			if ( $config['allow_admin_wysiwyg']) {

				$buffer = <<<HTML
<div class="editor-panel"><textarea class="wysiwygeditor" style="width:100%;max-width:950px;height:300px;" id="full_{$news_id}" name="content[{$news_id}][full]">{$content}</textarea></div>
<script>
jQuery(function($){
	init_dle_editor ( '#full_{$news_id}' );
});
</script>
HTML;
			} else {

			include(DLEPlugins::Check(ENGINE_DIR . '/inc/include/inserttag.php'));

			$buffer = <<<HTML
<div class="editor-panel"><div class="shadow-depth1">{$bb_panel}<textarea class="editor" style="width:100%;max-width:950px;height:300px;" id="full_{$news_id}" name="content[{$news_id}][full]" onfocus="setFieldName(this.id)">{$content}</textarea></div></div>
HTML;

			}

		} else $buffer = "<span style=\"color:red;\">".$lang['rss_error']."</span>";

	} else $buffer = "<span style=\"color:red;\">".$lang['rss_error']."</span>";


echo $buffer;
?>