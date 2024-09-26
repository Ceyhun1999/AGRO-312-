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
 File: sitemap.php
-----------------------------------------------------
 Use: Notice search engines about the sitemap
=====================================================
*/

if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if(!@file_exists(ROOT_DIR. "/uploads/sitemap.xml")){ 

	die( "error" );

} else {

	if ($config['allow_alt_url']) {

		$map_link = $config['http_home_url']."sitemap.xml";

	} else {

		$map_link = $config['http_home_url']."uploads/sitemap.xml";

	}
}

if( !$user_group[$member_id['user_group']]['admin_googlemap'] ) { die ("error"); }

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
	die ("error");
	
}

$buffer = "";

function send_url($engine, $url) {
		
	if( function_exists( 'curl_init' ) ) {
		
		$req = curl_init("{$engine}/ping?sitemap={$url}");
		curl_setopt($req, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($req, CURLOPT_TIMEOUT, 5);
		$data = curl_exec($req);
		curl_close($req);
		
		return $data;
		
	} else {

		return file_get_contents( "{$engine}/ping?sitemap={$url}" );

	}
	
}

$engines =  [
			'https://www.google.com' => 'Google',
			'https://www.bing.com' => 'Bing',
			'https://webmaster.yandex.com' => 'Yandex'
			];


foreach ($engines as $engine => $name) {
	send_url($engine, $map_link);
	$buffer .= "{$lang['sitemap_send']} {$name} {$lang['nl_finish']}<br>";
}

echo "<div class=\"findrelated\">".$buffer."</div>";

?>