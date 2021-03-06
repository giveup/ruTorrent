/*
 *      UI content.
 *
 */

function makeContent()
{
	$(".cat").mouseclick(theWebUI.labelContextMenu);

	$("#mnu_add").attr("title",theUILang.mnu_add+"...");
	$("#mnu_remove").attr("title",theUILang.mnu_remove);
	$("#mnu_start").attr("title",theUILang.mnu_start);
	$("#mnu_pause").attr("title",theUILang.mnu_pause);
	$("#mnu_stop").attr("title",theUILang.mnu_stop);
	$("#mnu_settings").attr("title",theUILang.mnu_settings+"...");
	$("#mnu_search").attr("title",theUILang.mnu_search+"...");
	$("#mnu_go").attr("title",theUILang.mnu_go);
	$("#mnu_help").attr("title",theUILang.mnu_help+"...");

	$("#query").keydown( function(e)
	{
		if (e.keyCode == 13)
			theSearchEngines.run();
	});

	(function () {
		var HDivider = document.querySelector('#HDivider'),
			HContent = document.querySelector('#CatList'),
			animation = null;

		function initDrag(e) {
		   startX = e.clientX;
		   startWidth = parseInt(document.defaultView.getComputedStyle(HContent).width, 10);
		   document.documentElement.addEventListener('mousemove', doDrag, false);
		   document.documentElement.addEventListener('mouseup', stopDrag, false);
		}

		function doDrag(e) {
			cancelAnimationFrame(animation);
		    animation = requestAnimationFrame(function(){
				HContent.style.width = (startWidth + (e.clientX - startX)) + 'px';
		    });
		}

		function stopDrag(e) {
		    document.documentElement.removeEventListener('mousemove', doDrag, false);
			document.documentElement.removeEventListener('mouseup', stopDrag, false);
			theWebUI.setHSplitter();
		}

		HDivider.addEventListener('mousedown', initDrag, false);
	})();

	(function () {
		var VDivider = document.querySelector('#VDivider'),
			VContent = document.querySelector('#tdetails'),
			animation = null;

		function initDrag(e) {
		   startY = e.clientY;
		   startHeight = parseInt(document.defaultView.getComputedStyle(VContent).height, 10);
		   document.documentElement.addEventListener('mousemove', doDrag, false);
		   document.documentElement.addEventListener('mouseup', stopDrag, false);
		}

		function doDrag(e) {
			cancelAnimationFrame(animation);
		    animation = requestAnimationFrame(function(){
				VContent.style.height = (startHeight - (e.clientY - startY)) + 'px';
		    });
		}

		function stopDrag(e) {
		    document.documentElement.removeEventListener('mousemove', doDrag, false);
			document.documentElement.removeEventListener('mouseup', stopDrag, false);
			theWebUI.setVSplitter();
		}

		VDivider.addEventListener('mousedown', initDrag, false);
	})();

	$(document.body).append($("<iframe name='uploadfrm'/>").css({visibility: "hidden"}).attr( { name: "uploadfrm" } ).width(0).height(0).on('load', function()
	{
		$("#torrent_file").val("");
		$("#add_button").prop("disabled",false);
		var d = (this.contentDocument || this.contentWindow.document);
		if (d && (d.location.href != "about:blank")) {
			try { var txt = d.body.textContent ? d.body.textContent : d.body.innerText; eval(txt); } catch(e) {}
		}
	}));
	$(document.body).append($("<iframe name='uploadfrmurl'/>").css({visibility: "hidden"}).attr( { name: "uploadfrmurl" } ).width(0).height(0).on('load', function()
	{
		$("#url").val("");
		var d = (this.contentDocument || this.contentWindow.document);
		if (d.location.href != "about:blank")
			try { eval(d.body.textContent ? d.body.textContent : d.body.innerText); } catch(e) {}
	}));
	theDialogManager.make("padd",theUILang.peerAdd,
		'<div class="content fxcaret">'+theUILang.peerAddLabel+'<br><input type="text" id="peerIP" class="Textbox" value="my.friend.addr:6881"/></div>'+
		'<div class="aright buttons-list"><input type="button" class="OK Button" value="'+theUILang.ok+'" onclick="theWebUI.addNewPeer();theDialogManager.hide(\'padd\');return(false);" />'+
			'<input type="button" class="Cancel Button" value="'+theUILang.Cancel+'"/></div>',
		true);
	theDialogManager.make("tadd",theUILang.torrent_add,
		'<div class="cont fxcaret">'+
			'<form action="addtorrent.php" id="addtorrent" method="post" enctype="multipart/form-data" target="uploadfrm">'+
				'<label>'+theUILang.Base_directory+':</label><input type="text" id="dir_edit" name="dir_edit" class="TextboxLarge"/><br/>'+
				'<label>&nbsp;</label><input type="checkbox" name="not_add_path" id="not_add_path"/>'+theUILang.Dont_add_tname+'<br/>'+
				'<label>&nbsp;</label><input type="checkbox" name="torrents_start_stopped" id="torrents_start_stopped"/>'+theUILang.Dnt_start_down_auto+'<br/>'+
				'<label>&nbsp;</label><input type="checkbox" name="fast_resume" id="fast_resume"/>'+theUILang.doFastResume+'<br/>'+
				'<label>&nbsp;</label><input type="checkbox" name="randomize_hash" id="randomize_hash"/>'+theUILang.doRandomizeHash+'<br/>'+
				'<label>'+theUILang.Label+':</label><input type="text" id="tadd_label" name="tadd_label" class="TextboxLarge" /><select id="tadd_label_select"></select><br/>'+
				'<hr/>'+
				'<label>'+theUILang.Torrent_file+':</label><input type="file" multiple="multiple" name="torrent_file[]" id="torrent_file" accept="application/x-bittorrent" class="TextboxLarge"/><br/>'+
				'<label>&nbsp;</label><input type="submit" value="'+theUILang.add_button+'" id="add_button" class="Button" /><br/>'+
			'</form>'+
			'<hr/>'+
			'<form action="addtorrent.php" id="addtorrenturl" method="post" target="uploadfrmurl">'+
				'<label>'+theUILang.Torrent_URL+':</label><input type="text" id="url" name="url" class="TextboxLarge"/><br/>'+
				'<label>&nbsp;</label><input type="submit" id="add_url" value="'+theUILang.add_url+'" class="Button" disabled="true"/>'+
			'</form>'+
		'</div>');

	$("#tadd_label_select").change( function(e)
	{
		var index = this.selectedIndex;
		switch (index)
		{
			case 1:
			{
				$(this).hide();
				$("#tadd_label").show();
			}
			case 0:
			{
				$("#tadd_label").val("");
				break;
			}
			default:
			{
				$("#tadd_label").val(this.options[index].value);
				break;
			}
		}
	});

	theDialogManager.setHandler('tadd','beforeShow',function()
	{
		$("#tadd_label").hide();
		$("#tadd_label_select").empty()
			.append('<option selected>'+theUILang.No_label+'</option>')
			.append('<option>'+theUILang.newLabel+'</option>').show();
		for (var lbl in theWebUI.cLabels)
			$("#tadd_label_select").append("<option>"+lbl+"</option>");
		$("#add_button").prop("disabled",false);
	});

	var input = $$('url');
	input.onupdate = input.onkeyup = function() { $('#add_url').prop('disabled', input.value.trim() === ''); };
	input.onpaste = function() { setTimeout( input.onupdate, 10 ) };
	var makeAddRequest = function(frm)
	{
		var s = theURLs.AddTorrentURL+"?";
		if ($("#torrents_start_stopped").prop("checked"))
			s += 'torrents_start_stopped=1&';
		if ($("#fast_resume").prop("checked"))
			s += 'fast_resume=1&';
		if ($("#not_add_path").prop("checked"))
			s += 'not_add_path=1&';
		if ($("#randomize_hash").prop("checked"))
			s += 'randomize_hash=1&';
		var dir = $("#dir_edit").val().trim();
		if (dir.length)
			s += ('dir_edit='+encodeURIComponent(dir)+'&');
		var lbl = $("#tadd_label").val().trim();
		if (lbl.length)
			s += ('label='+encodeURIComponent(lbl));
		frm.action = s;
		return(true);
	}
	$("#addtorrent").submit(function()
	{
		if (!$("#torrent_file").val().match(/\.torrent$/i))
		{
			alert(theUILang.Not_torrent_file);
	   		return(false);
   		}
		$("#add_button").prop("disabled",true);
		return(makeAddRequest(this));
	});
	$("#addtorrenturl").submit(function()
	{
	   	$("#add_url").prop("disabled",true);
	   	return(makeAddRequest(this));
	});
	theDialogManager.make("dlgProps",theUILang.Torrent_properties,
		'<div class="content fxcaret">'+
			'<fieldset>'+
				'<legend>'+theUILang.Bandwidth_sett+'</legend>'+
				'<div><input type="text" id="prop-ulslots" />'+theUILang.Number_ul_slots+':</div>'+
				'<div style="clear:right"><input type="text" id="prop-peers_min" />'+theUILang.Number_Peers_min+':</div>'+
				'<div style="clear:right"><input type="text" id="prop-peers_max" />'+theUILang.Number_Peers_max+':</div>'+
				'<div style="clear:right"><input type="text" id="prop-tracker_numwant" />'+theUILang.Tracker_Numwant+':</div>'+
				'<div class="props-spacer">'+
					'<input type="checkbox" id="prop-pex" /><label for="prop-pex" id="lbl_prop-pex">'+theUILang.Peer_ex+'</label>'+
					'<input type="checkbox" id="prop-superseed" /><label for="prop-superseed" id="lbl_prop-superseed">'+theUILang.SuperSeed+'</label>'+
				'</div>'+
			'</fieldset>'+
		'</div>'+
		'<div class="aright buttons-list"><input type="button" value="'+theUILang.ok+'" class="OK Button" onclick="theWebUI.setProperties(); return(false);" /><input type="button" value="'+theUILang.Cancel+'" class="Cancel Button"/></div>',
		true);
	theDialogManager.make("dlgHelp",theUILang.Help,
		'<div class="content">'+
			'<center>'+
				'<table width=100% border=0>'+
					'<tr><td><strong>F1</strong></td><td>'+theUILang.This_screen+'</td></tr>'+
					'<tr><td><strong><strong>F4</strong></td><td><a href="#" onclick="theWebUI.toggleMenu(); return(false);">'+theUILang.Toggle_menu+'</a></td></tr>'+
					'<tr><td><strong><strong>F6</strong></td><td><a href="#" onclick="theWebUI.toggleDetails(); return(false);">'+theUILang.Toggle_details+'</a></td></tr>'+
					'<tr><td><strong><strong>F7</strong></td><td><a href="#" onclick="theWebUI.toggleCategories(); return(false);">'+theUILang.Toggle_categories+'</a></td></tr>'+
					'<tr><td><strong><strong>Ctrl-O</strong></td><td><a href="#" onclick="theWebUI.showAdd(); return(false);">'+theUILang.torrent_add+'</a></td></tr>'+
					'<tr><td><strong><strong>Ctrl-P</strong></td><td><a href="#" onclick="theWebUI.showSettings(); return(false);">'+theUILang.ruTorrent_settings+'</a></td></tr>'+
					'<tr><td><strong><strong>Del</strong></td><td>'+theUILang.Delete_current_torrents+'</td></tr>'+
					'<tr><td><strong><strong>Ctrl-A</strong></td><td>'+theUILang.Select_all+'</td></tr>'+
					'<tr><td><strong><strong>Ctrl-Z</strong></td><td>'+theUILang.Deselect_all+'</td></tr>'+
				'</table>'+
			'</center>'+
		'</div>');
	theDialogManager.make("dlgLabel",theUILang.enterLabel,
		'<div class="content fxcaret">'+theUILang.Enter_label_prom+':<br/>'+
			'<input type="text" id="txtLabel" class="Textbox"/></div>'+
		'<div class="aright buttons-list"><input type="button" class="OK Button" value="'+theUILang.ok+'" onclick="theWebUI.createLabel();theDialogManager.hide(\'dlgLabel\');return(false);" />'+
			'<input type="button" class="Cancel Button" value="'+theUILang.Cancel+'"/></div>',
		true);
	theDialogManager.make("yesnoDlg","",
		'<div class="content" id="yesnoDlg-content"></div>'+
		'<div id="yesnoDlg-buttons" class="aright buttons-list"><input type="button" class="OK Button" value="'+theUILang.ok+'" id="yesnoOK">'+
		'<input type="button" class="Button Cancel" value="'+theUILang.Cancel+'" id="yesnoCancel"></div>',
		true);
	var languages = '';
	for (var i in AvailableLanguages)
		languages+="<option value='"+i+"'>"+AvailableLanguages[i]+"</option>";
	var retries = '';
	for (var i in theUILang.retryOnErrorList)
		retries+="<option value='"+i+"'>"+theUILang.retryOnErrorList[i]+"</option>";
	theDialogManager.make("stg",theUILang.ruTorrent_settings,
		'<div class="row">' +
		"<aside>"+
			"<ul>"+
				"<li class=\"first\"><a id=\"mnu_st_gl\" href=\"#\" onclick=\"theOptionsSwitcher.run(\'st_gl\'); return(false);\" class=\"focus\">"+
					theUILang.General+
				"</a></li>"+
				"<li id='hld_st_dl'><a id=\"mnu_st_dl\" href=\"#\" onclick=\"theOptionsSwitcher.run(\'st_dl\'); return(false);\">"+
					theUILang.Downloads+
				"</a></li>"+
				"<li id='hld_st_con'><a id=\"mnu_st_con\" href=\"#\" onclick=\"theOptionsSwitcher.run(\'st_con\'); return(false);\">"+
					theUILang.Connection+
				"</a></li>"+
				"<li id='hld_st_bt'><a id=\"mnu_st_bt\" href=\"#\" onclick=\"theOptionsSwitcher.run(\'st_bt\'); return(false);\">"+
					theUILang.BitTorrent+
				"</a></li>"+
				"<li  id='hld_st_ao' class=\"last\"><a id=\"mnu_st_ao\" href=\"#\" onclick=\"theOptionsSwitcher.run(\'st_ao\'); return(false);\">"+
					theUILang.Advanced+
				"</a></li>"+
			"</ul>"+
		"</aside>"+
		'<main class="auto">'+
			"<div id=\"st_gl\" class=\"stg_con\">"+
				"<fieldset>"+
					"<legend>"+theUILang.User_Interface+"</legend>"+
					"<div class=\"op50l\">"+
						"<input type=\"checkbox\" id=\"webui.confirm_when_deleting\" checked=\"true\" />"+
						"<label for=\"webui.confirm_when_deleting\">"+theUILang.Confirm_del_torr+"</label>"+
					"</div>"+
					"<div class=\"op50l algnright\">"+
						theUILang.Update_GUI_every+":&nbsp;<input type=\"text\" id=\"webui.update_interval\" class=\"TextboxShort\" value=\"3000\" />"+
						theUILang.ms+
					"</div>"+
					"<div class=\"op50l\"><input type=\"checkbox\" id=\"webui.alternate_color\" />"+
						"<label for=\"webui.alternate_color\">"+theUILang.Alt_list_bckgnd+"</label>"+
					"</div>"+
					"<div class=\"op50l algnright\">"+
						theUILang.ReqTimeout+":&nbsp;<input type=\"text\" id=\"webui.reqtimeout\" class=\"TextboxShort\" value=\"5000\" />"+
						theUILang.ms+
					"</div>"+
					"<div class=\"op50l\"><input type=\"checkbox\" id=\"webui.show_cats\" checked=\"true\" />"+
						"<label for=\"webui.show_cats\">"+theUILang.Show_cat_start+"</label>"+
					"</div>"+
					"<div class=\"op50l algnright\"><input type=\"checkbox\" id=\"webui.show_dets\" checked=\"true\" />"+
						"<label for=\"webui.show_dets\">"+theUILang.Show_det_start+"</label>"+
					"</div>"+
					"<div class=\"op50l\"><input type=\"checkbox\" id=\"webui.needmessage\"/>"+
						"<label for=\"webui.needmessage\">"+theUILang.GetTrackerMessage+"</label>"+
					"</div>"+

					"<div class=\"op50l algnright\">"+
						"<label for=\"webui.dateformat\">"+theUILang.DateFormat+":</label>&nbsp;"+
						"<select id=\"webui.dateformat\">"+
							"<option value='0'>31.12.2011</option>"+
							"<option value='1'>2011-12-31</option>"+
							"<option value='2'>12/31/2011</option>"+
						"</select>"+
					"</div>"+

					"<div class=\"op50l\">"+
						"<label for=\"webui.ignore_timeouts\">"+"<input type=\"checkbox\" id=\"webui.ignore_timeouts\" checked=\"true\" />"+theUILang.dontShowTimeouts+"</label>"+
					"</div>"+

					"<div class=\"op50l algnright\"><input type=\"checkbox\" id=\"webui.speedintitle\"/>"+
						"<label for=\"webui.speedintitle\">"+theUILang.showSpeedInTitle+"</label>"+
					"</div>"+

					"<div class=\"op100l\"><input type=\"checkbox\" id=\"webui.log_autoswitch\"/>"+
						"<label for=\"webui.log_autoswitch\">"+theUILang.logAutoSwitch+"</label>"+
					"</div>"+
					"<div class=\"op100l\"><input type=\"checkbox\" id=\"webui.show_labelsize\"/>"+
						"<label for=\"webui.show_labelsize\" >"+theUILang.showLabelSize+"</label>"+
					"</div>"+
					"<div class=\"op100l\">"+
						"<label for=\"webui.retry_on_error\">"+theUILang.retryOnErrorTitle+":</label>&nbsp;"+
						"<select id=\"webui.retry_on_error\">"+
							retries+
						"</select>"+
					"</div>"+
					"<div class=\"op50l\">"+
						"<label for=\"webui.lang\">"+theUILang.mnu_lang+":</label>&nbsp;"+
						"<select id=\"webui.lang\">"+
							languages+
						"</select>"+
					"</div>"+
				"</fieldset>"+
			"</div>"+
			"<div id=\"st_dl\" class=\"stg_con\">"+
				"<fieldset>"+
					"<legend>"+theUILang.Bandwidth_sett+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td>"+theUILang.Number_ul_slots+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_uploads\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Number_Peers_min+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.min_peers.normal\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Number_Peers_max+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_peers.normal\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Number_Peers_For_Seeds_min+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.min_peers.seed\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Number_Peers_For_Seeds_max+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_peers.seed\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Tracker_Numwant+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"trackers.numwant\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
				"<fieldset>"+
					"<legend>"+theUILang.Other_sett+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td><input id=\"pieces.hash.on_completion\" type=\"checkbox\"/>"+
								"<label for=\"pieces.hash.on_completion\">"+theUILang.Check_hash+"</label>"+
							"</td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Directory_For_Dl+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"directory.default\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
			"</div>"+
			"<div id=\"st_con\" class=\"stg_con\">"+
				"<div>"+
					"<input id=\"network.port_open\" type=\"checkbox\" onchange=\"linked(this, 0, ['network.port_range', 'network.port_random']);\" />"+
					"<label for=\"network.port_open\">"+
						theUILang.Enable_port_open+
					"</label>"+
				"</div>"+
				"<fieldset>"+
					"<legend>"+theUILang.Listening_Port+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td><label for=\"network.port_range\" class=\"disabled\">"+theUILang.Port_f_incom_conns+":</label></td>"+
							"<td class=\"alr\">"+
								"<input type=\"text\" id=\"network.port_range\" class=\"TextboxShort\" class=\"disabled\" maxlength=\"13\" />"+
							"</td>"+
						"</tr>"+
						"<tr>"+
							"<td colspan=\"2\">"+
								"<input id=\"network.port_random\" type=\"checkbox\"  class=\"disabled\"/>"+
								"<label for=\"network.port_random\"  class=\"disabled\">"+theUILang.Rnd_port_torr_start+"</label>"+
							"</td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
				"<fieldset>"+
					"<legend>"+theUILang.Bandwidth_Limiting+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td>"+theUILang.Global_max_upl+" ("+theUILang.kbs+"): [0: "+theUILang.unlimited+"]</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.global_up.max_rate\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Glob_max_downl+" ("+theUILang.kbs+"): [0: "+theUILang.unlimited+"]</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.global_down.max_rate\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
				"<fieldset>"+
					"<legend>"+theUILang.Other_Limiting+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td>"+theUILang.Number_ul_slots+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_uploads.global\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Number_dl_slots+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_downloads.global\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Glob_max_memory+" ("+theUILang.MB+"):</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"pieces.memory.max\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Glob_max_files+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"network.max_open_files\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Glob_max_http+":</td>"+
							"<td class=\"alr\"><input type=\"text\" id=\"network.http.max_open\" class=\"Textbox num\" maxlength=\"6\" /></td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
			"</div>"+
			"<div id=\"st_bt\" class=\"stg_con\">"+
				"<fieldset>"+
					"<legend>"+theUILang.Add_bittor_featrs+"</legend>"+
					"<table>"+
						"<tr>"+
							"<td><input id=\"dht\" type=\"checkbox\"  onchange=\"linked(this, 0, ['dht.port']);\" />"+
								"<label for=\"dht\">"+theUILang.En_DHT_ntw+"</label>"+
						"</td>"+
							"<td><input id=\"protocol.pex\" type=\"checkbox\" />"+
								"<label for=\"protocol.pex\">"+theUILang.Peer_exch+"</label>"+
							"</td>"+
						"</tr>"+
						"<tr>"+
							"<td class=\"disabled\">"+theUILang.dht_port+":</td>"+
							"<td><input type=\"text\" id=\"dht.port\" class=\"Textbox num\" maxlength=\"6\" class=\"disabled\"/></td>"+
						"</tr>"+
						"<tr>"+
							"<td>"+theUILang.Ip_report_track+":</td>"+
							"<td><input type=\"text\" id=\"network.local_address\" class=\"Textbox str\" maxlength=\"50\" /></td>"+
						"</tr>"+
					"</table>"+
				"</fieldset>"+
			"</div>"+
			"<div id=\"st_ao\" class=\"stg_con\">"+
				"<fieldset>"+
					"<legend>"+theUILang.Advanced+"</legend>"+
					"<div id=\"st_ao_h\">"+
						"<table width=\"99%\">"+
							"<tr>"+
								"<td>network.http.cacert</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.http.cacert\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.http.capath</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.http.capath\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>throttle.max_downloads.div</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_downloads.div\" class=\"Textbox num\" maxlength=\"5\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>throttle.max_uploads.div</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"throttle.max_uploads.div\" class=\"Textbox num\" maxlength=\"5\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>system.file.max_size</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"system.file.max_size\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>pieces.preload.type</td>"+
								"<td class=\"alr\">"+
									"<select id=\"pieces.preload.type\">"+
										"<option value=\"0\" selected=\"selected\">off</option>"+
										"<option value=\"1\">madvise</option>"+
										"<option value=\"2\">direct paging</option>"+
									"</select>"+
								"</td>"+
							"</tr>"+
							"<tr>"+
								"<td>pieces.preload.min_size</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"pieces.preload.min_size\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>pieces.preload.min_rate</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"pieces.preload.min_rate\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.receive_buffer.size</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.receive_buffer.size\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.send_buffer.size</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.send_buffer.size\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td><label for=\"pieces.sync.always_safe\">pieces.sync.always_safe</label></td>"+
								"<td class=\"alr\"><input type=\"checkbox\" id=\"pieces.sync.always_safe\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>pieces.sync.timeout_safe</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"pieces.sync.timeout_safe\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>pieces.sync.timeout</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"pieces.sync.timeout\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td><label for=\"network.scgi.dont_route\">network.scgi.dont_route</label></td>"+
								"<td class=\"alr\"><input type=\"checkbox\" id=\"network.scgi.dont_route\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>session.path</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"session.path\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td><label for=\"session.use_lock\">session.use_lock</label></td>"+
								"<td class=\"alr\"><input type=\"checkbox\" id=\"session.use_lock\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td><label for=\"session.on_completion\">session.on_completion</label></td>"+
								"<td class=\"alr\"><input type=\"checkbox\" id=\"session.on_completion\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>system.file.split_size</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"system.file.split_size\" class=\"Textbox num\" maxlength=\"20\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>system.file.split_suffix</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"system.file.split_suffix\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td><label for=\"trackers.use_udp\">trackers.use_udp</label></td>"+
								"<td class=\"alr\"><input type=\"checkbox\" id=\"trackers.use_udp\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.http.proxy_address</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.http.proxy_address\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.proxy_address</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.proxy_address\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
							"<tr>"+
								"<td>network.bind_address</td>"+
								"<td class=\"alr\"><input type=\"text\" id=\"network.bind_address\" class=\"Textbox str\" maxlength=\"100\" /></td>"+
							"</tr>"+
						"</table>"+
					"</div>"+
				"</fieldset>"+
			"</div>"+
			"<div id=\"st_btns\" class='aright buttons-list'>"+
				"<input type=\"button\" value=\"OK\" onclick=\"theDialogManager.hide('stg');theWebUI.setSettings();return(false);\" class=\"OK Button\" />"+
				"<input type=\"button\" value=\""+theUILang.Cancel+"\" class=\"Cancel Button\" />"+
			"</div>"+
		"</main>"+
		'</div>');
}

