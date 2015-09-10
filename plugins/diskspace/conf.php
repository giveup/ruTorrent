<?php

$diskUpdateInterval = 10;   // in seconds
$notifySpaceLimit = 512;    // in Mb

if (isLocalMode() && rTorrentSettings::get()->linkExist && file_exists(rTorrentSettings::get()->directory)) {
    // Then we can show the disk space of the download directory
    $partitionDirectory = rTorrentSettings::get()->directory;
} else {
    // Else, we show $topDirectory by default as fallback
    $partitionDirectory = &$topDirectory;
}
