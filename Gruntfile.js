'use strict';

module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({

		// Load grunt project configuration
		pkg: grunt.file.readJSON('package.json'),

		// LESS CSS compiler
		less: {
			develop: {
				options: {
					ieCompat: true
				},
				files: {
					'assets/css/admin.css': 'assets/src/less/admin.less'
				}
			},
			build: {
				options: {
					cleancss: true,
					ieCompat: true
				},
				files: {
					'assets/css/admin.min.css': 'assets/src/less/admin.less'
				}
			}
		},

		// JSHint
		jshint: {
			test: {
				src: 'assets/src/js/*.js'
			}
		},

		// Concatenate scripts
		concat: {
			build: {
				files: {
					'assets/js/admin.js': [
						'assets/src/js/submission-form.js',
						'assets/src/js/submission-form-*.js'
					]
				}
			}
		},

		// Minimize scripts
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			build: {
				files: {
					'assets/js/admin.min.js' : 'assets/js/admin.js'
				}
			}
		},

		// Auto-compile changes
		watch: {
			less: {
				files: ['assets/src/less/*.less'],
				tasks: ['less']
			},
			js: {
				files: ['assets/src/js/*.js'],
				tasks: ['jshint', 'concat', 'uglify']
			}
		}

	});

	// Load tasks
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default task(s).
	grunt.registerTask('default', ['watch']);

};
