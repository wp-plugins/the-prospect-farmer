/*
 * Shortcoder TinyMCE plugin 
 * http://www.theprospectfarmer.com
 * v1.0
*/

// For adding button in the visual editing toolbox
(function() {

	tinymce.create('tinymce.plugins.TPFButton', {
	
		init : function(ed, url) {	
			ed.addButton('tpfbutton', {
				title : 'Insert a The Prospect Farmer Form',
				image : url + '/icon.png',
				onclick : function() {
					if( typeof tpf_show_insert !== 'undefined' ) tpf_show_insert();
        }
			});	
		},
		
		getInfo : function() {
			return {
				longname : 'The Prospect Farmer Form',
				author : 'The Prospect Farmer',
				authorurl : 'http://www.theprospectfarmer.com/',
				infourl : 'http://www.theprospectfarmer.com/',
				version : '1.0'
			};
		}

	});
	
	tinymce.PluginManager.add('tpfbutton', tinymce.plugins.TPFButton);
	
})();