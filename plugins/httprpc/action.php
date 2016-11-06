<?php

require_once('../../php/xmlrpc.php');
require_once('rpccache.php');

$mode = "raw";
$add = array();
$ss = array();
$vs = array();
$hash = array();

$HTTP_RAW_POST_DATA = file_get_contents("php://input");
if (isset($HTTP_RAW_POST_DATA)) {
    $vars = explode('&', $HTTP_RAW_POST_DATA);
    foreach ($vars as $var) {
        $parts = explode("=", $var);
        switch ($parts[0]) {
            case "cmd":
                $c = getCmd(rawurldecode($parts[1]));
                if (strpos($c, "execute")===false) {
                    $add[] = $c;
                }
                break;
            case "s":
                $ss[] = rawurldecode($parts[1]);
                break;
            case "v":
                $vs[] = rawurldecode($parts[1]);
                break;
            case "hash":
                $hash[] = $parts[1];
                break;
            case "mode":
                $mode  = $parts[1];
                break;
            case "cid":
                $cid  = $parts[1];
                break;
        }
    }
}

function makeMulticall($cmds, $hash, $add, $prefix)
{
    $cmd = new rXMLRPCCommand($prefix.".multicall", [$hash, ""]);
    $cmd->addParameters(array_map("getCmd", $cmds));
    foreach ($add as $prm) {
        $cmd->addParameter($prm);
    }
    $cnt = count($cmds)+count($add);
    $req = new rXMLRPCRequest($cmd);
    if ($req->success()) {
            $result = array();
        for ($i = 0; $i<count($req->val); $i+=$cnt) {
            $result[] = array_slice($req->val, $i, $cnt);
        }
        return($result);
    }
    return(false);
}

function makeSimpleCall($cmds, $hash)
{
    $req = new rXMLRPCRequest();
    foreach ($hash as $h) {
        foreach ($cmds as $cmd) {
            $req->addCommand(new rXMLRPCCommand($cmd, $h));
        }
        return($req->success() ? $req->val : false);
    }
}

$result = null;

