const {getDefaultConfig, mergeConfig} = require('@react-native/metro-config');

/**
 * Metro configuration
 * https://facebook.github.io/metro/docs/configuration
 */
const config = {
  resolver: {
    nodeModulesPaths: [
      __dirname + '/node_modules',
      __dirname + '/../../node_modules',
    ],
    alias: {
      '@components': './src/components',
      '@screens': './src/screens',
      '@services': './src/services',
      '@utils': './src/utils',
      '@assets': './src/assets',
      '@constants': './src/constants',
      '@types': './src/types',
    },
  },
  watchFolders: [
    __dirname,
    __dirname + '/../..',
  ],
  transformer: {
    getTransformOptions: async () => ({
      transform: {
        experimentalImportSupport: false,
        inlineRequires: true,
      },
    }),
  },
};

module.exports = mergeConfig(getDefaultConfig(__dirname), config);
