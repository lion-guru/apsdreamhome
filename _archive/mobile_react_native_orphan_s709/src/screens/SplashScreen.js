import React, {useEffect} from 'react';
import {
  View,
  Text,
  StyleSheet,
  StatusBar,
  Dimensions,
  Animated,
} from 'react-native';
import {useTheme} from '../theme';
import {useNavigation} from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const {width, height} = Dimensions.get('window');

const SplashScreen = () => {
  const {theme} = useTheme();
  const navigation = useNavigation();
  const fadeAnim = new Animated.Value(0);
  const scaleAnim = new Animated.Value(0.3);

  useEffect(() => {
    // Animate splash screen
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 1000,
        useNativeDriver: true,
      }),
      Animated.spring(scaleAnim, {
        toValue: 1,
        tension: 10,
        friction: 3,
        useNativeDriver: true,
      }),
    ]).start();

    // Check authentication status
    const checkAuthStatus = async () => {
      try {
        const authToken = await AsyncStorage.getItem('auth_token');
        const userData = await AsyncStorage.getItem('user_data');

        setTimeout(() => {
          if (authToken && userData) {
            // User is logged in, navigate to main app
            navigation.replace('Main');
          } else {
            // User is not logged in, navigate to auth
            navigation.replace('Auth');
          }
        }, 2500);
      } catch (error) {
        console.error('Error checking auth status:', error);
        navigation.replace('Auth');
      }
    };

    checkAuthStatus();
  }, [navigation]);

  const styles = StyleSheet.create({
    container: {
      flex: 1,
      backgroundColor: theme.colors.primary,
      justifyContent: 'center',
      alignItems: 'center',
    },
    logoContainer: {
      alignItems: 'center',
      justifyContent: 'center',
    },
    logoText: {
      fontSize: theme.typography.fontSize.xxxl,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.light,
      textAlign: 'center',
    },
    tagline: {
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.light,
      marginTop: theme.spacing.sm,
      opacity: 0.9,
    },
    loadingContainer: {
      position: 'absolute',
      bottom: height * 0.1,
      alignItems: 'center',
    },
    loadingText: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.light,
      marginTop: theme.spacing.sm,
      opacity: 0.8,
    },
  });

  return (
    <>
      <StatusBar backgroundColor={theme.colors.primary} barStyle="light-content" />
      <View style={styles.container}>
        <Animated.View
          style={[
            styles.logoContainer,
            {
              opacity: fadeAnim,
              transform: [{scale: scaleAnim}],
            },
          ]}>
          <Text style={styles.logoText}>🏠</Text>
          <Text style={styles.logoText}>APS Dream Home</Text>
          <Text style={styles.tagline}>Your Dream Property Awaits</Text>
        </Animated.View>

        <Animated.View
          style={[
            styles.loadingContainer,
            {
              opacity: fadeAnim,
            },
          ]}>
          <Text style={styles.loadingText}>Loading...</Text>
        </Animated.View>
      </View>
    </>
  );
};

export default SplashScreen;
