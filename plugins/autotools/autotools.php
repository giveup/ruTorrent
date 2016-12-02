<?php

require_once( __DIR__."/../../php/settings.php");
eval(getPluginConf('autotools'));

class rAutoTools
{
    public $hash = "autotools.dat";
    public $enable_label = 0;
    public $label_template = "{DIR}";
    public $enable_move = 0;
    public $path_to_finished = "";
    public $fileop_type = "Move";
    public $enable_watch = 0;
    public $path_to_watch = "";
    public $watch_start = 0;
    public $automove_filter = "/.*/";
    public $addName = 0;
    public $addLabel = 0;

    public static function load()
    {
        $cache = new rCache();
        $at = new rAutoTools();
        $cache->get($at);
        if (!property_exists($at, "automove_filter") || (@preg_match($at->automove_filter, null) === false)) {
            $at->automove_filter = "/.*/";
        }
        if (!property_exists($at, "addName")) {
            $at->addName = 0;
        }
        if (!property_exists($at, "addLabel")) {
            $at->addLabel = 0;
        }
        return $at;
    }
    public function store()
    {
        $cache = new rCache();
        return $cache->set($this);
    }
    public function set()
    {
        if (!isset( $HTTP_RAW_POST_DATA )) {
            $HTTP_RAW_POST_DATA = file_get_contents("php://input");
        }
        if (isset( $HTTP_RAW_POST_DATA )) {
            $vars = explode('&', $HTTP_RAW_POST_DATA);
            $this->enable_label = 0;
            $this->label_template = "{DIR}";
            $this->enable_move = 0;
            $this->fileop_type = "Move";
            $this->path_to_finished = "";
            $this->enable_watch = 0;
            $this->path_to_watch = "";
            $this->watch_start = 0;
            $this->automove_filter = "/.*/";
            $this->addName = 0;
            $this->addLabel = 0;
            foreach ($vars as $var) {
                $parts = explode("=", $var);
                if ($parts[0] == "enable_label") {
                    $this->enable_label = $parts[1];
                } elseif ($parts[0] == "label_template") {
                    $this->label_template = $parts[1];
                } elseif ($parts[0] == "automove_filter") {
                    $this->automove_filter = $parts[1];
                    if (@preg_match($this->automove_filter, null) === false) {
                        $this->automove_filter = "/.*/";
                    }
                } elseif ($parts[0] == "enable_move") {
                    $this->enable_move = $parts[1];
                } elseif ($parts[0] == "fileop_type") {
                    $this->fileop_type = $parts[1];
                } elseif ($parts[0] == "path_to_finished") {
                    $this->path_to_finished = $parts[1];
                    if (!rTorrentSettings::get()->correctDirectory($this->path_to_finished)) {
                        $this->path_to_finished = '';
                    }
                } elseif ($parts[0] == "enable_watch") {
                    $this->enable_watch = $parts[1];
                } elseif ($parts[0] == "path_to_watch") {
                    $this->path_to_watch = $parts[1];
                    if (!rTorrentSettings::get()->correctDirectory($this->path_to_watch)) {
                        $this->path_to_watch = '';
                    }
                } elseif ($parts[0] == "watch_start") {
                    $this->watch_start = $parts[1];
                } elseif ($parts[0] == "add_label") {
                    $this->addLabel = $parts[1];
                } elseif ($parts[0] == "add_name") {
                    $this->addName = $parts[1];
                }
            }
            $this->setHandlers();
        }
        $this->store();
    }
    public function get()
    {
        $ret  = "theWebUI.autotools = { ";
        $ret .= "EnableLabel: ".$this->enable_label;
        $ret .= ", LabelTemplate: '".addslashes($this->label_template)."'";
        $ret .= ", EnableMove: ".$this->enable_move;
        $ret .= ", FileOpType: '".$this->fileop_type."'";
        $ret .= ", PathToFinished: '".addslashes($this->path_to_finished)."'";
        $ret .= ", EnableWatch: ".$this->enable_watch;
        $ret .= ", PathToWatch: '".addslashes($this->path_to_watch)."'";
        $ret .= ", MoveFilter: '".addslashes($this->automove_filter)."'";
        $ret .= ", WatchStart: ".$this->watch_start;
        $ret .= ", AddLabel: ".$this->addLabel;
        $ret .= ", AddName: ".$this->addName;
        return $ret." };\n";
    }
    public function setHandlers()
    {
        global $autowatch_interval;
        $theSettings = rTorrentSettings::get();
        $req = new rXMLRPCRequest(
            // old version fix
            $theSettings->getOnInsertCommand(array('autolabel'.getUser(), getCmd('cat=')))
        );
        $pathToAutoTools = __DIR__;

        if ($this->enable_label) {
            $cmd =  $theSettings->getOnInsertCommand(array('_autolabel'.getUser(),
                getCmd('branch').'=$'.getCmd('not').'=$'.getCmd("d.custom1").'=,"'.
                getCmd('execute').'={'.getPHP().','.$pathToAutoTools.'/label.php,$'.getCmd("d.hash").'=,'.getUser().'}"'));
        } else {
            $cmd =  $theSettings->getOnInsertCommand(array('_autolabel'.getUser(), getCmd('cat=')));
        }
        $req->addCommand($cmd);
        if ($this->enable_move && (trim($this->path_to_finished)!='')) {
            if ($this->fileop_type=="Move") {
                $cmd =  $theSettings->getOnFinishedCommand(array('automove'.getUser(),
                        getCmd('d.directory_base.set').'="$execute.capture'.
                        '={'.getPHP().','.$pathToAutoTools.'/check.php,$'.getCmd('d.base_path').'=,$'.
                        getCmd('d.base_filename').'=,$'.getCmd('d.is_multi_file').'=,$'.getCmd('d.custom1').'=,$'.getCmd('d.name').'=,'.getUser().'}" ; '.
                        getCmd('execute').'={'.getPHP().','.$pathToAutoTools.'/move.php,$'.getCmd('d.hash').'=,$'.getCmd('d.base_path').'=,$'.
                        getCmd('d.base_filename').'=,$'.getCmd('d.is_multi_file').'=,$'.getCmd('d.custom1').'=,$'.getCmd('d.name').'=,'.getUser().'}'
                    ));
            } else {
                $cmd =  $theSettings->getOnFinishedCommand(array('automove'.getUser(),
                        getCmd('d.custom.set').'=x-dest,"$execute.capture'.
                        '={'.getPHP().','.$pathToAutoTools.'/move.php,$'.getCmd('d.hash').'=,$'.getCmd('d.base_path').'=,$'.
                        getCmd('d.base_filename').'=,$'.getCmd('d.is_multi_file').'=,$'.getCmd('d.custom1').'=,$'.getCmd('d.name').'=,'.getUser().'}"'
                    ));
            }
        } else {
            $cmd = $theSettings->getOnFinishedCommand(array('automove'.getUser(), getCmd('cat=')));
        }
        $req->addCommand($cmd);
        if ($this->enable_watch && (trim($this->path_to_watch)!='')) {
            $cmd =  $theSettings->getAbsScheduleCommand(
                'autowatch',
                $autowatch_interval,
                getCmd('execute').'={sh,-c,'.escapeshellarg(getPHP()).' '.escapeshellarg($pathToAutoTools.'/watch.php').' '.escapeshellarg(getUser()).' &}'
            );
        } else {
            $cmd = $theSettings->getRemoveScheduleCommand('autowatch');
        }
        $req->addCommand($cmd);
        return($req->success());
    }
}
