<?php
declare(strict_types=1);

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
const CSP = [
    'default-src'     => [],
    'img-src'         => ["'self'", "data:"],
    'frame-src'       => ["'self'"], // Deprecated, replaced by child-src
    'child-src'       => ["'self'"],
    'style-src'       => ["'self'", "'unsafe-inline'"],
    'script-src'      => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
    'connect-src'     => ["'self'"],
    'font-src'        => ["'self'"], // Some 3rd party themes do like to use custom fonts / icon fonts
    'form-action'     => ["'self'"],
    'frame-ancestors' => [],
];
$CSPRules = [];
foreach (CSP as $Key => $Value) {
    //Empty array means 'none'
    if (count($Value) === 0) {
        $CSPRules[] = $Key." 'none'";
    } else {
        $CSPRules[] = $Key.' '.implode(' ', $Value);
    }
}

header('Content-Security-Policy: '.implode('; ', $CSPRules));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>ruTorrent</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <link href="./images/favicon.ico" rel="shortcut icon" />
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="./images/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="./images/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="./images/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="./images/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="./images/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="./images/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="./images/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="./images/apple-touch-icon-152x152.png" />
    <link rel="icon" sizes="any" type="image/svg+xml" href="./images/logo.svg" />
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="./images/mstile-144x144.png" />
    <link href="./css/stable.css" rel="stylesheet" type="text/css" />
    <link href="./css/style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" defer="defer" src="./node_modules/es6-promise/dist/es6-promise.min.js"></script>
    <script type="text/javascript" defer="defer" src="./node_modules/whatwg-fetch/fetch.js"></script>
    <script type="text/javascript" defer="defer" src="./node_modules/jquery/dist/jquery.min.js"></script>
    <?php require(__DIR__.'/html/localize-script.php'); ?>
    <script type="text/javascript" defer="defer" src="./lang/langs.js"></script>
    <script type="text/javascript" defer="defer" src="./js/common.js"></script>
    <script type="text/javascript" defer="defer" src="./js/objects.js"></script>
    <script type="text/javascript" defer="defer" src="./js/content.js"></script>
    <script type="text/javascript" defer="defer" src="./js/stable.js"></script>
    <script type="text/javascript" defer="defer" src="./node_modules/flot/jquery.flot.js"></script>
    <script type="text/javascript" defer="defer" src="./js/graph.js"></script>
    <script type="text/javascript" defer="defer" src="./js/plugins.js"></script>
    <script type="text/javascript" defer="defer" src="./js/webui.js"></script>
    <script type="text/javascript" defer="defer" src="./js/rtorrent.js"></script>
    <script type="text/javascript" defer="defer" src="./php/getplugins.php"></script>
    <script type="text/javascript" defer="defer" src="./js/localize.js"></script>
