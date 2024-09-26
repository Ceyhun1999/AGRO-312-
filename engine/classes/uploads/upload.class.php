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
 File: upload.class.php
-----------------------------------------------------
 Use: upload files on server
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class UploadFileViaFTP {

	private $path_file = "";
	private $file_name = "";
	
	public $error_code = false;
	public $force_replace = false;
	public $md5 = null;

	function __construct() {
		
	}

    function saveFile($path, $filename, $prefix=true, $force_prefix = false) {

        if( !DLEFiles::FileExists( "files/" . $this->path_file . $filename ) ){
            return false;
        }

        return $this->path_file . $filename;
    }

    function getFileName() {
	
		$path = trim(str_replace(chr(0), '', (string)$_POST['ftpurl']));
		$path = str_replace(array('/', '\\'), '/', $path);

		if( !$path ) return '';
		
		if (preg_match('#\p{C}+#u', $path)) {
			return '';
		}
	
		$path_parts = pathinfo( $path );

		$this->file_name = $path_parts['basename'];
		
		$parts = array_filter(explode('/', $path_parts['dirname']), 'strlen');
		
		$absolutes = array();
		
		foreach ($parts as $part) {
			$part = trim($part);
			
			if ('.' == $part OR '..' == $part OR !$part) continue;
			
			$absolutes[] = $part;
		}
	
		$path = implode('/', $absolutes);
	
		if ( $path ) {
			$this->path_file = $path.'/';
		}

		return $this->file_name;
	
    }


    function getFileSize() {

		return DLEFiles::Size( "files/" . $this->path_file . $this->file_name );

    }
	
    function getImage() {
        return ROOT_DIR . "/uploads/files/" . $this->path_file . $this->file_name;
    }
	
}

class UploadFileViaURL {  

	private $from = "";
	
	public $error_code = false;
	public $force_replace = false;
	public $md5 = null;
	
	function __construct() {
		
	}
	
    function saveFile($path, $filename, $auto_prefix = true, $force_prefix = false) {

		$file_prefix = "";
	
		if ( ($auto_prefix AND DLEFiles::FileExists( $path.$filename ) ) OR $force_prefix ) {

			$file_prefix = time()."_";

		}

		$filename = totranslit( $file_prefix.$filename );

		if( !DLEFiles::$error ) {
			
			$stream = @fopen( $this->from , 'rb');
			
			if (is_resource($stream)) {
				
				DLEFiles::WriteStream( $path.$filename, $stream);
				
			} else {
				
				DLEFiles::$error = 'PHP Error: Unable to open the stream with uploaded file';
				return false;
			
			}
			
			if (is_resource($stream)) {
				fclose($stream);
			}
			
			if( DLEFiles::$error ) return false;

		} else return false;

        return $filename;
    }
	
    function getFileName() {

		$imageurl = trim( strip_tags( $_POST['imageurl'] ) );
		$imageurl = str_replace(chr(0), '', $imageurl);
		$imageurl = str_replace( "\\", "/", $imageurl );

		$url = @parse_url ( $imageurl );

        if (!array_key_exists('host', $url)) {
            return '';
        }

		if($url['scheme'] != 'http' AND $url['scheme'] != 'https') {

            return '';
		}

		if($url['host'] == 'localhost' OR $url['host'] == '127.0.0.1') {

            return '';
		}

		if( stripos ( $url['host'], $_SERVER['HTTP_HOST'] ) !== false ) {

			return '';

		}

		if( stripos( $imageurl, ".php" ) !== false ) return '';
		if( stripos( $imageurl, ".phtm" ) !== false ) return '';

		$this->from = $imageurl;

		$imageurl = explode( "/", $imageurl );
		$imageurl = end( $imageurl );
		$imageurl = explode("?", $imageurl);
		$imageurl = reset($imageurl);

        return $imageurl;
    }
	
    function getFileSize() {

		$url = @parse_url( $this->from );

		if ( $url ) {
			
			if($url['scheme'] == "https" ) $port = 443; else $port = 80;

			$fp = @fsockopen( $url['host'], $port, $errno, $errstr, 10);

			if ($fp) {
				$x='';
	
				fputs($fp,"HEAD {$url['path']} HTTP/1.0\nHOST: {$url['host']}\n\n");
				while(!feof($fp)) $x.=fgets($fp,128);
				fclose($fp);

				if ( preg_match("#Content-Length: ([0-9]+)#i",$x,$size) ) {
					return intval($size[1]);
				} else {
					return strlen(@file_get_contents($this->from));
				}

			}

		}
		
		return 0;

    }
	
    function getImage() {
        return $this->from;
    }
	
}

class UploadFileViaForm {
	
	public $error_code = false;
	public $force_replace = false;
	
