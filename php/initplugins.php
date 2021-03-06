<?php
/*
 * initplugins.php, contributed by dmrom
 *
 * Script for loading all ruTorrent plugins on rTorrent's start.
 * Add the following lines into your "rtorrent.rc" file.
 *

# Load all ruTorrent plugins
execute = {sh,-c,/usr/local/bin/php /usr/local/www/rt/php/initplugins.php user_name &}

 *
 * All plugins would be run according their runlevel
 */

if (!chdir(__DIR__)) {
        exit();
}

function getPluginInfo($name)
{
        $info = array(
        'rtorrent.php.error'=>[],
        'rtorrent.external.error'=>[],
        'rtorrent.script.error'=>[],
        'rtorrent.version'=>0x802,
        'plugin.runlevel'=>10.0,
        'plugin.dependencies'=>[],
        'php.extensions.error'=>[],
        'plugin.may_be_shutdowned'=>1,
        'plugin.may_be_launched'=>1,
        );
        $fname = "../plugins/".$name."/plugin.info";
        if (is_readable($fname)) {
            $lines = file($fname);
            foreach ($lines as $line) {
                $fields = explode(":", $line, 2);
                if (count($fields)==2) {
                    $value = addcslashes(trim($fields[1]), "\\\'\"\n\r\t");
                    $field = trim($fields[0]);
                    switch ($field) {
                        case "plugin.may_be_shutdowned":
                        case "plugin.may_be_launched":
                            $info[$field] = intval($value);
                            break;
                        case "plugin.version":
                        case "plugin.runlevel":
                            $info[$field] = floatval($value);
                            break;
                        case "rtorrent.version":
                        case "rtorrent.script.error":
                        case "rtorrent.external.error":
                        case "rtorrent.php.error":
                        case "php.extensions.error":
                        case "plugin.dependencies":
                            $info[$field] = explode(',', $value);
                            break;
    // for compatibility
                        case "version":
                        case "runlevel":
                            $info['plugin.'.$field] = floatval($value);
                            break;
                    }
                }
            }
        }
        return(array_key_exists("plugin.version", $info) ? $info : false);
}

if (!function_exists('preg_match_all')) {
    exit();
}

if (count($argv) > 1) {
    $_SERVER['REMOTE_USER'] = $argv[1];
}

require_once('util.php');
require_once('settings.php');

$tmp = getTempDirectory();
if ($tmp!='/tmp/') {
    makeDirectory($tmp);
}

$theSettings = rTorrentSettings::get(true);
if ($theSettings->linkExist && ($handle = opendir('../plugins'))) {
    $init = [];
    $names = [];
    $phpVersion = phpversion();
    if (($pos=strpos($phpVersion, '-'))!==false) {
        $phpVersion = substr($phpVersion, 0, $pos);
    }

    $userPermissions = array( "__hash__"=>"plugins.dat" );
    $cache = new rCache();
    $cache->get($userPermissions);

    $loadedExtensions = array_map("strtolower", get_loaded_extensions());

    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && is_dir('../plugins/'.$file)) {
            if (!array_key_exists($file, $userPermissions)) {
                $userPermissions[$file] = true;
            }
            $info = getPluginInfo($file);
            if ($info &&
                $info["plugin.may_be_launched"] &&
                !$userPermissions[$file]) {
                $info = false;
            }
            if (($info!==false)) {
                if (count($info['rtorrent.external.error'])) {
                    eval( getPluginConf($file) );
                }
                $extError = false;
                foreach ($info['rtorrent.external.error'] as $external) {
                    if (findEXE($external)==false) {
                        $extError = true;
                        break;
                    }
                }
                if ($extError) {
                    continue;
                }
                foreach ($info['rtorrent.script.error'] as $external) {
                    $fname = $rootPath.'/plugins/'.$file.'/'.$external;
                    @chmod($fname, $profileMask & 0755);
                    if (!is_executable($fname) || !is_readable($fname)) {
                        $extError = true;
                        break;
                    }
                }
                if ($extError) {
                    continue;
                }
                foreach ($info['rtorrent.php.error'] as $external) {
                    $fname = $rootPath.'/plugins/'.$file.'/'.$external;
                    @chmod($fname, $profileMask & 0644);
                    if (!is_readable($fname)) {
                        $extError = true;
                        break;
                    }
                }
                if ($extError) {
                    continue;
                }
                foreach ($info['php.extensions.error'] as $extension) {
                    if (!in_array($extension, $loadedExtensions)) {
                        $extError = true;
                        break;
                    }
                }
                if ($extError) {
                    continue;
                }
                $php = "../plugins/".$file."/init.php";
                if (!is_readable($php)) {
                    $php = null;
                }
                $init[] = array( "php" => $php, "name" => $file, "level" => $info["plugin.runlevel"], "deps"=>$info["plugin.dependencies"] );
                $names[] = $file;
            }
        }
    }
    closedir($handle);
    usort($init, function($a, $b){
            $runlevel = ($a['level'] <=> $b['level']);

            if ($runlevel !== 0) {
                return $runlevel;
            }

            return strcasecmp($a['name'], $b['name']);
    });
    $do_diagnostic = false;
    $jResult = '';
    $jEnd = '';
    $pInfo = array( "perms" => 0x0100 );
    foreach ($init as $plugin) {
        if ($plugin["php"] && !count(array_diff($plugin["deps"], $names))) {
            require_once( $plugin["php"] );
        }

    }
    $theSettings->store();
}
