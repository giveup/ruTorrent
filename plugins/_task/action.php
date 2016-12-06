<?php

require_once( "task.php" );

$ret = [];

switch ($_REQUEST['cmd'])
{
    case "kill":
        $ret = rTask::kill($_REQUEST['no']);
        break;
    case "check":
        $ret = rTask::check($_REQUEST['no']);
        break;
    case "list":
        $ret = rTaskManager::obtain();
        break;
    case "remove":
        $list = [];
        $rawData = file_get_contents("php://input");
        if (isset($rawData)) {
            $vars = explode('&', $rawData);
            foreach ($vars as $var) {
                $parts = explode("=", $var);
                if ($parts[0]=="no") {
                    $value = trim(rawurldecode($parts[1]));
                    if (strlen($value) && intval($value)) {
                        $list[] = $value;
                    }
                }
            }
        }
        $ret = rTaskManager::remove($list);
        break;
}
cachedEcho(json_encode($ret), "application/json");
