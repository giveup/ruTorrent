/*
 *      Link to rTorrent.
 *
 */

var dStatus = { started : 1, paused : 2, checking : 4, hashing : 8, error : 16 };

var theRequestManager =
{
	aliases: {},
    trt: {
		commands: [
			"d.hash=",
			"d.is_open=",
			"d.is_hash_checking=",
			"d.is_hash_checked=",
			"d.state=",
			"d.name=",
			"d.size_bytes=",
			"d.completed_chunks=",
			"d.size_chunks=",
			"d.bytes_done=",
			"d.up.total=",
			"d.ratio=",
			"d.up.rate=",
			"d.down.rate=",
			"d.chunk_size=",
			"d.custom1=",
			"d.peers_accounted=",
			"d.peers_not_connected=",
			"d.peers_connected=",
			"d.peers_complete=",
			"d.left_bytes=",
			"d.priority=",
			"d.state_changed=",
			"d.skip.total=",
			"d.hashing=",
			"d.chunks_hashed=",
			"d.base_path=",
			"d.creation_date=",
			"d.tracker_focus=",
			"d.is_active=",
			"d.message=",
			"d.custom2=",
			"d.free_diskspace=",
			"d.is_private=",
			"d.is_multi_file="
		],
		handlers: []
	},
	trk:
	{
		commands:
		[
		    "t.url=",
			"t.type=",
			"t.is_enabled=",
			"t.group=",
			"t.scrape_complete=",
			"t.scrape_incomplete=",
			"t.scrape_downloaded=",
			"t.normal_interval=",
			"t.scrape_time_last="
		],
		handlers: []
	},
	fls:
	{
		commands:
		[
			"f.path=",
			"f.completed_chunks=",
			"f.size_chunks=",
			"f.size_bytes=",
			"f.priority="
		],
		handlers: []
	},
	prs:
	{
		commands:
		[
			"p.id=",
			"p.address=",
			"p.client_version=",
			"p.is_incoming=",
			"p.is_encrypted=",
			"p.is_snubbed=",
			"p.completed_percent=",
			"p.down_total=",
			"p.up_total=",
			"p.down_rate=",
			"p.up_rate=",
			"p.id_html=",
			"p.peer_rate=",
			"p.peer_total=",
			"p.port="
		],
		handlers: []
	},
	ttl:
	{
		commands:
		[
			"throttle.global_up.total",
			"throttle.global_down.total",
			"throttle.global_up.max_rate",
			"throttle.global_down.max_rate"
		],
		handlers: []
	},
	prp:
	{
		commands:
		[
			"d.peer_exchange",
			"d.peers_max",
			"d.peers_min",
			"d.tracker_numwant",
			"d.uploads_max",
			"d.is_private",
			"d.connection_seed"
		],
		handlers: []
	},
	stg:
	{
		commands:
		[
			"network.bind_address",
			"pieces.hash.on_completion",
			"dht.port",
			"directory.default",
			"throttle.global_down.max_rate",
			"network.http.cacert",
			"network.http.capath",
			"network.http.proxy_address",
			"network.local_address",
			"throttle.max_downloads.div",
			"throttle.max_downloads.global",
			"system.file.max_size",
			"pieces.memory.max",
			"network.max_open_files",
			"network.http.max_open",
			"throttle.max_peers.normal",
			"throttle.max_peers.seed",
			"throttle.max_uploads",
			"throttle.max_uploads.global",
			"throttle.min_peers.seed",
			"throttle.min_peers.normal",
			"protocol.pex",
			"network.port_open",
			"throttle.global_up.max_rate",
			"network.port_random",
			"network.port_range",
			"pieces.preload.min_size",
			"pieces.preload.min_rate",
			"pieces.preload.type",
			"network.proxy_address",
			"network.receive_buffer.size",
			"pieces.sync.always_safe",
			"network.scgi.dont_route",
			"network.send_buffer.size",
			"session.path",
			"session.use_lock",
			"session.on_completion",
			"system.file.split_size",
			"system.file.split_suffix",
			"pieces.sync.timeout_safe",
			"pieces.sync.timeout",
			"trackers.numwant",
			"trackers.use_udp",
			"throttle.max_uploads.div",
			"network.max_open_sockets"
		],
		handlers: []
	},
	init: function()
	{
        var self = this;
		$.each(["trt","trk", "fls", "prs", "ttl", "prp", "stg"], function(ndx,cmd)
		{
			self[cmd].count = self[cmd].commands.length;
		});
	},
	addRequest: function( system, command, responseHandler )
	{
		this[system].handlers.push( { ndx: command ? this[system].commands.length : null, response: responseHandler } );
		if (command)
		        this[system].commands.push(command);
	        return(this[system].handlers.length-1);
	},
	removeRequest: function( system, id )
	{
		this[system].handlers[id] = null;
	},
	map: function(cmd,no)
	{
		if (!$type(no))
		{
			var add = '';
			if (cmd.length && (cmd[cmd.length-1]=='='))
			{
				cmd = cmd.substr(0,cmd.length-1);
				add = '=';
			}
			return(this.aliases[cmd] ? this.aliases[cmd].name+add : cmd+add);
		}
		return( this.map(this[cmd].commands[no]) );
	},
	patchCommand: function( cmd, name )
	{
		if (this.aliases[name] && this.aliases[name].prm)
			cmd.addParameter("string","");
	},
	patchRequest: function( commands )
	{
		for ( var i in commands )
		{
			var cmd = commands[i];
			var prefix = '';
			if (cmd.command.indexOf('t.') === 0)
				prefix = ':t';
			else
			if (cmd.command.indexOf('p.') === 0)
				prefix = ':p';
			else
			if (cmd.command.indexOf('f.') === 0)
				prefix = ':f';
			if (prefix &&
				(cmd.params.length>1) &&
				(cmd.command.indexOf('.multicall')<0) &&
				(cmd.params[0].value.indexOf(':') < 0))
			{
				cmd.params[0].value = cmd.params[0].value+prefix+cmd.params[1].value;
				cmd.params.splice( 1, 1 );
			}
		}
	}
};

