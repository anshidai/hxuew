<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2015 www.destoon.com
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
class dsession {

    function dsession() {
		if(DT_DOMAIN) @ini_set('session.cookie_domain', '.'.DT_DOMAIN);
    	session_set_save_handler(array(&$this,'open'), array(&$this,'close'), array(&$this,'read'), array(&$this,'write'), array(&$this,'destroy'), array(&$this,'gc'));
		session_cache_limiter('private, must-revalidate');
		session_start();
		header("cache-control: private");
    }

	function open($path, $name) {
		return true;
	}

	function close() {
		return true;
	}

	function read($sid) {
		return eaccelerator_get($sid);
	}

	function write($sid, $data) {
		return eaccelerator_put($sid, $data, 1800);
	}

	function destroy($sid) {
	    return eaccelerator_rm($sid);
	}

	function gc() {
	    return eaccelerator_gc();
	}
}
?>