<?php (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && die('Access Denied');
/*
        [DISCUZ!] dps_exadmincp/hooker.inc.php - 用正则表达式管理嵌入点和非官方修改

        Version: 0.01
        Author: Bovvic(671064591@qq.com)
        Copyright: For author
        Last Modified: 2012.12.05
*/

loadcache('plugin');
global $_G;
$identifier = "dps_header";
$hookers    = array(
	array(
		'hooker'      => '{hook/global_header_xmlns}',
		'file'        => 'common/header_common.htm',
		'pattern'     => '/<html( xmlns(\:[a-z0-9]+)?\=(\'|\")[a-zA-Z0-9\:\/\.]+(\'|\"))*>/i',
		'replacement' => '<html$1$hooker>',
	),
	array(
		'hooker'      => '<!--{hook/global_header_meta}-->',
		'file'        => 'common/header_common.htm',
		'pattern'     => '/(\t*)(<!--{csstemplate}-->)/i',
		'replacement' => '$1$2\r\n$1$hooker',
	),
	array(
		'hooker'      => '<!--{hook/global_header_analytics}-->',
		'file'        => 'common/header.htm',
		'pattern'     => '/<\/head>/i',
		'replacement' => '\t$hooker\n</head>',
	),
);


/*检查文件与内容存在与否*/
function xm_file_content_exists($file, $message) {
	if(file_exists($file)) {
		$content = file_get_contents($file);
		if(substr($message, 0, 1) !== '/') {
			return stripos($content, $message) !== false;
		} else {
			return preg_match($message, $content);
		}
	}
	return false;
}

/*内容替换*/
function xm_file_replace($file, $pattern, $replace, $hooker, $limit = -1) {
	if(file_exists($file)) {
		$content = file_get_contents($file);
		if(is_callable($replace)) {
			$content = preg_replace_callback($pattern, $replace, $content, $limit);
		} elseif(substr($pattern, 0, 1) !== '/') {
			$content = str_replace($pattern, str_replace('$hooker', $hooker, $replace), $content, $limit);
		} else {
			$content = preg_replace($pattern, str_replace('\r', "\r", str_replace('$hooker', $hooker, str_replace('\t', "\t", str_replace('\n', "\n", $replace)))), $content, $limit);
		}
		if($content !== false) {
			file_put_contents($file, $content);
			return true;
		}
	}
	return false;
}

function ht($string) {
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

;

function dh($string) {
	return dhtmlspecialchars($string);
}

;

function he($string) {
	return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
}

;

if(!submitcheck('settingsubmit') && !submitcheck('inserthook')) {
	/*显示设置面板头部*/
	cpheader();
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier='.$identifier.'&pmod=hooker');
	showtableheader('');
	$tableClasses = array('class="td25"', 'class="td29"', 'class="td29"', 'class="td29"', 'class="td29"', 'class="td31"');
	showtablerow(
		'', $tableClasses, array(
			'',
			cplang('嵌入点($hooker)'),
			cplang('嵌入点所在文件($file)'),
			cplang('匹配规则($pattern)'),
			cplang('替换($replacement)'),
			'',
		)
	);


	/*显示已保存的每一条嵌入点插入数据*/
	foreach($hookers as $hook) {

		$str = '';
		/*判断当前模板*/
		$file = DISCUZ_ROOT.$_G['style']['tpldir'].'/'.$hook['file'];
		if(!file_exists($file)) {
			/*查找默认模板*/
			$file = DISCUZ_ROOT.'./template/default/'.$hook['file'];
			if(!file_exists($file)) {
				$str = '找不到对应模板文件';
			}
		}
		if($str == '') {
			$hooker       = he($hook['hooker']);
			$pattern      = he($hook['pattern']);
			$replacement  = he($hook['replacement']);
			$hooker_exist = xm_file_content_exists($file, $hooker);
			$tpd          = he($hook['id']);
			if($hooker_exist) {
				$str = '<span style="color:#999999;">找到嵌入点</span>';
			} else {
				$flag_exist = xm_file_content_exists($file, $pattern);
				if($flag_exist) {
					$str = "<input type=\"submit\" name=\"inserthook[$tpd]\" value=\"插入嵌入点\" />";
					//$result = xm_file_replace($file, $pattern, $replacement, $hooker);
				} else {
					$str = '找不到参照点';
				}
			}
		}
		if($hook['hooker'] !== '') {
			showtablerow(
				'', $tableClasses, array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$hook[id]\" disabled>",
					"<input type=\"text\" class=\"txt\" size=\"20\" name=\"hooker[$hook[id]]\" value=\"".ht($hook[hooker])."\" disabled>",
					"<input type=\"text\" class=\"txt\" size=\"20\" name=\"file[$hook[id]]\" value=\"".ht($hook[file])."\" disabled>",
					"<input type=\"text\" class=\"txt\" size=\"20\" name=\"pattern[$hook[id]]\" value=\"".ht($hook[pattern])."\" disabled>",
					"<input type=\"text\" class=\"txt\" size=\"20\" name=\"replacement[$hook[id]]\" value=\"".ht($hook[replacement])."\" disabled>",
					$str
				)
			);
		}
	}
	showtablefooter();
	showformfooter();
} else if(submitcheck('inserthook')) { /*按"插入嵌入点"按钮后处理*/


	$_CA                   = C::t('common_setting')->fetch_all(null);
	$_CA['templatehooker'] = (array)dunserialize($_CA['templatehooker']);

	//foreach($_CA['templatehooker'] as $id => $value){
	//	print_r($id.'=>'.$value.'<br />');
	//}


	foreach($_GET['inserthook'] as $inserthook => $value) {
		$hook = $_CA['templatehooker'][$inserthook];
		//print_r('1:'.serialize($_CA['templatehooker'][$inserthook]).'<br />');
		//print_r('2 :'.$inserthook.';');

		$str  = '';
		$file = DISCUZ_ROOT.$_G['style']['tpldir'].'/'.$hook['file'];
		if(!file_exists($file)) {
			$file = DISCUZ_ROOT.'./template/default/'.$hook['file'];
			if(!file_exists($file)) {
				$str = '找不到对应模板文件';
			}
		}
		if($str == '') {
			$hooker       = he($hook['hooker']);
			$pattern      = he($hook['pattern']);
			$replacement  = he($hook['replacement']);
			$hooker_exist = xm_file_content_exists($file, $hooker);
			if($hooker_exist) {
				$str = '<span style="color:#999999;">找到嵌入点</span>';
			} else {
				$flag_exist = xm_file_content_exists($file, $pattern);
				if($flag_exist) {
					$str    = "插入嵌入点:<input type=\"submit\" name=\"inserthook[$hook[id]]\" value=\"$hook[id]\" />";
					$result = xm_file_replace($file, $pattern, $replacement, $hooker);
				} else {
					$str = '找不到参照点';
				}
			}
		}
		if($result) {
			cpmsg('嵌入点插入成功', 'action=plugins&operation=config&do='.$pluginid.'&identifier='.$identifier.'&pmod=hooker', 'succeed');
		} else {
			cpmsg(('Error '.$str), 'action=plugins&operation=config&do='.$pluginid.'&identifier='.$identifier.'&pmod=hooker', 'error');
		}
	};
}