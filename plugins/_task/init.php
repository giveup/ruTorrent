<?php
eval(getPluginConf($plugin["name"]));
require_once( __DIR__."/task.php" );

rTaskManager::cleanup();
$theSettings->registerPlugin($plugin["name"], $pInfo["perms"]);

$jResult .= "plugin.maxConcurentTasks = ".$maxConcurentTasks.";";
$jResult .= "plugin.showTabAlways = ".$showTabAlways.";";
