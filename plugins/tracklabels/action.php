<?php

require_once( __DIR__."/../../php/util.php" );
require_once( __DIR__."/../../php/Snoopy.class.inc" );

ignore_user_abort(true);
set_time_limit(0);

if (isset($_REQUEST["label"])) {
    $label = strtolower(rawurldecode($_REQUEST["label"]));
    $name = getSettingsPath().'/labels';
    if (!is_dir($name)) {
        makeDirectory($name);
    }
    $name.=('/'.$label.".png");
    if (is_readable($name)) {
        sendFile($name, "image/png");
        exit;
    }
    $name = __DIR__."/labels/".$label.".png";
    if (is_readable($name)) {
        sendFile($name, "image/png");
        exit;
    }
}
if (isset($_REQUEST["tracker"])) {
    $tracker = rawurldecode($_REQUEST["tracker"]);
    $name = __DIR__."/trackers/".$tracker.".png";
    if (is_readable($name)) {
        sendFile($name, "image/png");
        exit;
    }
    $name = getSettingsPath().'/trackers';
    if (!is_dir($name)) {
        makeDirectory($name);
    }
    $name.='/';
    if (strlen($tracker)) {
        $name.=$tracker;
        $name.='.ico';
        if (!is_readable($name)) {
            $url = Snoopy::linkencode("http://".$tracker."/favicon.ico");
            $client = new Snoopy();
            @$client->fetchComplex($url);
            if ($client->status==200) {
                file_put_contents($name, $client->results);
            }
        }
        if (is_readable($name)) {
            sendFile($name, "image/x-icon");
            exit;
        }
    }
}

header("HTTP/1.0 302 Moved Temporarily");
header("Location: ".dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)).'/trackers/unknown.png');
exit();