function correctContent()
{
	if (!$type(theWebUI.systemInfo))
		theWebUI.systemInfo = { rTorrent: { version: '?', libVersion: '?', started: false, apiVersion : 0 } };

	delete theWebUI.tables.plg;
	if (!theWebUI.systemInfo.rTorrent.started)
	{
		rPlugin.prototype.removePageFromTabs("TrackerList");
		rPlugin.prototype.removePageFromTabs("FileList");
		rPlugin.prototype.removePageFromTabs("PeerList");
		rPlugin.prototype.removePageFromTabs("Speed");
		$("#st_up").remove();
		$("#st_down").remove();
		rPlugin.prototype.removeButtonFromToolbar("add");
		rPlugin.prototype.removeSeparatorFromToolbar("remove");
		rPlugin.prototype.removeButtonFromToolbar("remove");
		rPlugin.prototype.removeSeparatorFromToolbar("start");
		rPlugin.prototype.removeButtonFromToolbar("start");
		rPlugin.prototype.removeButtonFromToolbar("pause");
		rPlugin.prototype.removeButtonFromToolbar("stop");
		rPlugin.prototype.removeSeparatorFromToolbar("settings");
	}
	else
	{
		theRequestManager.addRequest("fls","f.prioritize_first=",function(hash, fls, value)
		{
			if (value=='1')
				fls.prioritize = 1;
		});
		theRequestManager.addRequest("fls","f.prioritize_last=",function(hash, fls, value)
		{
			if (value=='1')
				fls.prioritize = 2;
		});

		$('#st_ao_h table tr:first').remove();
		$('#st_ao_h table tr:first').remove();
		$('#st_ao_h table tr:first').remove();
	}

	$.extend(theRequestManager.aliases,
	{
		"f.set_priority"	:	{ name: "f.priority.set", prm: 0 },
		"fi.get_filename_last"	:	{ name: "fi.filename_last", prm: 0 },
		"get_bind"		:	{ name: "network.bind_address", prm: 0 },
		"get_check_hash"	:	{ name: "pieces.hash.on_completion", prm: 0 },
		"get_connection_leech"	:	{ name: "protocol.connection.leech", prm: 0 },
		"get_connection_seed"	:	{ name: "protocol.connection.seed", prm: 0 },
		"get_directory"		:	{ name: "directory.default", prm: 0 },
		"get_down_rate"		:	{ name: "throttle.global_down.rate", prm: 0 },
		"get_down_total"	:	{ name: "throttle.global_down.total", prm: 0 },
		"get_download_rate"	:	{ name: "throttle.global_down.max_rate", prm: 0 },
		"get_http_capath"	:	{ name: "network.http.capath", prm: 0 },
		"get_http_proxy"	:	{ name: "network.http.proxy_address", prm: 0 },
		"get_max_downloads_div"	:	{ name: "throttle.max_downloads.div", prm: 0 },
		"get_max_downloads_global"	:	{ name: "throttle.max_downloads.global", prm: 0 },
		"get_max_file_size"	:	{ name: "system.file.max_size", prm: 0 },
		"get_max_memory_usage"	:	{ name: "pieces.memory.max", prm: 0 },
		"get_max_open_files"	:	{ name: "network.max_open_files", prm: 0 },
		"get_max_open_http"	:	{ name: "network.http.max_open", prm: 0 },
		"get_max_open_sockets"	:	{ name: "network.max_open_sockets", prm: 0 },
		"get_max_peers"		:	{ name: "throttle.max_peers.normal", prm: 0 },
		"get_max_peers_seed"	:	{ name: "throttle.max_peers.seed", prm: 0 },
		"get_max_uploads"	:	{ name: "throttle.max_uploads", prm: 0 },
		"get_max_uploads_div"	:	{ name: "throttle.max_uploads.div", prm: 0 },
		"get_max_uploads_global"	:	{ name: "throttle.max_uploads.global", prm: 0 },
		"get_memory_usage"	:	{ name: "pieces.memory.current", prm: 0 },
		"get_min_peers"		:	{ name: "throttle.min_peers.normal", prm: 0 },
		"get_min_peers_seed"	:	{ name: "throttle.min_peers.seed", prm: 0 },
		"get_name"		:	{ name: "session.name", prm: 0 },
		"get_proxy_address"	:	{ name: "network.http.proxy_address", prm: 0 },
		"get_receive_buffer_size"	:	{ name: "network.receive_buffer.size", prm: 0 },
		"get_safe_sync"		:	{ name: "pieces.sync.always_safe", prm: 0 },
		"get_send_buffer_size"	:	{ name: "network.send_buffer.size", prm: 0 },
		"get_session"		:	{ name: "session.path", prm: 0 },
		"get_session_lock"	:	{ name: "session.use_lock", prm: 0 },
		"get_session_on_completion"	:	{ name: "session.on_completion", prm: 0 },
		"get_split_file_size"	:	{ name: "system.file.split_size", prm: 0 },
		"get_split_suffix"	:	{ name: "system.file.split_suffix", prm: 0 },
		"get_stats_not_preloaded"	:	{ name: "pieces.stats_not_preloaded", prm: 0 },
		"get_stats_preloaded"	:	{ name: "pieces.stats_preloaded", prm: 0 },
		"get_throttle_down_max"	:	{ name: "throttle.down.max", prm: 0 },
		"get_throttle_down_rate"	:	{ name: "throttle.down.rate", prm: 0 },
		"get_throttle_up_max"	:	{ name: "throttle.up.max", prm: 1 },
		"get_throttle_up_rate"	:	{ name: "throttle.up.rate", prm: 1 },
		"get_timeout_safe_sync"	:	{ name: "pieces.sync.timeout_safe", prm: 0 },
		"get_timeout_sync"	:	{ name: "pieces.sync.timeout", prm: 0 },
		"get_tracker_numwant"	:	{ name: "trackers.numwant", prm: 0 },
		"get_up_rate"		:	{ name: "throttle.global_up.rate", prm: 0 },
		"get_up_total"		:	{ name: "throttle.global_up.total", prm: 0 },
		"get_upload_rate"	:	{ name: "throttle.global_up.max_rate", prm: 0 },
		"http_capath"		:	{ name: "network.http.capath", prm: 0 },
		"http_proxy"		:	{ name: "network.proxy_address", prm: 0 },
		"session_save"		:	{ name: "session.save", prm: 0 },
		"set_bind"		:	{ name: "network.bind_address.set", prm: 1 },
		"set_directory"		:	{ name: "directory.default.set", prm: 1 },
		"set_download_rate"	:	{ name: "throttle.global_down.max_rate.set", prm: 1 },
		"set_http_capath"	:	{ name: "network.http.capath.set", prm: 1 },
		"set_http_proxy"	:	{ name: "network.http.proxy_address.set", prm: 1 },
		"set_proxy_address"	:	{ name: "network.http.proxy_address.set", prm: 1 },
		"set_receive_buffer_size"	:	{ name: "network.receive_buffer.size.set", prm: 1 },
		"set_send_buffer_size"	:	{ name: "network.send_buffer.size.set", prm: 1 },
		"set_session"		:	{ name: "session.path.set", prm: 1 },
		"set_session_lock"	:	{ name: "session.use_lock.set", prm: 1 },
		"set_session_on_completion"	:	{ name: "session.on_completion.set", prm: 1 },
		"set_tracker_numwant"	:	{ name: "trackers.numwant.set", prm: 1 },
		"set_upload_rate"	:	{ name: "throttle.global_up.max_rate.set", prm: 1 },
		"system.file_allocate"	:	{ name: "system.file.allocate", prm: 0 },
		"system.file_allocate.set"	:	{ name: "system.file.allocate.set", prm: 1 },
		"t.set_enabled"		:	{ name: "t.is_enabled.set", prm: 0 },
		"throttle_down"		:	{ name: "throttle.down", prm: 1 },
		"throttle_ip"		:	{ name: "throttle.ip", prm: 1 },
		"throttle_up"		:	{ name: "throttle.up", prm: 1 },
		"tracker_numwant"	:	{ name: "trackers.numwant", prm: 0 },
	});

	$("#rtorrentv").text(theWebUI.systemInfo.rTorrent.version+"/"+theWebUI.systemInfo.rTorrent.libVersion);
}
