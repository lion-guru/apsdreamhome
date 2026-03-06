import React, {useState} from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import {useTheme} from '../../theme';
import Icon from 'react-native-vector-icons/MaterialIcons';

const SearchBar = ({value, onChangeText, placeholder = 'Search...', onFilterPress}) => {
  const {theme} = useTheme();

  const styles = StyleSheet.create({
    container: {
      flexDirection: 'row',
      alignItems: 'center',
      backgroundColor: theme.colors.surface,
      marginHorizontal: theme.spacing.md,
      marginVertical: theme.spacing.sm,
      borderRadius: theme.borderRadius.lg,
      paddingHorizontal: theme.spacing.md,
      shadowColor: theme.colors.shadow,
      shadowOffset: {width: 0, height: 2},
      shadowOpacity: 0.1,
      shadowRadius: 4,
      elevation: 2,
    },
    searchInput: {
      flex: 1,
      paddingVertical: theme.spacing.md,
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.text,
    },
    filterButton: {
      padding: theme.spacing.sm,
      marginLeft: theme.spacing.sm,
    },
  });

  return (
    <View style={styles.container}>
      <Icon name="search" size={20} color={theme.colors.textSecondary} />
      <TextInput
        style={styles.searchInput}
        placeholder={placeholder}
        placeholderTextColor={theme.colors.textSecondary}
        value={value}
        onChangeText={onChangeText}
        returnKeyType="search"
      />
      {onFilterPress && (
        <TouchableOpacity style={styles.filterButton} onPress={onFilterPress}>
          <Icon name="filter-list" size={20} color={theme.colors.textSecondary} />
        </TouchableOpacity>
      )}
    </View>
  );
};

export default SearchBar;
