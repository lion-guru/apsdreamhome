import React from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Image,
  Dimensions,
} from 'react-native';
import {useTheme} from '../../theme';
import Icon from 'react-native-vector-icons/MaterialIcons';

const {width} = Dimensions.get('window');
const cardWidth = (width - 32) / 2; // Two columns with padding

const PropertyCard = ({property, onPress, style, viewMode = 'list'}) => {
  const {theme} = useTheme();

  const formatPrice = (price) => {
    if (price >= 10000000) {
      return `₹${(price / 10000000).toFixed(1)}Cr`;
    } else if (price >= 100000) {
      return `₹${(price / 100000).toFixed(1)}L`;
    }
    return `₹${price?.toLocaleString()}`;
  };

  const getPropertyTypeIcon = (type) => {
    switch (type?.toLowerCase()) {
      case 'house':
      case 'villa':
        return 'home';
      case 'apartment':
      case 'flat':
        return 'apartment';
      case 'commercial':
        return 'business';
      case 'plot':
      case 'land':
        return 'landscape';
      default:
        return 'home';
    }
  };

  const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
      case 'available':
        return theme.colors.success;
      case 'sold':
        return theme.colors.danger;
      case 'rented':
        return theme.colors.warning;
      default:
        return theme.colors.textSecondary;
    }
  };

  const styles = StyleSheet.create({
    card: {
      backgroundColor: theme.colors.card,
      borderRadius: theme.borderRadius.lg,
      shadowColor: theme.colors.shadow,
      shadowOffset: {width: 0, height: 2},
      shadowOpacity: 0.1,
      shadowRadius: 8,
      elevation: 4,
      overflow: 'hidden',
    },
    listCard: {
      flexDirection: 'row',
      marginBottom: theme.spacing.md,
      padding: theme.spacing.md,
    },
    gridCard: {
      width: cardWidth,
      marginBottom: theme.spacing.md,
      marginHorizontal: theme.spacing.xs,
    },
    imageContainer: {
      position: 'relative',
    },
    listImage: {
      width: 120,
      height: 90,
      borderRadius: theme.borderRadius.md,
    },
    gridImage: {
      width: '100%',
      height: 120,
      borderTopLeftRadius: theme.borderRadius.lg,
      borderTopRightRadius: theme.borderRadius.lg,
    },
    imagePlaceholder: {
      backgroundColor: theme.colors.surface,
      justifyContent: 'center',
      alignItems: 'center',
    },
    imageEmoji: {
      fontSize: 32,
    },
    featuredBadge: {
      position: 'absolute',
      top: theme.spacing.xs,
      left: theme.spacing.xs,
      backgroundColor: theme.colors.primary,
      paddingHorizontal: theme.spacing.sm,
      paddingVertical: theme.spacing.xs,
      borderRadius: theme.borderRadius.sm,
    },
    featuredText: {
      color: theme.colors.light,
      fontSize: theme.typography.fontSize.xs,
      fontWeight: theme.typography.fontWeight.semibold,
    },
    content: {
      flex: 1,
    },
    listContent: {
      flex: 1,
      paddingLeft: theme.spacing.md,
      justifyContent: 'space-between',
    },
    gridContent: {
      padding: theme.spacing.md,
    },
    title: {
      fontSize: viewMode === 'list' ? theme.typography.fontSize.md : theme.typography.fontSize.sm,
      fontWeight: theme.typography.fontWeight.semibold,
      color: theme.colors.text,
      marginBottom: theme.spacing.xs,
    },
    price: {
      fontSize: viewMode === 'list' ? theme.typography.fontSize.lg : theme.typography.fontSize.md,
      fontWeight: theme.typography.fontWeight.bold,
      color: theme.colors.primary,
      marginBottom: theme.spacing.xs,
    },
    location: {
      fontSize: theme.typography.fontSize.sm,
      color: theme.colors.textSecondary,
      marginBottom: theme.spacing.xs,
    },
    features: {
      flexDirection: 'row',
      alignItems: 'center',
      flexWrap: 'wrap',
      gap: theme.spacing.sm,
    },
    feature: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: theme.spacing.xs,
    },
    featureText: {
      fontSize: theme.typography.fontSize.xs,
      color: theme.colors.textSecondary,
    },
    statusBadge: {
      position: 'absolute',
      top: theme.spacing.xs,
      right: theme.spacing.xs,
      paddingHorizontal: theme.spacing.sm,
      paddingVertical: theme.spacing.xs,
      borderRadius: theme.borderRadius.sm,
    },
    statusText: {
      fontSize: theme.typography.fontSize.xs,
      fontWeight: theme.typography.fontWeight.semibold,
    },
  });

  const renderImage = () => (
    <View style={styles.imageContainer}>
      {property.pimage ? (
        <Image
          source={{uri: property.pimage}}
          style={viewMode === 'list' ? styles.listImage : styles.gridImage}
          resizeMode="cover"
        />
      ) : (
        <View style={[viewMode === 'list' ? styles.listImage : styles.gridImage, styles.imagePlaceholder]}>
          <Text style={styles.imageEmoji}>🏠</Text>
        </View>
      )}

      {property.isFeatured === 1 && (
        <View style={styles.featuredBadge}>
          <Text style={styles.featuredText}>Featured</Text>
        </View>
      )}

      <View style={[styles.statusBadge, {backgroundColor: getStatusColor(property.status)}]}>
        <Text style={[styles.statusText, {color: theme.colors.light}]}>
          {property.status?.charAt(0).toUpperCase() + property.status?.slice(1)}
        </Text>
      </View>
    </View>
  );

  const renderFeatures = () => (
    <View style={styles.features}>
      {property.bedroom && (
        <View style={styles.feature}>
          <Icon name="bed" size={12} color={theme.colors.textSecondary} />
          <Text style={styles.featureText}>{property.bedroom} bed</Text>
        </View>
      )}

      {property.bathroom && (
        <View style={styles.feature}>
          <Icon name="bathtub" size={12} color={theme.colors.textSecondary} />
          <Text style={styles.featureText}>{property.bathroom} bath</Text>
        </View>
      )}

      {property.size && (
        <View style={styles.feature}>
          <Icon name="square-foot" size={12} color={theme.colors.textSecondary} />
          <Text style={styles.featureText}>{property.size} sq ft</Text>
        </View>
      )}

      <View style={styles.feature}>
        <Icon name={getPropertyTypeIcon(property.type)} size={12} color={theme.colors.textSecondary} />
        <Text style={styles.featureText}>{property.type}</Text>
      </View>
    </View>
  );

  return (
    <TouchableOpacity style={[styles.card, style]} onPress={onPress}>
      {renderImage()}

      <View style={viewMode === 'list' ? styles.listContent : styles.gridContent}>
        <Text style={styles.title} numberOfLines={1}>
          {property.title}
        </Text>

        <Text style={styles.price}>
          {formatPrice(property.price)}
        </Text>

        <Text style={styles.location} numberOfLines={1}>
          {property.location}, {property.city}
        </Text>

        {renderFeatures()}
      </View>
    </TouchableOpacity>
  );
};

export default PropertyCard;
