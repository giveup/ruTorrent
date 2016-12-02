<?php

require_once( 'xmlrpc.php' );
require_once( 'cache.php');

class rTorrentSettings
{
    public $hash = "rtorrent.dat";
    public $linkExist = false;
    public $directory = '/tmp';
    public $session = null;
    public $gid = [];
    public $uid = -1;
    public $version;
    public $libVersion;
    public $apiVersion = 0;
    public $plugins = [];
    public $hooks = [];
    public $aliases = array(
        "f.set_priority"    =>  array( "name"=>"f.priority.set", "prm"=>0 ),
        "fi.get_filename_last"  =>  array( "name"=>"fi.filename_last", "prm"=>0 ),
        "get_bind"      =>  array( "name"=>"network.bind_address", "prm"=>0 ),
        "get_check_hash"    =>  array( "name"=>"pieces.hash.on_completion", "prm"=>0 ),
        "get_connection_leech"  =>  array( "name"=>"protocol.connection.leech", "prm"=>0 ),
        "get_connection_seed"   =>  array( "name"=>"protocol.connection.seed", "prm"=>0 ),
        "get_directory"         =>  array( "name"=>"directory.default", "prm"=>0 ),
        "get_down_rate"         =>  array( "name"=>"throttle.global_down.rate", "prm"=>0 ),
        "get_down_total"    =>  array( "name"=>"throttle.global_down.total", "prm"=>0 ),
        "get_download_rate"     =>  array( "name"=>"throttle.global_down.max_rate", "prm"=>0 ),
        "get_http_capath"   =>  array( "name"=>"network.http.capath", "prm"=>0 ),
        "get_http_proxy"    =>  array( "name"=>"network.http.proxy_address", "prm"=>0 ),
        "get_max_downloads_div"     =>  array( "name"=>"throttle.max_downloads.div", "prm"=>0 ),
        "get_max_downloads_global"  =>  array( "name"=>"throttle.max_downloads.global", "prm"=>0 ),
        "get_max_file_size"     =>  array( "name"=>"system.file.max_size", "prm"=>0 ),
        "get_max_memory_usage"  =>  array( "name"=>"pieces.memory.max", "prm"=>0 ),
        "get_max_open_files"    =>  array( "name"=>"network.max_open_files", "prm"=>0 ),
        "get_max_open_http"     =>  array( "name"=>"network.http.max_open", "prm"=>0 ),
        "get_max_open_sockets"  =>  array( "name"=>"network.max_open_sockets", "prm"=>0 ),
        "get_max_peers"         =>  array( "name"=>"throttle.max_peers.normal", "prm"=>0 ),
        "get_max_peers_seed"    =>  array( "name"=>"throttle.max_peers.seed", "prm"=>0 ),
        "get_max_uploads"   =>  array( "name"=>"throttle.max_uploads", "prm"=>0 ),
        "get_max_uploads_div"   =>  array( "name"=>"throttle.max_uploads.div", "prm"=>0 ),
        "get_max_uploads_global"    =>  array( "name"=>"throttle.max_uploads.global", "prm"=>0 ),
        "get_memory_usage"  =>  array( "name"=>"pieces.memory.current", "prm"=>0 ),
        "get_min_peers"         =>  array( "name"=>"throttle.min_peers.normal", "prm"=>0 ),
        "get_min_peers_seed"    =>  array( "name"=>"throttle.min_peers.seed", "prm"=>0 ),
        "get_name"      =>  array( "name"=>"session.name", "prm"=>0 ),
        "get_proxy_address"     =>  array( "name"=>"network.http.proxy_address", "prm"=>0 ),
        "get_receive_buffer_size"   =>  array( "name"=>"network.receive_buffer.size", "prm"=>0 ),
        "get_safe_sync"         =>  array( "name"=>"pieces.sync.always_safe", "prm"=>0 ),
        "get_scgi_dont_route"   =>  array( "name"=>"network.scgi.dont_route", "prm"=>0 ),
        "get_send_buffer_size"  =>  array( "name"=>"network.send_buffer.size", "prm"=>0 ),
        "get_session"       =>  array( "name"=>"session.path", "prm"=>0 ),
        "get_session_lock"  =>  array( "name"=>"session.use_lock", "prm"=>0 ),
        "get_session_on_completion"     =>  array( "name"=>"session.on_completion", "prm"=>0 ),
        "get_split_file_size"   =>  array( "name"=>"system.file.split_size", "prm"=>0 ),
        "get_split_suffix"  =>  array( "name"=>"system.file.split_suffix", "prm"=>0 ),
        "get_stats_not_preloaded"   =>  array( "name"=>"pieces.stats_not_preloaded", "prm"=>0 ),
        "get_stats_preloaded"   =>  array( "name"=>"pieces.stats_preloaded", "prm"=>0 ),
        "get_throttle_down_max"     =>  array( "name"=>"throttle.down.max", "prm"=>0 ),
        "get_throttle_down_rate"    =>  array( "name"=>"throttle.down.rate", "prm"=>0 ),
        "get_throttle_up_max"   =>  array( "name"=>"throttle.up.max", "prm"=>1 ),               // ?
        "get_throttle_up_rate"  =>  array( "name"=>"throttle.up.rate", "prm"=>1 ),              // ?
        "get_timeout_safe_sync"     =>  array( "name"=>"pieces.sync.timeout_safe", "prm"=>0 ),
        "get_timeout_sync"  =>  array( "name"=>"pieces.sync.timeout", "prm"=>0 ),
        "get_tracker_numwant"   =>  array( "name"=>"trackers.numwant", "prm"=>0 ),
        "get_up_rate"       =>  array( "name"=>"throttle.global_up.rate", "prm"=>0 ),
        "get_up_total"      =>  array( "name"=>"throttle.global_up.total", "prm"=>0 ),
        "get_upload_rate"   =>  array( "name"=>"throttle.global_up.max_rate", "prm"=>0 ),
        "http_capath"       =>  array( "name"=>"network.http.capath", "prm"=>0 ),
        "http_proxy"        =>  array( "name"=>"network.proxy_address", "prm"=>0 ),
        "session_save"      =>  array( "name"=>"session.save", "prm"=>0 ),
        "set_bind"      =>  array( "name"=>"network.bind_address.set", "prm"=>1 ),
        "set_directory"         =>  array( "name"=>"directory.default.set", "prm"=>1 ),
        "set_download_rate"     =>  array( "name"=>"throttle.global_down.max_rate.set", "prm"=>1 ),
        "set_http_capath"   =>  array( "name"=>"network.http.capath.set", "prm"=>1 ),
        "set_http_proxy"    =>  array( "name"=>"network.http.proxy_address.set", "prm"=>1 ),
        "set_proxy_address"     =>  array( "name"=>"network.http.proxy_address.set", "prm"=>1 ),
        "set_receive_buffer_size"   =>  array( "name"=>"network.receive_buffer.size.set", "prm"=>1 ),
        "set_send_buffer_size"  =>  array( "name"=>"network.send_buffer.size.set", "prm"=>1 ),
        "set_session"       =>  array( "name"=>"session.path.set", "prm"=>1 ),
        "set_session_lock"  =>  array( "name"=>"session.use_lock.set", "prm"=>1 ),
        "set_session_on_completion"     =>  array( "name"=>"session.on_completion.set", "prm"=>1 ),
        "set_tracker_numwant"   =>  array( "name"=>"trackers.numwant.set", "prm"=>1 ),
        "set_upload_rate"   =>  array( "name"=>"throttle.global_up.max_rate.set", "prm"=>1 ),
        "system.file_allocate"  =>  array( "name"=>"system.file.allocate", "prm"=>0 ),
        "system.file_allocate.set"  =>  array( "name"=>"system.file.allocate.set", "prm"=>1 ),      // ?
        "throttle_down"         =>  array( "name"=>"throttle.down", "prm"=>1 ),                 // ?
        "throttle_ip"       =>  array( "name"=>"throttle.ip", "prm"=>1 ),               // ?
        "throttle_up"       =>  array( "name"=>"throttle.up", "prm"=>1 ),               // ?
        "tracker_numwant"   =>  array( "name"=>"trackers.numwant", "prm"=>0 ),
    );
    public $started = 0;
    public $server = '';
    public $portRange = '6890-6999';
    public $port = '6890';
    public $idNotFound = false;
    public $home = '';

