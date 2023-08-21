<?php

//RewriteRule ^v/(.*)$ /inc/video/public/play.php?u=$1 [L,QSA]


$allowedDomains = ['https://www.morgantiuniversity.com', 'https://morgantiuniversity.com']; // Substitua pelos domínios autorizados

function isDomainAllowed($referer) {
    global $allowedDomains;
    foreach ($allowedDomains as $domain) {
        if (strpos($referer, $domain) === 0) {
            return true;
        }
    }
    return false;
}

if (!isset($_SERVER['HTTP_REFERER']) || !isDomainAllowed($_SERVER['HTTP_REFERER'])) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Acesso não autorizado.';
    exit;
}

require('../vendor/autoload.php');

$string = $_GET['u'];
$removedStart = substr($string, 2); // Remove os primeiros 2 caracteres
$urlA = substr($removedStart, 0, -2); // Remove os últimos 2 caracteres


$url = "https://www.youtube.com/watch?v=" . isset($urlA) ? $urlA : null;

function send_json($data)
{
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

if (!$url) {
    send_json([
        'error' => 'No URL provided!'
    ]);
}

$youtube = new \YouTube\YouTubeDownloader();

try {
    $links = $youtube->getDownloadLinks($url);

    $best = $links->getFirstCombinedFormat();

    if ($best) {
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

        header('Content-Type: video/mp4');
        readfile ($best->url);
		//echo $best->url;
		//exit;

   
    } else {
        send_json(['error' => 'No links found']);
    }

} catch (\YouTube\Exception\YouTubeException $e) {

    send_json([
        'error' => $e->getMessage()
    ]);
}