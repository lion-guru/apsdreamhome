import React, {useState, useEffect} from 'react';
import {
  View,
  Text,
  StyleSheet,
  Modal,
  TouchableOpacity,
  TextInput,
  ScrollView,
  Switch,
} from 'react-native';
import {useTheme} from '../../theme';
import Icon from 'react-native-vector-icons/MaterialIcons';
import apiService from '../../services/ApiService';

const FilterModal = ({visible, filters, onClose, onApply, onClear}) => {
  const {theme} = useTheme();

  const [localFilters, setLocalFilters] = useState(filters);
  const [cities, setCities] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLocalFilters(filters);
  }, [filters]);

  useEffect(() => {
    if (visible) {
      loadCities();
    }
  }, [visible]);

  const loadCities = async () => {
    try {
      setLoading(true);
      const response = await apiService.getCities();
      if (response.success) {
        setCities(response.data);
      }
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (key, value) => {
    setLocalFilters(prev => ({
      ...prev,
      [key]: value,
    }));
  };

  const handleApply = () => {
    onApply(localFilters);
  };

  const handleClear = () => {
    const clearedFilters = {
      type: '',
      city: '',
      minPrice: '',
      maxPrice: '',
      bedrooms: '',
      bathrooms: '',
      status: 'available',
    };
    setLocalFilters(clearedFilters);
    onClear();
  };

  const propertyTypes = [
    {value: '', label: 'All Types'},
    {value: 'house', label: 'House'},
    {value: 'apartment', label: 'Apartment'},
    {value: 'commercial', label: 'Commercial'},
    {value: 'plot', label: 'Plot'},
  ];

  const bedroomOptions = [
    {value: '', label: 'Any'},
    {value: '1', label: '1+'},
    {value: '2', label: '2+'},
    {value: '3', label: '3+'},
    {value: '4', label: '4+'},
    {value: '5', label: '5+'},
  ];

  const bathroomOptions = bedroomOptions.slice(); // Same as bedrooms

  const statusOptions = [
    {value: 'available', label: 'Available'},
    {value: 'sold', label: 'Sold'},
    {value: 'rented', label: 'Rented'},
  ];

  const renderInputField = (label, key, placeholder, keyboardType = 'default') => (
    <View style={styles.inputContainer}>
      <Text style={styles.inputLabel}>{label}</Text>
      <TextInput
        style={styles.textInput}
        placeholder={placeholder}
        placeholderTextColor={theme.colors.textSecondary}
        value={localFilters[key]?.toString() || ''}
        onChangeText={(value) => handleFilterChange(key, value)}
        keyboardType={keyboardType}
      />
    </View>
  );

  const renderSelectField = (label, key, options) => (
    <View style={styles.inputContainer}>
      <Text style={styles.inputLabel}>{label}</Text>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.optionsContainer}>
        {options.map((option) => (
          <TouchableOpacity
            key={option.value}
            style={[
              styles.optionButton,
              localFilters[key] === option.value && styles.selectedOption,
            ]}
            onPress={() => handleFilterChange(key, option.value)}>
            <Text
              style={[
                styles.optionText,
                localFilters[key] === option.value && styles.selectedOptionText,
              ]}>
              {option.label}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>
    </View>
  );

  const styles = StyleSheet.create({
    modalOverlay: {
      flex: 1,
      backgroundColor: 'rgba(0, 0, 0, 0.5)',
      justifyContent: 'flex-end',
    },
    modalContent: {
      backgroundColor: theme.colors.background,
      borderTopLeftRadius: theme.borderRadius.xl,
      borderTopRightRadius: theme.borderRadius.xl,
      maxHeight: '80%',
    },
    modalHeader: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      padding: theme.spacing.lg,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
    },
    modalTitle: {
      fontSize: theme.typography.fontSize.xl,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.text,
    },
    closeButton: {
      padding: theme.spacing.sm,
    },
    scrollContent: {
      padding: theme.spacing.lg,
    },
    inputContainer: {
      marginBottom: theme.spacing.lg,
    },
    inputLabel: {
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
      color: theme.colors.text,
      marginBottom: theme.spacing.sm,
    },
    textInput: {
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderRadius: theme.borderRadius.md,
      padding: theme.spacing.md,
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.text,
      backgroundColor: theme.colors.surface,
    },
    optionsContainer: {
      marginBottom: theme.spacing.sm,
    },
    optionButton: {
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
      backgroundColor: theme.colors.surface,
      borderRadius: theme.borderRadius.md,
      marginRight: theme.spacing.sm,
      borderWidth: 1,
      borderColor: theme.colors.border,
    },
    selectedOption: {
      backgroundColor: theme.colors.primary,
      borderColor: theme.colors.primary,
    },
    optionText: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.textSecondary,
    },
    selectedOptionText: {
      color: theme.colors.light,
      fontWeight: theme.typography.fontWeight.semibold,
    },
    modalActions: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      padding: theme.spacing.lg,
      borderTopWidth: 1,
      borderTopColor: theme.colors.border,
    },
    clearButton: {
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      borderRadius: theme.borderRadius.md,
      borderWidth: 1,
      borderColor: theme.colors.danger,
    },
    clearButtonText: {
      color: theme.colors.danger,
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
    },
    applyButton: {
      backgroundColor: theme.colors.primary,
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      borderRadius: theme.borderRadius.md,
    },
    applyButtonText: {
      color: theme.colors.light,
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
    },
  });

  return (
    <Modal
      visible={visible}
      animationType="slide"
      presentationStyle="pageSheet"
      onRequestClose={onClose}>
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Filters</Text>
            <TouchableOpacity style={styles.closeButton} onPress={onClose}>
              <Icon name="close" size={24} color={theme.colors.textSecondary} />
            </TouchableOpacity>
          </View>

          <ScrollView style={styles.scrollContent} showsVerticalScrollIndicator={false}>
            {renderSelectField('Property Type', 'type', propertyTypes)}
            {renderSelectField('City', 'city', cities.map(city => ({value: city.cname, label: city.cname})))}
            {renderInputField('Min Price', 'minPrice', 'Enter minimum price', 'numeric')}
            {renderInputField('Max Price', 'maxPrice', 'Enter maximum price', 'numeric')}
            {renderSelectField('Bedrooms', 'bedrooms', bedroomOptions)}
            {renderSelectField('Bathrooms', 'bathrooms', bathroomOptions)}
            {renderSelectField('Status', 'status', statusOptions)}
          </ScrollView>

          <View style={styles.modalActions}>
            <TouchableOpacity style={styles.clearButton} onPress={handleClear}>
              <Text style={styles.clearButtonText}>Clear All</Text>
            </TouchableOpacity>

            <TouchableOpacity style={styles.applyButton} onPress={handleApply}>
              <Text style={styles.applyButtonText}>Apply Filters</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
};

export default FilterModal;