</head>
<body class="col">
    <!--
    <div id="preload"></div>
    <div id="cover">
        <div id="msg"></div>
    </div>
    -->
    <div id="modalbg"></div>
    <div id="dragmask"></div>
    <div id="rs"></div>
    <div id="sel"></div>
    <div id="sc"></div>
    <header>
        <a id="mnu_add" href="#" onclick="theWebUI.showAdd(); return(false);" onfocus="this.blur()" title="Add Torrent...">
            <div id="add"></div>
        </a>
        <div class="TB_Separator"></div>
        <a id="mnu_remove" href="#" onclick="theWebUI.remove(); return(false);" onfocus="this.blur()" title="Remove">
            <div id="remove"></div>
        </a>
        <div class="TB_Separator"></div>
        <a id="mnu_start" href="#" onclick="theWebUI.start(); return(false);" onfocus="this.blur()" title="Start">
            <div id="start"></div>
        </a><a id="mnu_pause" href="#" onclick="theWebUI.pause(); return(false);" onfocus="this.blur()" title="Pause">
            <div id="pause"></div>
        </a><a id="mnu_stop" href="#" onclick="theWebUI.stop(); return(false);" onfocus="this.blur()" title="Stop">
            <div id="stop"></div>
        </a>
        <div class="TB_Separator"></div>
        <a id="mnu_settings" href="#" onclick="theWebUI.showSettings(); return(false);" onfocus="this.blur()" title="Settings">
            <div id="setting"></div>
        </a>
        <div class="TB_Separator"></div>
        <a id="mnu_help" href="#" onclick="theDialogManager.toggle('dlgHelp'); return(false);" onfocus="this.blur()" title="Help">
            <div id="help"></div>
        </a>
        <table id='rc'><tr id='rrow'>
            <td>
                <a id="mnu_search" class="top-menu-item" href="#" onclick="theSearchEngines.show(); return(false);" onfocus="this.blur()" title="Search">
                    <div id="search" class="top-menu-item"></div>
                </a>
            </td>
            <td>
                <input type="text" class="TextboxMid" id="query" onfocus="$('#sc').hide();"/>
            </td>
            <td>
                <a id="mnu_go" href="#" onclick="theSearchEngines.run(); return(false);" onfocus="this.blur()" title="Go">
                    <div id="go"></div>
                </a>
            </td>
            <td>
                <div id="ind"></div>
            </td>
        </tr></table>
    </header>
    <main class="auto row">
        <aside id="CatList">
            <div class="catpanel" id="pstate" ru-string="pnlState" onclick="theWebUI.togglePanel(this); return(false);"></div>
            <ul id="pstate_cont" class="catpanel_cont">
                <li id="-_-_-all-_-_-" class="sel cat"><span ru-string="All"></span> (<span id="-_-_-all-_-_-c">0</span>)</li>
                <li id="-_-_-dls-_-_-" class="cat"><span ru-string="Downloading"></span> (<span id="-_-_-dls-_-_-c">0</span>)</li>
                <li id="-_-_-com-_-_-" class="cat"><span ru-string="Finished"></span> (<span id="-_-_-com-_-_-c">0</span>)</li>
                <li id="-_-_-act-_-_-" class="cat"><span ru-string="Active"></span> (<span id="-_-_-act-_-_-c">0</span>)</li>
                <li id="-_-_-iac-_-_-" class="cat"><span ru-string="Inactive"></span> (<span id="-_-_-iac-_-_-c">0</span>)</li>
                <li id="-_-_-err-_-_-" class="cat"><span ru-string="Error"></span> (<span id="-_-_-err-_-_-c">0</span>)</li>
            </ul>
            <div class="catpanel" id="plabel" onclick="theWebUI.togglePanel(this); return(false);"></div>
            <div class="catpanel_cont" id="plabel_cont">
                <ul>
                <li id="-_-_-nlb-_-_-" class="cat"><span ru-string="No_label"></span> (<span id="-_-_-nlb-_-_-c">0</span>)</li>
                </ul>
                <ul id="lbll">
                </ul>
            </div>
            <div class="catpanel" id="flabel" onclick="theWebUI.togglePanel(this); return(false);"></div>
            <div class="catpanel_cont" id="flabel_cont">
                <ul id="lblf">
                </ul>
            </div>
        </aside>

        <div id="HDivider"></div>

        <div id="maincont" class="auto col">
            <div id="List" class="table_tab auto"></div>
            <div id="VDivider"></div>
            <div id="tdetails" class="col">
                <ul id="tabbar" class="tabbar"></ul>
                <div id="tdcont" class="auto">
                    <div id="gcont" class="tab">
                        <table width="100%" summary="layout table" id="mainlayout">
                            <tr>
                                <td colspan="6" class="Header" ru-string="Transfer"></td>
                            </tr>
                            <tr>
                                <td width="6%" ru-string="Time_el"></td><td width="11%" nowrap><span id="et" class="det"></span></td>
                                <td width="6%" ru-string="Remaining"></td><td width="11%" nowrap><span id="rm" class="det"></span></td>
                                <td width="6%" ru-string="Share_ratio"></td><td width="11%" nowrap><span id="ra" class="det"></span></td>
                            </tr>
                            <tr>
                                <td width="6%" ru-string="Downloaded"></td><td width="11%" nowrap><span id="dl" class="det"></span></td>
                                <td width="6%" ru-string="Down_speed"></td><td width="11%" nowrap><span id="ds" class="det"></span></td>
                                <td width="6%" ru-string="Wasted"></td><td width="11%" nowrap><span id="wa" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Uploaded"></td><td nowrap><span id="ul" class="det"></span></td>
                                <td ru-string="Ul_speed"></td><td nowrap><span id="us" class="det"></span></td>
                                <td nowrap>&nbsp;</td><td nowrap>&nbsp;</td>
                            </tr>
                            <tr>
                                <td ru-string="Seeds"></td><td nowrap><span id="se" class="det"></span></td>
                                <td ru-string="Peers"></td><td nowrap><span id="pe" class="det"></span></td>
                                <td nowrap>&nbsp;</td><td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="Header" ru-string="Tracker"></td>
                            </tr>
                            <tr>
                                <td ru-string="Track_URL"></td>
                                <td colspan="5" nowrap><span id="tu" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Track_status"></td>
                                <td colspan="5" nowrap><span id="ts" class="det"></span></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="Header" ru-string="General"></td>
                            </tr>
                            <tr>
                                <td ru-string="Save_as"></td>
                                <td colspan="5" nowrap><span id="bf" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Free_Disk_Space"></td>
                                <td colspan="5" nowrap><span id="dsk" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Created_on"></td>
                                <td colspan="5" nowrap><span id="co" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Hash"></td>
                                <td colspan="5" nowrap><span id="hs" class="det"></span></td>
                            </tr>
                            <tr>
                                <td ru-string="Comment"></td>
                                <td colspan="5"><span id="cmt" class="det"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div id="FileList" class="table_tab"></div>
                    <div id="TrackerList" class="table_tab"></div>
                    <div id="PeerList" class="table_tab"></div>
                    <div id="Speed" class="graph_tab"></div>
                    <div id="PluginList" class="table_tab"></div>
                    <div id="lcont" class="tab"></div>
                </div>
            </div>
        </div>
    </main>

    <footer id="StatusBar" class="row">
        <div id="st_up">
            <strong ru-string="Speed"></strong> <span id="stup_speed"></span>
            <strong ru-string="limit"></strong> <span id="stup_limit"></span>
            <strong ru-string="Total"></strong> <span id="stup_total"></span>
        </div>
        <div id="st_down">
                <strong ru-string="Speed"></strong> <span id="stdown_speed"></span>
                <strong ru-string="limit"></strong> <span id="stdown_limit"></span>
                <strong ru-string="Total"></strong> <span id="stdown_total"></span>
        </div>
        <div id="st_system">
            <strong>rTorrent:</strong> <span id="rtorrentv"></span>
        </div>
    </footer>
</body>
</html>
