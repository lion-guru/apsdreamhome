module.exports = {
  presets: ['module:metro-react-native-babel-preset'],
  plugins: [
    [
      'module-resolver',
      {
        root: ['./src'],
        extensions: ['.ios.js', '.android.js', '.js', '.ts', '.tsx', '.json'],
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
    ],
    'react-native-reanimated/plugin', // This should be last
  ],
};
