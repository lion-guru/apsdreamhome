# AI-Powered Features for APS Dream Homes

This document outlines the AI features that have been implemented to enhance the property listing and search functionality of the APS Dream Homes website.

## Features Implemented

### 1. AI-Powered Property Search
- **Natural Language Processing (NLP) Search**: Users can search for properties using natural language queries like "Show me 3 BHK flats in Mumbai under 1.5 Cr"
- **Voice Search**: Users can use voice commands to search for properties
- **Smart Filters**: The AI understands and applies filters based on the search query

### 2. Personalized Property Recommendations
- **User-Based Recommendations**: Suggests properties based on user preferences and behavior
- **Context-Aware**: Recommendations adapt based on the user's current search context
- **Smart Sorting**: Properties are sorted based on relevance to the user's preferences

### 3. AI Price Prediction
- **Accurate Valuations**: Predicts property prices based on various factors
- **Detailed Analysis**: Provides a breakdown of how the price was calculated
- **Market Insights**: Shows how different features affect the property value

## Files Added

### Backend
- `/includes/ai/PropertyAI.php` - Main AI class for property recommendations and predictions
- `/api/ai/search.php` - API endpoint for AI-powered property search
- `/api/ai/recommendations.php` - API endpoint for property recommendations
- `/api/ai/predict-price.php` - API endpoint for price prediction

### Frontend
- `/assets/js/ai-property-search.js` - JavaScript for AI search and interactions
- `/assets/css/ai-features.css` - Styling for AI components
- `/templates/ai-features-demo.php` - Example template showing AI features

## How to Integrate

1. **Include Required Files**
   Add these to your page:
   ```html
   <!-- CSS -->
   <link rel="stylesheet" href="/assets/css/ai-features.css">
   
   <!-- JavaScript -->
   <script src="/assets/js/ai-property-search.js"></script>
   ```

2. **Add AI Search Box**
   ```html
   <div class="ai-search-container">
       <form id="aiSearchForm" class="ai-search-box">
           <input type="text" 
                  id="aiSearchInput" 
                  class="ai-search-input" 
                  placeholder="Try: 'Show me 3 BHK flats in Mumbai under 1.5 Cr'"
                  autocomplete="off">
           <div class="ai-search-buttons">
               <button type="button" id="voiceSearchBtn" class="ai-search-btn" title="Voice Search">
                   <i class="fas fa-microphone"></i>
               </button>
               <button type="submit" class="ai-search-btn" title="Search">
                   <i class="fas fa-search"></i>
               </button>
           </div>
       </form>
       <div class="ai-error-container mt-2"></div>
   </div>
   <div id="searchResults" class="mt-5"></div>
   ```

3. **Add AI Recommendations**
   ```html
   <section id="aiRecommendations" class="ai-recommendations">
       <div class="container">
           <div class="section-header">
               <h2>Recommended For You <span class="ai-badge"><i class="fas fa-robot"></i> AI-Powered</span></h2>
               <p class="text-muted">Properties selected based on your preferences and behavior</p>
           </div>
           <div class="recommendations-container">
               <!-- Recommendations will be loaded here -->
           </div>
       </div>
   </section>
   ```

4. **Add Price Prediction Button**
   ```html
   <button type="button" class="btn btn-primary" id="pricePredictionBtn">
       <i class="fas fa-calculator me-2"></i> Get Price Prediction
   </button>
   ```

## Configuration

1. **API Keys**
   - Update the `$openai_api_key` in `PropertyAI.php` if you're using OpenAI's API for enhanced features

2. **Database**
   - Make sure the `user_preferences` table exists for personalized recommendations
   - The `properties` table should have the necessary fields for search and filtering

## Dependencies

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- Font Awesome 6
- jQuery (for AJAX requests)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (Chrome for Android, Safari for iOS)

## Performance Considerations

- AI search results are cached to improve performance
- Large property datasets are paginated to prevent slow loading
- Images are lazy-loaded to improve page load times

## Security

- All user inputs are sanitized to prevent XSS and SQL injection
- API endpoints require authentication where necessary
- Sensitive data is not exposed in client-side code

## Troubleshooting

1. **Search not working**
   - Check browser console for JavaScript errors
   - Verify that the API endpoints are accessible
   - Ensure the database connection is working

2. **No recommendations showing**
   - Make sure the user is logged in for personalized recommendations
   - Check if there are properties in the database
   - Verify that the recommendation API is returning data

3. **Price prediction not accurate**
   - Ensure all required fields are filled in the prediction form
   - Check that the property data in the database is accurate
   - The prediction is an estimate and may not reflect the exact market value

## Future Enhancements

1. **Integration with external real estate APIs** for more accurate pricing data
2. **Machine learning model training** on historical transaction data
3. **Sentiment analysis** of property reviews
4. **Virtual property tours** with AI-powered navigation
5. **Chatbot integration** for instant property inquiries

## Support

For any issues or questions, please contact the development team at [support@apsdreamhomes.com](mailto:support@apsdreamhomes.com).
