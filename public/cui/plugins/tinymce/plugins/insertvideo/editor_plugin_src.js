/**
 * $Id: editor_plugin_src.js $
 *
 * @author Laurent Chenay
 */

(function() {
	tinymce.create('tinymce.plugins.InsertvideoPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertvideo', function() {
				ed.windowManager.open({
					file   : '/bnpp/admin-video/choose',
					width  : 550,
					height : 380,
					inline : 1
				}, {
					plugin_url : url
				});
			});
			// Register buttons
			ed.addButton('insertvideo', {title : 'Videos', 
										cmd   : 'mceInsertvideo',
								        image : [url, '/img/embed.gif'].join('')});
		},

		getInfo : function() {
			return {
				longname : 'Insert Video',
				author : 'Laurent CHENAY',
				authorurl : '',
				infourl : '',
				version : 0.1
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('insertvideo', tinymce.plugins.InsertvideoPlugin);
})();