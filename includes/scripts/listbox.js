
function moveUp(listbox) {
	if(listbox.length < 1) return;
	
	var options = listbox.options;
	var highlightedIDs = new Array();
	var index = 0;
	
	for(var x = 1; x < listbox.length; x++) {
		if(options[x].selected) {
			highlightedIDs[index] = x;
			index++;
		}
	}

	for(var x = 0; x < highlightedIDs.length; x++) {
		refId = highlightedIDs[x];
		
		swapOptions(listbox,refId-1,refId);
		listbox[refId-1].selected = true;
	}
	
	listbox.focus ();
}

// [JAS]: Asc or Desc Bubble Sort on a Listbox
function sortList(listbox,is_desc) {
	var listlen = listbox.length;
	var options = listbox.options;
	
	if(listlen < 1) return;
	
	for(x=listlen-1;x>0;x--) {
		for(y=0;y<x;y++) {
			try {
				var swap = false;
				
				if(is_desc) { 
					if(options[y].text < options[y+1].text)
						swap = true;
				}
				else {
					if(options[y].text > options[y+1].text)
						swap = true;
				}
				
				if(swap) {
					swapOptions(listbox,y,y+1);
				}
				
			} catch(e) {
//				alert(e + " x: " + x + " y: " + y);
			}
		}	
	}
}

function moveDown(listbox) {
	if(listbox.length < 1) return;
	
	var options = listbox.options;
	var highlightedIDs = new Array();
	var index = 0;
	
	for(var x = 0; x < listbox.length-1; x++) {
		if(options[x].selected) {
			highlightedIDs[index] = x;
			index++;
		}
	}

	lastId = highlightedIDs.length - 1;
	
	for(var x = lastId; x >= 0; x--) {
		refId = highlightedIDs[x];
		
		swapOptions(listbox,refId,refId+1);
		listbox[refId+1].selected = true;
	}
	
	listbox.focus ();
}

function dropOptions(list) {
	
	if(!list.length) return;
	
	var options = list.options;
	var highlightedIDs = new Array();
	var index = 0;
	
	for(var x = 0; x < list.length; x++) {
		if(options[x].selected) {
			highlightedIDs[index] = x;
			index++;
		}
	}
	
	lastId = highlightedIDs.length - 1;
	
	for(var x = lastId; x >= 0; x--) {
		list[highlightedIDs[x]] = null;
	}
	
	list.focus ();
}

function swapOptions(list,opt1_idx,opt2_idx) {
	tmp = new Option(list[opt1_idx].text,list[opt1_idx].value);
	list[opt1_idx] = new Option(list[opt2_idx].text,list[opt2_idx].value);
	list[opt2_idx] = tmp;
}

function saveListState(list,field) {
	len = list.length;
	field.value = "";
	
	for(var x = 0; x < len; x++) {
		if (x == 0) {
			field.value = field.value + list[x].value;
		}
		else {
			field.value = field.value + "," + list[x].value;
		}
	}
}