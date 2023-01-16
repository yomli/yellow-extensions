<?php

$url = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
$url .= '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';

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

