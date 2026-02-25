const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve( process.cwd(), 'src', 'index.js' ),
	},
	output: {
		path: path.resolve( process.cwd(), 'build' ),
		filename: '[name].js',
	},
	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},
};