theRequestManager.init();

function rXMLRPCCommand( cmd )
{
	this.command = theRequestManager.map(cmd);
	this.params = new Array();
	theRequestManager.patchCommand( this, cmd );
}

rXMLRPCCommand.prototype.addParameter = function(aType,aValue)
{
	this.params.push( {type : aType, value : aValue} );
}

function rTorrentStub( URI )
{
	this.action = "none";
	this.hashes = new Array();
	this.ss = new Array();
	this.vs = new Array();
	this.listRequired = false;
	this.content = null;
	this.mountPoint = theURLs.XMLRPCMountPoint;
	this.faultString = [];
	this.contentType = "text/xml; charset=UTF-8";
	this.dataType = "xml";
	this.method = "POST";
	this.ifModified = false;
	this.cache = false;

	var loc = URI.indexOf("?");
	if (loc>=0)
		URI = URI.substr(loc);
	this.URI = URI;
	if (URI.indexOf("?list=1")==0)
	{
		this.action = "list";
	}
	else
	{
		var vars = URI.split("&");
		for (var i=0; i<vars.length; i++)
		{
			var parts = vars[i].split("=");
			if (parts[0]=="?action")
				this.action = parts[1];
			else
			if (parts[0]=="hash")
				this.hashes.push(parts[1]);
			else
			if (parts[0]=="s" || parts[0]=="p")
				this.ss.push(parts[1]);
			else
			if (parts[0]=="v" || parts[0]=="f")
				this.vs.push(parts[1]);
			else
			if (parts[0]=="list")
				this.listRequired = true;
		}
	}
	this.commands = new Array();
	if (eval('typeof(this.'+this.action+') != "undefined"'))
		eval("this."+this.action+"()");
	if (this.commands.length>0)
		this.makeMultiCall();
}

rTorrentStub.prototype.getfiles = function()
{
	var cmd = new rXMLRPCCommand("f.multicall");
	cmd.addParameter("string",this.hashes[0]);
	cmd.addParameter("string","");
	for ( var i in theRequestManager.fls.commands )
		cmd.addParameter("string",theRequestManager.map("fls",i));
	this.commands.push( cmd );
}

rTorrentStub.prototype.getpeers = function()
{
	var cmd = new rXMLRPCCommand("p.multicall");
	cmd.addParameter("string",this.hashes[0]);
	cmd.addParameter("string","");
	for ( var i in theRequestManager.prs.commands )
		cmd.addParameter("string",theRequestManager.map("prs",i));
	this.commands.push( cmd );
}

rTorrentStub.prototype.gettrackers = function()
{
	var cmd = new rXMLRPCCommand("t.multicall");
	cmd.addParameter("string",this.hashes[0]);
	cmd.addParameter("string","");
	for ( var i in theRequestManager.trk.commands )
		cmd.addParameter("string",theRequestManager.map("trk",i));
	this.commands.push( cmd );
}

