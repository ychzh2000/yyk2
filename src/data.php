<?php
namespace yyk;
class Data{
	const _Default = false;
	const Int	= 1;
	const Number 	= 2;
	const Float	= 3;
	const Date	= 4;
	const Datetime = 5;
	const Timestamp=6;
	const Arr	= 7;
	const Str 	= 8;
	const Email 	= 9;
	const Hex 	= 10;
	const Bool 	= 11;
	const Object 	= 12;

	const XSS 	= 9;
	const HTML 	= 2;

	static public function get($param, $type, $default=self::_Default){
		switch ($type) {
			case self::Int:
				if (is_numeric($param) && strpos($param, '.')===false) {
					return $param;
				}
				break;

			case self::Number:
				if (is_numeric($param)) {
					return $param;
				}
				break;

			case self::Float:
				if (is_float($param)) {
					return $param;
				}
				break;

			case self::Date:
				if ($param == date('Y-m-d', strtotime($param)) ) {
					return $param;
				}
				break;

			case self::Datetime:
				if ($param == date('Y-m-d H:i:s', strtotime($param)) ) {
					return $param;
				}
				break;

			case self::Timestamp:
				if (is_int($param) && strtotime('1970-01-01')<$param &&  strtotime('2050-12-31')<$param) {
					return $param;
				}
				break;

			case self::Arr:
				if (is_array($param) ) {
					return $param;
				}
				break;

			case self::Str:
				if (is_string($param) ) {
					return $param;
				}
				break;

			case self::Email:
				if (filter_var($param, FILTER_VALIDATE_EMAIL)) {
					return $param;
				}
				break;

			case self::Hex:
				if (is_string($param) && preg_match('/^[0-9a-f]+$/', strtolower($param)) ) {
					return $param;
				}
				break;

			case self::Bool:
				if (is_bool($param) ) {
					return $param;
				}
				break;

			case self::Object:
				if (is_object($param) ) {
					return $param;
				}
				break;

			default:
				# code...
				break;
		}
		return $default;
	}
	

	static public function filter($val, $rank){
		switch ($rank) {
			case 9:
				// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
				// this prevents some character re-spacing such as <java\0script>
				// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
				$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

				// straight replacements, the user should never need these since they're normal characters
				// this prevents like <IMG SRC=@avascript:alert('XSS')>
				$search = 'abcdefghijklmnopqrstuvwxyz';
				$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$search .= '1234567890!@#$%^&*()';
				$search .= '~`";:?+/={}[]-_|\'\\';
				for ($i = 0; $i < strlen($search); $i++) {
					// ;? matches the ;, which is optional
					// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

					// @ @ search for the hex values
					$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
					// @ @ 0{0,7} matches '0' zero to seven times
					$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
				}

				// now the only remaining whitespace attacks are \t, \n, and \r
				$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
				$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
				$ra = array_merge($ra1, $ra2);

				$found = true; // keep replacing as long as the previous round replaced something
				while ($found == true) {
					$val_before = $val;
					for ($i = 0; $i < sizeof($ra); $i++) {
						$pattern = '/';
						for ($j = 0; $j < strlen($ra[$i]); $j++) {
							if ($j > 0) {
								$pattern .= '((&#[xX]0{0,8}([9ab]);)||(&#0{0,8}([9|10|13]);))*';
							}
							$pattern .= $ra[$i][$j];
						}
						$pattern .= '/i';
						$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
						$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
						if ($val_before == $val) {
							// no replacements were made, so exit the loop
							$found = false;
						}
					}
				}
			case 2:
				$val = htmlspecialchars($val, ENT_QUOTES);
				break;
			case 0://不进行任何过滤直接退出
				return $val;
		}
		$val = str_replace("\n", '<br>', $val);

		return $val;
	}
}