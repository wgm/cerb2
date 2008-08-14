{if $session->vars.login_handler->user_prefs->keyboard_shortcuts}

<script>
{literal}

if(window.Event) {
	document.captureEvents(Event.KEYDOWN);
}

var browser=navigator.userAgent.toLowerCase();
var is_ie=(browser.indexOf("msie")!=-1 && document.all);
var win=window;

function CreateKeyHandler(win) {
	if(is_ie) {
		return win.eval("blank=function(e) {return window.doShortcuts(window,e);}");
	}
	else {
		return function (e) {return window.doShortcuts(win,e);}
	}
}

	function GetKeyTarget(e) {
		var src=null;
		
		try {
			if(e.srcElement) src=e.srcElement;
			else if(e.target) src=e.target;
		}
		catch(e) {}
		
		return src;
	}

	function doShortcuts(win,e) {

	 	if(is_ie)
	 	try {
	 		if(e.srcElement);
	 	}
	 	catch(e) { return; }
		
		  if(window.Event) {
		  	if(e.altKey || e.metaKey || e.ctrlKey) {
		  		return;
		  	}
		    mykey = e.which;
		  }
		  else {
		  	if((event.modifiers & event.ALT_MASK) || (event.modifiers & event.CTRL_MASK)) {
				return;
			}
     		mykey = event.keyCode
		  }
		  
		  mykey = String.fromCharCode(mykey);
		  
		  src = GetKeyTarget(e);
		  
		for(var element=src;element!=null;element=element.parentNode) {
			var nodename=element.nodeName;
			if(nodename=="TEXTAREA"	
				|| (nodename=="SELECT")
				|| (nodename=="INPUT") //  && element.type != "checkbox"
				|| (nodename=="BUTTON")
				)
				{ return; }
		}
		
		// [JAS]: Header Shortcuts		
		switch(mykey) {
			case "0": // header
			case "1":
			case "2":
			case "3":
			case "4":
			case "5":
			case "6":
			case "7":
			case "8":
			case "9":
				if(document.getElementById) {
					document.getElementById("header_advanced").style.display = "block";
					document.getElementById("goto_input").focus();
				}
				break;
			case "a": // header
			case "A": // header
				toggleHeaderAdvanced();
				break;
			case "c": // header
			case "C": // header
				document.location.href=formatURL("clients.php?x=");
				break;
			case "g": // header
			case "G": // header
				if(document.getElementById) {
					document.getElementById("header_advanced").style.display = "block";
					if(is_ie) e.keyCode = null;
					document.getElementById("goto_input").focus();
				}
				break;
			case "h": // header
			case "H": // header
				document.location.href=formatURL("index.php?x=");
				break;
			case "k": // header
			case "K": // header
				document.location.href=formatURL("knowledgebase.php?x=");
				break;
			case "p": // header
			case "P": // header
				document.location.href=formatURL("my_cerberus.php?mode=preferences");
				break;
			case "r": // header
			case "R": // header
				document.location.href=formatURL("reports.php?x=");
				break;
			case "s": // header
			case "S": // header
				document.location.href=formatURL("ticket_list.php?x=");
				break;
			case "v": // header
			case "V": // header
				{/literal}
				{if !empty($session->vars.login_handler->ticket_id)}
					document.location.href="{$session->vars.login_handler->ticket_url}";
				{/if}
				{literal}
				break;
			case "x": // header
			case "X": // header
				document.location.href=formatURL("configuration.php?x=");
				break;
				
			{/literal}
			
			// [JAS]: Local Page Shortcuts
			{if $page == "index.php"}
				case "q":
				case "Q": // home
					toggleSystemStatus();
					break;
			{/if}
			
			{if $page == "display.php"}
				case "l": // display - latest
				case "L":
					{if empty($mode) }
						document.location.href="#latest";
					{else}
						document.location.href=formatURL("display.php?ticket={$ticket}") + "#latest";
					{/if}
					break;
				case "t": // display - top
				case "T":
					{if empty($mode) }
						document.location.href="#top";
					{else}
						document.location.href=formatURL("display.php?ticket={$ticket}") + "#top";
					{/if}
					break;
			{/if}
			
			{literal}
		}
	}
	
	function doShortcutsIE(win,e) {
		if(is_ie) {
			doShortcuts(win,e);
		}
	}
	
document.onkeydown = CreateKeyHandler(win);

{/literal}

</script>

{/if}