switch ($mode) {
    case "list":    /**/
        $cmds = array(
            "d.hash=",
            "d.is_open=",
            "d.is_hash_checking=",
            "d.is_hash_checked=",
            "d.state=",
            "d.name=",
            "d.size_bytes=",
            "d.completed_chunks=",
            "d.size_chunks=",
            "d.bytes_done=",
            "d.up.total=",
            "d.ratio=",
            "d.up.rate=",
            "d.down.rate=",
            "d.chunk_size=",
            "d.custom1=",
            "d.peers_accounted=",
            "d.peers_not_connected=",
            "d.peers_connected=",
            "d.peers_complete=",
            "d.left_bytes=",
            "d.priority=",
            "d.state_changed=",
            "d.skip.total=",
            "d.hashing=",
            "d.chunks_hashed=",
            "d.base_path=",
            "d.creation_date=",
            "d.tracker_focus=",
            "d.is_active=",
            "d.message=",
            "d.custom2=",
            "d.free_diskspace=",
            "d.is_private=",
            "d.is_multi_file="
        );
        $cmd = new rXMLRPCCommand("d.multicall", "main");
        $cmd->addParameters(array_map("getCmd", $cmds));
        foreach ($add as $prm) {
            $cmd->addParameter($prm);
        }
        $cnt = count($cmds)+count($add);
        $req = new rXMLRPCRequest($cmd);
        if ($req->success()) {
            $theCache = new rpcCache();
            $dTorrents = array();
            $torrents = array();
            foreach ($req->val as $index => $value) {
                if ($index % $cnt == 0) {
                    $current_index = $value;
                    $torrents[$current_index] = array();
                } else {
                    $torrents[$current_index][] = $value;
                }
            }

            $theCache->calcDifference($cid, $torrents, $dTorrents);
            $result = array( "t"=>$torrents, "cid"=>$cid );
            if (count($dTorrents)) {
                $result["d"] = $dTorrents;
            }
        }
        break;
    case "fls":    /**/
        $result = makeMulticall(array(
            "f.path=",
            "f.completed_chunks=",
            "f.size_chunks=",
            "f.size_bytes=",
            "f.priority="
        ), $hash[0], $add, 'f');
        break;
    case "prs":    /**/
        $result = makeMulticall(array(
            "p.id=",
            "p.address=",
            "p.client_version=",
            "p.is_incoming=",
            "p.is_encrypted=",
            "p.is_snubbed=",
            "p.completed_percent=",
            "p.down_total=",
            "p.up_total=",
            "p.down_rate=",
            "p.up_rate=",
            "p.id_html=",
            "p.peer_rate=",
            "p.peer_total=",
            "p.port="
        ), $hash[0], $add, 'p');
        break;
    case "trk":    /**/
        $result = makeMulticall(array(
            "t.url=",
            "t.type=",
            "t.is_enabled=",
            "t.group=",
            "t.scrape_complete=",
            "t.scrape_incomplete=",
            "t.scrape_downloaded=",
            "t.normal_interval=",
            "t.scrape_time_last="
        ), $hash[0], $add, 't');
        break;
    case "stg":    /**/
        $cmds = array(
            "network.bind_address",
            "pieces.hash.on_completion",
            "dht.port",
            "directory.default",
            "throttle.global_down.max_rate",
            "network.http.cacert",
            "network.http.capath",
            "network.http.proxy_address",
            "network.local_address",
            "throttle.max_downloads.div",
            "throttle.max_downloads.global",
            "system.file.max_size",
            "pieces.memory.max",
            "network.max_open_files",
            "network.http.max_open",
            "throttle.max_peers.normal",
            "throttle.max_peers.seed",
            "throttle.max_uploads",
            "throttle.max_uploads.global",
            "throttle.min_peers.seed",
            "throttle.min_peers.normal",
            "protocol.pex",
            "network.port_open",
            "throttle.global_up.max_rate",
            "network.port_random",
            "network.port_range",
            "pieces.preload.min_size",
            "pieces.preload.min_rate",
            "pieces.preload.type",
            "network.proxy_address",
            "network.receive_buffer.size",
            "pieces.sync.always_safe",
            "network.scgi.dont_route",
            "network.send_buffer.size",
            "session.path",
            "session.use_lock",
            "session.on_completion",
            "system.file.split_size",
            "system.file.split_suffix",
            "pieces.sync.timeout_safe",
            "pieces.sync.timeout",
            "trackers.numwant",
            "trackers.use_udp",
            "throttle.max_uploads.div",
            "network.max_open_sockets"
        );

        $cmds[5] = $cmds[6] = $cmds[7] = "cat";
        $req = new rXMLRPCRequest(new rXMLRPCCommand("dht.statistics"));
        foreach ($cmds as $cmd) {
            $req->addCommand(new rXMLRPCCommand($cmd));
        }
        foreach ($add as $prm) {
            $req->addCommand(new rXMLRPCCommand($prm));
        }
        if ($req->success()) {
            $result = array();
            $dht_active = $req->val[0];
            $dht = $req->val[1];
            $i = 3;
            if ($dht_active!='0') {
                $i+=(count($req->val)-51);
                $dht = $req->val[5];
            }
            $result = array_slice($req->val, $i, count($cmds));
            array_unshift($result, (($dht=="auto") || ($dht=="on")) ? 1 : 0);
        }
        break;
    case "ttl":    /**/
        $cmds = array(
                "get_up_total", "get_down_total", "get_upload_rate", "get_download_rate"
                );
        $req = new rXMLRPCRequest();
        foreach ($cmds as $cmd) {
            $req->addCommand(new rXMLRPCCommand($cmd));
        }
        foreach ($add as $prm) {
            $req->addCommand(new rXMLRPCCommand($prm));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "prp":    /**/
        $cmds = array(
            "d.peer_exchange", "d.peers_max", "d.peers_min", "d.tracker_numwant", "d.uploads_max",
            "d.is_private", "d.connection_seed"
                );
        $req = new rXMLRPCRequest();
        foreach ($cmds as $cmd) {
            $req->addCommand(new rXMLRPCCommand($cmd, $hash[0]));
        }
        foreach ($add as $prm) {
            $req->addCommand(new rXMLRPCCommand($prm, $hash[0]));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "trkstate":    /**/
        $req = new rXMLRPCRequest();
        foreach ($vs as $ndx => $value) {
            $req->addCommand(new rXMLRPCCommand("t.set_enabled", array($hash[0], intval($value), intval($ss[0]))));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "setprio":    /**/
        $req = new rXMLRPCRequest();
        foreach ($vs as $v) {
            $req->addCommand(new rXMLRPCCommand("f.set_priority", array($hash[0], intval($v), intval($ss[0]))));
        }
        $req->addCommand(new rXMLRPCCommand("d.update_priorities", $hash[0]));
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "recheck":    /**/
        $result = makeSimpleCall(array("d.check_hash"), $hash);
        break;
    case "start":    /**/
        $result = makeSimpleCall(array("d.open","d.start"), $hash);
        break;
    case "stop":    /**/
        $result = makeSimpleCall(array("d.stop","d.close"), $hash);
        break;
    case "pause":    /**/
        $result = makeSimpleCall(array("d.stop"), $hash);
        break;
    case "unpause":    /**/
        $result = makeSimpleCall(array("d.start"), $hash);
        break;
    case "remove":    /**/
        $result = makeSimpleCall(array("d.erase"), $hash);
        break;
    case "dsetprio":    /**/
        $req = new rXMLRPCRequest();
        foreach ($hash as $ndx => $h) {
            $req->addCommand(new rXMLRPCCommand("d.priority.set", array($h, intval($vs[0]))));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "setlabel":    /**/
        $req = new rXMLRPCRequest();
        foreach ($hash as $ndx => $h) {
            $req->addCommand(new rXMLRPCCommand("d.custom1.set", array($h, $vs[0])));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "trkall":    /**/
        $cmds = array(
            "t.url=",
            "t.type=",
            "t.is_enabled=",
            "t.group=",
            "t.scrape_complete=",
            "t.scrape_incomplete=",
            "t.scrape_downloaded="
        );
        $result = array();
        if (empty($hash)) {
            $prm = getCmd("cat").'="$'.getCmd("t.multicall=").getCmd("d.hash=").",";
            foreach ($cmds as $tcmd) {
                $prm.=getCmd($tcmd).','.getCmd("cat=#").',';
            }
            foreach ($add as $tcmd) {
                $prm.=getCmd($tcmd).','.getCmd("cat=#").',';
            }
            $prm = substr($prm, 0, -1).'"';
            $cnt = count($cmds)+count($add);
            $req = new rXMLRPCRequest();
            $req->addCommand(new rXMLRPCCommand("d.multicall", array(
                "main",
                getCmd("d.hash="),
                $prm
            )));
            if ($req->success()) {
                for ($i = 0; $i< count($req->val); $i+=2) {
                    $tracker = explode('#', $req->val[$i+1]);
                    if (!empty($tracker)) {
                        unset($tracker[ count($tracker)-1 ]);
                    }
                    $result[ $req->val[$i] ] = array_chunk($tracker, $cnt);
                }
            }
        } else {
            foreach ($hash as $ndx => $h) {
                $ret = makeMulticall($cmds, $h, $add, 't');
                if ($ret===false) {
                    $result[$h] = array();
                } else {
                    $result[$h] = $ret;
                }
            }
        }
        break;
    case "setsettings":
        $req = new rXMLRPCRequest();
        foreach ($vs as $ndx => $v) {
            if ($ss[$ndx][0]=='n') {
                $v = floatval($v);
            }
            if (($ss[$ndx]=="sdirectory") && !rTorrentSettings::get()->correctDirectory($v)) {
                continue;
            }
            if ($ss[$ndx]=="ndht") {
                $cmd = new rXMLRPCCommand('dht', (($v==0) ? "disable" : "auto"));
            } else {
                $cmd = new rXMLRPCCommand(substr($ss[$ndx], 1).'.set', $v);
            }
            $req->addCommand($cmd);
        }
        if ($req->getCommandsCount()) {
            if ($req->success()) {
                $result = $req->val;
            }
        } else {
            $result = array();
        }
        break;
    case "setprops":    /**/
        $req = new rXMLRPCRequest();
        foreach ($ss as $ndx => $s) {
            if ($s=="superseed") {
                $conn = ($vs[$ndx]!=0) ? "initial_seed" : "seed";
                $cmd = new rXMLRPCCommand("branch", array(
                    $hash[0],
                    getCmd("d.is_active="),
                    getCmd("cat").'=$'.getCmd("d.stop=").',$'.getCmd("d.close=").',$'.getCmd("d.connection_seed.set=").$conn.',$'.getCmd("d.open=").',$'.getCmd("d.start="),
                    getCmd("d.connection_seed.set=").$conn
                    ));
            } else {
                if ($s=="ulslots") {
                    $cmd = new rXMLRPCCommand("d.uploads_max.set");
                } elseif ($s=="pex") {
                    $cmd = new rXMLRPCCommand("d.peer_exchange.set");
                } else {
                    $cmd = new rXMLRPCCommand("d.set_".$s);
                }
                $cmd->addParameters([$hash[0], $vs[$ndx]]);
            }
            $req->addCommand($cmd);
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "setul":    /**/
        $req = new rXMLRPCRequest(new rXMLRPCCommand("set_upload_rate", $ss[0]));
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "setdl":    /**/
        $req = new rXMLRPCRequest(new rXMLRPCCommand("set_download_rate", $ss[0]));
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "unsnub":
    case "snub":
        $on = (($mode=="snub") ? 1 : 0);
        $req = new rXMLRPCRequest();
        foreach ($vs as $v) {
            $req->addCommand(new rXMLRPCCommand("p.snubbed.set", array($hash[0].":p".$v,$on)));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "ban":
        $req = new rXMLRPCRequest();
        foreach ($vs as $v) {
            $req->addCommand(new rXMLRPCCommand("p.banned.set", array($hash[0].":p".$v,1)));
            $req->addCommand(new rXMLRPCCommand("p.disconnect", $hash[0].":p".$v));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "kick":
        $req = new rXMLRPCRequest();
        foreach ($vs as $v) {
            $req->addCommand(new rXMLRPCCommand("p.disconnect", $hash[0].":p".$v));
        }
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "add_peer":
        $req = new rXMLRPCRequest(new rXMLRPCCommand("add_peer", [$hash[0], $vs[0]]));
        if ($req->success()) {
            $result = $req->val;
        }
        break;
    case "getchunks":
        $req = new rXMLRPCRequest([
            new rXMLRPCCommand("d.bitfield", $hash[0]),
            new rXMLRPCCommand("d.chunk_size", $hash[0]),
            new rXMLRPCCommand("d.size_chunks", $hash[0])
        ]);

        $req->addCommand(new rXMLRPCCommand("d.chunks_seen", $hash[0]));
        if ($req->success()) {
            $result = array( "chunks"=>$req->val[0], "size"=>$req->val[1], "tsize"=>$req->val[2] );
            $result["seen"] = $req->val[3];
        }
        break;
    default:
        if (isset($HTTP_RAW_POST_DATA)) {
            $result = rXMLRPCRequest::send($HTTP_RAW_POST_DATA);
            if (!empty($result)) {
                $pos = strpos($result, "\r\n\r\n");
                if ($pos !== false) {
                    $result = substr($result, $pos + 4);
                }
                cachedEcho($result, "text/xml");
            }
        }
        break;
}


if (is_null($result)) {
    header("HTTP/1.0 500 Server Error");
    cachedEcho((isset($req) && $req->fault) ? "Warning: XMLRPC call is failed." : "Link to XMLRPC failed. May be, rTorrent is down?", "text/html");
} else {
    cachedEcho(json_encode($result), "application/json");
}
