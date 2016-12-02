<?php

require_once( '../../php/settings.php' );
require_once( '../../php/rtorrent.php' );

ignore_user_abort(true);
set_time_limit(0);
$errors = [];
$hashes = [];
$rawData = file_get_contents("php://input");
if (isset($rawData)) {
    $vars = explode('&', $rawData);
    $announce_list = [];
    $trackers = [];
    $comment = '';
    $trackersCount = 0;
    $private = 0;
    $setComment = false;
    $setTrackers = false;
    $setPrivate = false;
    foreach ($vars as $var) {
        $parts = explode("=", $var);
        if ($parts[0]=="hash") {
            $hashes[] = $parts[1];
        } elseif ($parts[0]=="comment") {
            $comment = trim(rawurldecode($parts[1]));
        } elseif ($parts[0]=="private") {
            $private = intval($parts[1]);
        } elseif ($parts[0]=="set_comment") {
            $setComment = intval($parts[1]);
        } elseif ($parts[0]=="set_trackers") {
            $setTrackers = intval($parts[1]);
        } elseif ($parts[0]=="set_private") {
            $setPrivate = intval($parts[1]);
        } elseif ($parts[0]=="tracker") {
            $value = trim(rawurldecode($parts[1]));
            if (strlen($value)) {
                $trackers[] = $value;
                $trackersCount = $trackersCount+1;
            } else {
                if (count($trackers)>0) {
                    $announce_list[] = $trackers;
                    $trackers = [];
                }
            }
        }
    }
    if (count($trackers)>0) {
        $announce_list[] = $trackers;
    }
    if ($setComment || $setTrackers || $setPrivate) {
        foreach ($hashes as $hash) {
            $req = new rXMLRPCRequest( array(
                new rXMLRPCCommand("get_session"),
                new rXMLRPCCommand("d.is_open",$hash),
                new rXMLRPCCommand("d.is_active",$hash),
                new rXMLRPCCommand("d.state",$hash),
                new rXMLRPCCommand("d.tied_to_file",$hash),
                new rXMLRPCCommand("d.custom1",$hash),
                new rXMLRPCCommand("d.directory_base",$hash),
                new rXMLRPCCommand("d.connection_seed",$hash),
                new rXMLRPCCommand("d.complete",$hash),
                ) );
            $throttle = null;
            if (rTorrentSettings::get()->isPluginRegistered("throttle"))
                $req->addCommand(new rXMLRPCCommand("d.throttle_name",$hash));
            if ($req->run() && !$req->fault)
            {
                $isStart = (($req->val[1]!=0) && ($req->val[2]!=0) && ($req->val[3]!=0));
                $fname = $req->val[0].$hash.".torrent";
                if (empty($req->val[0]) || !is_readable($fname))
                {
                    if (strlen($req->val[4]) && is_readable($req->val[4]))
                        $fname = $req->val[4];
                    else
                        $fname = null;
                }
                if ($fname) {
                    $torrent = new Torrent($fname);
                    if (!$torrent->errors()) {
                        if ($setPrivate) {
                            $torrent->is_private($private);
                        }
                        if ($setTrackers) {
                            $torrent->clear_announce();
                            $torrent->clear_announce_list();
                            if (count($announce_list)>0)
                            {
                                $torrent->announce($announce_list[0][0]);
                                if ($trackersCount>1)
                                    $torrent->announce_list($announce_list);
                            }
                        }
                        if ($setComment) {
                            $torrent->clear_comment();
                            $comment = trim($comment);
                            if (strlen($comment))
                                $torrent->comment($comment);
                        }
                        if (isset($torrent->{'rtorrent'}))
                            unset($torrent->{'rtorrent'});
                        if (count($req->val)>9)
                            $throttle = getCmd("d.throttle_name.set=").$req->val[9];
                        $eReq = new rXMLRPCRequest( new rXMLRPCCommand("d.erase", $hash ) );
                        if ($eReq->run() && !$eReq->fault)
                        {
                            $label = rawurldecode($req->val[5]);
                            if (!rTorrent::sendTorrent($torrent, $isStart, false, $req->val[6], $label, false, ($req->val[8]==1), false,
                                    array(    getCmd("d.custom3.set")."=1",
                                    getCmd("d.connection_seed.set=").$req->val[7],
                                    $throttle)))
                                $errors[] = array('desc'=>"theUILang.errorAddTorrent", 'prm'=>$fname);
                        }
                        else
                            $errors[] = array('desc'=>"theUILang.badLinkTorTorrent", 'prm'=>'');
                    }
                    else
                        $errors[] = array('desc'=>"theUILang.errorReadTorrent", 'prm'=>$fname);
                }
                else
                    $errors[] = array('desc'=>"theUILang.cantFindTorrent", 'prm'=>'');
                    }
                    else
                $errors[] = array('desc'=>"theUILang.badLinkTorTorrent", 'prm'=>'');
        }
    }
}

cachedEcho(json_encode(array( "errors"=>$errors, "hash"=>$hashes )),"application/json");
