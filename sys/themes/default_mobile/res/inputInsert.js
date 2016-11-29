
(function (window) {
    "use strict";

    function InputInsert(node, Open, Close, CursorEnd) {
        node.focus();
        if (window.attachEvent && navigator.userAgent.indexOf('Opera') === -1) { // IE
            var s = node.sel;
            if (s) {
                var l = s.text.length;
                s.text = Open + s.text + Close;
                s.moveEnd("character", -Close.length);
                s.moveStart("character", -l);
                s.select();
            }
        } else {
            var ss = node.scrollTop;
            var sel1 = node.value.substr(0, node.selectionStart);
            var sel2 = node.value.substr(node.selectionEnd);
            var sel = node.value.substr(node.selectionStart, node.selectionEnd - node.selectionStart);

            node.value = sel1 + Open + sel + Close + sel2;
            if (CursorEnd) {
                node.selectionStart = sel1.length + Open.length + sel.length + Close.length;
                node.selectionEnd = node.selectionStart;
            } else {
                node.selectionStart = sel1.length + Open.length;
                node.selectionEnd = node.selectionStart + sel.length;
            }
            node.scrollTop = ss;
        }
        return false;
    }

    window.InputInsert = InputInsert;
})(window);