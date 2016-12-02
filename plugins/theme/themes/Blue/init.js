plugin.loadCSS("jquery.safari-checkbox");

plugin.correctCheckboxes = function()
{
	$('input:checkbox:not([safari])').checkbox();
	$('input[safari]:checkbox').checkbox();
	$('input:radio').checkbox();
}

plugin.showShadow = function(id,callback)
{
	var p = $("#"+id);
//	p.css("z-index", p.css("z-index")+1);
	var shadow = $("#"+id+"-shadow")
	shadow.find(".xstc,.xsmc,.xsbc").width(p.width()-4);
	shadow.find(".xsc").height(p.height()-12);
	shadow.width( p.width()+8 ).height( p.height() ).css({ left: p.offset().left-4, top: p.offset().top+4 }).show();
	if (callback)
		callback();
}

plugin.oldAllDone = plugin.allDone;
plugin.allDone = function()
{
	plugin.oldAllDone.call(this);
	injectScript(plugin.path+"jquery.checkbox.js", function()
	{
		plugin.correctCheckboxes();

		if (thePlugins.isInstalled('rss'))
		{
			plugin.loadFilters = theWebUI.loadFilters;
			theWebUI.loadFilters = function( flt, additions )
			{
				plugin.loadFilters.call(theWebUI,flt, additions);
				plugin.correctCheckboxes();
			}

			plugin.addNewFilter = theWebUI.addNewFilter;
			theWebUI.addNewFilter = function()
			{
				plugin.addNewFilter.call(theWebUI);
				plugin.correctCheckboxes();
			}

			plugin.fillRSSGroups = theWebUI.fillRSSGroups;
			theWebUI.fillRSSGroups = function()
			{
				plugin.fillRSSGroups.call(theWebUI);
				plugin.correctCheckboxes();
			}

			plugin.trafDraw = theWebUI.trafGraph.draw;
			theWebUI.trafGraph.draw = function()
			{
				plugin.trafDraw.call(this);
				plugin.correctCheckboxes();
			}
		}
		if (thePlugins.isInstalled('rssurlrewrite'))
		{
			plugin.loadRules = theWebUI.loadRules;
			theWebUI.loadRules = function( rle )
			{
				plugin.loadRules.call(theWebUI,rle);
				plugin.correctCheckboxes();
			}
		}
	});


	$('input[type="text"],input[type="password"],input[type="file"],select,textarea').each( function()
	{
		$(this).focus( function()
		{
			$(this).css( { "border-color": "#7eadd9" } );
		});
		$(this).blur( function()
		{
			$(this).css( { "border-color": "#b5b8c8" } );
		});
	});

	plugin.show = theDialogManager.show;
	theDialogManager.show = function( id, callback )
	{
		if (this.divider && !$('#'+id).data("modal"))
			plugin.show.call(theDialogManager,id, function()
			{
				plugin.showShadow(id,callback);
			});
		else
		{
			plugin.show.call(theDialogManager,id);
			plugin.showShadow(id);
		}
	}

	plugin.hide = theDialogManager.hide;
	theDialogManager.hide = function( id, callback )
	{
		$("#"+id+"-shadow").hide();
		plugin.hide.call(theDialogManager,id);
	}

	plugin.bringToTop = theDialogManager.bringToTop;
	theDialogManager.bringToTop = function( id )
	{
		plugin.bringToTop.call(theDialogManager,id);
		var p = $("#"+id);
		var shadow = $("#"+id+"-shadow")
		if (p.length && shadow.length)
		{
			shadow.css("z-index",theDialogManager.maxZ);
			p.css("z-index",++theDialogManager.maxZ);
		}
	}

}
