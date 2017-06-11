<?php
/**
 +------------------------------------------------------------------------------
 * Xxuyou class
 +------------------------------------------------------------------------------
 * @author guanxuejun@gmail.com
 +------------------------------------------------------------------------------
 */
class Xxuyou {
	const SSL = 'https://';
	const TIMEOUT = 30;
	static function get($url) {
		return self::_get($url);
	}
	static function put($url, array $data) {
		return self::_post($url, 'PUT', '', $data);
	}
	static function post($url, array $data) {
		return self::_post($url, 'POST', '', $data);
	}
	static function delete($url) {
		return self::_post($url, 'DELETE', '');
	}
	/**
	 +----------------------------------------------------------
	 * GET 方式打开远程 url 地址
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $url 远程地址
	 * @param string $userAgent 模拟浏览器代理
	 * @param string $timeout 运行超时限制
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	static function _get($url, $userAgent='', $timeout=self::TIMEOUT) {
		$ssl = substr($url, 0, 8) == self::SSL ? true : false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($userAgent != '') curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		if ($ssl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
		};
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // IPv4
		$r = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return array('info' => $info, 'result' => $r);
	}
	/**
	 +----------------------------------------------------------
	 * POST 数据给远程 url 地址
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $url 远程地址
	 * @param string $method 设置自定义提交方法
	 * @param string $header 设置HTTP头
	 * @param array $body 键值对形式的数据
	 * @param string $userAgent 模拟浏览器代理
	 * @param string $timeout 运行超时限制
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	static function _post($url, $method='', $header, $body, $userAgent='', $timeout=self::TIMEOUT) {
		$ssl = substr($url, 0, 8) == self::SSL ? true : false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($method) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);           //POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);    //POST数据
		if ($userAgent != '') curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		if ($ssl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
		};
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // IPv4
		$r = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return array('info' => $info, 'result' => $r);
	}
}