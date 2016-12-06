<?php
require_once( __DIR__."/../../php/cache.php" );

class rRetrackers
{
    public $hash = "retrackers.dat";
    public $list = [];
    public $todelete = [];
    public $dontAddPrivate = 1;
    public $addToBegin = 0;

    static public function load()
    {
        $cache = new rCache();
        $rt = new rRetrackers();
        $cache->get($rt);
        if (!isset($rt->todelete))
            $rt->todelete = [];
        return($rt);
    }
    public function store()
    {
        $cache = new rCache();
        return($cache->set($this));
    }
    public function set()
    {
        $rawData = file_get_contents("php://input");
        if (isset($rawData)) {
            $vars = explode('&', $rawData);
            $this->list = [];
            $this->todelete = [];
            $this->dontAddPrivate = 0;
            $trackers = [];
            foreach ($vars as $var)
            {
                $parts = explode("=", $var);
                if ($parts[0]=="dont_private") {
                    $this->dontAddPrivate = $parts[1];
                } elseif ($parts[0]=="add_begin") {
                    $this->addToBegin = $parts[1];
                } elseif ($parts[0]=="todelete") {
                    $this->todelete[] = trim(rawurldecode($parts[1]));
                } elseif ($parts[0]=="tracker") {
                    $value = trim(rawurldecode($parts[1]));
                    if (strlen($value)) {
                        $trackers[] = $value;
                    } else {
                        if (count($trackers)>0) {
                            $this->list[] = $trackers;
                            $trackers = [];
                        }
                    }
                }
            }
            if (count($trackers)>0) {
                $this->list[] = $trackers;
            }
        }
        $this->store();
    }
    public function get()
    {
        return("theWebUI.retrackers = ".json_encode($this).";\n");
    }
}
