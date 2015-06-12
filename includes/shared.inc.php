<?php
if (!class_exists('zingiri')) {
	class zingiri {

		static function create_api_key($namespace='') {
			$key='';
			$uid=uniqid(home_url(), false);
			$data=$namespace . serialize($_SERVER);
			$hash=strtoupper(hash('ripemd128', $uid . $key . md5($data)));
			$key=substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);
			return $key;
		}

		static function create_secret($namespace='') {
			$secret='';
			$uid=uniqid(home_url(), false);
			$data=$namespace . serialize($_SERVER);
			$secret=hash('crc32', $uid . $secret . md5($data));
			return $secret;
		}

		static function form_sanitize($var, $type=null) {
			$flags=NULL;
			switch ($type) {
				case 'url' :
					$filter=FILTER_SANITIZE_URL;
					break;
				case 'int' :
					$filter=FILTER_SANITIZE_NUMBER_INT;
					break;
				case 'float' :
					$filter=FILTER_SANITIZE_NUMBER_FLOAT;
					$flags=FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
					break;
				case 'email' :
					$var=substr($var, 0, 254);
					$filter=FILTER_SANITIZE_EMAIL;
					break;
				case 'string' :
				default :
					$filter=FILTER_SANITIZE_STRING;
					$flags=FILTER_FLAG_NO_ENCODE_QUOTES;
					break;
			}
			$output=filter_var($var, $filter, $flags);
			return ($output);
		}

		static function findNearestServer($servers) {
			$min=microtime(true);
			foreach ($servers as $id => $server) {
				$time=microtime(true);
				if (!isset($selected)) $selected=$id;
				$news=new bookingsHttpRequest('http://' . $server[1], 'form');
				$news->timeout=5;
				$news->noErrors=true;
				if ($news->curlInstalled() && $news->live()) {
					$buffer=$news->DownloadToString();
					$delta=microtime(true) - $time;
					if ($delta < $min) {
						$min=$delta;
						$selected=$id;
					}
				}
			}
			return $selected;
		}

		static function isSecure() {
			return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 1 : 0;
		}
	}
}