<?php

require_once('util.php');
require_once('settings.php');

function getPluginInfo($name)
{
    $info = [
        'rtorrent.need'=>1,
        'rtorrent.remote'=>'ok',
        'rtorrent.external.warning'=>[],
        'rtorrent.external.error'=>[],
        'rtorrent.script.error'=>[],
        'rtorrent.php.error'=>[],
        'rtorrent.version'=>0x802,
        'rtorrent.version.readable'=>'0.8.2',
        'plugin.may_be_shutdowned'=>1,
        'plugin.may_be_launched'=>1,
        'plugin.runlevel'=>10.0,
        'plugin.description'=>'',
        'plugin.author'=>'unknown',
        'plugin.dependencies'=>[],
        'php.extensions.warning'=>[],
        'php.extensions.error'=>[],
        'web.external.warning'=>[],
        'web.external.error'=>[],
        'plugin.help'=>'',
    ];
    $fname = "../plugins/".$name."/plugin.info";
    if (is_readable($fname)) {
        $lines = file($fname);
        foreach ($lines as $line) {
            $fields = explode(":", $line, 2);
            if (count($fields)==2) {
                $value = addcslashes(trim($fields[1]), "\\\'\"\n\r\t");
                $field = trim($fields[0]);
                switch ($field)
                {
                    case "plugin.help":
                    case "plugin.author":
                    case "plugin.description":
                    case "rtorrent.remote":
                        $info[$field] = $value;
                        break;
                    case "plugin.may_be_shutdowned":
                    case "plugin.may_be_launched":
                    case "rtorrent.need":
                        $info[$field] = intval($value);
                        break;
                    case "plugin.version":
                    case "plugin.runlevel":
                        $info[$field] = floatval($value);
                        break;
                    case "rtorrent.version":
                    case "plugin.dependencies":
                    case "rtorrent.external.warning":
                    case "rtorrent.external.error":
                    case "rtorrent.script.error":
                    case "rtorrent.php.error":
                    case "web.external.warning":
                    case "web.external.error":
                    case "php.extensions.warning":
                    case "php.extensions.error":
                        $info[$field] = explode(',', $value);
                        break;

                    // for compatibility
                    case "author":
                    case "description":
                        $info['plugin.'.$field] = $value;
                        break;
                    case "remote":
                        $info['rtorrent.remote'] = $value;
                        break;
                    case "need_rtorrent":
                        $info['rtorrent.need'] = intval($value);
                        break;
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

function findRemoteEXE($exe, $err, &$remoteRequests)
{
    $st = getSettingsPath().'/'.rand();
    if (!array_key_exists($exe, $remoteRequests)) {
        $path=realpath(dirname('.'));
        global $pathToExternals;
        $cmd = [ "sh", addslash($path)."test.sh", $exe, $st ];
        if (isset($pathToExternals[$exe]) && !empty($pathToExternals[$exe])) {
            $cmd[] = $pathToExternals[$exe];
        }
        $req = new rXMLRPCRequest(new rXMLRPCCommand("execute", $cmd));
        $req->run();
        $remoteRequests[$exe] = [ "path"=>$st, "err"=> [] ];
    }
    $remoteRequests[$exe]["err"][] = $err;
}

function testRemoteRequests($remoteRequests)
{
    $ret = "";
    foreach ($remoteRequests as $exe => $info) {
        $file = $info["path"].$exe.".found";
        if (!is_file($file)) {
            foreach ($info["err"] as $err) {
                $ret.=$err;
            }
        } else {
            @unlink($file);
        }
    }
    return($ret);
}

$jResult .= "theURLs.XMLRPCMountPoint = '".$XMLRPCMountPoint."';\n";
$jResult.="theWebUI.systemInfo = {};\ntheWebUI.systemInfo.php = { canHandleBigFiles : ".((PHP_INT_SIZE<=4) ? "false" : "true")." };\n";

if ($handle = opendir('../plugins')) {
    ignore_user_abort(true);
    set_time_limit(0);
    $tmp = getTempDirectory();
    if ($tmp!='/tmp/') {
        makeDirectory($tmp);
    }

    if (!@file_exists($tempDirectory.'/.') || !is_readable($tempDirectory) || !is_writable($tempDirectory)) {
        $jResult.="noty(theUILang.badTempPath+' (".$tempDirectory.")','error');";
    }

    if (!function_exists('preg_match_all')) {
        $jResult.="noty(theUILang.PCRENotFound,'error');";
        $jResult.="theWebUI.systemInfo.rTorrent = { started: false, version : '?', libVersion : '?' };\n";
    } else {
        $remoteRequests = [];
        $theSettings = rTorrentSettings::get(true);
        if (!$theSettings->linkExist) {
            $jResult.="noty(theUILang.badLinkTorTorrent,'error');";
            $jResult.="theWebUI.systemInfo.rTorrent = { started: false, version : '?', libVersion : '?', apiVersion : 0 };\n";
        } else {
            if ($theSettings->idNotFound) {
                $jResult.="noty(theUILang.idNotFound,'error');";
            }
            $jResult.="theWebUI.systemInfo.rTorrent = { started: true, version : '".
                $theSettings->version."', libVersion : '".$theSettings->libVersion."', apiVersion : ".$theSettings->apiVersion." };\n";
            if ($do_diagnostic) {
                $up = getUploadsPath();
                    $st = getSettingsPath();
                @chmod($up, $profileMask);
                @chmod($st, $profileMask);
                @chmod('./test.sh', $profileMask & 0755);
                if (PHP_USE_GZIP && (findEXE('gzip')===false)) {
                    @define('PHP_USE_GZIP', false);
                    $jResult.="noty(theUILang.gzipNotFound,'error');";
                }
                if (PHP_INT_SIZE<=4) {
                    if (findEXE('stat')===false) {
                        $jResult.="noty(theUILang.statNotFoundW,'error');";
                    }
                                    findRemoteEXE('stat', "noty(theUILang.statNotFound,'error');", $remoteRequests);
                }
                if (!@file_exists($up.'/.') || !is_readable($up) || !is_writable($up)) {
                    $jResult.="noty(theUILang.badUploadsPath+' (".$up.")','error');";
                }
                if (!@file_exists($st.'/.') || !is_readable($st) || !is_writable($st)) {
                    $jResult.="noty(theUILang.badSettingsPath+' (".$st.")','error');";
                }
                if (isLocalMode() && !$theSettings->idNotFound) {
                    if ($theSettings->uid<0) {
                        $jResult.="noty(theUILang.cantObtainUser,'error');";
                    } else {
                        if (!isUserHavePermission($theSettings->uid, $theSettings->gid, $tempDirectory, 0x0007)) {
                            $jResult.="noty(theUILang.badTempPath2+' (".$tempDirectory.")','error');";
                        }
                        if (!isUserHavePermission($theSettings->uid, $theSettings->gid, $up, 0x0007)) {
                            $jResult.="noty(theUILang.badUploadsPath2+' (".$up.")','error');";
                        }
                        if (!isUserHavePermission($theSettings->uid, $theSettings->gid, $st, 0x0007)) {
                            $jResult.="noty(theUILang.badSettingsPath2+' (".$st.")','error');";
                        }
                        if (!isUserHavePermission($theSettings->uid, $theSettings->gid, './test.sh', 0x0005)) {
                            $jResult.="noty(theUILang.badTestPath+' (".realpath('./test.sh').")','error');";
                        }
                    }
                }
            }
        }
        $init = [];
        $names = [];
        $disabled = [];
        $phpVersion = phpversion();
        if (($pos=strpos($phpVersion, '-'))!==false) {
            $phpVersion = substr($phpVersion, 0, $pos);
        }
        $phpRequired = false;

        $userPermissions = [ "__hash__"=>"plugins.dat" ];
        $cache = new rCache();
        $cache->get($userPermissions);

        $cantBeShutdowned   = 0x0080;
        $canBeLaunched      = 0x0100;
        $disabledByUser     = 0x8000;

        $loadedExtensions = array_map("strtolower", get_loaded_extensions());

        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && is_dir('../plugins/'.$file)) {
                if (!array_key_exists($file, $userPermissions)) {
                    $userPermissions[$file] = true;
                }
                $info = getPluginInfo($file);
                if ($info) {
                    if ($info["plugin.may_be_launched"]) {
                        $info["perms"] |= $canBeLaunched;
                        if (!$userPermissions[$file]) {
                            $info["perms"] |= $disabledByUser;
                            $disabled[$file] = $info;
                            $info = false;
                        }
                    } else {
                        $info["perms"] |= $cantBeShutdowned;
                    }
                }
                if ($info!==false) {
                    if (!$theSettings->linkExist && $info["rtorrent.need"]) {
                        $disabled[$file] = $info;
                        continue;
                    }
                    $extError = false;

                    foreach ($info['php.extensions.error'] as $extension) {
                        if (!in_array($extension, $loadedExtensions)) {
                            $jResult.="noty('".$file.": '+theUILang.phpExtensionNotFoundError+' ('+'".$extension."'+').','error');";
                            $extError = true;
                        }
                    }
                    if ($extError) {
                        $disabled[$file] = $info;
                        continue;
                    }
                    if (count($info['web.external.error']) ||
                        count($info['web.external.warning']) ||
                        count($info['rtorrent.external.error']) ||
                        count($info['rtorrent.external.warning'])) {
                        eval( getPluginConf($file) );
                    }
                    foreach ($info['web.external.error'] as $external) {
                        if (findEXE($external)==false) {
                            $jResult.="noty('".$file.": '+theUILang.webExternalNotFoundError+' ('+'".$external."'+').','error');";
                            $extError = true;
                        } else {
                            if ($external=='php') {
                                                        $phpRequired = true;
                            }
                        }
                    }
                    if ($extError) {
                        $disabled[$file] = $info;
                        continue;
                    }
                    if ($theSettings->linkExist) {
                        foreach ($info['rtorrent.external.error'] as $external) {
                                findRemoteEXE($external, "noty('".$file.": '+theUILang.rTorrentExternalNotFoundError+' ('+'".$external."'+').','error'); thePlugins.get('".$file."').disable();", $remoteRequests);
                            if ($external=='php') {
                                $phpRequired = true;
                            }
                        }
                        foreach ($info['rtorrent.script.error'] as $external) {
                            $fname = $rootPath.'/plugins/'.$file.'/'.$external;
                            @chmod($fname, $profileMask & 0755);
                            if (!isUserHavePermission($theSettings->uid, $theSettings->gid, $fname, 0x0005)) {
                                $jResult.="noty('".$file.": '+theUILang.rTorrentBadScriptPath+' ('+'".$fname."'+').','error');";
                                $extError = true;
                            }
                        }
                        if ($extError) {
                            $disabled[$file] = $info;
                            continue;
                        }
                        foreach ($info['rtorrent.php.error'] as $external) {
                            $fname = $rootPath.'/plugins/'.$file.'/'.$external;
                            @chmod($fname, $profileMask & 0644);
                            if (!isUserHavePermission($theSettings->uid, $theSettings->gid, $fname, 0x0004)) {
                                $jResult.="noty('".$file.": '+theUILang.rTorrentBadPHPScriptPath+' ('+'".$fname."'+').','error');";
                                $extError = true;
                            }
                        }
                        if ($extError) {
                            $disabled[$file] = $info;
                            continue;
                        }
                        if (!isLocalMode()) {
                            if ($info["rtorrent.remote"]=="error") {
                                $jResult.="noty('".$file.": '+theUILang.errMustBeInSomeHost,'error');";
                                $disabled[$file] = $info;
                                continue;
                            }
                            if ($do_diagnostic && ($info["rtorrent.remote"]=="warning")) {
                                $jResult.="noty('".$file.": '+theUILang.warnMustBeInSomeHost,'error');";
                            }
                        }
                    }
                    if ($do_diagnostic) {
                        if ($theSettings->linkExist) {
                            foreach ($info['rtorrent.external.warning'] as $external) {
                                findRemoteEXE($external, "noty('".$file.": '+theUILang.rTorrentExternalNotFoundWarning+' ('+'".$external."'+').','error');", $remoteRequests);
                            }
                        }
                        foreach ($info['web.external.warning'] as $external) {
                            if (findEXE($external)==false) {
                                $jResult.="noty('".$file.": '+theUILang.webExternalNotFoundWarning+' ('+'".$external."'+').','error');";
                            }
                        }
                        foreach ($info['php.extensions.warning'] as $extension) {
                            if (!in_array($extension, $loadedExtensions)) {
                                $jResult.="noty('".$file.": '+theUILang.phpExtensionNotFoundWarning+' ('+'".$extension."'+').','error');";
                            }
                        }
                    }
                    $js = "../plugins/".$file."/init.js";
                    if (!is_readable($js)) {
                        $js = null;
                    }
                                $php = "../plugins/".$file."/init.php";
                    if (!is_readable($php)) {
                        $php = null;
                    }
                    $init[] = [ "js" => $js, "php" => $php, "info" => $info, "name" => $file ];
                    $names[] = $file;
                }
            }
        }
        if ($phpRequired) {
            $val = strtoupper(ini_get("register_argc_argv"));
            if ($val!=='' && $val!='ON' && $val!='1' && $val!='TRUE') {
                $jResult.="noty(theUILang.phpParameterUnavailable,'error');";
            }
        }
        usort($init, function ($a, $b) {
            $runlevel = ($a['info']['plugin.runlevel'] <=> $b['info']['plugin.runlevel']);

            if ($runlevel !== 0) {
                return $runlevel;
            }

            return strcasecmp($a['name'], $b['name']);
        });
        foreach ($init as $plugin) {
            $jEnd = '';
                $pInfo = $plugin["info"];

            $deps = array_diff($pInfo["plugin.dependencies"], $names);
            if (count($deps)) {
                $jResult.="noty('".$plugin["name"].": '+theUILang.dependenceError+' ".implode(",", $deps)."','error');";
                $disabled[$plugin["name"]] = $pInfo;
                continue;
            }

            $jResult.="(function () { var plugin = new rPlugin( '".$plugin["name"]."',".$pInfo["plugin.version"].
                ",'".$pInfo["plugin.author"]."','".$pInfo["plugin.description"]."',".$pInfo["perms"].",'".$pInfo["plugin.help"]."' );\n";
            if ($plugin["php"]) {
                require_once( $plugin["php"] );
            } else {
                $theSettings->registerPlugin($plugin["name"], $pInfo["perms"]);
            }
            if ($plugin["js"]) {
                $js = file_get_contents($plugin["js"]);
                if ($theSettings->isPluginRegistered($plugin["name"])) {
                    $jResult.=$js;
                } else {
                    if (strpos($js, "plugin.loadLang()")!==false) {
                        $jResult.="plugin.loadLang();";
                    }
                }
                $jResult.="\n";
            }
            $jResult.=$jEnd;
            $jResult.="\n})();";
        }
        foreach ($disabled as $name => $pInfo) {
            $jResult.="(function () { var plugin = new rPlugin( '".$name."',".$pInfo["plugin.version"].
                ",'".$pInfo["plugin.author"]."','".$pInfo["plugin.description"]."',".$pInfo["perms"].",'".$pInfo["plugin.help"]."' );\n";
            $jResult.="plugin.disable(); ";
            if ($pInfo["perms"] & $disabledByUser) {
                $jResult.="plugin.unlaunch(); ";
            }
            $jResult.="\n})();";
        }
        $jResult.=testRemoteRequests($remoteRequests);
        $theSettings->store();
    }
    closedir($handle);
}

cachedEcho($jResult, "application/javascript", true);
