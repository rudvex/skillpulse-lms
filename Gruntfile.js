module.exports = function (grunt) {
    // Load the required plugin
    grunt.loadNpmTasks('grunt-replace');
    grunt.loadNpmTasks('grunt-wp-i18n');

    // Read the package.json to get the version
    const pkg = grunt.file.readJSON('package.json');

    grunt.initConfig({
        // Replace task configuration
        replace: {
            replace_php_version: {
                options: {
                    patterns: [
                        {
                            match: /\[SPLMS_VERSION\]/g, // Match with Plugin version.
                            replacement: pkg.SPLMSVersion, // Replace with the value from package.json.
                        },
                    ],
                },
                files: [
                    {
                        expand: true,
                        src: ['**/*.php','**/*.js','**/*.scss'], // Match all PHP files.
                    },
                ],
            },
        },
        makepot: {
            target: {
                options: {
                    domainPath: 'languages/',
                    potFilename: 'skillpulse-lms.pot',
                    type: 'wp-plugin',
                    exclude: ['node_modules/.*', 'vendor/.*'],
                    copyrightHolder: 'SkillPulseLMS',
                    processPot: function (pot, options) {
                        pot.headers['report-msgid-bugs-to'] = 'https://skillpulselms.com/contact';
                        pot.headers['language-team'] = 'SkillPulseLMS <team@skillpulselms.com>';
                        pot.headers['last-translator'] = 'SkillPulseLMS <team@skillpulselms.com>';
                        pot.headers['x-generator'] = 'grunt-wp-i18n';
                        pot.headers['project-id-version'] = 'SkillPulse LMS v' + pkg.SPLMSVersion;
                        return pot;
                    }
                }
            }
        }
    });

    // Register the default task
    grunt.registerTask('default', ['replace','makepot','replace-version']);
    grunt.registerTask('replace-version', ['replace:replace_php_version']);
};
