import React from 'react';
import {NavigationContainer} from '@react-navigation/native';
import {createStackNavigator} from '@react-navigation/stack';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {Provider} from 'react-redux';
import {PersistGate} from 'redux-persist/integration/react';
import {store, persistor} from './src/store';
import {ThemeProvider} from './src/theme';
import SplashScreen from './src/screens/SplashScreen';
import LoginScreen from './src/screens/auth/LoginScreen';
import RegisterScreen from './src/screens/auth/RegisterScreen';
import HomeScreen from './src/screens/main/HomeScreen';
import PropertiesScreen from './src/screens/main/PropertiesScreen';
import PropertyDetailScreen from './src/screens/main/PropertyDetailScreen';
import FavoritesScreen from './src/screens/main/FavoritesScreen';
import ProfileScreen from './src/screens/main/ProfileScreen';
import BookingScreen from './src/screens/booking/BookingScreen';
import CommissionScreen from './src/screens/commission/CommissionScreen';
import AgentDashboardScreen from './src/screens/agent/AgentDashboardScreen';
import AdminPanelScreen from './src/screens/admin/AdminPanelScreen';
import {AuthProvider} from './src/context/AuthContext';
import {NetworkProvider} from './src/context/NetworkContext';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

const MainTabNavigator = () => {
  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        tabBarIcon: ({focused, color, size}) => {
          // Icon logic here
          return null;
        },
        tabBarActiveTintColor: '#3498db',
        tabBarInactiveTintColor: 'gray',
        headerShown: false,
      })}>
      <Tab.Screen name="Home" component={HomeScreen} />
      <Tab.Screen name="Properties" component={PropertiesScreen} />
      <Tab.Screen name="Favorites" component={FavoritesScreen} />
      <Tab.Screen name="Profile" component={ProfileScreen} />
    </Tab.Navigator>
  );
};

const AuthNavigator = () => {
  return (
    <Stack.Navigator screenOptions={{headerShown: false}}>
      <Stack.Screen name="Login" component={LoginScreen} />
      <Stack.Screen name="Register" component={RegisterScreen} />
    </Stack.Navigator>
  );
};

const MainNavigator = () => {
  return (
    <Stack.Navigator screenOptions={{headerShown: false}}>
      <Stack.Screen name="MainTabs" component={MainTabNavigator} />
      <Stack.Screen name="PropertyDetail" component={PropertyDetailScreen} />
      <Stack.Screen name="Booking" component={BookingScreen} />
      <Stack.Screen name="Commission" component={CommissionScreen} />
      <Stack.Screen name="AgentDashboard" component={AgentDashboardScreen} />
      <Stack.Screen name="AdminPanel" component={AdminPanelScreen} />
    </Stack.Navigator>
  );
};

const App = () => {
  return (
    <Provider store={store}>
      <PersistGate loading={null} persistor={persistor}>
        <ThemeProvider>
          <AuthProvider>
            <NetworkProvider>
              <NavigationContainer>
                <Stack.Navigator screenOptions={{headerShown: false}}>
                  <Stack.Screen name="Splash" component={SplashScreen} />
                  <Stack.Screen name="Auth" component={AuthNavigator} />
                  <Stack.Screen name="Main" component={MainNavigator} />
                </Stack.Navigator>
              </NavigationContainer>
            </NetworkProvider>
          </AuthProvider>
        </ThemeProvider>
      </PersistGate>
    </Provider>
  );
};

export default App;
