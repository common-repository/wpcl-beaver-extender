const path = require('path');
module.exports = {
	entry: {
		'jquery.betabs': './src/scripts/jquery.betabs.js',
		'jquery.bemaps': './src/scripts/jquery.bemaps.js',
		'ui-field-scss': './src/scripts/ui-field-scss.js',
	},
	output: {
		filename: '[name].min.js',
		path: path.resolve( __dirname, 'js' )
	},
	mode: "production",
	module : {
		rules : [
			{
				test: /.js$/,
				exclude: /(node_modules)/,
				use : {
					loader : 'babel-loader',
					options: {
						presets: [ "@wordpress/babel-preset-default" ]
					}
				}
			}
		]
	}
};