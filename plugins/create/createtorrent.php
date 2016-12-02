<?php

if (count($argv) > 2) {
    $_SERVER['REMOTE_USER'] = $argv[2];
}
if (count($argv) > 1) {
    require_once( __DIR__.'/../../php/xmlrpc.php' );
    require_once( __DIR__.'/../../php/Torrent.php' );
    require_once( __DIR__.'/../../php/rtorrent.php' );
    require_once( __DIR__.'/../_task/task.php' );
    eval(getPluginConf('create'));

    $taskNo = $argv[1];
    $fname = rTask::formatPath($taskNo).'/params';
    if (is_file($fname) && is_readable($fname)) {
        $request = unserialize(file_get_contents($fname));
        $comment = '';
        $announce_list = [];
        $trackers = [];
        $trackersCount = 0;
        if (isset($request['trackers'])) {
            $arr = explode("\r", $request['trackers']);
            foreach ($arr as $key => $value) {
                $value = trim($value);
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
        $path_edit = trim($request['path_edit']);
        $piece_size = $request['piece_size'];
        $callback_log = function ($msg) {
            fputs(STDOUT, $msg."\n");
        };
        $callback_err = function ($msg) {
            fputs(STDERR, $msg."\n");
        };

        if (count($announce_list)>0) {
            $torrent = new Torrent($path_edit, $announce_list[0][0], $piece_size, $callback_log, $callback_err);
            if ($trackersCount>1) {
                $torrent->announce_list($announce_list);
            }
        } else {
            $torrent = new Torrent($path_edit, [], $piece_size, $callback_log, $callback_err);
        }

        if (isset($request['comment'])) {
            $comment = trim($request['comment']);
            if (strlen($comment)) {
                $torrent->comment($comment);
            }
        }
        if ($request['private']) {
            $torrent->is_private(true);
        }
        $fname = rTask::formatPath($taskNo).'/result.torrent';
        $torrent->save($fname);

        if ($request['start_seeding']) {
            $fname = getUniqueUploadedFilename($torrent->info['name'].'.torrent');
            $path_edit = trim($request['path_edit']);
            if (is_dir($path_edit)) {
                $path_edit = addslash($path_edit);
            }
            if (rTorrentSettings::get()->correctDirectory($path_edit)) {
                $path_edit = dirname($path_edit);
                if ($resumed = rTorrent::fastResume($torrent, $path_edit)) {
                    $torrent = $resumed;
                }
                $torrent->save($fname);
                rTorrent::sendTorrent($torrent, true, true, $path_edit, null, true, isLocalMode());
                @chmod($fname, $profileMask & 0666);
            }
        }
        exit(0);
    }
    exit(1);
}
