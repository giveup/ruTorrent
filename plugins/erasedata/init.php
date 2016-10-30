<?php
declare(strict_types=1);

require_once('xmlrpc.php');

$req = new rXMLRPCRequest([
    new rXMLRPCCommand('method.get', ['', 'd.delete_files']),
]);


if ($req->success()) {
    $theSettings->registerPlugin($plugin["name"], $pInfo["perms"]);
} else {
    $jResult.="noty('erasedata: Attempting bootstrap');";

    $req = new rXMLRPCRequest([
        new rXMLRPCCommand('import', ['', __DIR__.'/rtorrent.rc']),
    ]);


    if (!$req->success()) {
        $jResult.="plugin.disable();";
        $jResult.="noty('erasedata: Failed to import ".__DIR__."/rtorrent.rc');";
    }
}
