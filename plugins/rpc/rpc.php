<?php

require_once( '../../php/xmlrpc.php' );

$rawData = file_get_contents("php://input");
if (isset($rawData)
    && !preg_match("/(execute|import)\s*=/i", $rawData)) {
    $result = rXMLRPCRequest::send($rawData);
    if (!empty($result)) {
        $pos = strpos($result, "\r\n\r\n");
        if ($pos !== false) {
            $result = substr($result, $pos+4);
        }
        cachedEcho($result, "text/xml");
    }
}

header("HTTP/1.0 500 Server Error");
cachedEcho("Link to XMLRPC failed. May be, rTorrent is down?", "text/html");
