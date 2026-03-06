import React, {useState, useEffect} from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import {useTheme} from '../../theme';
import {useNavigation} from '@react-navigation/native';
import {useSelector, useDispatch} from 'react-redux';
import Icon from 'react-native-vector-icons/MaterialIcons';
import apiService from '../../services/ApiService';
import {fetchProperties} from '../../store/actions/propertyActions';
import PropertyCard from '../../components/PropertyCard';
import FilterModal from '../../components/FilterModal';
import SortModal from '../../components/SortModal';

const PropertiesScreen = () => {
  const {theme} = useTheme();
  const navigation = useNavigation();
  const dispatch = useDispatch();

  const {properties, loading, error} = useSelector(state => state.properties);

  const [filters, setFilters] = useState({
    type: '',
    city: '',
    minPrice: '',
    maxPrice: '',
    bedrooms: '',
    bathrooms: '',
    status: 'available',
  });
  const [sortBy, setSortBy] = useState('date_desc');
  const [showFilters, setShowFilters] = useState(false);
  const [showSort, setShowSort] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [viewMode, setViewMode] = useState('list'); // 'list' or 'grid'

  useEffect(() => {
    loadProperties();
  }, [filters, sortBy]);

  const loadProperties = () => {
    const searchFilters = {
      ...filters,
      sort: sortBy,
    };
    dispatch(fetchProperties(searchFilters));
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await loadProperties();
    setRefreshing(false);
  };

  const handlePropertyPress = (property) => {
    navigation.navigate('PropertyDetail', {property});
  };

  const handleFilterChange = (newFilters) => {
    setFilters(newFilters);
    setShowFilters(false);
  };

  const handleSortChange = (newSortBy) => {
    setSortBy(newSortBy);
    setShowSort(false);
  };

  const getSortOptions = () => [
    {value: 'date_desc', label: 'Newest First'},
    {value: 'date_asc', label: 'Oldest First'},
    {value: 'price_asc', label: 'Price: Low to High'},
    {value: 'price_desc', label: 'Price: High to Low'},
    {value: 'size_desc', label: 'Size: Large to Small'},
    {value: 'size_asc', label: 'Size: Small to Large'},
  ];

  const getActiveFilterCount = () => {
    return Object.values(filters).filter(value =>
      value !== '' && value !== null && value !== undefined
    ).length;
  };

  const clearFilters = () => {
    setFilters({
      type: '',
      city: '',
      minPrice: '',
      maxPrice: '',
      bedrooms: '',
      bathrooms: '',
      status: 'available',
    });
  };

  const renderProperty = ({item}) => (
    <PropertyCard
      property={item}
      onPress={() => handlePropertyPress(item)}
      style={styles.propertyCard}
      viewMode={viewMode}
    />
  );

  const renderHeader = () => (
    <View style={styles.header}>
      <View style={styles.headerTop}>
        <Text style={styles.headerTitle}>Properties</Text>
        <View style={styles.headerActions}>
          <TouchableOpacity
            style={[styles.headerButton, viewMode === 'grid' && styles.activeButton]}
            onPress={() => setViewMode('grid')}>
            <Icon name="grid-view" size={20} color={viewMode === 'grid' ? theme.colors.primary : theme.colors.textSecondary} />
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.headerButton, viewMode === 'list' && styles.activeButton]}
            onPress={() => setViewMode('list')}>
            <Icon name="list" size={20} color={viewMode === 'list' ? theme.colors.primary : theme.colors.textSecondary} />
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.filterBar}>
        <TouchableOpacity
          style={styles.filterButton}
          onPress={() => setShowFilters(true)}>
          <Icon name="filter-list" size={16} color={theme.colors.primary} />
          <Text style={styles.filterButtonText}>
            Filters {getActiveFilterCount() > 0 && `(${getActiveFilterCount()})`}
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.sortButton}
          onPress={() => setShowSort(true)}>
          <Icon name="sort" size={16} color={theme.colors.primary} />
          <Text style={styles.sortButtonText}>Sort</Text>
        </TouchableOpacity>

        {getActiveFilterCount() > 0 && (
          <TouchableOpacity style={styles.clearButton} onPress={clearFilters}>
            <Text style={styles.clearButtonText}>Clear</Text>
          </TouchableOpacity>
        )}
      </View>
    </View>
  );

  const styles = StyleSheet.create({
    container: {
      flex: 1,
      backgroundColor: theme.colors.background,
    },
    header: {
      backgroundColor: theme.colors.card,
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.md,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
    },
    headerTop: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: theme.spacing.sm,
    },
    headerTitle: {
      fontSize: theme.typography.fontSize.xxl,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.text,
    },
    headerActions: {
      flexDirection: 'row',
      gap: theme.spacing.sm,
    },
    headerButton: {
      padding: theme.spacing.xs,
      borderRadius: theme.borderRadius.sm,
      backgroundColor: theme.colors.surface,
    },
    activeButton: {
      backgroundColor: theme.colors.primary,
    },
    filterBar: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    filterButton: {
      flexDirection: 'row',
      alignItems: 'center',
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
      backgroundColor: theme.colors.surface,
      borderRadius: theme.borderRadius.md,
      gap: theme.spacing.xs,
    },
    filterButtonText: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.primary,
      fontWeight: theme.typography.fontWeight.medium,
    },
    sortButton: {
      flexDirection: 'row',
      alignItems: 'center',
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
      backgroundColor: theme.colors.surface,
      borderRadius: theme.borderRadius.md,
      gap: theme.spacing.xs,
    },
    sortButtonText: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.primary,
      fontWeight: theme.typography.fontWeight.medium,
    },
    clearButton: {
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
    },
    clearButtonText: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.danger,
      fontWeight: theme.typography.fontWeight.medium,
    },
    propertiesList: {
      flex: 1,
      padding: theme.spacing.md,
    },
    propertyCard: {
      marginBottom: theme.spacing.md,
    },
    loadingContainer: {
      flex: 1,
      justifyContent: 'center',
      alignItems: 'center',
    },
    errorContainer: {
      flex: 1,
      justifyContent: 'center',
      alignItems: 'center',
      padding: theme.spacing.xl,
    },
    errorText: {
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.textSecondary,
      textAlign: 'center',
      marginBottom: theme.spacing.lg,
    },
    retryButton: {
      backgroundColor: theme.colors.primary,
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      borderRadius: theme.borderRadius.md,
    },
    retryButtonText: {
      color: theme.colors.light,
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
    },
    emptyContainer: {
      flex: 1,
      justifyContent: 'center',
      alignItems: 'center',
      padding: theme.spacing.xl,
    },
    emptyText: {
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.textSecondary,
      textAlign: 'center',
    },
  });

  return (
    <View style={styles.container}>
      <FlatList
        data={properties}
        renderItem={renderProperty}
        keyExtractor={(item) => item.pid.toString()}
        ListHeaderComponent={renderHeader}
        contentContainerStyle={styles.propertiesList}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            colors={[theme.colors.primary]}
          />
        }
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          loading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color={theme.colors.primary} />
            </View>
          ) : error ? (
            <View style={styles.errorContainer}>
              <Text style={styles.errorText}>
                Unable to load properties. Please try again.
              </Text>
              <TouchableOpacity style={styles.retryButton} onPress={loadProperties}>
                <Text style={styles.retryButtonText}>Retry</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>
                No properties found matching your criteria.
              </Text>
            </View>
          )
        }
      />

      {/* Filter Modal */}
      <FilterModal
        visible={showFilters}
        filters={filters}
        onClose={() => setShowFilters(false)}
        onApply={handleFilterChange}
        onClear={clearFilters}
      />

      {/* Sort Modal */}
      <SortModal
        visible={showSort}
        sortBy={sortBy}
        sortOptions={getSortOptions()}
        onClose={() => setShowSort(false)}
        onApply={handleSortChange}
      />
    </View>
  );
};

export default PropertiesScreen;
