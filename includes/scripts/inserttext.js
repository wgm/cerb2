<!--

function storeCaret (input)
{
  if (input.createTextRange)
     input.caretPos = document.selection.createRange().duplicate();
}

function setCaretToPos (input, pos) {
    input.setSelectionRange(pos, pos);
}

function insertText (input, textString) {
  if (input.setSelectionRange) {
    var insertionStart = input.selectionStart;
    input.value = input.value.substring(0, insertionStart)
                  + textString
                  + input.value.substring(insertionStart);
    setCaretToPos(input, insertionStart + textString.length);
  }
  else if (input.createTextRange && input.caretPos) {
    var caretPos = input.caretPos;
    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? textString + ' ' : textString;
  }
  else
    input.value = '' + input.value + textString;
}

//-->