	private $name;
	private $tmp_name;
	private $size;
	private $max_file_size;
	
	private $chunk;
	private $chunks;
	public  $chunk_tmp_name;
	public $md5 = null;
	
	function __construct() {
		global $config, $member_id, $user_group;
		
		$this->chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
		$this->chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;
		
		$this->name = isset($_REQUEST['name']) ? $_REQUEST['name'] : $_FILES['qqfile']['name'];
		$this->name = $this->getFileName();
		
		$this->tmp_name = isset($_FILES['qqfile']['tmp_name']) ? $_FILES['qqfile']['tmp_name'] : false;
		$this->size = $_FILES['qqfile']['size'];
		
		if ( !$this->name ){
			die( json_encode(array('error' => 'File not send to server' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        }

		if( $this->chunks > 1 ) {
			
			$this->chunk_tmp_name = ROOT_DIR . "/uploads/files/".md5($this->name.$member_id['name'].SECURE_AUTH_KEY).'.tmp';
			
			$max_file_size = intval($config['max_up_size']);
			
			if( $user_group[$member_id['user_group']]['allow_file_upload'] ) {
	
				if( !intval($user_group[$member_id['user_group']]['max_file_size']) ) $max_file_size = 0;
				elseif( intval($user_group[$member_id['user_group']]['max_file_size']) > $max_file_size ) $max_file_size = intval($user_group[$member_id['user_group']]['max_file_size']);
	
			} elseif( !$max_file_size ) {
				$max_file_size = 20 * 1024 * 1024;
			}
	
			$this->max_file_size = $max_file_size * 1024;
			
			if( !$this->max_file_size ) $this->max_file_size = 1024 * 1024 * 1024;
		
		}
		
		if( $this->getErrorCode() ) {
			header( "HTTP/1.1 403 Forbidden" );
			die( json_encode(array('error' => $this->getErrorCode() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );	
		}
		
		if (!$this->tmp_name || !is_uploaded_file($this->tmp_name) ) {
			die( json_encode(array('error' => 'File not send to server' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
		
		if( $this->chunks > 1 ) {
			$this->uploadchunk();
		}
		
	}
	
    function saveFile($path, $filename, $auto_prefix = true, $force_prefix = false) {
		
		$file_prefix = "";
	
		if ( ($auto_prefix AND DLEFiles::FileExists( $path.$filename ) ) OR $force_prefix ) {

			$file_prefix = time()."_";

		}

		$filename = totranslit( $file_prefix.$filename );

		if( !DLEFiles::$error ) {
			
			$stream = @fopen( $this->tmp_name , 'rb');
			
			if (is_resource($stream)) {
				
				DLEFiles::WriteStream( $path.$filename, $stream);
				
			} else {
				
				DLEFiles::$error = 'PHP Error: Unable to open the stream with uploaded file';
				return false;
			
			}
			
			if (is_resource($stream)) {
				fclose($stream);
			}
			
			if( DLEFiles::$error ) return false;

		} else return false;

		$this->md5 = md5_file($this->tmp_name);

		if( $this->chunks > 1 ) {
			@unlink( $this->chunk_tmp_name );
			$this->chunk_tmp_name = '';
		}
		
		$this->cleanup_old_tmp();
		
        return $filename;
    }
	
    function cleanup_old_tmp(){
		
		$files = glob(ROOT_DIR . '/uploads/files/*.tmp');

		foreach ($files as $tmpFile) {
			
			if (is_file($tmpFile)) {
				
				if (time() - filemtime($tmpFile) < (5 * 3600) ) {
					continue;
				}
				
				@unlink($tmpFile);
			
			}

		}
    }
	
	function uploadchunk() {
		global $lang;

		if (!$in = @fopen($this->tmp_name, "rb")) {
			header( "HTTP/1.1 403 Forbidden" );
			die( json_encode(array('error' => 'PHP Error: Unable to open the stream with uploaded file'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
		
        if ( !$out = @fopen($this->chunk_tmp_name, $this->chunk ? "ab" : "wb" ) ) {
			header( "HTTP/1.1 403 Forbidden" );
            die( json_encode(array('error' => 'PHP Error: Unable to write uploaded file, check CHMOD for folder /uploads/files/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        }
		
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		
		fflush($out);
		
        @fclose($in);	
        @fclose($out);
		
		clearstatcache(true, $this->chunk_tmp_name);
		$this->size = filesize( $this->chunk_tmp_name );
		
		if( $this->max_file_size AND $this->size > $this->max_file_size) {
			
			@unlink( $this->chunk_tmp_name );
			header( "HTTP/1.1 403 Forbidden" );
			die( json_encode(array('error' => $lang['files_too_big'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			
		}
		
		if ($this->chunks == $this->chunk + 1) {
			
			$this->tmp_name = $this->chunk_tmp_name;
			
		} else {
			
			die( json_encode(array('result' => 'chunk uploaded'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			
		}

	}
	
    function getFileName() {

		$path_parts = @pathinfo($this->name);

        return $path_parts['basename'];

    }
	
    function getFileSize() {
        return $this->size;
    }
	
    function getImage() {
        return array( 'tmp_name' => $this->tmp_name,  'name' => $this->getFileName() );
    }
	
    function getErrorCode() {

		$error_code = $_FILES['qqfile']['error'];

		if ($error_code !== UPLOAD_ERR_OK) {

		    switch ($error_code) { 
		        case UPLOAD_ERR_INI_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
		        case UPLOAD_ERR_FORM_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
		        case UPLOAD_ERR_PARTIAL: 
		            $error_code = 'PHP Error: The uploaded file was only partially uploaded'; break;
		        case UPLOAD_ERR_NO_FILE: 
		            $error_code = 'PHP Error: No file was uploaded'; break;
		        case UPLOAD_ERR_NO_TMP_DIR: 
		            $error_code = 'PHP Error: Missing a PHP temporary folder'; break;
		        case UPLOAD_ERR_CANT_WRITE: 
		            $error_code = 'PHP Error: Failed to write file to disk'; break;
		        case UPLOAD_ERR_EXTENSION: 
		            $error_code = 'PHP Error: File upload stopped by extension'; break;
		        default: 
		            $error_code = 'Unknown upload error';  break;
		    } 

		} else return false;

        return $error_code;
    }
}

class FileUploader {

	private $allowed_extensions = array ("gif", "jpg", "jpeg", "png", "webp", "bmp", "avif", "heic");
	private $allowed_video = array ("mp4", "mp3", "m4v", "m4a", "mov", "webm", "m3u8", "mkv" );
	private $allowed_files = array();
	private $area = "";
	private $author = "";
	private $news_id = "";
	private $t_size = "";
	private $t_seite = 0;
	private $make_thumb = true;
	private $m_size = "";
	private $m_seite = 0;
	private $make_medium = false;
	private $hidpi = 0;
	private $make_watermark = true;
	private $upload_path = "posts/";
	private $file = null;

    function __construct($area, $news_id, $author, $t_size, $t_seite, $make_thumb = true, $make_watermark = true, $m_size = 0, $m_seite = 0, $make_medium = false, $hidpi = false){        
		global $config, $db, $member_id, $user_group;

        $this->area = totranslit($area);

		if ( $this->area == "adminupload" ) {

			if (!isset($_FILES['qqfile']) OR $member_id['user_group'] != 1) die( "Hacking attempt!" );

			if( isset($_REQUEST['userdir']) AND $_REQUEST['userdir']) $userdir = cleanpath( $_REQUEST['userdir'] ). "/"; else $userdir = "";
			if( isset($_REQUEST['subdir']) AND $_REQUEST['subdir']) $subdir = cleanpath( $_REQUEST['subdir'] ). "/"; else $subdir = "";

			$this->upload_path = $userdir.$subdir;

		} else {

	        $this->allowed_files = explode( ',', strtolower( $user_group[$member_id['user_group']]['files_type'] ) );
		}

        $this->author = $db->safesql( $author );
        $this->news_id = intval($news_id);
        $this->t_size = $t_size;
        $this->t_seite = $t_seite;
        $this->make_thumb = $make_thumb;
        $this->m_size = $m_size;
        $this->m_seite = $m_seite;
        $this->make_medium = $make_medium;
        $this->make_watermark = $make_watermark;

		if( $hidpi ) $this->hidpi = 1; else $this->hidpi = 0;

		$ftp_upload_flag = false;
      
		if ( isset($_POST['imageurl']) AND $_POST['imageurl'] ) {

            $this->file = new UploadFileViaURL();

        } elseif ( $member_id['user_group'] == 1 AND isset($_POST['ftpurl']) AND $_POST['ftpurl'] ) {

            $this->file = new UploadFileViaFTP();
			$ftp_upload_flag = true;
			
        } else {

            $this->file = new UploadFileViaForm();

        }

		if ($ftp_upload_flag OR $this->area == "adminupload" )
			define( 'FOLDER_PREFIX', "" );
		else
			define( 'FOLDER_PREFIX', date( "Y-m" )."/" );

    }

	private function check_filename( $filename ) {
		
		$filename = (string)$filename;
		
		if( !$filename ) return false;
			
		$filename = str_replace(chr(0), '', $filename);
		$filename = str_replace( "\\", "/", $filename );
		$filename = preg_replace( '#[.]+#i', '.', $filename );
		$filename = str_replace( "/", "", $filename );
		$filename = str_ireplace( "php", "", $filename );

		$filename_arr = explode( ".", $filename );
		
		if(count($filename_arr) < 2) {
			return false;
		}
		
		$type = totranslit( end( $filename_arr ) );
		
		if(!$type) return false;
		
		$curr_key = key( $filename_arr );
		
		unset( $filename_arr[$curr_key] );

		$filename = totranslit( implode( "_", $filename_arr ) );
		
		if( !$filename ) {
			$filename = time() + rand( 1, 100 );
		}
		
		$filename = $filename . "." . $type;

		$filename = preg_replace( '#[.]+#i', '.', $filename );

		if( stripos ( $filename, ".php" ) !== false ) return false;
		if( stripos ( $filename, ".phtm" ) !== false ) return false;
		if( stripos ( $filename, ".shtm" ) !== false ) return false;
		if( stripos ( $filename, ".htaccess" ) !== false ) return false;
		if( stripos ( $filename, ".cgi" ) !== false ) return false;
		if( stripos ( $filename, ".htm" ) !== false ) return false;
		if( stripos ( $filename, ".ini" ) !== false ) return false;

		if( stripos ( $filename, "." ) === 0 ) return false;
		if( stripos ( $filename, "." ) === false ) return false;
		
		if( strlen( $filename ) > 200 ) {
			return false;
		}

		return $filename;

	}

	private function msg_error($message, $code = 500) {
		
		if( isset( $this->file->chunk_tmp_name ) AND $this->file->chunk_tmp_name ) {
			
			@unlink($this->file->chunk_tmp_name);
			$this->file->chunk_tmp_name = '';
			
		}
		
		return json_encode(array('error' => $message ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	
	}
	
	function FileUpload() {
		
		global $config, $db, $lang, $member_id, $user_group;
		
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
		
		$_IP = get_ip();
		$added_time = time();
		$xfvalue = "";
		$driver = null;
		$tinypng_error = false;
		$flink = false;
		$link = false;
		$commentsfileid = false;
		
		if (!$this->file){
			return $this->msg_error( $lang['upload_error_3'] );
        }

		$filename = $this->check_filename( $this->file->getFileName() );

		if ( !$filename ){
			return $this->msg_error( $lang['upload_error_4'] );
        }

		$filename_arr = explode( ".", $filename );
		$type = end( $filename_arr );

		if ( !$type ){
			return $this->msg_error( $lang['upload_error_4'] );
        }
		
		$size = $this->file->getFileSize();
	
        if (!$size) {
            return $this->msg_error( $lang['upload_error_5'] );
        }
			
		if( $config['files_allow'] AND $user_group[$member_id['user_group']]['allow_file_upload'] AND in_array($type, $this->allowed_files ) ) {

			if( intval( $user_group[$member_id['user_group']]['max_file_size'] ) AND $size > ((int)$user_group[$member_id['user_group']]['max_file_size'] * 1024) ) {
				
				return $this->msg_error( $lang['files_too_big'] );
			
			}

			if( $this->area != "template" AND $user_group[$member_id['user_group']]['max_files'] ) {
				
				$row = $db->super_query( "SELECT COUNT(*) as count  FROM " . PREFIX . "_files WHERE author = '{$this->author}' AND news_id = '{$this->news_id}'" );
				$count_files = $row['count'];
		
				if ($count_files AND $count_files >= $user_group[$member_id['user_group']]['max_files'] ) return $this->msg_error( $lang['error_max_files'] );
		
			}
			
			if ( isset($_REQUEST['public_file']) AND $_REQUEST['public_file'] ) $is_public = 1; else $is_public = 0;
			
			if( $user_group[$member_id['user_group']]['allow_public_file_upload'] AND $is_public) {
				$this->upload_path = "public_files/";
				$auto_prefix = true;
				$force_prefix = false;
			} else {
				$this->upload_path = "files/";
				$is_public = 0;
				$auto_prefix = false;
				$force_prefix = true;
			}
			
			$config['files_remote'] = intval( $config['files_remote'] );
			if ( $config['files_remote'] > -1 ) $driver = $config['files_remote'];
			
			DLEFiles::init( $driver, $config['local_on_fail'] );
			
			$uploaded_filename = $this->file->saveFile($this->upload_path . FOLDER_PREFIX, $filename, $auto_prefix, $force_prefix);

			if ( DLEFiles::$error ){
				return $this->msg_error( DLEFiles::$error );
			}
			
			if ( !$uploaded_filename ){
				return $this->msg_error( $lang['images_uperr_3'] );
			}

			$added_time = time();
			$file_link = $config['http_home_url'] . "engine/skins/images/all_file.png";
			$data_url = "#";
			$file_play = "";
			$size = DLEFiles::Size( $this->upload_path . FOLDER_PREFIX . $uploaded_filename );
			$driver = DLEFiles::$driver;

			if( !$this->file->md5 ) {

				$md5 = DLEFiles::Checksum($this->upload_path . FOLDER_PREFIX . $uploaded_filename);

			} else $md5 = $this->file->md5;

			$http_url = DLEFiles::GetBaseURL();

			if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '36', '{$uploaded_filename}')" );

			if( in_array( $type, $this->allowed_video ) ) {
			
				if( $type == "mp3" ) {
						
					$file_link = $config['http_home_url'] . "engine/skins/images/mp3_file.png";
					$file_play = "audio";
	
				} else {
						
					$file_link = $config['http_home_url'] . "engine/skins/images/video_file.png";
					$file_play = "video";
				}
				
				$data_url = $http_url . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;
				
			}

			if( $user_group[$member_id['user_group']]['allow_public_file_upload'] AND $is_public) {
				$data_url = $http_url . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;
			}
			
			if( $this->area == "template" ) {
				
				$db->query( "INSERT INTO " . PREFIX . "_static_files (static_id, author, date, name, onserver, size, checksum, driver, is_public) values ('{$this->news_id}', '{$this->author}', '{$added_time}', '{$filename}', '". FOLDER_PREFIX ."{$uploaded_filename}', '{$size}', '{$md5}', '{$driver}', '{$is_public}')" );
				$id = $db->insert_id();
				$del_name = 'static_files';
			
			} else {
				
				$db->query( "INSERT INTO " . PREFIX . "_files (news_id, name, onserver, author, date, size, checksum, driver, is_public) values ('{$this->news_id}', '{$filename}', '". FOLDER_PREFIX ."{$uploaded_filename}', '{$this->author}', '{$added_time}', '{$size}', '{$md5}', '{$driver}', '{$is_public}')" );
				$id = $db->insert_id();
				$del_name = "files";
			
			}
			$size = formatsize($size);
			
$return_box = <<<HTML
<div class="file-preview-card" data-type="file" data-area="{$del_name}" data-deleteid="{$id}" data-url="{$data_url}" data-path="{$id}:{$filename}" data-play="{$file_play}" data-public="{$is_public}">
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<img src="{$file_link}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="ID: {$id}, {$filename}">{$filename}</div>
			<div class="file-size-info">({$size})</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview"><a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a></div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;

			if( $this->area == "xfieldsfile" ) {
				
				$return_box = "&nbsp;<button class=\"qq-upload-button btn btn-sm bg-danger btn-raised\" onclick=\"xffiledelete('".$_REQUEST['xfname']."','".$id."');return false;\">{$lang['xfield_xfid']}</button>";
				
				if( $is_public ) {
					$xfvalue = $data_url;
				} else {
					$xfvalue = "[attachment={$id}:{$filename}]";
				}
				
			}

			if ($this->area == "xfieldsvideo" OR $this->area == "xfieldsaudio") {

				$xfvalue = "{$data_url}|{$id}|{$size}";
				$xf_id = md5($xfvalue);

				$return_box = "<div class=\"file-preview-card uploadedfile\" id=\"xf_{$xf_id}\" data-id=\"{$xfvalue}\" data-alt=\"\"><div class=\"active-ribbon\"><span><i class=\"mediaupload-icon mediaupload-icon-ok\"></i></span></div><div class=\"file-content\"><img src=\"{$file_link}\" class=\"file-preview-image\"></div><div class=\"file-footer\"><div class=\"file-footer-caption\"><div class=\"file-caption-info\" rel=\"tooltip\" title=\"{$filename}\">{$filename}</div><div class=\"file-size-info\">({$size})</div></div><div class=\"file-footer-bottom\"><div class=\"file-preview\"><a onclick=\"xfaddalt('" . $xf_id . "', '" . $_REQUEST['xfname'] . "');return false;\" href=\"#\" rel=\"tooltip\" title=\"{$lang['xf_img_descr']}\"><i class=\"mediaupload-icon mediaupload-icon-edit\"></i></a></div><div class=\"file-delete\"><a onclick=\"xfplaylistdelete_".md5($_REQUEST['xfname'])."('" . $_REQUEST['xfname'] . "','" . $id . "', '" . $xf_id . "');return false;\" href=\"#\"><i class=\"mediaupload-icon mediaupload-icon-trash\"></i></a></div></div></div></div>";

			}

		} elseif ( in_array( $type, $this->allowed_extensions ) AND $user_group[$member_id['user_group']]['allow_image_upload'] ) {

			$min_size_upload = true;
			$hidpi_name ='';

			$config['comments_remote'] = intval($config['comments_remote']);
			$config['static_remote'] = intval($config['static_remote']);
			$config['image_remote'] = intval($config['image_remote']);

			if( $this->area == "comments" AND $config['comments_remote'] > -1 ) $driver = $config['comments_remote'];
			elseif ( $this->area == "template" AND $config['static_remote'] > -1 ) $driver = $config['static_remote'];
			elseif ( $this->area == "adminupload" AND isset($_REQUEST['upload_driver']) ) $driver = intval($_REQUEST['upload_driver']);
			elseif ( $config['image_remote'] > -1 ) $driver = $config['image_remote'];
	
			DLEFiles::init( $driver, $config['local_on_fail'] );
			
			if( intval( $config['max_up_size'] ) AND $size > ((int)$config['max_up_size'] * 1024) ) {
				
				return $this->msg_error( $lang['images_big'] );
			
			}

			if( $this->area != "template" AND $this->area != "adminupload" AND $this->area != "comments" AND $user_group[$member_id['user_group']]['max_images'] ) {
				
				$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE author = '{$this->author}' AND news_id = '{$this->news_id}'" );
				if ($row['images']) $count_images = count(explode( "|||", $row['images'] )); else $count_images = false;		
				if( $count_images AND $count_images >= $user_group[$member_id['user_group']]['max_images'] ) return $this->msg_error( $lang['error_max_images'] );
				
			}
			
			if( $this->area == "comments" AND $user_group[$member_id['user_group']]['up_count_image'] ) {
				
				$row = $db->super_query( "SELECT COUNT(*) as count  FROM " . PREFIX . "_comments_files WHERE c_id = '{$this->news_id}' AND author = '{$this->author}'" );
		
				if( $row['count'] >= $user_group[$member_id['user_group']]['up_count_image'] ) return $this->msg_error( $lang['error_max_images'] );
				
			}

			if(  $this->area == "adminupload" AND DLEFiles::FileExists( $this->upload_path . FOLDER_PREFIX . $filename ) ) {
				
				return $this->msg_error( $lang['images_uperr_4'] );

			}
			
			if( $this->area == "adminupload" ){
				$min_size_upload = false;
			}

			$image = new thumbnail( $this->file->getImage(), true, $min_size_upload );
			
			if ( $image->error ){
				return $this->msg_error( $image->error );
			}

			if ($this->hidpi) {
				$image->re_save = true;
			}

			if ($this->hidpi) {
				$image->size_auto( intval($image->width / 2) , 1);
			}

			if ($config['max_up_side']) $image->size_auto($config['max_up_side'], $config['o_seite']);

			$dimension = $image->width . "x" . $image->height;

			if ($this->make_watermark) $image->insert_watermark($config['max_watermark']);

			if ($member_id['user_group'] != 1 OR $image->re_save) {

				$uploaded_filename = $image->save($this->upload_path . FOLDER_PREFIX . $filename, true);

			} else {

				$uploaded_filename = $this->file->saveFile($this->upload_path . FOLDER_PREFIX, $filename, true);

			}

			if ($image->error) {
				return $this->msg_error($image->error);
			}

			if (DLEFiles::$error) {
				return $this->msg_error(DLEFiles::$error);
			}

			if (!$uploaded_filename) {
				return $this->msg_error($lang['images_uperr_3']);
			}


			if ($this->hidpi) {

				$hidpi_name = pathinfo($uploaded_filename, PATHINFO_FILENAME) . '@x2.' . pathinfo($uploaded_filename, PATHINFO_EXTENSION);

				if ($config['max_up_side']) $image->size_auto($config['max_up_side'], $config['o_seite'], $this->hidpi);

				if ($this->make_watermark) $image->insert_watermark($config['max_watermark'], $this->hidpi );

				$image->save($this->upload_path . FOLDER_PREFIX . $hidpi_name, false);

			}
			
			$size = formatsize( DLEFiles::Size( $this->upload_path . FOLDER_PREFIX . $uploaded_filename ) );
			$thumb_data = 0;
			$added_time = time();
		
			if( $this->make_thumb ) {
				
				if( $image->size_auto( $this->t_size, $this->t_seite, $this->hidpi ) ) {
					
					if( $this->make_watermark ) $image->insert_watermark( $config['max_watermark'], $this->hidpi );
					
					if( $this->hidpi ) {

						$image->save($this->upload_path . FOLDER_PREFIX . "thumbs/" . $hidpi_name, false);
						
						$image->size_auto($this->t_size, $this->t_seite);
						
						if ($this->make_watermark) $image->insert_watermark($config['max_watermark']);

						$image->save($this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename, false);


					} else {

						$image->save($this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename, false);

					}
					

					$thumb_data = 1;
					
				}
				
				if ( $image->error ){
					return $this->msg_error( $image->error );
				}
			
			}

			$medium_data = 0;
			
			if( $this->make_medium ) {
				
				if( $image->size_auto( $this->m_size, $this->m_seite, $this->hidpi ) ) {
					
					if( $this->make_watermark ) $image->insert_watermark( $config['max_watermark'], $this->hidpi );
					
					if ($this->hidpi) {

						$image->save($this->upload_path . FOLDER_PREFIX . "medium/" . $hidpi_name, false);
 						
						$image->size_auto( $this->m_size, $this->m_seite);

						if ($this->make_watermark) $image->insert_watermark($config['max_watermark']);

						$image->save($this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename, false);

					} else {
						$image->save($this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename, false);
					}
					
					$medium_data = 1;
					
				}
				
				if ( $image->error ){
					return $this->msg_error( $image->error );
				}
				
			}
			
			if( $image->tinypng_error ) $tinypng_error = $image->tinypng_error;
			
			$http_url = DLEFiles::GetBaseURL();

			if ( DLEFiles::$driver ) {

				$insert_image = $http_url . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;
				
			} else {
				
				$insert_image = FOLDER_PREFIX . $uploaded_filename;
				
			}

			$insert_image .= "|{$thumb_data}|{$medium_data}|{$dimension}|{$size}";
		
			if($this->area != "comments" AND $this->area != "xfieldsimage" AND $this->area != "xfieldsimagegalery" AND $this->area != "adminupload" ) {
				$insert_image .= "|{$this->hidpi}";
			}

			if( $this->hidpi ) {
				$hidpi_data = " data-hidpi=\"{$hidpi_name}\"";
			} else $hidpi_data ='';

			if( $this->area != "template" AND $this->area != "adminupload" AND $this->area != "comments") {
				
				$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_images WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
				
				if( !$row['count'] ) {
					
					$db->query( "INSERT INTO " . PREFIX . "_images (images, author, news_id, date) values ('{$insert_image}', '{$this->author}', '{$this->news_id}', '{$added_time}')" );
				
				} else {
					
					$update_images = true;
					
					$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
					
					$listimages = array ();
					$update_images = true;
					
					if( $row['images'] ) {
						
						$listimages = explode( "|||", $row['images'] );
						
						foreach ( $listimages as $file_image ) {
							
							$file_image = get_uploaded_image_info( $file_image );
							
							if( $file_image->path == FOLDER_PREFIX . $uploaded_filename ) $update_images = false;
						
						}
					}
					
					if( $update_images ) {
						
						$listimages[] = $insert_image;
						$listimages = implode( "|||", $listimages );
						
						$db->query( "UPDATE " . PREFIX . "_images SET images='{$listimages}' WHERE news_id = '{$this->news_id}' AND author = '{$this->author}'" );
						
					}
				}
			}
			
			$driver = DLEFiles::$driver;

			if( $this->area == "template" ) {

				$db->query("INSERT INTO " . PREFIX . "_static_files (static_id, author, date, name, driver) values ('{$this->news_id}', '{$this->author}', '{$added_time}', '{$insert_image}', '{$driver}')");
				$id = $db->insert_id();

			}

			if( $this->area == "comments" ) {

				$db->query( "INSERT INTO " . PREFIX . "_comments_files (c_id, author, date, name, driver) values ('{$this->news_id}', '{$this->author}', '{$added_time}', '{$insert_image}', '{$driver}')" );
				$id = $commentsfileid = $db->insert_id();
	
			}
			
			if ($user_group[$member_id['user_group']]['allow_admin']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '36', '{$uploaded_filename}')" );
			
			$img_url = $data_url = $link = $flink = $http_url . $this->upload_path . FOLDER_PREFIX . $uploaded_filename;
			$image_path = FOLDER_PREFIX . $uploaded_filename;

			if( $medium_data ) {
				
				$img_url = 	$http_url . $this->upload_path . FOLDER_PREFIX . "medium/" . $uploaded_filename;
				$medium_data = "yes";
				$tm_url = $img_url;
				
			} else $medium_data = "no";

			if( $thumb_data ) {
				
				$img_url = 	$http_url . $this->upload_path . FOLDER_PREFIX . "thumbs/" . $uploaded_filename;
				$thumb_data = "yes";
				$th_url = $img_url;
				
			} else $thumb_data = "no";
			
			if($medium_data == "yes" ) $link = $tm_url;
			elseif( $thumb_data == "yes" ) $link = $th_url;
			else $flink = false;
			
			if( $this->area == "comments" OR $this->area == "template") {
				
				if( $this->area == "comments" ) {
					
					$del_name = 'comments_files';
					
				} else $del_name = 'static_files';

$return_box = <<<HTML
<div class="file-preview-card" data-type="image" data-area="{$del_name}" data-deleteid="{$id}" data-url="{$data_url}" data-path="{$image_path}" data-thumb="{$thumb_data}" data-medium="{$medium_data}"{$hidpi_data}>
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<img src="{$img_url}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$uploaded_filename}">{$uploaded_filename}</div>
			<div class="file-size-info">{$dimension} ({$size})</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a href="{$data_url}" data-highslide="single" target="_blank" rel="tooltip" title="{$lang['up_im_expand']}"><i class="mediaupload-icon mediaupload-icon-zoom"></i></a>
				<a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a>	
			</div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;
	
			} elseif( $this->area == "xfieldsimage" OR $this->area == "xfieldsimagegalery" ) {

				$xfvalue = $insert_image;
				$xf_id = md5($xfvalue);
				
				if( $this->area == "xfieldsimage" ) {
					
					$del_name = "xfimagedelete('".$_REQUEST['xfname']."','".FOLDER_PREFIX . $uploaded_filename."');return false;";
					
				} else $del_name = "xfimagegalerydelete_".md5($_REQUEST['xfname'])."('".$_REQUEST['xfname']."','".FOLDER_PREFIX . $uploaded_filename."', '".$xf_id."');return false;";
				
				$return_box = "<div id=\"xf_{$xf_id}\" data-id=\"{$xfvalue}\" data-alt=\"\" class=\"uploadedfile\"><div class=\"info\">{$uploaded_filename}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"xfaddalt('".$xf_id."', '".$_REQUEST['xfname']."');return false;\">{$lang['xf_img_descr']}</a><br><a href=\"#\" onclick=\"{$del_name}\">{$lang['xfield_xfid']}</a></div></div>";

$return_box = <<<HTML
<div class="file-preview-card uploadedfile" id="xf_{$xf_id}" data-id="{$xfvalue}" data-alt="">
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<img src="{$img_url}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$uploaded_filename}">{$uploaded_filename}</div>
			<div class="file-size-info">{$dimension} ({$size})</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a onclick="xfaddalt('{$xf_id}', '{$_REQUEST['xfname']}');return false;" href="#" rel="tooltip" title="{$lang['xf_img_descr']}"><i class="mediaupload-icon mediaupload-icon-edit"></i></a>
			</div>
			<div class="file-delete"><a href="#" onclick="{$del_name}"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;

			} else {

$return_box = <<<HTML
<div class="file-preview-card" data-type="image" data-area="images" data-deleteid="{$image_path}" data-url="{$data_url}" data-path="{$image_path}" data-thumb="{$thumb_data}" data-medium="{$medium_data}"{$hidpi_data}>
	<div class="active-ribbon"><span><i class="mediaupload-icon mediaupload-icon-ok"></i></span></div>
	<div class="file-content">
		<img src="{$img_url}" class="file-preview-image">
	</div>
	<div class="file-footer">
		<div class="file-footer-caption">
			<div class="file-caption-info" rel="tooltip" title="{$uploaded_filename}">{$uploaded_filename}</div>
			<div class="file-size-info">{$dimension} ({$size})</div>
		</div>
		<div class="file-footer-bottom">
			<div class="file-preview">
				<a href="{$data_url}" data-highslide="single" target="_blank" rel="tooltip" title="{$lang['up_im_expand']}"><i class="mediaupload-icon mediaupload-icon-zoom"></i></a>
				<a class="clipboard-copy-link" href="#" rel="tooltip" title="{$lang['up_im_copy']}"><i class="mediaupload-icon mediaupload-icon-copy"></i></a>	
			</div>
			<div class="file-delete"><a class="file-delete-link" href="#"><i class="mediaupload-icon mediaupload-icon-trash"></i></a></div>
		</div>
	</div>
</div>
HTML;

			}
			
			if( isset( $this->file->chunk_tmp_name ) AND $this->file->chunk_tmp_name ) {
				
				@unlink($this->file->chunk_tmp_name);
				$this->file->chunk_tmp_name = '';
				
			}

		} else return $this->msg_error( $lang['images_uperr_2'] );
		
		$return_array = array (
			'success' => true,
			'returnbox' => $return_box,
			'uploaded_filename' => $uploaded_filename,
			'xfvalue' => $xfvalue,
			'link' => $link,
			'flink' => $flink,
			'commentsfileid' => $commentsfileid,
			'remote_error' => DLEFiles::$remote_error,
			'tinypng_error' => $tinypng_error
		);
		
		return json_encode($return_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	}

}

?>