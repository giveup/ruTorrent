<?php
require_once('rules.php');

$cmd = '';
if (isset($_REQUEST['mode'])) {
    $cmd = $_REQUEST['mode'];
}
$mngr = rRatioRulesList::load();
$val = null;

switch ($cmd) {
    case "setrules":
        $mngr->set();
        break;
    case "checklabels":
        $hash = [];
        $rawData = file_get_contents("php://input");
        if (isset($rawData)) {
            $vars = explode('&', $rawData);
            foreach ($vars as $var) {
                $parts = explode("=", $var);
                switch ($parts[0]) {
                    case "hash":
                        $hash[] = $parts[1];
                        break;
                }
            }
        }
        $mngr->checkLabels($hash);
        $val = [];
        break;
}

if (is_null($val)) {
    $val = $mngr->getContents();
}

cachedEcho(json_encode($val), "application/json", true);