    static private $theSettings = null;

    private function __clone()
    {
    }

    public function registerPlugin($plugin, $data = true)
    {
        $this->plugins[$plugin] = $data;
    }
    public function unregisterPlugin($plugin)
    {
        unset($this->plugins[$plugin]);
    }
    public function getPluginData($plugin)
    {
        $ret = null;
        if (array_key_exists($plugin, $this->plugins)) {
            $ret = $this->plugins[$plugin];
        }
        return($ret);
    }
    public function isPluginRegistered($plugin)
    {
        return(array_key_exists($plugin, $this->plugins));
    }

    public function registerEventHook($plugin, $ename)
    {
        if (is_array($ename)) {
            foreach ($ename as $name) {
                $this->hooks[$name][] = $plugin;
            }
        } else {
            $this->hooks[$ename][] = $plugin;
        }
    }
    public function unregisterEventHook($plugin, $ename)
    {
        for ($i = 0; $i<count($this->hooks[$ename]); $i++) {
            if ($this->hooks[$ename][$i] == $plugin) {
                unset($this->hooks[$ename][$i]);
                if (count($this->hooks[$ename])==0) {
                    unset($this->hooks[$ename]);
                }
                break;
            }
        }
    }
    public function pushEvent($ename, $prm)
    {
        if (array_key_exists($ename, $this->hooks)) {
            for ($i = 0; $i<count($this->hooks[$ename]); $i++) {
                $pname = $this->hooks[$ename][$i];
                $file = __DIR__.'/../plugins/'.$pname.'/hooks.php';
                if (is_file($file)) {
                    require_once( $file );
                    $func = $pname.'Hooks::On'.$ename;
                    if (is_callable($func) &&
                    (call_user_func_array($func, array($prm))==true)) {
                        break;
                    }
                }
            }
        }
    }

