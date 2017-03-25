<?php
/**
 * wechat php test
 */

// define your token
define ( "TOKEN", "weixin936300116" );
$wechatObj = new wechatCallback ();
$wechatObj->valid ();
class wechatCallback {
	public function valid() {
		$echoStr = $_GET ["echostr"];
		
		// valid signature , option
		if ($this->checkSignature ()) {
			//$this->responseMsg ();
			echo $echoStr;
			exit ();
		}
	}
	public function responseMsg() {
		// get post data, May be due to the different environments
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		
		// extract post data
		if (! empty ( $postStr )) {
			/*
			 * libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			 * the best way is to check the validity of xml by yourself
			 */
			libxml_disable_entity_loader ( true );
			//写日志
			$this->loger ( "	T:\r\n" . $postStr . "\r\n" );
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim ( $postObj->Content );
			$time = time ();
			$textTpl = <<<hello
	<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
	</xml>             
hello;
			if (! empty ( $keyword )) {
				$msgType = "text";
				$contentStr = "Welcome to wechat world!";
				$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr );
				//写日志
				$this->loger ( "	R:\r\n" . $resultStr . "\r\n" );
				echo $resultStr;
			} else {
				echo "Input something...";
			}
		} else {
			echo "no HTTP_RAW_POST_DATA ";
			exit ();
		}
	}
	private function checkSignature() {
		// you must define TOKEN by yourself
		if (! defined ( "TOKEN" )) {
			throw new Exception ( 'TOKEN is not defined!' );
		}
		
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		// use SORT_STRING rule
		sort ( $tmpArr, SORT_STRING );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
	private function loger($logText) {
		$basedir = dirname ( __FILE__ );
		$filename = $basedir . '\wxlog.xml';
		$max_size = (1024 * 1024);
		if (file_exists ( $filename ) && abs ( filesize ( $filename ) ) > $max_size) {
			unlink ( $filename );
		}
		file_put_contents ( $filename, date ( "Y-m-d H:i:s" ) . $logText, FILE_APPEND );
	}
}

?>