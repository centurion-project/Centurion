tinyMCEPopup.requireLangPack();

var InsertvideoDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(content) {
		var ed = tinyMCEPopup.editor, dom = ed.dom;
		tinyMCEPopup.execCommand('mceInsertContent', false, content);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(InsertvideoDialog.init, InsertvideoDialog);