    public function store()
    {
        $cache = new rCache();
        return($cache->set($this));
    }
    public static function get($create = false)
    {
        if (is_null(self::$theSettings)) {
            self::$theSettings = new rTorrentSettings();
            if ($create) {
                self::$theSettings->obtain();
            } else {
                $cache = new rCache();
                $cache->get(self::$theSettings);
            }
        }
        return(self::$theSettings);
    }
    public function obtain()
    {
        $req = new rXMLRPCRequest(new rXMLRPCCommand("system.client_version"));
        if ($req->run() && count($req->val)) {
            $this->linkExist = true;
            $this->version = $req->val[0];
            $parts = explode('.', $this->version);

            $this->apiVersion = 0;
            $req = new rXMLRPCRequest(new rXMLRPCCommand("system.api_version"));
            $req->important = false;
            if ($req->success()) {
                $this->apiVersion = $req->val[0];
            }

            $req = new rXMLRPCRequest([
                new rXMLRPCCommand("get_directory"),
                new rXMLRPCCommand("get_session"),
                new rXMLRPCCommand("system.library_version"),
                new rXMLRPCCommand("network.xmlrpc.size_limit.set", ["", 67108863]),
                new rXMLRPCCommand("get_name"),
                new rXMLRPCCommand("network.port_range"),
            ]);
            if ($req->success()) {
                $this->directory = $req->val[0];
                $this->session = $req->val[1];
                $this->libVersion = $req->val[2];
                $this->server = $req->val[4];
                $this->portRange = $req->val[5];
                $this->port = intval($this->portRange);

                $req = new rXMLRPCRequest(new rXMLRPCCommand("network.listen.port"));
                $req->important = false;
                if ($req->success()) {
                    $this->port = intval($req->val[0]);
                }

                if (isLocalMode()) {
                    if (!empty($this->session)) {
                        $this->started = @filemtime($this->session.'/rtorrent.lock');
                        if ($this->started===false) {
                            $this->started = 0;
                        }
                    }
                    $id = getExternal('id');
                    $req = new rXMLRPCRequest(
                        new rXMLRPCCommand("execute.capture", ["", "sh","-c",$id." -u ; ".$id." -G ; echo ~ "])
                    );
                    if ($req->run() && !$req->fault && (($line=explode("\n", $req->val[0]))!==false) && (count($line)>2)) {
                        $this->uid = intval(trim($line[0]));
                        $this->gid = explode(' ', trim($line[1]));
                        $this->home = trim($line[2]);
                        if (!empty($this->directory) &&
                            ($this->directory[0]=='~')) {
                            $this->directory = $this->home.substr($this->directory, 1);
                        }
                    } else {
                        $this->idNotFound = true;
                    }
                }
                $this->store();
            }
        }
    }
    public function getCommand($cmd)
    {
        $add = '';
        $len = strlen($cmd);
        if ($len && ($cmd[$len-1] == '=')) {
            $cmd = substr($cmd, 0, -1);
            $add = '=';
        }
        return(array_key_exists($cmd, $this->aliases) ? $this->aliases[$cmd]["name"].$add : $cmd.$add);
    }
    public function getEventCommand($cmd1, $cmd2, $args)
    {
        $cmd = new rXMLRPCCommand('method.set_key', ["", 'event.download.'.$cmd2]);
        $cmd->addParameters($args);
        return($cmd);
    }
    public function getOnInsertCommand($args)
    {
        return($this->getEventCommand('on_insert', 'inserted_new', $args));
    }
    public function getOnEraseCommand($args)
    {
        return($this->getEventCommand('on_erase', 'erased', $args));
    }
    public function getOnFinishedCommand($args)
    {
        return($this->getEventCommand('on_finished', 'finished', $args));
    }
    public function getOnResumedCommand($args)
    {
        return($this->getEventCommand('on_start', 'resumed', $args));
    }
    public function getOnHashdoneCommand($args)
    {
        return($this->getEventCommand('on_hash_done', 'hash_done', $args));
    }
    public function getAbsScheduleCommand($name, $interval, $cmd) // $interval in seconds
    {
        global $schedule_rand;
        if (!isset($schedule_rand)) {
            $schedule_rand = 10;
        }
        $startAt = $interval+rand(0, $schedule_rand);
        return( new rXMLRPCCommand("schedule", array( $name.getUser(), $startAt."", $interval."", $cmd )) );
    }
    public function getScheduleCommand($name, $interval, $cmd, &$startAt = null)   // $interval in minutes
    {
        global $schedule_rand;
        if (!isset($schedule_rand)) {
            $schedule_rand = 10;
        }
        $tm = getdate();
        $startAt = mktime(
            $tm["hours"],
            ((integer)($tm["minutes"]/$interval))*$interval+$interval,
            0,
            $tm["mon"],
            $tm["mday"],
            $tm["year"]
        )-$tm[0]+rand(0, $schedule_rand);
        if ($startAt<0) {
            $startAt = 0;
        }
        $interval = $interval*60;
        return( new rXMLRPCCommand("schedule", array( $name.getUser(), $startAt."", $interval."", $cmd )) );
    }
    public function getRemoveScheduleCommand($name)
    {
        return( new rXMLRPCCommand("schedule_remove", $name.getUser()) );
    }
    public function correctDirectory(&$dir, $resolve_links = false)
    {
        global $topDirectory;
        if (strlen($dir) && ($dir[0]=='~')) {
            $dir = $this->home.substr($dir, 1);
        }
        $dir = fullpath($dir, $this->directory);
        if ($resolve_links) {
            $path = realpath($dir);
            if (!$path) {
                $dir = addslash(realpath(dirname($dir))).basename($dir);
            } else {
                $dir = $path;
            }
        }
        return(strpos(addslash($dir), $topDirectory)===0);
    }
    public function patchDeprecatedCommand($cmd, $name)
    {
        if (array_key_exists($name, $this->aliases) && $this->aliases[$name]["prm"]) {
            $cmd->addParameter("");
        }
    }
    public function patchDeprecatedRequest($commands)
    {
        foreach ($commands as $cmd) {
            $prefix = '';
            if (strpos($cmd->command, 't.') === 0) {
                $prefix = ':t';
            } elseif (strpos($cmd->command, 'p.') === 0) {
                $prefix = ':p';
            } elseif (strpos($cmd->command, 'f.') === 0) {
                $prefix = ':f';
            }
            if (!empty($prefix) &&
                (count($cmd->params)>1) &&
                (substr($cmd->command, -10) !== '.multicall') &&
                (strpos($cmd->params[0]->value, ':') === false) ) {
                $cmd->params[0]->value = $cmd->params[0]->value.$prefix.$cmd->params[1]->value;
                array_splice($cmd->params, 1, 1);
            }
        }
    }
}
