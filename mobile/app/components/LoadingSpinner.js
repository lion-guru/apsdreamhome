import React from 'react';
import { View, ActivityIndicator, StyleSheet } from 'react-native';

const LoadingSpinner = ({ size = 'large', color = '#3498db', style }) => {
  return (
    <View style={[styles.container, style]}>
      <ActivityIndicator size={size} color={color} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default LoadingSpinner;
