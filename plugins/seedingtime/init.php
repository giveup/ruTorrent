<?php

$req = new rXMLRPCRequest(array(
    $theSettings->getOnFinishedCommand(array("seedingtime".getUser(),
        getCmd('d.custom.set').'=seedingtime,"$'.getCmd('execute_capture').'={date,+%s}"')),
    $theSettings->getOnInsertCommand(array("addtime".getUser(),
        getCmd('d.custom.set').'=addtime,"$'.getCmd('execute_capture').'={date,+%s}"')),

    $theSettings->getOnHashdoneCommand(array("seedingtimecheck".getUser(),
        getCmd('branch=').'$'.getCmd('not=').'$'.getCmd('d.complete=').',,'.
        getCmd('d.custom').'=seedingtime,,"'.getCmd('d.custom.set').'=seedingtime,$'.getCmd('d.custom').'=addtime'.'"')),
    ));
if ($req->success()) {
        $theSettings->registerPlugin($plugin["name"], $pInfo["perms"]);
} else {
    $jResult .= "plugin.disable(); noty('seedingtime: '+theUILang.pluginCantStart,'error');";
}
