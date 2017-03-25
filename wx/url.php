<?php
define ( "BASEDIR", dirname ( __FILE__ ) );
define ( "TOKENPATH", BASEDIR . '\token.xml' );

$type = $_GET ['type'];
if (isset ( $_GET ['type'] ) && ! empty ( $type )) {
	switch ($type) {
		case 'token' :
			echo (saveToken ());
			break;
		case 'menu' :
			$accessToken = getToken ();
			$menuurl = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $accessToken;
			$data_json = <<<Json
  {
    "button": [
        {
            "type": "click",
            "name": "听歌曲",
            "key": "V1001_TODAY_MUSIC"
        },
        {
            "type": "click",
            "name": "歌手简介",
            "key": "V1001_TODAY_SINGER"
        },
        {
            "name": "菜单",
            "sub_button": [
                {
                    "type": "view",
                    "name": "搜索",
                    "url": "http://www.soso.com/"
                },
                {
                    "type": "view",
                    "name": "视频",
                    "url": "http://v.qq.com/"
                },
                {
                    "type": "click",
                    "name": "赞一下我们",
                    "key": "V1001_GOOD"
                }
            ]
        }
    ]
}
Json;
			$result = my_https_curl ( $menuurl, $data_json ); // json格式或内容不合法，会导致定义菜单失败。
			var_dump ( $result );
			break;
		default :
			echo "ERROR OPERATION";
	}
}
function my_https_curl($url, $data = null) {
	$curl = curl_init ();
	curl_setopt ( $curl, CURLOPT_URL, $url );
	
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
	if (! empty ( $data )) {
		curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen ( $data ) 
		) );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
	}
	$res = curl_exec ( $curl );
	if (curl_errno ( $curl )) {
		echo curl_error ( $curl );
	}
	curl_close ( $curl );
	return $res;
}
function saveToken() {
	$filename = TOKENPATH;
	$AppID = 'wx601e642ca1d61caf';
	$AppSecret = '2f61ee93424973e8002e5b14367b3101';
	$tokenurl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $AppID . '&secret=' . $AppSecret;
	$res = my_https_curl ( $tokenurl );
	$res = json_decode ( $res, true );
	$tokenTpl = <<<EOT
<xml><access><token><![CDATA[%s]]></token><time><![CDATA[%s]]></time></access></xml>
EOT;
	$tokenXml = sprintf ( $tokenTpl, $res ['access_token'], time () );
	file_put_contents ( $filename, $tokenXml, LOCK_EX );
	return $res ['access_token'];
}
function getToken() {
	$filename = TOKENPATH;
	if (! file_exists ( $filename )) {
		return saveToken ();
	} else {
		$xml = simplexml_load_file ( $filename, 'SimpleXMLElement', LIBXML_NOCDATA );
		$t1 = $xml->access->time;
		if (abs ( time () - $t1 ) <= 7000) {
			$token = $xml->access->token;
			return $token;
		} else {
			return saveToken ();
		}
	}
}

?>