<?php

function getSize($filesize) {
	if($filesize >= 1073741824) {
	 $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';

	} elseif($filesize >= 1048576) {
	 $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';

	} elseif($filesize >= 1024) {
	 $filesize = round($filesize / 1024 * 100) / 100 . ' KB';

	} else {
	 $filesize = $filesize . ' 字节';

	}
	return $filesize;
}

function getFileicon($type){
	$type = strtolower($type);
	if($type == 'mp4'){
		return 'play_circle_filled';
	}elseif(in_array($type,["gif","jpeg","jpg","png"])){
		return 'photo';
	}elseif(in_array($type,["mp3","ogg","wav"])){
		return 'music_note';
	}else{
		return 'insert_drive_file';
	}
}

function isPreview($type){
	$type = strtolower($type);
	if($type == 'mp4' || in_array($type,["gif","jpeg","jpg","png"]) || in_array($type,["mp3","ogg","wav"])){
		return '?preview';
	}else{
		return false;
	}
}

function curl($url,$post=false,$cookie=false,$header=false,$split=false,$referer=false){
	$ch = curl_init();
	if($header){
		curl_setopt($ch,CURLOPT_HEADER, 1);
	}else{
		curl_setopt($ch,CURLOPT_HEADER, 0);
	}
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36');
	if($post){
		curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post));
    }
    if($cookie){
		curl_setopt($ch, CURLOPT_COOKIE,$cookie);
    }
    if($referer){
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
	$result = curl_exec($ch);
	if($split){
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $headerSize);
		$body = substr($result, $headerSize);
		$result=array();
		$result['header']=$header;
		$result['body']=$body;
	}
	curl_close($ch);
	return $result;
}

function is_post(){
    return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])=='POST';
}

function is_ajax(){
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'])=='XMLHTTPREQUEST';
}