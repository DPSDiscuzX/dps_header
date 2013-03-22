<?php
/*
        [DISCUZ!] dps_exadmincp/hooker.class.php - 文件作用描述

        Version: 0.01
        Author: Bovvic(671064591@qq.com)
        Copyright: For author
        Last Modified: 2012.12.05
*/


class plugin_dps_header{


	function  __construct() {

	}


	function global_header_analytics(){
		global $_G;
		$siteurl = $_G['siteurl'];
		$siteurl = preg_replace('/(http|https)\:\/\//i', '', $siteurl);
		$siteurl = substr($siteurl, 0, strpos($siteurl, '/'));
		$siteurl = substr($siteurl, strrpos($siteurl,'.',strrpos($siteurl, '.')-strlen($siteurl)-1));
		$analytics = (array)dunserialize($_G['setting']['analytics']);
		include template('dps_header:_tpl_'.__FUNCTION__);
		return $return;
	}

	function global_header_xmlns(){
		global $_G;
		$xmlnslist = (array)dunserialize($_G['setting']['xmlns']);
		include template('dps_header:_tpl_'.__FUNCTION__);
		return $return;
	}

	function global_header_meta(){
		global $_G;
		include template('dps_header:_tpl_'.__FUNCTION__);
		return $return;
	}


}

?>