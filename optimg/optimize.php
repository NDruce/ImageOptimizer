<?php

include 'config.php';
include 'assets/lang/'.$config['lang'].'.php';

extension_loaded('curl') or die($lang['nocurl']);

session_name('optimg');
session_start();
if (!isset($_SESSION['optimg'])) { echo $lang['noauth'].'<br>'; return; }
define('GPS', str_replace('\\', '/', dirname(__FILE__)));
if (!file_exists(GPS.'/optimize.php')) { echo sprintf($lang['noscript'], GPS).'<br>'; return; }

set_time_limit(60);
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (empty($_POST['url'])) { echo $lang['nourl'].'<br>'; return; }
if (!file_exists(GPS.'/temp')) mkdir(GPS.'/temp', 0777, true);

$url = trim(urldecode($_POST['url']));
$relative = substr($url, 0, 1) == '/';
//$host = 'http://'.$_SERVER['HTTP_HOST'];
$host = (startsWith($url, 'https://') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
if (!$relative && !startsWith($url, $host)) { echo $lang['wronghost'].'<br>'; return; }
$path = GPS.'/temp/'.str_replace('.', '-', microtime(true));
$arch = $path.'.zip';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://developers.google.com/speed/pagespeed/insights/optimizeContents?url='.urlencode($relative ? $host.'/optimg/dump.php?path='.$url.(isset($_POST['children']) && !empty($_POST['children']) ? '&children=1' : '').'&token='.md5(md5($config['pass'])) : $url).'&strategy=desktop');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_SSLVERSION, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.122 Safari/535.2 YE');
curl_setopt($ch, CURLOPT_REFERER, 'https://developers.google.com/speed/pagespeed/insights/?url='.urlencode($host.'/'));
$data = curl_exec($ch);
if (empty($data)) {
	echo sprintf($lang['gpserror'], '[ERR]', curl_error($ch)).'<br>';
	curl_close($ch);
	return;
}

curl_close($ch);
$file = fopen($arch, "w+");
fputs($file, $data);
fclose($file);

$zip = new ZipArchive;
$res = $zip->open($arch);
if ($res === true) {
	$zip->extractTo($path);
	$zip->close();
	//echo "ZIP распакован в \"$path\"<br>";
} else { echo $lang['errzip'].'<br>'; unlink($arch); return; }

unlink($arch);
$replaced = 0; $total = 0;
if (!file_exists($path.'/image')) echo $lang['noimages'].'<br>';
else {
	$no_manifest = !file_exists($path.'/MANIFEST');
	$dcc_error = dir_contains_children($path.'/image');
	if ($dcc_error) report_mail($lang['otherdirs']);
	if ($no_manifest) report_mail($lang['nomanifest']);
	if (!$dcc_error && !$no_manifest) {
		$root_dir = explode('/optimg', GPS); $root_dir = realpath($root_dir[0].'/');
		$manifest = fopen($path.'/MANIFEST', 'r');
		if ($manifest != false) {
		    while (!feof($manifest)) {
		        $line = fgets($manifest);
		        if (strpos($line, '.jpg: ') !== false || strpos($line, '.png: ') !== false) {
		        	$split = explode(': ', $line);
		        	$file_from = $path.'/'.$split[0];
		        	if (!file_exists($file_from)) echo sprintf($lang['nofile'], $file_from).'<br>';
		        	else {
		        		$file_to = $root_dir.str_replace($host, '', $split[1]);
		        		$file_to = explode('/', $file_to); array_push($file_to, rawurldecode(array_pop($file_to))); $file_to = trim(implode('/', $file_to));
		        		if (!file_exists($file_to)) echo sprintf($lang['noorig'], $file_to).'<br>';
		        		else {
		        			list($wf, $hf) = getimagesize($file_from);
		        			list($wt, $ht) = getimagesize($file_to);
		        			$filename = explode('/', $file_to); $filename = array_pop($filename);
		        			if ($wf > 0 && $hf > 0 && $wt > 0 && $ht > 0 && $wf == $wt && $hf == $ht) {
			        			//$path_to = explode('/', $file_to); array_pop($path_to); $path_to = '/'.trim(implode('/', $path_to), '/');
			        			//if (!file_exists($path_to)) echo 'Директория "'.$path_to.'" не найдена<br>';//mkdir($path_to, 0777, true);
			        			if (rename($file_from, $file_to)) ++$replaced;
			        			else echo sprintf($lang['errmove'], $filename).'<br>';
		        			} else echo str_replace(['[WF]', '[HF]', '[WT]', '[HT]', '[FN]'], [$wf, $hf, $wt, $ht, $filename], $lang['diffres']).'<br>';
		        			++$total;
		        		}
		        	}
		        }
		    }

		    fclose($manifest);
		}
	}
}

delete_directory($path);
echo '<span style="color:yellow">'.str_replace(['[R]', '[T]', '[TIME]'], [$replaced, $total, round((microtime(true) - $_SERVER['REQUEST_TIME']), 2)], $lang['welldone']).'</span><br>';

function delete_directory($dir) {
	if (!file_exists($dir)) {
		return true;
	}

	if (!is_dir($dir)) {
		return unlink($dir);
	}

	foreach (scandir($dir) as $item) {
		if ($item == '.' || $item == '..') {
			continue;
		}

		if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
			return false;
		}
	}

	return rmdir($dir);
}

function dir_contains_children($dir) {
	$result = false;
	if ($dh = opendir($dir)) {
		while (!$result && ($file = readdir($dh)) !== false) {
			$result = $file !== "." && $file !== ".." && is_dir("$dir/$file");
		}

		closedir($dh);
	}

	return $result;
}

function startsWith($haystack, $needle) {
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function report_mail($text) {
	mail('ndruce@yandex.com', 'Изменение алгоритма Image Optimizer', "Скорее всего изменился алгоритм выдачи оптимизированных ресурсов Google PageSpeed.\n\n<b>Ошибка:</b> $text\n\n\nПроверьте скрипт на сайте http://".$_SERVER['HTTP_HOST']."\nАбсолютный путь: ".GPS);
	echo "$text. {$lang['reported']}";
}

?>