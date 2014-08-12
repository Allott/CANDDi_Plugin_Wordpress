module.exports = function (grunt) {

    var strArchiveName = 'CANDDi_Wordpress.zip';

    grunt.initConfig({
        clean : {
            package: ['build/'+strArchiveName]
        },
        compress : {
            wordpress : {
                options : {
                    pretty: true,
                    archive : 'build/'+strArchiveName
                },
                files : [
                    {
                        expand: true,
                        cwd : 'src/',
                        src : [
                            '**/*'
                        ],
                        dest : '/',
                        dot: true
                    }
                ]
            }
        },
        aws: grunt.file.readJSON('local/_grunt-aws.json'),
        s3: {
            options: {
                key: '<%= aws.key %>',
                secret: '<%= aws.secret %>',
                bucket: 'plugins.canddi.com',
                region: 'eu-west-1',
            },
            live: {
                upload: [{
                    src: 'build/'+strArchiveName,
                    dest: strArchiveName
                }]
            }
        },
    });

    require('matchdep').filterDev(['grunt-*']).forEach(grunt.loadNpmTasks);

    grunt.registerTask('build',     ['clean:package', 'compress:wordpress']);
    grunt.registerTask('deploy',    ['build', 's3']);

    grunt.registerTask('default',   ['build']);
};
