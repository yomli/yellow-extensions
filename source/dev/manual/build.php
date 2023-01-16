<?php

// Datenstrom Yellow is for people who make websites, https://datenstrom.se/yellow/
// Copyright (c) 2013-2023 Datenstrom, https://datenstrom.se
// This file may be used and distributed under the terms of the public license.

// Much faster than md5_file on large files
// (~50% faster with 1GB file)
function sameFile($file1, $file2) {
	if (filetype($file1) !== filetype($file2)) return false;
	if (filesize($file1) !== filesize($file2)) return false;

    if (!$handle1 = fopen($file1, 'rb')) return false;
    if (!$handle2 = fopen($file2, 'rb')) {
        fclose($handle1);
        return false;
    }

    $same = true;
    while (!feof($handle1) && !feof($handle2)) {
        if (fread($handle1, 4096) !== fread($handle2, 4096)) {
            $same = false;
            break;
        }
	}

    if (feof($handle1) !== feof($handle2)) $same = false;

    fclose($handle1);
    fclose($handle2);

    return $same;

}


function deleteDir($path) {
	clearstatcache();
	$iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($files as $file) {
		if ($file->getType() == "dir") {
			@rmdir($file->getPathname());
		} else {
			@unlink($file->getPathname());
		}
	}
	return @rmdir($path);
}

function getDirectoryContent($path) {
	$content = array();
	$iterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($files as $file) {
		$content[] = trim(str_replace($path, '', $file->getPathname()), DIRECTORY_SEPARATOR);
	}
	return $content;
}

$execTime = -microtime(true);

$root = __DIR__ . DIRECTORY_SEPARATOR;
$yellowDir = $root . "site";
$staticDir = $yellowDir . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR;

$url = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
$url .= '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
echo "<pre>Building static website $url</pre>";

chdir($yellowDir);
require_once("system/extensions/core.php");

$yellow = new YellowCore();
$yellow->load();

// Set
$yellow->system->set("staticUrl", $url);

// Clean any leftover
if (is_dir($staticDir)) deleteDir($staticDir);

// Clean /media/extensions and /media/resources
// because they are not updated when building the site…
if (is_dir("media/extensions")) deleteDir(getcwd() . "/media/extensions");
if (is_dir("media/resources")) deleteDir(getcwd() . "/media/resources");

// BUILD SEEMS TO COPY ANY FILE THAT IS PRESENT ON ROOT FOLDER WHICH IS NOT .HTACCESS OR YELLOW.PHP
// INVESTIGATE THAT
// So: command::getContentLocations() get the pages from core::index() *with invisible*
// So: to avoid that behaviour, we *must* place Yellow in a subdir
// and generate.php will be placed either in the same subdir or on root
// REMEMBER THAT ALL FILES IN YELLOW SUBDIR WILL BE COPIED EXCEPT YELLOW.PHP AND .HTACCESS
echo "<pre>";
$statusCode = $yellow->command("build", $staticDir);
echo "</pre>";

// Incremental listing
$staticDirContent = getDirectoryContent($staticDir);
$lastBuildJSON = $root . "build.json";
if (is_file($lastBuildJSON)) {
	$lastBuild = json_decode(file_get_contents($lastBuildJSON), true); // Not safe PHP

	$diff = array_diff($lastBuild, $staticDirContent);

	clearstatcache();
	foreach ($diff as $file) {
		$path = $root . $file;
		if (is_dir($path)) {
			// rmdir() will not remove non-empty dirs
			// making it safe to use here
			@rmdir($path);
		} else {
			@unlink($path);
		}
	}

//	echo "Last build:";
//	echo "<pre>";
//	print_r($lastBuild);
//	echo "</pre>";

	if (!empty($diff)) {
		echo "Diff:";
		echo "<pre>";
		print_r($diff);
		echo "</pre>";
	}
}

clearstatcache();
foreach ($staticDirContent as $file) {
	$path = dirname($root . $file);
	if (!empty($path) && !is_dir($path)) @mkdir($path, 0777, true);
	// No need to copy if same file
	// if (md5_file($staticDir . $file) != md5_file($root . $file)) @copy($staticDir . $file, $root . $file); // 7s with 1GB files
	if (!sameFile($staticDir . $file, $root . $file)) @copy($staticDir . $file, $root . $file); // 3 s with 1GB files
}

file_put_contents($lastBuildJSON, json_encode($staticDirContent)); // Not safe PHP

if ($statusCode < 400 && is_dir($staticDir)) {
	echo "<pre>Deleting temporary folder</pre>";
	deleteDir($staticDir);
	$execTime += microtime(true);
	echo "<pre>Static website built in $execTime sec.</pre>"; // 0,07 s
} else {
	echo "<pre>Error on building the static website!\nStatus code:$statusCode</pre>";
	exit(1);
}

// Varnish cache
define("SILENT", false); // Set to true to hide Varnish result
echo "<pre>Purging the Varnish cache</pre>";
if ($ch = curl_init($url)) {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PURGEALL");
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_NOBODY, SILENT);
    curl_exec($ch);
    curl_close($ch);
}

exit(0);
