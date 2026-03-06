import React, {createContext, useContext} from 'react';
import {DefaultTheme, DarkTheme} from '@react-navigation/native';

export const CustomTheme = {
  light: {
    ...DefaultTheme,
    colors: {
      ...DefaultTheme.colors,
      primary: '#3498db',
      secondary: '#2c3e50',
      success: '#27ae60',
      warning: '#f39c12',
      danger: '#e74c3c',
      info: '#17a2b8',
      light: '#f8f9fa',
      dark: '#343a40',
      background: '#ffffff',
      surface: '#f8f9fa',
      text: '#212529',
      textSecondary: '#6c757d',
      border: '#dee2e6',
      shadow: 'rgba(0, 0, 0, 0.1)',
      card: '#ffffff',
      notification: '#e74c3c',
    },
    spacing: {
      xs: 4,
      sm: 8,
      md: 16,
      lg: 24,
      xl: 32,
      xxl: 48,
    },
    borderRadius: {
      sm: 4,
      md: 8,
      lg: 12,
      xl: 16,
      xxl: 24,
    },
    typography: {
      fontSize: {
        xs: 12,
        sm: 14,
        md: 16,
        lg: 18,
        xl: 20,
        xxl: 24,
        xxxl: 32,
      },
      fontWeight: {
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
      },
    },
  },
  dark: {
    ...DarkTheme,
    colors: {
      ...DarkTheme.colors,
      primary: '#3498db',
      secondary: '#2c3e50',
      success: '#27ae60',
      warning: '#f39c12',
      danger: '#e74c3c',
      info: '#17a2b8',
      light: '#f8f9fa',
      dark: '#343a40',
      background: '#1a1a1a',
      surface: '#2d2d2d',
      text: '#ffffff',
      textSecondary: '#b3b3b3',
      border: '#404040',
      shadow: 'rgba(0, 0, 0, 0.3)',
      card: '#2d2d2d',
      notification: '#e74c3c',
    },
  },
};

const ThemeContext = createContext({
  theme: CustomTheme.light,
  toggleTheme: () => {},
});

export const ThemeProvider = ({children}) => {
  const [isDarkMode, setIsDarkMode] = React.useState(false);

  const theme = isDarkMode ? CustomTheme.dark : CustomTheme.light;

  const toggleTheme = () => {
    setIsDarkMode(!isDarkMode);
  };

  return (
    <ThemeContext.Provider value={{theme, toggleTheme}}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

export default CustomTheme;
