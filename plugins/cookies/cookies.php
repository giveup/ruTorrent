<?php
require_once( __DIR__."/../../php/cache.php" );

class rCookies
{
    public $hash = "cookies.dat";
    public $list = [];

    public static function load()
    {
        $cache = new rCache();
        $rt = new rCookies();
        $cache->get($rt);
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
            foreach ($vars as $var) {
                $parts = explode("=", $var);
                if ($parts[0]=="cookie") {
                    $value = trim(rawurldecode($parts[1]));
                    if (strlen($value)) {
                        $tmp = explode("|", $value);
                        if (count($tmp>1) && (trim($tmp[1])!='')) {
                            $cookies = [];
                            $tmp1 = explode(";", $tmp[1]);
                            foreach ($tmp1 as $item) {
                                $tmp2 = explode("=", $item);
                                if (count($tmp2)>1) {
                                    $name = trim($tmp2[0]);
                                    $val = trim($tmp2[1]);
                                    if (($name!='') && ($val!='')) {
                                        $cookies[$name] = trim($val);
                                    }
                                }
                            }
                            if (!empty($cookies)) {
                                $this->list[strtolower($tmp[0])] = $cookies;
                            }
                        }
                    }
                }
            }
        }
        $this->store();
    }
    public function get()
    {
                $ret = "hostCookies = [";
        foreach ($this->list as $host => $cookies) {
            $c = '';
            foreach ($cookies as $name => $value) {
                $c.=($name."=".$value.";");
            }
            $ret.="{ host: ".quoteAndDeslashEachItem($host).", cookies: '".addslashes($c)."' },";
        }
        $len = strlen($ret);
        if ($ret[$len-1]==',') {
            $ret = substr($ret, 0, $len-1);
        }
        return($ret."];\n");
    }

    public function getInfo()
    {
        return($this->list);
    }
    public function getCookiesForHost($host)
    {
        $host = strtolower($host);
        if (array_key_exists($host, $this->list)) {
            return($this->list[$host]);
        }
        return([]);
    }
    public function add($host, $values)
    {
        $cookies = [];
        $tmp = explode(";", $values);
        foreach ($tmp as $item) {
            $tmp2 = explode("=", $item);
            if (count($tmp2)>1) {
                $name = trim($tmp2[0]);
                $val = trim($tmp2[1]);
                if (($name!='') && ($val!='')) {
                    $cookies[$name] = trim($val);
                }
            }
        }
        if (!empty($cookies)) {
            $this->list[$host] = $cookies;
        } else {
            unset($this->list[$host]);
        }
        $this->store();
    }
}
