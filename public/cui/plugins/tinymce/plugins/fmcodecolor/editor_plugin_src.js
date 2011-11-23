/**
 * $Id: editor_plugin_src.js $
 *
 * @author Mathias DESLOGES
 */

(function() {
	tinymce.create('tinymce.plugins.FmCodeColorPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertCode', function() {
				ed.windowManager.open({
					file   : url + '/insertcode.htm',
					width  : 530,
					height : 380,
					inline : 1
				}, {
					plugin_url : url
				});
			});
			
			// Register buttons
			ed.addButton('fmcodecolor', {title : 'Code Color', 
										 cmd   : 'mceInsertCode',
								         image : [url, '/img/embed.gif'].join('')});
		},

		getInfo : function() {
			return {
				longname : 'Insert Code',
				author : 'Mathias DESLOGES',
				authorurl : '',
				infourl : '',
				version : 0.1
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('fmcodecolor', tinymce.plugins.FmCodeColorPlugin);
})();