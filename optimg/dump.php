<?php

include 'config.php';
include 'assets/lang/'.$config['lang'].'.php';
if ($_GET['token'] != md5(md5($config['pass']))) die($lang['wrongtoken']);

$path = str_replace('\\', '/', trim($_GET['path']));
if (substr($path, 0, 1) != '/') die($lang['wrongpath']);
if (substr($path, -1) == '/') $path = substr($path, 0, strlen($path)-1);

$main_dir = explode('/optimg', str_replace('\\', '/', dirname(__FILE__))); $main_dir = realpath($main_dir[0].'/');
//if (substr($main_dir, -1) == '/') $main_dir = substr($main_dir, 0, strlen($main_dir)-1);
$dir = $main_dir.$path.'/';
if (!file_exists($dir)) die(sprintf($lang['nodir'], $dir));

if (empty($_GET['children'])) {
	$images = glob($dir.'*.{jpg,png}', GLOB_BRACE);
	$result = '';
	foreach ($images as $image) {
		$image = explode('/', $image);
		$image = $image[count($image)-1];
		echo '<img src="'.$path.'/'.$image.'"><br><br>';
	}
} else {
	$Directory = new RecursiveDirectoryIterator($dir);
	$Iterator = new RecursiveIteratorIterator($Directory);
	$Regex = new RegexIterator($Iterator, '/^.+(.jpe?g|.png)$/i', RecursiveRegexIterator::GET_MATCH);
	foreach ($Regex as $image => $Regex) {
		echo '<img src="'.str_replace($main_dir, '', $image).'"><br><br>';
	}
}

?>