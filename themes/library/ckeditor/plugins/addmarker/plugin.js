CKEDITOR.plugins.add( 'addmarker',
{
	init : function( editor )
	{
		var buttonsConfig = editor.config.addmarker;
		if (!buttonsConfig)
			return;

		function createCommand( definition )
		{
			return {
				exec: function( editor ) {
					editor.insertHtml( definition.html );
				}
			};
		}

		// Create the command for each button
		for(var i=0; i<buttonsConfig.length; i++)
		{
			var button = buttonsConfig[ i ];
			var commandName = button.name;
			editor.addCommand( commandName, createCommand(button, editor) );

			editor.ui.addButton( commandName,
			{
				label : button.title,
				command : commandName,
				icon : this.path + button.icon
			});
		}
	} //Init

} );

CKEDITOR.config.addmarker =  [
	{
		name:'button1',
		icon:'icon1.png',
		html:'[CITY]',
		title:'City Marker'
	},
	{
		name:'button2',
		icon:'icon2.png',
		html:'[ZONE]',
		title:'Zone Marker'
	},
	{
		name:'button3',
		icon:'icon3.png',
		html:'[REGION]',
		title:'Region Marker'
	}
];