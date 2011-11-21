tinyMCEPopup.requireLangPack();

var InsertcodeDialog = {
	init : function(ed) {
		tinyMCEPopup.resizeToInnerSize();
	},

	insert : function(content, language) {
		var ed = tinyMCEPopup.editor, dom = ed.dom;

		tinyMCEPopup.execCommand('mceInsertContent', false, '<pre><code class="' + language + '">' + content + '</code></pre>');

		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(InsertcodeDialog.init, InsertcodeDialog);
