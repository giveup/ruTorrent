<?php
require_once( '../../php/rtorrent.php' );

if (isset($_REQUEST['result'])) {
    cachedEcho('noty(theUILang.cantFindTorrent,"error");', "text/html");
}
if (isset($_REQUEST['hash'])) {
    $torrent = rTorrent::getSource($_REQUEST['hash']);
    if ($torrent) {
        $torrent->send();
    }
}
header("HTTP/1.0 302 Moved Temporarily");
header("Location: ".parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH).'?result=0');
