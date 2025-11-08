import React, {useState} from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  ActivityIndicator,
} from 'react-native';
import {useTheme} from '../theme';
import {useNavigation} from '@react-navigation/native';
import {useDispatch} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiService from '../services/ApiService';
import {loginSuccess} from '../store/actions/authActions';

const LoginScreen = () => {
  const {theme} = useTheme();
  const navigation = useNavigation();
  const dispatch = useDispatch();

  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};

    if (!formData.email) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email';
    }

    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Password must be at least 6 characters';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleLogin = async () => {
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    try {
      const response = await apiService.login(formData.email, formData.password);

      if (response.success) {
        // Store authentication data
        await AsyncStorage.setItem('auth_token', response.data.token);
        await AsyncStorage.setItem('user_data', JSON.stringify(response.data.user));
        await AsyncStorage.setItem('refresh_token', response.data.refresh_token || '');

        // Update Redux state
        dispatch(loginSuccess(response.data.user, response.data.token));

        // Navigate to main app
        navigation.replace('Main');
      } else {
        Alert.alert('Login Failed', response.error || 'Invalid credentials');
      }
    } catch (error) {
      console.error('Login error:', error);
      Alert.alert(
        'Login Error',
        error.response?.data?.error || 'Unable to login. Please try again.',
      );
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field, value) => {
    setFormData(prev => ({
      ...prev,
      [field]: value,
    }));

    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({
        ...prev,
        [field]: '',
      }));
    }
  };

  const styles = StyleSheet.create({
    container: {
      flex: 1,
      backgroundColor: theme.colors.background,
    },
    scrollContainer: {
      flexGrow: 1,
      justifyContent: 'center',
      padding: theme.spacing.lg,
    },
    logoContainer: {
      alignItems: 'center',
      marginBottom: theme.spacing.xxl,
    },
    logoText: {
      fontSize: theme.typography.fontSize.xxxl,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.primary,
    },
    tagline: {
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.textSecondary,
      marginTop: theme.spacing.sm,
    },
    formContainer: {
      backgroundColor: theme.colors.card,
      borderRadius: theme.borderRadius.lg,
      padding: theme.spacing.xl,
      shadowColor: theme.colors.shadow,
      shadowOffset: {width: 0, height: 2},
      shadowOpacity: 0.1,
      shadowRadius: 8,
      elevation: 4,
    },
    inputContainer: {
      marginBottom: theme.spacing.md,
    },
    label: {
      fontSize: theme.typography.fontSize.sm,
      fontWeight: theme.typography.fontWeight.medium,
      color: theme.colors.text,
      marginBottom: theme.spacing.xs,
    },
    input: {
      borderWidth: 1,
      borderColor: errors.email || errors.password ? theme.colors.danger : theme.colors.border,
      borderRadius: theme.borderRadius.md,
      padding: theme.spacing.md,
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.text,
      backgroundColor: theme.colors.surface,
    },
    inputError: {
      borderColor: theme.colors.danger,
    },
    errorText: {
      fontSize: theme.typography.fontSize.xs,
      color: theme.colors.danger,
      marginTop: theme.spacing.xs,
    },
    passwordContainer: {
      flexDirection: 'row',
      alignItems: 'center',
      borderWidth: 1,
      borderColor: errors.password ? theme.colors.danger : theme.colors.border,
      borderRadius: theme.borderRadius.md,
      backgroundColor: theme.colors.surface,
    },
    passwordInput: {
      flex: 1,
      padding: theme.spacing.md,
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.text,
    },
    eyeButton: {
      padding: theme.spacing.md,
    },
    loginButton: {
      backgroundColor: theme.colors.primary,
      borderRadius: theme.borderRadius.md,
      padding: theme.spacing.md,
      alignItems: 'center',
      marginTop: theme.spacing.lg,
    },
    loginButtonText: {
      color: theme.colors.light,
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
    },
    registerContainer: {
      flexDirection: 'row',
      justifyContent: 'center',
      alignItems: 'center',
      marginTop: theme.spacing.lg,
    },
    registerText: {
      color: theme.colors.textSecondary,
      fontSize: theme.typography.fontSize.sm,
    },
    registerLink: {
      color: theme.colors.primary,
      fontSize: theme.typography.fontSize.sm,
      fontWeight: theme.typography.fontWeight.semibold,
    },
  });

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView contentContainerStyle={styles.scrollContainer}>
        <View style={styles.logoContainer}>
          <Text style={styles.logoText}>🏠</Text>
          <Text style={styles.logoText}>Welcome Back</Text>
          <Text style={styles.tagline}>Sign in to your account</Text>
        </View>

        <View style={styles.formContainer}>
          <View style={styles.inputContainer}>
            <Text style={styles.label}>Email Address</Text>
            <TextInput
              style={[styles.input, errors.email && styles.inputError]}
              value={formData.email}
              onChangeText={(value) => handleInputChange('email', value)}
              placeholder="Enter your email"
              placeholderTextColor={theme.colors.textSecondary}
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />
            {errors.email && <Text style={styles.errorText}>{errors.email}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <Text style={styles.label}>Password</Text>
            <View style={[styles.passwordContainer, errors.password && styles.inputError]}>
              <TextInput
                style={styles.passwordInput}
                value={formData.password}
                onChangeText={(value) => handleInputChange('password', value)}
                placeholder="Enter your password"
                placeholderTextColor={theme.colors.textSecondary}
                secureTextEntry={!showPassword}
                autoCapitalize="none"
                autoCorrect={false}
              />
              <TouchableOpacity
                style={styles.eyeButton}
                onPress={() => setShowPassword(!showPassword)}>
                <Icon
                  name={showPassword ? 'visibility-off' : 'visibility'}
                  size={20}
                  color={theme.colors.textSecondary}
                />
              </TouchableOpacity>
            </View>
            {errors.password && <Text style={styles.errorText}>{errors.password}</Text>}
          </View>

          <TouchableOpacity
            style={styles.loginButton}
            onPress={handleLogin}
            disabled={loading}>
            {loading ? (
              <ActivityIndicator color={theme.colors.light} />
            ) : (
              <Text style={styles.loginButtonText}>Sign In</Text>
            )}
          </TouchableOpacity>

          <View style={styles.registerContainer}>
            <Text style={styles.registerText}>Don't have an account? </Text>
            <TouchableOpacity onPress={() => navigation.navigate('Register')}>
              <Text style={styles.registerLink}>Sign Up</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
};

export default LoginScreen;
