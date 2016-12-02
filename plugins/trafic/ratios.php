<?php
    require_once( __DIR__."/../../php/xmlrpc.php" );
    require_once( __DIR__.'/stat.php' );

function getRatiosStat()
{
    $req = new rXMLRPCRequest(
        new rXMLRPCCommand("d.multicall2", ["", "main", "d.hash="])
    );
    $ret = 'theWebUI.ratiosStat = {';
    if ($req->run() && !$req->fault) {
        $tm = time();
        for ($i=0; $i<count($req->val); $i++) {
            $st = new rStat("torrents/".$req->val[$i].".csv");
            $ratios = $st->getRatios($tm);
            if ($ret!='theWebUI.ratiosStat = {') {
                $ret.=',';
            }
            $ret.=('"'.$req->val[$i].'": ['.$ratios[0].','.$ratios[1].','.$ratios[2].']');
        }
    }
    return($ret.'}; ');
}
