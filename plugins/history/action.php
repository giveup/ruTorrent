<?php
require_once('history.php');

if (isset($_REQUEST['cmd'])) {
    $cmd = $_REQUEST['cmd'];
    switch ($cmd) {
        case "set":
            $up = rHistory::load();
            $up->set();
            cachedEcho($up->get(), "application/javascript");
            break;
        case "get":
            $up = rHistoryData::load();
            cachedEcho(json_encode($up->get($_REQUEST['mark'])), "application/json");
            break;
        case "delete":
            $up = rHistoryData::load();
            $hashes = [];
            $rawData = file_get_contents("php://input");
            if (isset($rawData)) {
                $vars = explode('&', $rawData);
                foreach ($vars as $var) {
                    $parts = explode("=", $var);
                    $hashes[] = $parts[1];
                }
                $up->delete($hashes);
            }
            cachedEcho(json_encode($up->get(0)), "application/json");
            break;
    }
}
