<?php
require_once( __DIR__."/../../php/xmlrpc.php" );
require_once( __DIR__."/../../php/cache.php");
require_once( __DIR__."/../../php/settings.php");
require_once( __DIR__.'/../_task/task.php' );
eval( getPluginConf('unpack') );

class rUnpack
{
    public $hash = "unpack.dat";
    public $enabled = 0;
    public $filter = '/.*/';
    public $path = "";
    public $addLabel = 0;
    public $addName = 0;

    public static function load()
    {
        $cache = new rCache();
        $up = new rUnpack();
        $cache->get($up);
        return($up);
    }
    public function store()
    {
        $cache = new rCache();
        return($cache->set($this));
    }
    public function set()
    {
        if (!isset( $HTTP_RAW_POST_DATA )) {
            $HTTP_RAW_POST_DATA = file_get_contents("php://input");
        }
        if (isset( $HTTP_RAW_POST_DATA )) {
            $vars = explode('&', $HTTP_RAW_POST_DATA);
            $this->enabled = 0;
            $this->path = "";
            foreach ($vars as $var) {
                $parts = explode("=", $var);
                if ($parts[0] == "unpack_enabled") {
                    $this->enabled = $parts[1];
                } elseif ($parts[0] == "unpack_label") {
                    $this->addLabel = $parts[1];
                } elseif ($parts[0] == "unpack_name") {
                    $this->addName = $parts[1];
                } elseif ($parts[0] == "unpack_filter") {
                    $this->filter = trim(rawurldecode($parts[1]));
                    if (@preg_match($this->filter, null) === false) {
                        $this->filter = "/.*/";
                    }
                } elseif ($parts[0] == "unpack_path") {
                    $this->path = trim(rawurldecode($parts[1]));
                    if (($this->path != '') && !rTorrentSettings::get()->correctDirectory($this->path)) {
                        $this->path = '';
                    }
                }
            }
        }
        $this->store();
    }
    public function get()
    {
        return("theWebUI.unpackData = { enabled: ".$this->enabled.", path : '".addslashes($this->path).
            "', filter : '".addslashes($this->filter)."', addLabel: ".$this->addLabel.", addName: ".$this->addName." };\n");
    }
    public function startSilentTask($basename, $downloadname, $label, $name, $hash)
    {
        global $rootPath;
        global $cleanupAutoTasks;
        global $deleteAutoArchives;
        global $unpackToTemp;
        global $unpack_debug_enabled;
        if (rTorrentSettings::get()->isPluginRegistered('quotaspace')) {
            require_once( __DIR__."/../quotaspace/rquota.php" );
            $qt = rQuota::load();
            if (!$qt->check()) {
                return;
            }
        }

        $pathToUnrar = getExternal("unrar");
        $pathToUnzip = getExternal("unzip");
        $zipPresent = false;
        $rarPresent = false;
        $outPath = $this->path;
        
        if (($outPath!='') && !rTorrentSettings::get()->correctDirectory($outPath)) {
            $outPath = '';
        }
        
        if (is_dir($basename)) {
            $postfix = "_dir";
            if ($outPath=='') {
                $outPath = $basename;
            }
            $basename = addslash($basename);
            
            $filesToDelete = "";
            $downloadname = addslash($downloadname);
            $Directory = new RecursiveDirectoryIterator($basename);
            $Iterator = new RecursiveIteratorIterator($Directory);
            $rarRegex = new RegexIterator($Iterator, '/.*\.(rar|r\d\d|\d\d\d)$/si');
            $zipRegex = new RegexIterator($Iterator, '/.*\.zip$/si');
            
            if (USE_UNRAR && (sizeof(iterator_to_array($rarRegex)) > 0)) {
                $rarPresent = true;
                if ($deleteAutoArchives) {
                    if ($downloadname === $basename) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: No move operation enabled. Not deleting files.");
                        }
                    } elseif (!file_exists($downloadname)) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: Move operation enabled. Not deleting files.");
                        }
                    } else {
                        foreach ($rarRegex as $fileName) {
                            $filePath = $fileName->getPathname();
                            if (is_link($filePath)) {
                                if ($unpack_debug_enabled) {
                                    toLog("Unpack: SoftLink operation enabled. Deleting " . $filePath);
                                }
                                $filesToDelete .= $filePath . ";";
                            } else {
                                $stat = stat($filePath);
                                if ($stat) {
                                    if ($stat['nlink'] > 1) {
                                        if ($unpack_debug_enabled) {
                                                toLog("Unpack: HardLink operation enabled. Deleting " . $filePath);
                                        }
                                            $filesToDelete .= $filePath . ";";
                                    } else {
                                        if ($unpack_debug_enabled) {
                                            toLog("Unpack: Copy operation enabled. Deleting " . $filePath);
                                        }
                                        $filesToDelete .= $filePath . ";";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (USE_UNZIP && (sizeof(iterator_to_array($zipRegex)) > 0)) {
                $zipPresent = true;
                if ($deleteAutoArchives) {
                    if ($downloadname === $basename) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: No move operation enabled. Not deleting files.");
                        }
                    } elseif (!file_exists($downloadname)) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: Move operation enabled. Not deleting files.");
                        }
                    } else {
                        foreach ($zipRegex as $fileName) {
                            $filePath = $fileName->getPathname();
                            if (is_link($filePath)) {
                                if ($unpack_debug_enabled) {
                                    toLog("Unpack: SoftLink operation enabled. Deleting " . $filePath);
                                }
                                $filesToDelete .= $filePath . ";";
                            } else {
                                $stat = stat($filePath);
                                if ($stat) {
                                    if ($stat['nlink'] > 1) {
                                        if ($unpack_debug_enabled) {
                                            toLog("Unpack: HardLink operation enabled. Deleting " . $filePath);
                                        }
                                        $filesToDelete .= $filePath . ";";
                                    } else {
                                        if ($unpack_debug_enabled) {
                                            toLog("Unpack: Copy operation enabled. Deleting " . $filePath);
                                        }
                                        $filesToDelete .= $filePath . ";";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $mode = (($rarPresent && $zipPresent) ? "all" : ($zipPresent ? "zip" : ($rarPresent ? "rar" : null)));
        } else {
            $postfix = "_file";
            if (USE_UNRAR && (preg_match("'.*\.(rar|r\d\d|\d\d\d)$'si", $basename)==1)) {
                $rarPresent = true;
                if ($deleteAutoArchives) {
                    if ($downloadname === $basename) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: No move operation enabled. Not deleting files.");
                        }
                    } elseif (!file_exists($downloadname)) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: Move operation enabled. Not deleting files.");
                        }
                    } else {
                        if (is_link($basename)) {
                            if ($unpack_debug_enabled) {
                                toLog("Unpack: SoftLink operation enabled. Deleting " . $basename);
                            }
                            $filesToDelete .= $basename;
                        } else {
                            $stat = stat($basename);
                            if ($stat) {
                                if ($stat['nlink'] > 1) {
                                    if ($unpack_debug_enabled) {
                                        toLog("Unpack: HardLink operation enabled. Deleting " . $basename);
                                    }
                                    $filesToDelete .= $basename;
                                } else {
                                    if ($unpack_debug_enabled) {
                                        toLog("Unpack: Copy operation enabled. Deleting " . $basename);
                                    }
                                    $filesToDelete .= $basename;
                                }
                            }
                        }
                    }
                }
            } elseif (USE_UNZIP && (preg_match("'.*\.zip$'si", $basename)==1)) {
                $zipPresent = true;
                if ($deleteAutoArchives) {
                    if ($downloadname === $basename) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: No move operation enabled. Not deleting files.");
                        }
                    } elseif (!file_exists($downloadname)) {
                        if ($unpack_debug_enabled) {
                            toLog("Unpack: Move operation enabled. Not deleting files.");
                        }
                    } else {
                        if (is_link($basename)) {
                            if ($unpack_debug_enabled) {
                                toLog("Unpack: SoftLink operation enabled. Deleting " . $basename);
                            }
                            $filesToDelete .= $basename;
                        } else {
                            $stat = stat($basename);
                            if ($stat) {
                                if ($stat['nlink'] > 1) {
                                    if ($unpack_debug_enabled) {
                                        toLog("Unpack: HardLink operation enabled. Deleting " . $basename);
                                    }
                                    $filesToDelete .= $basename;
                                } else {
                                    if ($unpack_debug_enabled) {
                                        toLog("Unpack: Copy operation enabled. Deleting " . $basename);
                                    }
                                    $filesToDelete .= $basename;
                                }
                            }
                        }
                    }
                }
            }
            if ($outPath=='') {
                $outPath = dirname($basename);
            }
            $mode = ($zipPresent ? 'zip' : ($rarPresent ? 'rar' : null));
        }
        if ($mode) {
            $arh = (($mode == "zip") ? $pathToUnzip : $pathToUnrar);
            $outPath = addslash($outPath);
            if ($this->addLabel && ($label!='')) {
                $outPath.=addslash($label);
            }
            if ($this->addName && ($name!='')) {
                $outPath.=addslash($name);
            }
            if ($unpackToTemp) {
                $randTempDirectory = addslash(uniqid(getTempDirectory()."archive-"));
                if ($unpack_debug_enabled) {
                    toLog("Unpack: Unpack to temp enabled. Unpacking to " . $randTempDirectory);
                }
            } else {
                $randTempDirectory = "";
            }
                $commands[] = escapeshellarg($rootPath.'/plugins/unpack/un'.$mode.$postfix.'.sh')." ".
                escapeshellarg($arh)." ".
                escapeshellarg($basename)." ".
                escapeshellarg($outPath)." ".
                escapeshellarg($pathToUnzip)." ".
                escapeshellarg($filesToDelete)." ".
                escapeshellarg($randTempDirectory);
            if ($cleanupAutoTasks) {
                $commands[] = 'rm -r "${dir}"';
            }
            $task = new rTask([
                'arg'=>basename(delslash($basename)),
                'requester'=>'unpack',
                'name'=>'unpack',
                'hash'=>$hash,
                'dir'=>$outPath,
                'mode'=>null,
                'no'=>null
            ]);
            $task->start($commands, 0);
        }
    }

