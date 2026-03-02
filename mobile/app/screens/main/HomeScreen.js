import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
  Dimensions,
  Image,
} from 'react-native';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigation } from '@react-navigation/native';
import { fetchProperties } from '../../store/slices/propertySlice';
import { fetchNotifications } from '../../store/slices/notificationSlice';
import PropertyCard from '../../components/PropertyCard';
import SearchBar from '../../components/SearchBar';
import CategoryFilter from '../../components/CategoryFilter';
import LoadingSpinner from '../../components/LoadingSpinner';
import ErrorMessage from '../../components/ErrorMessage';

const { width } = Dimensions.get('window');

const HomeScreen = () => {
  const [refreshing, setRefreshing] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');

  const navigation = useNavigation();
  const dispatch = useDispatch();

  const {
    properties,
    loading,
    error,
    featured,
    recent,
  } = useSelector(state => state.properties);
  const { notifications } = useSelector(state => state.notifications);
  const { user } = useSelector(state => state.auth);

  useEffect(() => {
    loadData();
    loadNotifications();
  }, []);

  const loadData = async () => {
    try {
      await dispatch(fetchProperties()).unwrap();
    } catch (err) {
      console.error('Failed to load properties:', err);
    }
  };

  const loadNotifications = async () => {
    try {
      await dispatch(fetchNotifications()).unwrap();
    } catch (err) {
      console.error('Failed to load notifications:', err);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadData();
    await loadNotifications();
    setRefreshing(false);
  };

  const handlePropertyPress = (property) => {
    navigation.navigate('PropertyDetail', { propertyId: property.id });
  };

  const handleSearch = (query) => {
    setSearchQuery(query);
    if (query.trim()) {
      navigation.navigate('Search', { query });
    }
  };

  const handleCategoryFilter = (category) => {
    setSelectedCategory(category);
  };

  const renderFeaturedProperty = ({ item }) => (
    <TouchableOpacity
      style={styles.featuredCard}
      onPress={() => handlePropertyPress(item)}
    >
      <Image source={{ uri: item.images[0] }} style={styles.featuredImage} />
      <View style={styles.featuredOverlay}>
        <Text style={styles.featuredPrice}>${item.price.toLocaleString()}</Text>
        <Text style={styles.featuredTitle}>{item.title}</Text>
        <Text style={styles.featuredLocation}>{item.location}</Text>
      </View>
    </TouchableOpacity>
  );

  const renderPropertyItem = ({ item }) => (
    <PropertyCard
      property={item}
      onPress={() => handlePropertyPress(item)}
      style={styles.propertyCard}
    />
  );

  const renderHeader = () => (
    <View>
      {/* Welcome Section */}
      <View style={styles.welcomeSection}>
        <Text style={styles.welcomeText}>Welcome back,</Text>
        <Text style={styles.userName}>{user?.name || 'User'}</Text>
        <Text style={styles.notificationBadge}>
          {notifications.filter(n => !n.read).length} new notifications
        </Text>
      </View>

      {/* Search Bar */}
      <SearchBar
        value={searchQuery}
        onChangeText={setSearchQuery}
        onSubmitEditing={handleSearch}
        placeholder="Search properties..."
      />

      {/* Category Filter */}
      <CategoryFilter
        selectedCategory={selectedCategory}
        onCategoryChange={handleCategoryFilter}
      />

      {/* Featured Properties */}
      {featured.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Featured Properties</Text>
          <FlatList
            data={featured}
            renderItem={renderFeaturedProperty}
            keyExtractor={(item) => item.id.toString()}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.featuredList}
          />
        </View>
      )}

      {/* Recent Properties */}
      {recent.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Recent Properties</Text>
        </View>
      )}
    </View>
  );

  if (loading && properties.length === 0) {
    return <LoadingSpinner />;
  }

  if (error) {
    return <ErrorMessage message={error} onRetry={loadData} />;
  }

  return (
    <View style={styles.container}>
      <FlatList
        data={recent}
        renderItem={renderPropertyItem}
        keyExtractor={(item) => item.id.toString()}
        ListHeaderComponent={renderHeader}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
        contentContainerStyle={styles.listContainer}
        showsVerticalScrollIndicator={false}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8f9fa',
  },
  listContainer: {
    padding: 15,
  },
  welcomeSection: {
    marginBottom: 20,
  },
  welcomeText: {
    fontSize: 16,
    color: '#7f8c8d',
  },
  userName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 5,
  },
  notificationBadge: {
    fontSize: 12,
    color: '#e74c3c',
    backgroundColor: '#ffeaea',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    alignSelf: 'flex-start',
  },
  section: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 15,
  },
  featuredList: {
    paddingRight: 15,
  },
  featuredCard: {
    width: width * 0.7,
    height: 200,
    marginRight: 15,
    borderRadius: 12,
    overflow: 'hidden',
  },
  featuredImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  featuredOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.7)',
    padding: 15,
  },
  featuredPrice: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  featuredTitle: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 3,
  },
  featuredLocation: {
    color: '#ecf0f1',
    fontSize: 12,
  },
  propertyCard: {
    marginBottom: 15,
  },
});

export default HomeScreen;
