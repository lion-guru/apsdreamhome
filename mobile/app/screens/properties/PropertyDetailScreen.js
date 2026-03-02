import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Image,
  Dimensions,
  Share,
  Alert,
} from 'react-native';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigation, useRoute } from '@react-navigation/native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { fetchPropertyById } from '../../store/slices/propertySlice';
import { addToFavorites, removeFromFavorites } from '../../store/slices/favoriteSlice';
import LoadingSpinner from '../../components/LoadingSpinner';
import ErrorMessage from '../../components/ErrorMessage';
import ImageGallery from '../../components/ImageGallery';
import ContactForm from '../../components/ContactForm';
import PropertyFeatures from '../../components/PropertyFeatures';
import PropertyLocation from '../../components/PropertyLocation';

const { width, height } = Dimensions.get('window');

const PropertyDetailScreen = () => {
  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showContactForm, setShowContactForm] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  const navigation = useNavigation();
  const route = useRoute();
  const dispatch = useDispatch();

  const { propertyId } = route.params;
  const { user } = useSelector(state => state.auth);
  const { favorites } = useSelector(state => state.favorites);

  useEffect(() => {
    loadProperty();
  }, [propertyId]);

  const loadProperty = async () => {
    try {
      setLoading(true);
      const propertyData = await dispatch(fetchPropertyById(propertyId)).unwrap();
      setProperty(propertyData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleFavoriteToggle = () => {
    if (!user) {
      Alert.alert('Login Required', 'Please login to save favorites');
      return;
    }

    const isFavorite = favorites.some(fav => fav.id === propertyId);
    
    if (isFavorite) {
      dispatch(removeFromFavorites(propertyId));
    } else {
      dispatch(addToFavorites(property));
    }
  };

  const handleShare = async () => {
    try {
      await Share.share({
        message: `Check out this property: ${property.title}\n${property.location}\nPrice: $${property.price.toLocaleString()}`,
        url: `https://apsdreamhome.com/property/${propertyId}`,
      });
    } catch (error) {
      console.error('Error sharing:', error);
    }
  };

  const handleContact = () => {
    if (!user) {
      Alert.alert('Login Required', 'Please login to contact the agent');
      return;
    }
    setShowContactForm(true);
  };

  const handleImageChange = (index) => {
    setCurrentImageIndex(index);
  };

  const isFavorite = favorites.some(fav => fav.id === propertyId);

  if (loading) {
    return <LoadingSpinner />;
  }

  if (error || !property) {
    return <ErrorMessage message={error || 'Property not found'} onRetry={loadProperty} />;
  }

  return (
    <View style={styles.container}>
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Image Gallery */}
        <ImageGallery
          images={property.images}
          currentIndex={currentImageIndex}
          onImageChange={handleImageChange}
          height={height * 0.4}
        />

        {/* Action Buttons */}
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={[styles.actionButton, isFavorite && styles.favoriteButton]}
            onPress={handleFavoriteToggle}
          >
            <Icon
              name={isFavorite ? 'favorite' : 'favorite-border'}
              size={24}
              color={isFavorite ? '#e74c3c' : '#7f8c8d'}
            />
          </TouchableOpacity>
          
          <TouchableOpacity style={styles.actionButton} onPress={handleShare}>
            <Icon name="share" size={24} color="#7f8c8d" />
          </TouchableOpacity>
        </View>

        {/* Property Details */}
        <View style={styles.detailsContainer}>
          <Text style={styles.price}>${property.price.toLocaleString()}</Text>
          <Text style={styles.title}>{property.title}</Text>
          <Text style={styles.location}>
            <Icon name="location-on" size={16} color="#3498db" />
            {property.location}
          </Text>

          {/* Property Stats */}
          <View style={styles.statsContainer}>
            <View style={styles.statItem}>
              <Icon name="bed" size={20} color="#7f8c8d" />
              <Text style={styles.statText}>{property.bedrooms} Beds</Text>
            </View>
            <View style={styles.statItem}>
              <Icon name="bathtub" size={20} color="#7f8c8d" />
              <Text style={styles.statText}>{property.bathrooms} Baths</Text>
            </View>
            <View style={styles.statItem}>
              <Icon name="square-foot" size={20} color="#7f8c8d" />
              <Text style={styles.statText}>{property.area} sqft</Text>
            </View>
          </View>

          {/* Property Type */}
          <View style={styles.typeContainer}>
            <Text style={styles.typeLabel}>Property Type</Text>
            <Text style={styles.typeValue}>{property.property_type}</Text>
          </View>

          {/* Description */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Description</Text>
            <Text style={styles.description}>{property.description}</Text>
          </View>

          {/* Features */}
          <PropertyFeatures features={property.features} />

          {/* Location */}
          <PropertyLocation
            location={property.location}
            coordinates={property.coordinates}
          />

          {/* Agent Information */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Listed by</Text>
            <View style={styles.agentContainer}>
              <Image
                source={{ uri: property.agent.avatar }}
                style={styles.agentAvatar}
              />
              <View style={styles.agentInfo}>
                <Text style={styles.agentName}>{property.agent.name}</Text>
                <Text style={styles.agentTitle}>{property.agent.title}</Text>
                <Text style={styles.agentPhone}>{property.agent.phone}</Text>
              </View>
            </View>
          </View>
        </View>
      </ScrollView>

      {/* Contact Button */}
      <View style={styles.contactContainer}>
        <TouchableOpacity style={styles.contactButton} onPress={handleContact}>
          <Text style={styles.contactButtonText}>Contact Agent</Text>
        </TouchableOpacity>
      </View>

      {/* Contact Form Modal */}
      {showContactForm && (
        <ContactForm
          property={property}
          onClose={() => setShowContactForm(false)}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  scrollView: {
    flex: 1,
  },
  actionButtons: {
    position: 'absolute',
    top: height * 0.4 - 60,
    right: 20,
    flexDirection: 'row',
  },
  actionButton: {
    backgroundColor: '#ffffff',
    borderRadius: 25,
    padding: 10,
    marginLeft: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  favoriteButton: {
    backgroundColor: '#ffeaea',
  },
  detailsContainer: {
    padding: 20,
  },
  price: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 8,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 8,
  },
  location: {
    fontSize: 16,
    color: '#7f8c8d',
    marginBottom: 20,
  },
  statsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 20,
    paddingBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  statItem: {
    alignItems: 'center',
  },
  statText: {
    fontSize: 14,
    color: '#7f8c8d',
    marginTop: 4,
  },
  typeContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
    paddingBottom: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#ecf0f1',
  },
  typeLabel: {
    fontSize: 16,
    color: '#7f8c8d',
  },
  typeValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
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
  description: {
    fontSize: 16,
    lineHeight: 24,
    color: '#7f8c8d',
  },
  agentContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  agentAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    marginRight: 15,
  },
  agentInfo: {
    flex: 1,
  },
  agentName: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 4,
  },
  agentTitle: {
    fontSize: 14,
    color: '#7f8c8d',
    marginBottom: 4,
  },
  agentPhone: {
    fontSize: 14,
    color: '#3498db',
  },
  contactContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#ffffff',
    borderTopWidth: 1,
    borderTopColor: '#ecf0f1',
    padding: 20,
  },
  contactButton: {
    backgroundColor: '#3498db',
    borderRadius: 8,
    padding: 15,
    alignItems: 'center',
  },
  contactButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default PropertyDetailScreen;