rTorrentStub.prototype.getalltrackers = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("t.multicall");
		cmd.addParameter("string",this.hashes[i]);
		cmd.addParameter("string","");
		for ( var j in theRequestManager.trk.commands )
			cmd.addParameter("string",theRequestManager.map("trk",j));
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.settrackerstate = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("t.is_enabled.set");
		cmd.addParameter("string",this.hashes[0]);
		cmd.addParameter("i4",this.vs[i]);
		cmd.addParameter("i4",this.ss[0]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.list = function()
{
	var cmd = new rXMLRPCCommand("d.multicall2");
	cmd.addParameter("string","");
	cmd.addParameter("string","main");
	for ( var i in theRequestManager.trt.commands )
	{
		if (!theWebUI.settings["webui.needmessage"] && (theRequestManager.trt.commands[i]=="d.message="))
			cmd.addParameter("string",theRequestManager.map("d.custom5="));
		else
			cmd.addParameter("string",theRequestManager.map("trt",i));
	}
	this.commands.push( cmd );
}

rTorrentStub.prototype.setuisettings = function()
{
	this.content = "v="+this.vs[0];
	this.mountPoint = theURLs.SetSettingsURL;
	this.contentType = "application/x-www-form-urlencoded";
	this.dataType = "text";
}

rTorrentStub.prototype.doneplugins = function()
{
	this.mountPoint = theURLs.GetDonePluginsURL;
	this.dataType = "script";
	this.content = "cmd="+this.ss[0];
	this.contentType = "application/x-www-form-urlencoded";
	for (var i=0; i<this.hashes.length; i++)
		this.content += ("&plg="+this.hashes[i]);
}

rTorrentStub.prototype.recheck = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.check_hash");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.setsettings = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var prmType = "string";
		if (this.ss[i].charAt(0)=='n')
			prmType = "i8";
		var prm = this.vs[i];
		var cmd = null;
		if (this.ss[i]=="ndht")
		{
			if (prm==0)
				prm = "disable";
			else
				prm = "auto";
			prmType = "string";
			cmd = new rXMLRPCCommand('dht');
		}
		else
			cmd = new rXMLRPCCommand(this.ss[i].substr(1) + ".set");
		cmd.addParameter(prmType,prm);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.getsettings = function()
{
	this.commands.push(new rXMLRPCCommand("dht.statistics"));
	for ( var cmd in theRequestManager.stg.commands )
		this.commands.push(new rXMLRPCCommand(theRequestManager.stg.commands[cmd]));
}

rTorrentStub.prototype.start = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.open");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
		cmd = new rXMLRPCCommand("d.start");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.stop = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.stop");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
		cmd = new rXMLRPCCommand("d.close");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.updateTracker = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.tracker_announce");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.pause = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.stop");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.unpause = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.start");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.remove = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.erase");
		cmd.addParameter("string",this.hashes[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.dsetprio = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.priority.set");
		cmd.addParameter("string",this.hashes[i]);
		cmd.addParameter("i4",this.vs[0]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.setprio = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("f.priority.set");
		cmd.addParameter("string",this.hashes[0]);
		cmd.addParameter("i4",this.vs[i]);
		cmd.addParameter("i4",this.ss[0]);
		this.commands.push( cmd );
	}
	cmd = new rXMLRPCCommand("d.update_priorities");
	cmd.addParameter("string",this.hashes[0]);
	this.commands.push( cmd );
}

rTorrentStub.prototype.setprioritize = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		switch (this.ss[0])
		{
			case '0':
			{
				var cmd = new rXMLRPCCommand( "f.prioritize_first.disable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				cmd = new rXMLRPCCommand( "f.prioritize_last.disable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				break;
			}
			case '1':
			{
				var cmd = new rXMLRPCCommand( "f.prioritize_first.enable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				cmd = new rXMLRPCCommand( "f.prioritize_last.disable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				break;
			}
			case '2':
			{
				var cmd = new rXMLRPCCommand( "f.prioritize_first.disable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				cmd = new rXMLRPCCommand( "f.prioritize_last.enable" );
				cmd.addParameter("string",this.hashes[0]+":f"+this.vs[i]);
				this.commands.push( cmd );
				break;
			}
		}
	}
	cmd = new rXMLRPCCommand("d.update_priorities");
	cmd.addParameter("string",this.hashes[0]);
	this.commands.push( cmd );
}

rTorrentStub.prototype.setlabel = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("d.custom1.set");
		cmd.addParameter("string",this.hashes[i]);
		cmd.addParameter("string",this.vs[0]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.gettotal = function()
{
	for (var i in theRequestManager.ttl.commands)
		this.commands.push( new rXMLRPCCommand(theRequestManager.ttl.commands[i]) );
}

rTorrentStub.prototype.getprops = function()
{
	for (var i in theRequestManager.prp.commands)
	{
		var cmd = new rXMLRPCCommand(theRequestManager.prp.commands[i]);
		cmd.addParameter("string",this.hashes[0]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.setprops = function()
{
	var cmd = null;
	for (var i=0; i<this.ss.length; i++)
	{
		if (this.ss[i]=="superseed")
		{
        		var conn = (this.vs[i]!=0) ? "initial_seed" : "seed";
			cmd = new rXMLRPCCommand("branch");
			cmd.addParameter("string",this.hashes[0]);
			cmd.addParameter("string",theRequestManager.map("d.is_active="));
			cmd.addParameter("string",theRequestManager.map("cat")+
				'=$'+theRequestManager.map("d.stop=")+
				',$'+theRequestManager.map("d.close=")+
				',$'+theRequestManager.map("d.connection_seed.set=")+conn+
				',$'+theRequestManager.map("d.open=")+
				',$'+theRequestManager.map("d.start="));
			cmd.addParameter("string",theRequestManager.map("d.connection_seed.set=")+conn);
		}
		else
		{
			if (this.ss[i]=="ulslots")
				cmd = new rXMLRPCCommand("d.uploads_max.set");
			else
			if (this.ss[i]=="pex")
				cmd = new rXMLRPCCommand("d.peer_exchange.set");
			else
				cmd = new rXMLRPCCommand("d."+this.ss[i]+".set");
			cmd.addParameter("string",this.hashes[0]);
			cmd.addParameter("i4",this.vs[i]);
		}
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.setulrate = function()
{
	var cmd = new rXMLRPCCommand("throttle.global_up.max_rate.set");
	cmd.addParameter("string",this.ss[0]);
	this.commands.push( cmd );
}

rTorrentStub.prototype.setdlrate = function()
{
	var cmd = new rXMLRPCCommand("throttle.global_down.max_rate");
	cmd.addParameter("string",this.ss[0]);
	this.commands.push( cmd );
}

rTorrentStub.prototype.snub = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("p.snubbed.set");
		cmd.addParameter("string",this.hashes[0]+":p"+this.vs[i]);
                cmd.addParameter("i4",1);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.unsnub = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("p.snubbed.set");
		cmd.addParameter("string",this.hashes[0]+":p"+this.vs[i]);
                cmd.addParameter("i4",0);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.ban = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("p.banned.set");
		cmd.addParameter("string",this.hashes[0]+":p"+this.vs[i]);
                cmd.addParameter("i4",1);
		this.commands.push( cmd );
		cmd = new rXMLRPCCommand("p.disconnect");
		cmd.addParameter("string",this.hashes[0]+":p"+this.vs[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.kick = function()
{
	for (var i=0; i<this.vs.length; i++)
	{
		var cmd = new rXMLRPCCommand("p.disconnect");
		cmd.addParameter("string",this.hashes[0]+":p"+this.vs[i]);
		this.commands.push( cmd );
	}
}

rTorrentStub.prototype.addpeer = function()
{
	var cmd = new rXMLRPCCommand("add_peer");
	cmd.addParameter("string",this.hashes[0]);
	cmd.addParameter("string",decodeURIComponent(this.vs[0]));
	this.commands.push( cmd );
}

rTorrentStub.prototype.createqueued = function()
{
	for (var i=0; i<this.hashes.length; i++)
	{
		var cmd = new rXMLRPCCommand("f.multicall");
		cmd.addParameter("string",this.hashes[i]);
		cmd.addParameter("string","");
		cmd.addParameter("string",theRequestManager.map("f.set_create_queued=")+'0');
		cmd.addParameter("string",theRequestManager.map("f.set_resize_queued=")+'0');
		this.commands.push( cmd );
	}

}

rTorrentStub.prototype.makeMultiCall = function()
{
	theRequestManager.patchRequest( this.commands );
	this.content = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>';
	if (this.commands.length==1)
	{
		var cmd = this.commands[0];
	        this.content+=(cmd.command+'</methodName><params>');
	        for (var i=0; i<cmd.params.length; i++)
	        {
	        	var prm = cmd.params[i];
			this.content += ('<param><value><'+prm.type+'>'+prm.value+
				'</'+prm.type+'></value></param>');
	        }
	        cmd = null;
	}
	else
	{
		this.content+='system.multicall</methodName><params><param><value><array><data>';
		for (var i=0; i<this.commands.length; i++)
		{
			var cmd = this.commands[i];
			this.content+=('<value><struct><member><name>methodName</name><value><string>'+
				cmd.command+'</string></value></member><member><name>params</name><value><array><data>');
			for (var j=0; j<cmd.params.length; j++)
			{
				var prm = cmd.params[j];
				this.content += ('<value><'+prm.type+'>'+
					prm.value+'</'+prm.type+'></value>');
			}
			this.content+="</data></array></value></member></struct></value>";
			cmd = null;
		}
		this.content+='</data></array></value></param>';
	}
	this.content += '</params></methodCall>';
}

rTorrentStub.prototype.getValue = function(values,i)
{
    var ret = "";
	if (values && values.length && (values.length>i)) {
		var value = values[i];
		var el = value.childNodes[0];
		while(!el.tagName)
			el = el.childNodes[0];
		ret = $type(el.textContent) ? el.textContent.trim() :
			el.childNodes.length ?
			el.childNodes[0].data : "";
	}
	return((ret==null) ? "" : ret);
}

rTorrentStub.prototype.getResponse = function(data)
{
	var ret = "";
	if (this.dataType=="xml")
	{
		if (!data)
			return(ret);
		var fault = data.getElementsByTagName('fault');
		if (fault && fault.length)
		{
			var names = data.getElementsByTagName('value');
			this.faultString.push("XMLRPC Error: "+this.getValue(names,2)+" ["+this.action+"]");
		}
		else
		{
			var names = data.getElementsByTagName('name');
			if (names)
				for (var i=0; i<names.length; i++)
					if (names[i].childNodes[0].data=="faultString")
					{
						var values = names[i].parentNode.getElementsByTagName('value');
						this.faultString.push("XMLRPC Error: "+this.getValue(values,0)+" ["+this.action+"]");
					}
		}
	}
	if (!this.isError())
	{
		if (eval('typeof(this.'+this.action+'Response) != "undefined"'))
			eval("ret = this."+this.action+"Response(data)");
		else
			ret = data;
	}
	return(ret);
}

rTorrentStub.prototype.setprioResponse = function(xml)
{
	return(this.hashes[0]);
}

rTorrentStub.prototype.setprioritizeResponse = function(xml)
{
	return(this.hashes[0]);
}

rTorrentStub.prototype.getpropsResponse = function(xml)
{
	var datas = xml.getElementsByTagName('data');
	var data = datas[0];
	var values = data.getElementsByTagName('value');
	var ret = {};
	var hash = this.hashes[0];
	ret[hash] =
	{
		pex: (this.getValue(values,11)!='0') ? -1 : this.getValue(values,1),
		peers_max: this.getValue(values,3),
		peers_min: this.getValue(values,5),
		tracker_numwant: this.getValue(values,7),
		ulslots: this.getValue(values,9),
		superseed: (this.getValue(values,13)=="initial_seed") ? 1 : 0
	};
	var self = this;
	$.each( theRequestManager.prp.handlers, function(i,handler)
	{
	        if (handler)
			handler.response( hash, ret, (handler.ndx===null) ? null : self.getValue(values,handler.ndx*2+1) );
	});
	return(ret);
}

rTorrentStub.prototype.gettotalResponse = function(xml)
{
	var datas = xml.getElementsByTagName('data');
	var data = datas[0];
	var values = data.getElementsByTagName('value');
	var ret = { UL: this.getValue(values,1), DL: this.getValue(values,3), rateUL: this.getValue(values,5), rateDL: this.getValue(values,7) };
	var self = this;
	$.each( theRequestManager.ttl.handlers, function(i,handler)
	{
	        if (handler)
			handler.response( ret, (handler.ndx===null) ? null : self.getValue(values,handler.ndx*2+1) );
	});
	return( ret );
}

rTorrentStub.prototype.getsettingsResponse = function(xml)
{
	var datas = xml.getElementsByTagName('data');
	var data = datas[0];
	var values = data.getElementsByTagName('value');
	var ret = {};

	var i = 5;
	var dht_active = this.getValue(values,2);
	var dht = this.getValue(values,3);
	if (dht_active!='0')
	{
		i+=(values.length-101);
		dht = this.getValue(values,7);
	}
	if ((dht=="auto") || (dht=="on"))
		ret.dht = 1;
	else
		ret.dht = 0;

	for (;i<255; i++)
	{
		var s = this.getValue(values,i).replace(/(^\s+)|(\s+$)/g, "");
		if (s.length)
			break;
	}

	for ( var cmd=0; cmd<theRequestManager.stg.count; cmd++ )
	{
        var v = this.getValue(values,i);
		ret[theRequestManager.stg.commands[cmd]] = v;
		i+=2;
	}
	var self = this;
	$.each( theRequestManager.stg.handlers, function(i,handler)
	{
	        if (handler)
			handler.response( ret, (handler.ndx===null) ? null : self.getValue(values,i) );
		i+=2;
	});
	return(ret);
}

rTorrentStub.prototype.getfilesResponse = function(xml)
{
	var ret = {};
	var hash = this.hashes[0];
	ret[hash] = [];
	var datas = xml.getElementsByTagName('data');
	var self = this;
	for (var j=1;j<datas.length;j++)
	{
		var data = datas[j];
		var values = data.getElementsByTagName('value');
		var fls = {};
		fls.name = this.getValue(values,0);
		fls.size = parseInt(this.getValue(values,3));
		var get_size_chunks = parseInt(this.getValue(values,2));	// f.get_size_chunks
		var get_completed_chunks = parseInt(this.getValue(values,1));	// f.get_completed_chunks
		if (get_completed_chunks>get_size_chunks)
			get_completed_chunks = get_size_chunks;
		var get_completed_bytes = (get_size_chunks==0) ? 0 : fls.size/get_size_chunks*get_completed_chunks;
		fls.done = get_completed_bytes;
		fls.priority = this.getValue(values,4);

		$.each( theRequestManager.fls.handlers, function(i,handler)
		{
        	        if (handler)
				handler.response( hash, fls, (handler.ndx===null) ? null : self.getValue(values,handler.ndx) );
		});

                ret[hash].push(fls);
	}
	return(ret);
}

rTorrentStub.prototype.getpeersResponse = function(xml)
{
	var ret = {};
	var datas = xml.getElementsByTagName('data');
	var self = this;
	for (var j=1;j<datas.length;j++)
	{
		var data = datas[j];
		var values = data.getElementsByTagName('value');
		var peer = {};
		peer.name = this.getValue(values,1);
		peer.ip = peer.name;
		var cv = this.getValue(values,2);
		var mycv = theBTClientVersion.get(this.getValue(values,11));
		if ((mycv.indexOf("Unknown")>=0) && (cv.indexOf("Unknown")<0))
			mycv = cv;
		peer.version = mycv;
		peer.flags = '';
		if (this.getValue(values,3)==1)	//	p.is_incoming
			peer.flags+='I';
		if (this.getValue(values,4)==1)	//	p.is_encrypted
			peer.flags+='E';
		peer.snubbed = 0;
		if (this.getValue(values,5)==1)	//	p.is_snubbed
		{
			peer.flags+='S';
			peer.snubbed = 1;
		}
		peer.done = this.getValue(values,6);		//	get_completed_percent
		peer.downloaded = this.getValue(values,7);	//	p.get_down_total
		peer.uploaded = this.getValue(values,8);	//	p.get_up_total
		peer.dl = this.getValue(values,9);		//	p.get_down_rate
		peer.ul = this.getValue(values,10);		//	p.get_up_rate
		peer.peerdl = this.getValue(values,12);		//	p.get_peer_rate
		peer.peerdownloaded = this.getValue(values,13);	//	p.get_peer_total
		peer.port = this.getValue(values,14);		//	p.port
		var id = this.getValue(values,0);
		$.each( theRequestManager.prs.handlers, function(i,handler)
		{
        	        if (handler)
				handler.response( id, peer, (handler.ndx===null) ? null : self.getValue(values,handler.ndx) );
		});

		ret[id] = peer;
	}
	return(ret);
}

rTorrentStub.prototype.gettrackersResponse = function(xml)
{
	var ret = {};
	var hash = this.hashes[0];
	ret[hash] = [];
	var datas = xml.getElementsByTagName('data');
	var self = this;
	for (var j=1;j<datas.length;j++)
	{
		var data = datas[j];
		var values = data.getElementsByTagName('value');
	        var trk = {};
		trk.name = this.getValue(values,0);
		trk.type = this.getValue(values,1);
		trk.enabled = this.getValue(values,2);
		trk.group = this.getValue(values,3);
		trk.seeds = this.getValue(values,4);
		trk.peers = this.getValue(values,5);
		trk.downloaded = this.getValue(values,6);
		trk.interval = this.getValue(values,7);
		trk.last = this.getValue(values,8);

		$.each( theRequestManager.trk.handlers, function(i,handler)
		{
		        if (handler)
				handler.response( hash, trk, (handler.ndx===null) ? null : self.getValue(values,handler.ndx) );
		});

		ret[hash].push(trk);
	}
	return(ret);
}

rTorrentStub.prototype.getalltrackersResponse = function(xml)
{
    var allDatas = xml.getElementsByTagName('data');
	var ret = {};
	var delta = (this.hashes.length>1) ? 1 : 0;
	var cnt = delta;
	var self = this;
	for ( var i=0; i<this.hashes.length; i++) {
		var datas = allDatas[cnt].getElementsByTagName('data');
		var hash = this.hashes[i];
		ret[hash] = [];
		for (var j=delta;j<datas.length;j++) {
			var data = datas[j];
			var values = data.getElementsByTagName('value');
		        var trk = {};
			trk.name = this.getValue(values,0);
			trk.type = this.getValue(values,1);
			trk.enabled = this.getValue(values,2);
			trk.group = this.getValue(values,3);
			trk.seeds = this.getValue(values,4);
			trk.peers = this.getValue(values,5);
			trk.downloaded = this.getValue(values,6);

			$.each( theRequestManager.trk.handlers, function(i,handler)
			{
    	        if (handler)
					handler.response( hash, trk, (handler.ndx===null) ? null : self.getValue(values,handler.ndx) );
			});

			ret[hash].push(trk);
		}
		cnt+=(datas.length+1);
	}
	return(ret);
}

rTorrentStub.prototype.listResponse = function(xml)
{
	var ret = {};
	ret.torrents = {};
	ret.labels = {};
	ret.labels_size = {};
	var datas = xml.getElementsByTagName('data');
	var self = this;
	for (var j=1;j<datas.length;j++) {
		var data = datas[j];
		var values = data.getElementsByTagName('value');
		var torrent = {};
		var state = 0;
		var is_open = this.getValue(values,1);
		var is_hash_checking = this.getValue(values,2);
		var is_hash_checked = this.getValue(values,3);
		var get_state = this.getValue(values,4);
		var get_hashing = this.getValue(values,24);
		var is_active = this.getValue(values,29);
		torrent.msg = this.getValue(values,30);
		if (is_open!=0) {
			state|=dStatus.started;
			if ((get_state==0) || (is_active==0))
				state|=dStatus.paused;
		}
		if (get_hashing!=0)
			state|=dStatus.hashing;
		if (is_hash_checking!=0)
			state|=dStatus.checking;
		if (torrent.msg.length && torrent.msg!="Tracker: [Tried all trackers.]")
			state|=dStatus.error;
		torrent.state = state;
		torrent.name = this.getValue(values,5);
		torrent.size = this.getValue(values,6);
		var get_completed_chunks = parseInt(this.getValue(values,7));
		var get_hashed_chunks = parseInt(this.getValue(values,25));
		var get_size_chunks = parseInt(this.getValue(values,8));
		var chunks_processing = (is_hash_checking==0) ? get_completed_chunks : get_hashed_chunks;
		torrent.done = Math.floor(chunks_processing/get_size_chunks*1000);
		torrent.downloaded = this.getValue(values,9);
		torrent.uploaded = this.getValue(values,10);
		torrent.ratio = this.getValue(values,11);
		torrent.ul = this.getValue(values,12);
		torrent.dl = this.getValue(values,13);
		var get_chunk_size = parseInt(this.getValue(values,14));
		torrent.eta = (torrent.dl>0) ? Math.floor((get_size_chunks-get_completed_chunks)*get_chunk_size/torrent.dl) : -1;
		try {
			torrent.label = decodeURIComponent(this.getValue(values,15)).trim();
		} catch(e) {
			torrent.label = '';
		}
		if (torrent.label.length>0) {
			if (!$type(ret.labels[torrent.label])) {
				ret.labels[torrent.label] = 1;
				ret.labels_size[torrent.label] = parseInt(torrent.size);
			} else {
				ret.labels[torrent.label]++;
				ret.labels_size[torrent.label] = parseInt(ret.labels_size[torrent.label]) + parseInt(torrent.size);
			}
		}
		var get_peers_not_connected = parseInt(this.getValue(values,17));
		var get_peers_connected = parseInt(this.getValue(values,18));
		var get_peers_all = get_peers_not_connected+get_peers_connected;
		torrent.peers_actual = this.getValue(values,16);
		torrent.peers_all = get_peers_all;
		torrent.seeds_actual = this.getValue(values,19);
		torrent.seeds_all = get_peers_all;
		torrent.remaining = this.getValue(values,20);
		torrent.priority = this.getValue(values,21);
		torrent.state_changed = this.getValue(values,22);
		torrent.skip_total = this.getValue(values,23);
		torrent.base_path = this.getValue(values,26);
		torrent.created = this.getValue(values,27);
		torrent.tracker_focus = this.getValue(values,28);
		try {
			torrent.comment = this.getValue(values,31);
			if (torrent.comment.search("VRS24mrker")==0) {
				torrent.comment = decodeURIComponent(torrent.comment.substr(10));
			}
		} catch(e) {
			torrent.comment = '';
		}
		torrent.free_diskspace = this.getValue(values,32);
		torrent.private = this.getValue(values,33);
		torrent.multi_file = iv(this.getValue(values,34));
		torrent.seeds = torrent.seeds_actual + " (" + torrent.seeds_all + ")";
		torrent.peers = torrent.peers_actual + " (" + torrent.peers_all + ")";
		var hash = this.getValue(values,0);
		$.each( theRequestManager.trt.handlers, function(i,handler)
		{
	        if (handler)
				handler.response( hash, torrent, (handler.ndx===null) ? null : self.getValue(values,handler.ndx) );
		});
		ret.torrents[hash] = torrent;
	}
	return(ret);
}

rTorrentStub.prototype.isError = function()
{
	return(this.faultString.length);
}

rTorrentStub.prototype.logErrorMessages = function()
{
	for (var i in this.faultString)
		noty(this.faultString[i],"error");
}

function Ajax(URI, isASync, onComplete, onTimeout, onError, reqTimeout)
{
    var stub = new rTorrentStub(URI);
	$.ajax(
	{
		type: stub.method,
		url: stub.mountPoint,
		async: (isASync == null) ? true : isASync,
		contentType: stub.contentType,
		data: (stub.content == null) ? "" : stub.content,
		processData: false,
		timeout: reqTimeout || 10000,
		cache: stub.cache,
		ifModified: stub.ifModified,
		dataType: stub.dataType,
		traditional: true,
		global: true,

		complete: function(XMLHttpRequest, textStatus)
		{
			stub = null;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			if ((textStatus=="timeout") && ($type(onTimeout) == "function")) {
				onTimeout();
			} else if (($type(onError) == "function")) {
		        var status = "Status unavailable";
		        var response = "Response unavailable";
				try {
					status = XMLHttpRequest.status;
					response = XMLHttpRequest.responseText;
				} catch(e) {
					// Do nothing
				}
				if ( stub.dataType=="script" )
					response = errorThrown;
				onError(status+" ["+textStatus+","+stub.action+"]",response);
			}
		},
		success: function(data, textStatus)
		{
			var responseText = stub.getResponse(data);
			stub.logErrorMessages();
			if (stub.listRequired) {
				Ajax("?list=1", isASync, onComplete, onTimeout, onError, reqTimeout);
			} else {
        		if (!stub.isError()) {
        			switch ($type(onComplete)) {
						case "function":
							onComplete(responseText);
							break;
						case "array":
							onComplete[0].apply(onComplete[1],
								new Array(responseText, onComplete[2]));
							break;
					}
				}
			}
		}
	});
}

$(document).ready(function()
{
	var timer = null;

	$(document).ajaxStart( function()
	{
		timer = window.setTimeout( function()
		{
			$('#ind').css( { visibility: 'visible' } )
		}, 500);
	});
	$(document).ajaxStop( function()
	{
	        if (timer)
        	{
        		window.clearTimeout(timer);
	        	timer = null;
		}
		$('#ind').css( { visibility: "hidden" } );
	});
});
