const { config } = require( 'grunt' );

module.exports = function ( grunt ) {
	'use strict';

	const pkg = grunt.file.readJSON( 'package.json' );
	const fileName =
		grunt.option( 'file' ) || 'astra-premium-sites-' + pkg.version + '.zip';

	// Project configuration
	grunt.initConfig( {
		rtlcss: {
			options: {
				// rtlcss options
				config: {
					preserveComments: true,
					greedy: true,
				},
				// generate source maps
				map: false,
			},
			dist: {
				files: [
					{
						expand: true,
						cwd: 'inc/assets/css/',
						src: [ '*.css', '!*-rtl.css' ],
						dest: 'inc/assets/css/',
						ext: '-rtl.css',
					},
				],
			},
		},

		addtextdomain: {
			options: {
				textdomain: 'astra-sites',
				updateDomains: true,
			},
			target: {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!vendor/**',
						'!php-tests/**',
						'!bin/**',
						'!admin/bsf-core/**',
						'!inc/importers/class-astra-widget-importer.php',
						'!inc/importers/wxr-importer/class-wp-importer-logger.php',
						'!inc/importers/wxr-importer/class-wxr-importer.php',
					],
				},
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'astra-sites.php',
					potFilename: 'astra-sites.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					type: 'wp-plugin',
					updateTimestamp: true,
				},
			},
		},

		copy: {
			main: {
				files: [
					{
						options: {
							mode: true,
						},
						src: [
							'**',
							'*.zip',
							'!node_modules/**',
							'!build/**',
							'!css/sourcemap/**',
							'!.git/**',
							'!bin/**',
							'!.gitlab-ci.yml',
							'!src/**',
							'!tests/**',
							'!phpunit.xml.dist',
							'!*.sh',
							'!*.map',
							'!Gruntfile.js',
							'!package.json',
							'!.gitignore',
							'!phpunit.xml',
							'!README.md',
							'!sass/**',
							'!codesniffer.ruleset.xml',
							'!vendor/**',
							'!wordpress/**',
							'!yarn.lock',
							'!webpack.config.js',
							'!composer.json',
							'!composer.lock',
							'!package-lock.json',
							'!phpcs.xml.dist',
							'!inc/assets/js/src/**',
							'!inc/scripts/**',
							'!inc/lib/bsf-quick-links/readme.md',
							'!inc/lib/intelligent-starter-templates/packages/**',
						],
						dest: 'astra-pro-sites/',
					},
				],
			},
			inc: {
				files: [
					{
						options: {
							mode: true,
						},
						src: [ 'inc/**', 'admin/bsf-analytics/**' ],
						dest: '../astra-sites/',
					},
				],
			},
			config: {
				files: [
					{
						options: {
							mode: true,
						},
						src: [
							'.distignore',
							'.editorconfig',
							'.eslintignore',
							'.eslintrc.js',
							'.gitignore',
							'.prettierignore',
							'.prettierrc.js',
							'composer.json',
							'package.json',
							'phpxs.xml',
							'phpstan.neon',
							'phpstan-baseline.neon',
							'webpack.config.js',
							'postcss.config.js',
							'tailwind.config.js',
						],
						dest: '../astra-sites/',
					},
				],
			},
		},

		compress: {
			main: {
				options: {
					archive: fileName,
					mode: 'zip',
				},
				files: [
					{
						src: [ './astra-pro-sites/**' ],
					},
				],
			},
		},

		clean: {
			main: [ 'astra-pro-sites' ],
			zip: [ '*.zip' ],
		},

		bumpup: {
			options: {
				updateProps: {
					pkg: 'package.json',
				},
			},
			file: 'package.json',
		},

		replace: {
			plugin_main: {
				src: [ 'astra-pro-sites.php' ],
				overwrite: true,
				replacements: [
					{
						from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Version: <%= pkg.version %>',
					},
				],
			},

			plugin_const: {
				src: [ 'astra-pro-sites.php', 'astra-sites.php' ],
				overwrite: true,
				replacements: [
					{
						from: /ASTRA_PRO_SITES_VER', '.*?'/g,
						to: "ASTRA_PRO_SITES_VER', '<%= pkg.version %>'",
					},
					{
						from: /ASTRA_SITES_VER', '.*?'/g,
						to: "ASTRA_SITES_VER', '<%= pkg.version %>'",
					},
				],
			},

			plugin_function_comment: {
				src: [
					'*.php',
					'**/*.php',
					'!node_modules/**',
					'!tests/**',
					'!php-tests/**',
					'!bin/**',
					'!admin/bsf-core/**',
				],
				overwrite: true,
				replacements: [
					{
						from: 'x.x.x',
						to: '<%=pkg.version %>',
					},
				],
			},
		},
	} );

	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-bumpup' );
	grunt.loadNpmTasks( 'grunt-text-replace' );

	// rtlcss, you will still need to install ruby and sass on your system manually to run this
	grunt.registerTask( 'rtl', [ 'rtlcss' ] );

	// Generate .pot file.
	grunt.registerTask( 'i18n', [ 'addtextdomain', 'makepot' ] );
	grunt.registerTask( 'textdomain', [ 'addtextdomain' ] );

	// Grunt release - Create installable package of the local files
	// use command `npm run release` instead of this.
	grunt.registerTask( 'release-deprecated', [
		'clean:zip',
		'copy',
		'compress',
	] );
	grunt.registerTask( 'release', [
		'clean:zip',
		'copy',
		'compress',
		'clean:main',
	] );
	grunt.registerTask( 'release-no-clean', [ 'copy', 'compress' ] );

	// Grunt copy-inc - Copy the files to Starter Template free plugin
	grunt.registerTask( 'copy-inc', [ 'copy:inc' ] );

	// Grunt copy-config - Copy the config files to Starter Template free plugin
	grunt.registerTask( 'copy-config', [ 'copy:config' ] );

	// Bump Version - `grunt version-bump --ver=<version-number>`
	grunt.registerTask( 'version-bump', function ( ver ) {
		let newVersion = grunt.option( 'ver' );

		if ( newVersion ) {
			newVersion = newVersion ? newVersion : 'patch';

			grunt.task.run( 'bumpup:' + newVersion );
			grunt.task.run( 'replace' );
		}
	} );

	grunt.util.linefeed = '\n';
};
