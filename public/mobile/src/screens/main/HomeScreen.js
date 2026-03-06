import React, {useState, useEffect} from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  TextInput,
  Dimensions,
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
import SearchBar from '../../components/SearchBar';
import FilterModal from '../../components/FilterModal';

const {width} = Dimensions.get('window');

const HomeScreen = () => {
  const {theme} = useTheme();
  const navigation = useNavigation();
  const dispatch = useDispatch();

  const {properties, loading, error} = useSelector(state => state.properties);
  const {user} = useSelector(state => state.auth);

  const [searchQuery, setSearchQuery] = useState('');
  const [filters, setFilters] = useState({
    type: '',
    city: '',
    minPrice: '',
    maxPrice: '',
    bedrooms: '',
  });
  const [showFilters, setShowFilters] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [featuredProperties, setFeaturedProperties] = useState([]);

  useEffect(() => {
    loadProperties();
    loadFeaturedProperties();
  }, [filters]);

  const loadProperties = () => {
    const searchFilters = {
      ...filters,
      search: searchQuery,
      featured: false,
    };
    dispatch(fetchProperties(searchFilters));
  };

  const loadFeaturedProperties = async () => {
    try {
      const response = await apiService.getProperties({featured: true, limit: 5});
      if (response.success) {
        setFeaturedProperties(response.data);
      }
    } catch (error) {
      console.error('Error loading featured properties:', error);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await loadProperties();
    await loadFeaturedProperties();
    setRefreshing(false);
  };

  const handlePropertyPress = (property) => {
    navigation.navigate('PropertyDetail', {property});
  };

  const handleSearch = (query) => {
    setSearchQuery(query);
    // Debounced search - implement with useCallback if needed
    loadProperties();
  };

  const handleFilterChange = (newFilters) => {
    setFilters(newFilters);
    setShowFilters(false);
  };

  const renderFeaturedProperty = ({item}) => (
    <TouchableOpacity
      style={styles.featuredCard}
      onPress={() => handlePropertyPress(item)}>
      <View style={styles.featuredImage}>
        <Text style={styles.featuredEmoji}>🏠</Text>
      </View>
      <View style={styles.featuredContent}>
        <Text style={styles.featuredTitle} numberOfLines={1}>
          {item.title}
        </Text>
        <Text style={styles.featuredPrice}>
          ₹{item.price?.toLocaleString()}
        </Text>
        <Text style={styles.featuredLocation} numberOfLines={1}>
          {item.location}, {item.city}
        </Text>
      </View>
    </TouchableOpacity>
  );

  const renderProperty = ({item}) => (
    <PropertyCard
      property={item}
      onPress={() => handlePropertyPress(item)}
      style={styles.propertyCard}
    />
  );

  const styles = StyleSheet.create({
    container: {
      flex: 1,
      backgroundColor: theme.colors.background,
    },
    header: {
      backgroundColor: theme.colors.primary,
      paddingTop: theme.spacing.xl,
      paddingBottom: theme.spacing.md,
      paddingHorizontal: theme.spacing.md,
    },
    headerTitle: {
      fontSize: theme.typography.fontSize.xxl,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.light,
      marginBottom: theme.spacing.xs,
    },
    welcomeText: {
      fontSize: theme.typography.fontSize.md,
      color: theme.colors.light,
      opacity: 0.9,
    },
    searchContainer: {
      flexDirection: 'row',
      alignItems: 'center',
      backgroundColor: theme.colors.card,
      margin: theme.spacing.md,
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
    sectionTitle: {
      fontSize: theme.typography.fontSize.xl,
      fontWeight: theme.typography.fontWeight.semibold,
      color: theme.colors.text,
      marginHorizontal: theme.spacing.md,
      marginVertical: theme.spacing.md,
    },
    featuredContainer: {
      paddingHorizontal: theme.spacing.md,
      marginBottom: theme.spacing.md,
    },
    featuredCard: {
      flexDirection: 'row',
      backgroundColor: theme.colors.card,
      borderRadius: theme.borderRadius.md,
      padding: theme.spacing.md,
      marginRight: theme.spacing.md,
      shadowColor: theme.colors.shadow,
      shadowOffset: {width: 0, height: 2},
      shadowOpacity: 0.1,
      shadowRadius: 4,
      elevation: 2,
      width: width * 0.7,
    },
    featuredImage: {
      width: 60,
      height: 60,
      borderRadius: theme.borderRadius.md,
      backgroundColor: theme.colors.surface,
      justifyContent: 'center',
      alignItems: 'center',
      marginRight: theme.spacing.md,
    },
    featuredEmoji: {
      fontSize: 24,
    },
    featuredContent: {
      flex: 1,
      justifyContent: 'center',
    },
    featuredTitle: {
      fontSize: theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.semibold,
      color: theme.colors.text,
      marginBottom: theme.spacing.xs,
    },
    featuredPrice: {
      fontSize: theme.typography.fontSize.lg,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.primary,
      marginBottom: theme.spacing.xs,
    },
    featuredLocation: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.textSecondary,
    },
    propertiesList: {
      flex: 1,
      paddingHorizontal: theme.spacing.md,
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
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>🏠 APS Dream Home</Text>
        <Text style={styles.welcomeText}>
          {user ? `Welcome back, ${user.name}!` : 'Find your dream property'}
        </Text>
      </View>

      {/* Search Bar */}
      <View style={styles.searchContainer}>
        <Icon name="search" size={20} color={theme.colors.textSecondary} />
        <TextInput
          style={styles.searchInput}
          placeholder="Search properties..."
          placeholderTextColor={theme.colors.textSecondary}
          value={searchQuery}
          onChangeText={handleSearch}
        />
        <TouchableOpacity
          style={styles.filterButton}
          onPress={() => setShowFilters(true)}>
          <Icon name="filter-list" size={20} color={theme.colors.textSecondary} />
        </TouchableOpacity>
      </View>

      {/* Featured Properties */}
      {featuredProperties.length > 0 && (
        <>
          <Text style={styles.sectionTitle}>Featured Properties</Text>
          <FlatList
            data={featuredProperties}
            renderItem={renderFeaturedProperty}
            keyExtractor={(item) => item.pid.toString()}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.featuredContainer}
          />
        </>
      )}

      {/* All Properties */}
      <Text style={styles.sectionTitle}>All Properties</Text>

      {loading ? (
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
      ) : properties.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Text style={styles.emptyText}>
            No properties found matching your criteria.
          </Text>
        </View>
      ) : (
        <FlatList
          data={properties}
          renderItem={renderProperty}
          keyExtractor={(item) => item.pid.toString()}
          contentContainerStyle={styles.propertiesList}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={[theme.colors.primary]}
            />
          }
          showsVerticalScrollIndicator={false}
        />
      )}

      {/* Filter Modal */}
      <FilterModal
        visible={showFilters}
        filters={filters}
        onClose={() => setShowFilters(false)}
        onApply={handleFilterChange}
      />
    </View>
  );
};

export default HomeScreen;
