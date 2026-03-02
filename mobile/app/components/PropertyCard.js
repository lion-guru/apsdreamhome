import React from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';

const { width } = Dimensions.get('window');

const PropertyCard = ({ property, onPress, style }) => {
  const {
    title,
    price,
    location,
    images,
    bedrooms,
    bathrooms,
    area,
    property_type,
  } = property;

  return (
    <TouchableOpacity
      style={[styles.container, style]}
      onPress={onPress}
      activeOpacity={0.8}
    >
      <Image
        source={{ uri: images?.[0] || 'https://via.placeholder.com/300' }}
        style={styles.image}
      />
      
      <View style={styles.overlay}>
        <View style={styles.priceContainer}>
          <Text style={styles.price}>${price.toLocaleString()}</Text>
        </View>
      </View>

      <View style={styles.content}>
        <Text style={styles.title} numberOfLines={2}>
          {title}
        </Text>
        
        <View style={styles.locationContainer}>
          <Icon name="location-on" size={16} color="#7f8c8d" />
          <Text style={styles.location} numberOfLines={1}>
            {location}
          </Text>
        </View>

        <View style={styles.stats}>
          <View style={styles.stat}>
            <Icon name="bed" size={16} color="#7f8c8d" />
            <Text style={styles.statText}>{bedrooms}</Text>
          </View>
          <View style={styles.stat}>
            <Icon name="bathtub" size={16} color="#7f8c8d" />
            <Text style={styles.statText}>{bathrooms}</Text>
          </View>
          <View style={styles.stat}>
            <Icon name="square-foot" size={16} color="#7f8c8d" />
            <Text style={styles.statText}>{area} sqft</Text>
          </View>
        </View>

        <View style={styles.footer}>
          <Text style={styles.type}>{property_type}</Text>
          <View style={styles.rating}>
            <Icon name="star" size={16} color="#f39c12" />
            <Text style={styles.ratingText}>4.5</Text>
          </View>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
    marginBottom: 15,
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: 200,
    resizeMode: 'cover',
  },
  overlay: {
    position: 'absolute',
    top: 10,
    right: 10,
  },
  priceContainer: {
    backgroundColor: 'rgba(0,0,0,0.7)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
  },
  price: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  content: {
    padding: 15,
  },
  title: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 8,
    lineHeight: 22,
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  location: {
    fontSize: 14,
    color: '#7f8c8d',
    marginLeft: 4,
    flex: 1,
  },
  stats: {
    flexDirection: 'row',
    marginBottom: 12,
  },
  stat: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 15,
  },
  statText: {
    fontSize: 12,
    color: '#7f8c8d',
    marginLeft: 4,
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#ecf0f1',
  },
  type: {
    fontSize: 12,
    color: '#7f8c8d',
    backgroundColor: '#ecf0f1',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  rating: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ratingText: {
    fontSize: 12,
    color: '#7f8c8d',
    marginLeft: 4,
  },
});

export default PropertyCard;
