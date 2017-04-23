var del          = require( 'del' );
var gulp         = require( 'gulp' );
var autoprefixer = require( 'gulp-autoprefixer' );
var concat       = require( 'gulp-concat' );
var jshint       = require( 'gulp-jshint' );
var livereload   = require( 'gulp-livereload' );
var notify       = require( 'gulp-notify' );
var postcss      = require( 'gulp-postcss' );
var sass         = require( 'gulp-sass' );
var sourcemaps   = require( 'gulp-sourcemaps' );
var uglify       = require( 'gulp-uglify' );
var watch        = require( 'gulp-watch' );
var zip          = require( 'gulp-zip' );

function logError( error ){
	console.log( error.toString() );
	this.emit( 'end' );
}

gulp.task( 'assets', function(){ 
	return del( [ 'dist/assets/**/*' ] ).then(
		gulp.src( 'src/assets/**' ) 
        .pipe( gulp.dest( 'dist/assets' ) )
    	.pipe( livereload() )
    );
} );

gulp.task( 'build-plugin-zip', function(){
	return gulp.src( [
			'./**',
			'!node_modules/**',
			'!node_modules',
			'!src/**',
			'!src',
			'!.bowerrc',
			'!.gitignore',
			'!bower.json',
			'!debug.log',
			'!gulpfile.js',
			'!package.json'
		])
		.pipe( zip( 'flexmls-idx.zip' ) )
		.pipe( gulp.dest( './' ) );
} );

gulp.task( 'font-awesome', function(){ 
	return gulp.src( 'bower_components/font-awesome/fonts/**.*' ) 
        .pipe( gulp.dest( 'wp-content/themes/makespace-child/fonts' ) ); 
} );

gulp.task( 'lint', function(){
	return gulp.src( [
			'src/js/**/*.js',
			'src/js/*.js'
		] )
		.pipe( jshint( {
			multistr: true
		} ) )
		.pipe( notify( function( file ){
			if( file.jshint.success ){
				return false;
			}
			var errors = file.jshint.results.map( function( data ){
				if( data.error ){
					return 'Line ' + data.error.line + ': ' + data.error.reason;
				}
			} ).join( '\n' );
			return '\n-----------------\n' + file.relative + ' (' + file.jshint.results.length + ' errors)\n-----------------\n' + errors + '\n';
		} ) );
} );

gulp.task( 'php', function(){
	return gulp.src( [
			'*.php',
			'**/*.php'
		] )
		.pipe( livereload() );
} );

gulp.task( 'sass', function(){
	return gulp.src( 'src/scss/style-public.scss' )
		.pipe( sass( {
			outputStyle: 'compressed'
		} ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( postcss( [ autoprefixer ] ) )
		.pipe( gulp.dest( './dist/' ) )
		.pipe( livereload() );
} );

gulp.task( 'sass-admin', function(){
	return gulp.src( 'src/scss/style-admin.scss' )
		.pipe( sass( {
			outputStyle: 'compressed'
		} ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( postcss( [ autoprefixer ] ) )
		.pipe( gulp.dest( './dist/' ) )
		.pipe( livereload() );
} );

gulp.task( 'scripts', [ 'lint' ], function(){
	return gulp.src( [
			//'src/bower_components/slick-carousel/slick/slick.js',
			'src/js/optimized-events.js',
			'src/js/public/*.js'
		] )
		.pipe( concat( 'dist/scripts-public.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( concat( 'dist/scripts-public.min.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( uglify().on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( livereload() );
} );

gulp.task( 'scripts-admin', [ 'lint' ], function(){
	return gulp.src( [
			'src/bower_components/select2/dist/js/select2.full.js',
			'src/js/optimized-events.js',
			'src/js/admin/*.js'
		] )
		.pipe( concat( 'dist/scripts-admin.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( uglify().on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( concat( 'dist/scripts-admin.min.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( livereload() );
} );

gulp.task( 'scripts-tinymce', [ 'lint' ], function(){
	return gulp.src( [
			'src/js/tinymce/*.js'
		] )
		.pipe( concat( 'dist/scripts-tinymce.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( uglify().on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( concat( 'dist/scripts-tinymce.min.js' ).on( 'error', notify.onError( 'Error: <%= error.message %>' ) ) )
		.pipe( gulp.dest( '' ) )
		.pipe( livereload() );
} );

gulp.task( 'watch', function(){
	livereload.listen();
	gulp.watch( [ '*.php', '**/*.php' ], [ 'php' ] );
	gulp.watch( 'src/assets/**', [ 'assets' ] );
	gulp.watch( 'src/js/**', [ 'lint', 'scripts', 'scripts-admin', 'scripts-tinymce' ] );
	gulp.watch( [ 'src/scss/**', 'src/scss/**/*.scss' ], [ 'sass', 'sass-admin' ] );
} );

gulp.task( 'default', [
	'assets', 'sass', 'sass-admin', 'lint', 'scripts', 'scripts-admin', 'scripts-tinymce', 'watch'
] );

gulp.task( 'build', [ 'build-plugin-zip' ] );