    public function startTask($hash, $outPath, $mode, $fileno)
    {
        global $rootPath;
        $ret = array( "no"=>-1, "pid"=>0, "status"=>255, "log"=>array(), "errors"=>array("Unknown error.") );

        if (rTorrentSettings::get()->isPluginRegistered('quotaspace')) {
            require_once( __DIR__."/../quotaspace/rquota.php" );
            $qt = rQuota::load();
            if (!$qt->check()) {
                $ret["errors"] = array("Quota limitation was reached. Unpack failed.");
                return($ret);
            }
        }

        $taskArgs = array
        (
            'requester'=>'unpack',
            'name'=>'unpack',
            'hash'=>$hash,
            'dir'=>$outPath,
            'mode'=>$mode,
            'no'=>$fileno
        );
        if (($outPath!='') && !rTorrentSettings::get()->correctDirectory($outPath)) {
            $outPath = '';
        }

        if (!empty($mode)) {
            $req = new rXMLRPCRequest(
                new rXMLRPCCommand("f.frozen_path", array($hash,intval($fileno)))
            );
            if ($req->success()) {
                $filename = $req->val[0];
                if ($filename=='') {
                    $req = new rXMLRPCRequest(array(
                        new rXMLRPCCommand("d.open", $hash),
                        new rXMLRPCCommand("f.frozen_path", array($hash,intval($fileno))),
                        new rXMLRPCCommand("d.close", $hash) ));
                    if ($req->success()) {
                        $filename = $req->val[1];
                    }
                }

                if ($outPath=='') {
                    $outPath = dirname($filename);
                }

                $commands = array();
                $arh = getExternal(($mode == "zip") ? 'unzip' : 'unrar');
                $commands[] = escapeshellarg($rootPath.'/plugins/unpack/un'.$mode.'_file.sh')." ".
                    escapeshellarg($arh)." ".
                    escapeshellarg($filename)." ".
                    escapeshellarg(addslash($outPath));
                $taskArgs['arg'] = basename($filename);
                $task = new rTask($taskArgs);
                $ret = $task->start($commands, 0);
            }
        } else {
            $req = new rXMLRPCRequest(array(
                new rXMLRPCCommand("d.base_path", $hash),
                new rXMLRPCCommand("d.custom1", $hash),
                new rXMLRPCCommand("d.name", $hash) ));
            if ($req->success()) {
                $basename = $req->val[0];
                $label = rawurldecode($req->val[1]);
                $tname = $req->val[2];
                if ($basename=='') {
                    $req = new rXMLRPCRequest(array(
                        new rXMLRPCCommand("d.open", $hash),
                        new rXMLRPCCommand("d.base_path", $hash),
                        new rXMLRPCCommand("d.close", $hash) ));
                    if ($req->success()) {
                        $basename = $req->val[1];
                    }
                }
                $req = new rXMLRPCRequest(
                    new rXMLRPCCommand("f.multicall", array($hash,"",getCmd("f.path=")))
                );
                if ($req->success()) {
                    $rarPresent = false;
                        $zipPresent = false;
                    foreach ($req->val as $no => $name) {
                        if (USE_UNRAR && (preg_match("'.*\.(rar|r\d\d|\d\d\d)$'si", $name)==1)) {
                            $rarPresent = true;
                        } elseif (USE_UNZIP && (preg_match("'.*\.zip$'si", $name)==1)) {
                            $zipPresent = true;
                        }
                    }
                    $mode = ($rarPresent && $zipPresent) ? 'all' : ($rarPresent ? 'rar' : ($zipPresent ? 'zip' : null));
                    if ($mode) {
                        $pathToUnrar = getExternal("unrar");
                        $pathToUnzip = getExternal("unzip");
                        $arh = (($mode == "zip") ? $pathToUnzip : $pathToUnrar);
                        if (is_dir($basename)) {
                            $postfix = "_dir";
                            if ($outPath=='') {
                                $outPath = $basename;
                            }
                            $basename = addslash($basename);
                        } else {
                            $postfix = "_file";
                            if ($outPath=='') {
                                $outPath = dirname($basename);
                            }
                                $pathToUnzip = "";
                        }
                        $outPath = addslash($outPath);
                        if ($this->addLabel && ($label!='')) {
                            $outPath.=addslash($label);
                        }
                        if ($this->addName && ($tname!='')) {
                            $outPath.=addslash($tname);
                        }

                            $commands[] = escapeshellarg($rootPath.'/plugins/unpack/un'.$mode.$postfix.'.sh')." ".
                            escapeshellarg($arh)." ".
                            escapeshellarg($basename)." ".
                            escapeshellarg($outPath)." ".
                            escapeshellarg($pathToUnzip);
                        $taskArgs['arg'] = basename(delslash($basename));
                        $task = new rTask($taskArgs);
                        $ret = $task->start($commands, 0);
                    }
                }
            }
        }
        return($ret);
    }

    public function setHandlers()
    {
        global $rootPath;
        if ($this->enabled) {
            $cmd =  rTorrentSettings::get()->getOnFinishedCommand(array('unpack'.getUser(),
                    getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/unpack/update.php,$'.getCmd('d.directory').'=,$'.getCmd('d.base_filename').'=,$'.getCmd('d.is_multi_file').
                    '=,$'.getCmd('d.custom1').'=,$'.getCmd('d.name').'=,$'.getCmd('d.hash').'=,$'.getCmd('d.custom').'=x-dest,'.getUser().'}'));
        } else {
            $cmd = rTorrentSettings::get()->getOnFinishedCommand(array('unpack'.getUser(), getCmd('cat=')));
        }
        $req = new rXMLRPCRequest($cmd);
        return($req->success());
    }
}
