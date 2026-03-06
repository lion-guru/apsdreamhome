/**
 * APS Dream Home Mobile App
 * Entry point for React Native application
 */

import {AppRegistry} from 'react-native';
import App from './App';
import {name as appName} from './package.json';

// Register the app
AppRegistry.registerComponent(appName, () => App);

export default App;
