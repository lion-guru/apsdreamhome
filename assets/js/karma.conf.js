// Karma configuration
module.exports = function (config) {
  config.set({
    basePath: '',
    frameworks: ['jasmine'],
    files: [
      { pattern: 'assets/**/*.js', watched: true },
      { pattern: 'src/**/*.js', watched: true },
      { pattern: 'test/**/*.spec.js', watched: true }
    ],
    exclude: [],
    preprocessors: {
      'assets/**/*.js': ['babel'],
      'src/**/*.js': ['babel'],
      'test/**/*.spec.js': ['babel']
    },
    babelPreprocessor: {
      options: {
        sourceMap: 'inline',
        presets: [
          [
            '@babel/preset-env',
            {
              targets: {
                browsers: ['last 2 versions', 'not dead']
              },
              useBuiltIns: false
            }
          ]
        ]
      }
    },
    reporters: ['spec', 'progress', 'kjhtml', 'coverage'],
    coverageReporter: {
      dir: 'coverage',
      reporters: [
        { type: 'html', subdir: 'html' },
        { type: 'text-summary' }
      ]
    },
    plugins: [
      'karma-jasmine',
      'karma-chrome-launcher',
      'karma-firefox-launcher',
      'karma-edge-launcher',
      'karma-jasmine-html-reporter',
      'karma-coverage',
      'karma-babel-preprocessor',
      'karma-spec-reporter'
    ],
    browsers: ['Edge'],
    singleRun: true,
    autoWatch: false,
    concurrency: Infinity,
    client: {
      clearContext: false // keep Jasmine Spec Runner output visible in browser
    }
  });
};
