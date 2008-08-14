      
// Visitor Javascript     
// You shouldn't edit anything in here unless you know what your doing 
// Any changes could easily break the visitor side functionality
      
	function trimString (s) {
		while (s.substring(0,1) == ' ') {
			s = s.substring(1,s.length);
		}
		while (s.substring(s.length-1,s.length) == ' ') {
			s = s.substring(0,s.length-1);
		}
		return s;
	}
	
	function getCookies() {
		// [JAS]: Set up the scope for reading back in the cookies
		tempArray = document.cookie.split(';');
		cookieArray = new Array();
		x = 0;
		
		for (idx in tempArray) {
	  		cookieArray[x] = tempArray[idx].split('=');
	  		cookieArray[x][0] = trimString(cookieArray[x][0]);
	  		x++;
	  	}
	
		return cookieArray;
	}
	
	function getCookieVal(cookies,findCookieName) {
		for (idx in cookies) {
			cookieName = cookies[idx][0];
			if(cookieName == findCookieName) {
				return cookies[idx][1];
			}
		}
		
		return false;
	}
	
	function getCerPacketData(strPacket) {
		tempArray = strPacket.split(';');
		packetArray = new Array();
		x = 0;
		
		for (idx in tempArray) {
			packetArray[x] = tempArray[x].split('=');
			packetArray[x][0] = trimString(packetArray[x][0]);
			x++;
		}
		
		return packetArray;
	}
	
	function getCerPacketFlag(packetFlags,findPacketFlag) {
		for (idx in packetFlags) {
			flagName = packetFlags[idx][0];
			if(flagName == findPacketFlag) {
				return packetFlags[idx][1];
			}
		}
		
		return false;
	}
	
	function chatInviteAccept() {
		var voiderThread = new Image();
		voiderThread.src = "%%CHAT_SERVER_URL%%?channel=chat&module=invite&command=accept_invite&chatVisitor=" + chatVisitor;
		hideInviteWindow();
		cer_launchChat();
	}
	
	function chatInviteReject() {
		var voiderThread = new Image();
		voiderThread.src = "%%CHAT_SERVER_URL%%?channel=chat&module=invite&command=reject_invite&chatVisitor=" + chatVisitor;
		hideInviteWindow();
	}

	function hideInviteWindow() {
		try {
			inviteWindow = document.getElementById("chatWdw");
			inviteWindow.style.position = "absolute";
			inviteWindow.style.top = "-500px";
			inviteWindow.style.left = "-500px";
			inviteWindow.style.visibility = "hidden";
		}
		catch(e) {
			// alert(e);
		}
	}
	
	function showInviteWindow() {
		
		try {
			
			inviteWindow = document.getElementById("chatWdw");
			
			if(undefined == inviteWindow) {
				var tmpInviteWdw = document.createElement("DIV");
				tmpInviteWdw.id = "chatWdw";
				document.body.appendChild(tmpInviteWdw);
				
				inviteWindow = document.getElementById("chatWdw");
			}
			
			inviteWindow.innerHTML = "" + 
			"<iframe style='border: 1;' frameborder='1' src='%%CHAT_SERVER_URL%%?channel=chat&module=invite&command=get_invite_msg&chatVisitor=" + chatVisitor + "' width='300' height='100' border='0'></iframe>" +
			"<br>" +
			"<table width='300'><tr>"+
				"<td align='left'><input type='button' style='background-color: #56D919; color: #FFFFFF; font-weight: bold;' value='Chat!' onclick='javascript:top.chatInviteAccept();'></td>"+
				"<td align='right'><input type='button' style='background-color: #E32F2F; color: #FFFFFF; font-weight: bold;' value='No thanks!' onclick='javascript:top.chatInviteReject();'></td>"+
			"</tr></table>";
			
			positionInviteWindow();
			
		} catch(exception) {
			//alert(exception);
		}
	}
	
	function positionInviteWindow() {
	
		if(document.getElementById) {
			inviteWindow = document.getElementById("chatWdw");
		}
		else
			return;
		
		if(null == inviteWindow)
			return;
			
		if(inviteWindow.style.visibility == "hidden")
			return;
	
		// Konq
		if (window && window.innerHeight && document.body && document.body.scrollTop != undefined) {
			xPos = window.innerWidth;
			yPos = window.innerHeight;
			xPos = (xPos - 250) / 2;
			yPos = (yPos - 150) / 2;
			xPos += document.body.scrollLeft;
			yPos += document.body.scrollTop;
		}
		// IE + Firefox
		else if(document.body && document.body.clientWidth) {
			xPos = document.body.clientWidth;
			yPos = document.body.clientHeight;
			xPos = (xPos - 250) / 2;
			yPos = (yPos - 150) / 2;
			xPos += document.body.scrollLeft;
			yPos += document.body.scrollTop;
		}
		else if(document.documentElement && document.documentElement.clientWidth) {
			xPos = document.documentElement.clientWidth;
			yPos = document.documentElement.clientHeight;
			xPos = (xPos - 250) / 2;
			yPos = (yPos - 150) / 2;
			xPos += document.documentElement.scrollLeft;
			yPos += document.documentElement.scrollTop;
		}
		else {
			xPos = 100;
			yPos = 100;
		}

		inviteWindow.style.position = "absolute";
		inviteWindow.style.top = yPos + "px";
		inviteWindow.style.left = xPos + "px";
		inviteWindow.style.visibility = "visible";
		
		setTimeout('positionInviteWindow()', 100);
	}
	
	function uniqueval()
	{
		var date = new Date();
		return date.getTime();
	}
	
	function heartEventHandler() {
		//alert(eventPacket.width);
		
		if(eventPacket.width == 2) {
			if(!cerInviteActive) {
				showInviteWindow();
				cerInviteActive = true;
			}
		}
	}
	
	// [JAS]: Keep a heartbeat going so we can detect visitors who have idled out from
	//	those who are simply reading a page a long time.
	function runChatHeartbeat(is_heartbeat) {
	
		// [JAS]: Thump, thump.

		var u = uniqueval();
		var url = "%%CHAT_SERVER_URL%%?channel=chat&module=heartbeat&command=update_heartbeat&first=" + is_heartbeat + "&chatVisitor=" + chatVisitor + "&location=" + escape(document.location) + "&referrer=" + escape(document.referrer) + "&uz=" + u;
		
		/*
		 * [JAS]: Update our status.
		 */
		eventPacket = new Image;
		eventPacket.src = url;
		eventPacket.onload = heartEventHandler;
		
		num_heartbeats++;
		
		// [JAS]: Only keep the heartbeat alive for 15 mins on a single page
		if(num_heartbeats <= 60) {
			setTimeout('runChatHeartbeat(1)',(num_heartbeats < 4) ? 5000 : 15000);
		}
	}
	
	function cer_launchChat() {
		var u = uniqueval();
		window.open("%%CHAT_SERVER_URL%%?channel=chat&module=window&command=getwindow_prechat&visitor=" + chatVisitor + "&u=" + u,"cer_ChatWnd","width=400,height=500,resize=0,scrollbars=0,status=0,location=0,menubar=0,toolbar=0");
	}
	
	var num_heartbeats = 0;
	var cookies = new Array();
	var chatVisitor = null;
	var cerData = null;
	var cerDataPacketString = null;
	var cerDataPacket = new Array();
	var cerReload = null;
	var cerInviteActive = false;
	var eventPacket = new Image;
	
	cookies = getCookies();
	
	var todayDate = new Date;
	todayDate.setDate(todayDate.getDate() + 7);
	
	if(chatVisitor = getCookieVal(cookies,"chatVisitor")) {
		// alert("We are a returning visitor with GUID: " + chatVisitor);
	}
	else {
		chatVisitor = escape('%%GUID%%');
		document.cookie = 'chatVisitor=' + chatVisitor + '; expires=' + todayDate.toGMTString() + ';';
	}
	
	setTimeout('runChatHeartbeat(0);',1000);