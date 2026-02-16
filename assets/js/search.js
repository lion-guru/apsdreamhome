/**
 * Property Search Functionality for APS Dream Home
 * Handles property search, filtering, and results display
 */

// Global variables
let currentPage = 1;
const itemsPerPage = 12;
let isLoading = false;
let hasMore = true;
let currentSearchParams = {};
let priceRangeSlider;

// Initialize price range slider
function initPriceRangeSlider() {
  const range = document.getElementById("priceRange");
  if (!range) return;

  const minPriceInput = document.getElementById("minPrice");
  const maxPriceInput = document.getElementById("maxPrice");

  // Default values
  const min = 0;
  const max = 10000000;
  const from = 1000000;
  const to = 5000000;

  // Initialize the slider
  priceRangeSlider = new RangeSlider(range, {
    min: min,
    max: max,
    from: from,
    to: to,
    step: 100000,
    scale: false,
    onStart: function (data) {
      minPriceInput.value = data.from;
      maxPriceInput.value = data.to;
    },
    onChange: function (data) {
      minPriceInput.value = data.from;
      maxPriceInput.value = data.to;
    },
  });

  // Update slider when inputs change
  minPriceInput.addEventListener("change", function () {
    priceRangeSlider.update({
      from: parseInt(this.value) || min,
    });
  });

  maxPriceInput.addEventListener("change", function () {
    priceRangeSlider.update({
      to: parseInt(this.value) || max,
    });
  });
}

/**
 * Initialize property search functionality
 */
function initPropertySearch() {
  const searchForm = document.getElementById("propertySearchForm");
  const searchResults = document.getElementById("searchResults");

  if (!searchForm || !searchResults) return;

  // Initialize price range slider if it exists
  const priceRange = document.getElementById("priceRange");
  const priceValue = document.getElementById("priceValue");
  if (priceRange && priceValue) {
    priceValue.textContent = formatPrice(priceRange.value);
    priceRange.addEventListener("input", (e) => {
      priceValue.textContent = formatPrice(e.target.value);
    });
  }

  // Handle form submission
  searchForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Reset pagination
    currentPage = 1;
    hasMore = true;

    // Clear previous results
    const resultsContainer = document.getElementById("searchResultsContainer");
    if (resultsContainer) {
      resultsContainer.innerHTML = "";
    }

    // Show loading state
    const submitBtn = searchForm.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Searching...';

    try {
      // Get form data
      const formData = new FormData(searchForm);
      currentSearchParams = Object.fromEntries(formData.entries());

      // Add pagination
      currentSearchParams.page = currentPage;
      currentSearchParams.limit = itemsPerPage;

      // Make API request
      const response = await fetchProperties(currentSearchParams);

      // Display results
      if (
        response.success &&
        response.properties &&
        response.properties.length > 0
      ) {
        displaySearchResults(response);

        // Show results section if it was hidden
        if (searchResults) {
          searchResults.style.display = "block";
        }

        // Scroll to results
        searchResults.scrollIntoView({ behavior: "smooth", block: "start" });
      } else {
        showNoResults();
      }
    } catch (error) {
      console.error("Search failed:", error);
      showError("Failed to load search results. Please try again later.");
    } finally {
      // Reset button state
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
    }
  });

  // Initialize advanced search toggle
  const toggleBtn = document.getElementById("toggleAdvancedSearch");
  const advancedOptions = document.getElementById("advancedSearchOptions");

  if (toggleBtn && advancedOptions) {
    toggleBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const isHidden =
        advancedOptions.style.display === "none" ||
        !advancedOptions.style.display;

      if (isHidden) {
        // Show advanced options with animation
        advancedOptions.style.display = "block";
        advancedOptions.style.height = "0";
        advancedOptions.style.overflow = "hidden";
        advancedOptions.style.transition = "height 0.3s ease";

        // Calculate the full height
        const fullHeight = advancedOptions.scrollHeight + "px";

        // Set the height to animate from 0 to full height
        setTimeout(() => {
          advancedOptions.style.height = fullHeight;
        }, 10);

        // Change icon
        const icon = toggleBtn.querySelector("i");
        if (icon) {
          icon.classList.remove("fa-sliders-h");
          icon.classList.add("fa-times");
        }
      } else {
        // Hide advanced options with animation
        advancedOptions.style.height = "0";

        // After animation completes, hide the element
        setTimeout(() => {
          advancedOptions.style.display = "none";

          // Change icon back
          const icon = toggleBtn.querySelector("i");
          if (icon) {
            icon.classList.remove("fa-times");
            icon.classList.add("fa-sliders-h");
          }
        }, 300);
      }
    });
  }

  // Initialize load more button if it exists
  const loadMoreBtn = document.getElementById("loadMoreResults");
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", loadMoreResults);
  }
}

/**
 * Fetch properties from the API with filters and sorting
 * @param {Object} params - Search parameters
 * @returns {Promise<Object>} Search results
 */
async function fetchProperties(params = {}) {
  try {
    // Update loading state
    updateLoadingState(true);

    // Get form data
    const form = document.getElementById("propertySearchForm");
    const formData = new FormData(form);

    // Add pagination
    formData.append("page", currentPage);
    formData.append("per_page", itemsPerPage);

    // Add sorting
    const sortBy = document.getElementById("sortBy")?.value || "newest";
    formData.append("sort_by", sortBy);

    // Convert FormData to URLSearchParams for GET request
    const searchParams = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
      // Handle array values (like features[])
      if (key.endsWith("[]")) {
        const values = formData.getAll(key);
        values.forEach((val) => searchParams.append(key, val));
      } else {
        searchParams.append(key, value);
      }
    }

    // Make API request to the modern endpoint
    const response = await fetch(`/api/properties?${searchParams.toString()}`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    // Check if there are more results
    hasMore = data.length >= itemsPerPage;

    return data;
  } catch (error) {
    console.error("Error fetching properties:", error);
    showError("Failed to load properties. Please try again later.");
    return [];
  } finally {
    updateLoadingState(false);
  }
}

/**
 * Create a property card element
 * @param {Object} property - Property data
 * @returns {HTMLElement} Property card element
 */
function createPropertyCard(property) {
  if (!property) return null;

  const card = document.createElement("div");
  card.className = "col-md-6 col-lg-4 mb-4";
  card.innerHTML = `
        <div class="property-card card h-100 border-0 shadow-sm" 
             data-id="${property.id || ""}"
             data-title="${
               property.title ? property.title.replace(/"/g, "&quot;") : ""
             }"
             data-price="${property.price || 0}"
             data-bedrooms="${property.bedrooms || 0}"
             data-bathrooms="${property.bathrooms || 0}"
             data-latitude="${property.latitude || ""}"
             data-longitude="${property.longitude || ""}">
            <div class="position-relative">
                <img src="${
                  property.thumbnail ||
                  "assets/images/properties/placeholder.jpg"
                }" 
                     class="card-img-top property-thumbnail" 
                     alt="${property.title || "Property"}">
                <div class="property-badge">${property.type || "For Sale"}</div>
                <div class="property-price">${formatPrice(property.price)}</div>
                <button class="btn btn-icon btn-light rounded-circle property-favorite" data-property-id="${
                  property.id || ""
                }">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            <div class="card-body">
                <h5 class="card-title text-truncate">${
                  property.title || "Property"
                }</h5>
                <p class="card-text text-muted">
                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                    ${property.address || "Location not specified"}
                </p>
                <div class="property-features d-flex justify-content-between border-top pt-3 mt-3">
                    <div class="feature">
                        <i class="fas fa-bed me-1 text-muted"></i>
                        <span>${property.bedrooms || 0} Beds</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bath me-1 text-muted"></i>
                        <span>${property.bathrooms || 0} Baths</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-ruler-combined me-1 text-muted"></i>
                        <span>${
                          property.area ? property.area + " sq.ft" : "N/A"
                        }</span>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0">
                <a href="property-details.php?id=${
                  property.id || ""
                }" class="btn btn-outline-primary w-100">
                    View Details
                </a>
            </div>
        </div>
    `;
  return card;
}

/**
 * Display search results with filtering and sorting options
 * @param {Array} properties - Array of property objects
 * @param {number} total - Total number of properties
 * @param {boolean} append - Whether to append to existing results
 */
function displaySearchResults(properties, total, append = false) {
  const resultsContainer = document.getElementById("searchResults");
  const resultsCount = document.getElementById("resultsCount");
  const loadMoreBtn = document.getElementById("loadMoreResults");
  const searchResultsHeader = document.getElementById("searchResultsHeader");
  const noResultsMessage = document.getElementById("noResultsMessage");
  const viewToggleButtons = document.getElementById("viewToggleButtons");

  // Update loading state
  isLoading = false;

  // Update results count
  if (resultsCount) {
    resultsCount.textContent = `${total} propert${
      total === 1 ? "y" : "ies"
    } found`;
  }

  // Show/hide no results message
  if (noResultsMessage) {
    if (total === 0 && currentPage === 1) {
      noResultsMessage.classList.remove("d-none");
      if (viewToggleButtons) viewToggleButtons.classList.add("d-none");
    } else {
      noResultsMessage.classList.add("d-none");
      if (viewToggleButtons) viewToggleButtons.classList.remove("d-none");
    }
  }

  // Show results header if it's the first page
  if (currentPage === 1) {
    if (searchResultsHeader) {
      searchResultsHeader.classList.remove("d-none");
    }

    // Clear existing results if not appending
    if (!append) {
      resultsContainer.innerHTML = "";
    }
  }

  // Add properties to results
  if (properties && properties.length > 0) {
    properties.forEach((property) => {
      const propertyCard = createPropertyCard(property);
      if (propertyCard) {
        resultsContainer.appendChild(propertyCard);
      }
    });

    // Show/hide load more button
    if (loadMoreBtn) {
      const remaining = total - currentPage * itemsPerPage;
      loadMoreBtn.classList.toggle("d-none", remaining <= 0);

      // Update the load more button text
      loadMoreBtn.innerHTML =
        remaining > 0
          ? `<span class="loading-spinner d-none"></span> Load ${Math.min(
              remaining,
              itemsPerPage
            )} More`
          : "No More Results";
    }

    // If we're in map view, update the map with the new properties
    if (currentView === "map") {
      const propertyData = Array.from(
        document.querySelectorAll(".property-card")
      ).map((card) => ({
        id: card.dataset.id,
        title: card.dataset.title,
        price: card.dataset.price,
        bedrooms: card.dataset.bedrooms,
        bathrooms: card.dataset.bathrooms,
        latitude: card.dataset.latitude,
        longitude: card.dataset.longitude,
      }));

      updateMap(propertyData);
    }
  } else if (currentPage === 1) {
    // No results found
    resultsContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No properties found matching your criteria. Try adjusting your search filters.
                </div>
            </div>
        `;

    // Hide load more button if no results
    if (loadMoreBtn) {
      loadMoreBtn.classList.add("d-none");
    }
  }

  // Show/hide load more button
  if (loadMoreBtn) {
    loadMoreBtn.style.display = hasMore ? "block" : "none";
  }

  // Scroll to results if not on first page
  if (currentPage > 1) {
    searchResults.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
  updateLoadingState(false);
}

/**
 * Create a property card element
 * @param {Object} property - Property data
 * @returns {HTMLElement} Property card element
 */
function createPropertyCard(property) {
  const col = document.createElement("div");
  col.className = "col-md-6 col-lg-4 mb-4";

  col.innerHTML = `
    <div class="card property-card h-100">
      ${
        property.image_url
          ? `<img src="${property.image_url}" class="card-img-top" alt="${property.title}">`
          : '<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">' +
            '<i class="fas fa-home fa-4x text-muted"></i></div>'
      }
      <div class="card-body">
        <h5 class="card-title">${property.title || "Property"}</h5>
        <p class="text-muted">
          <i class="fas fa-map-marker-alt me-2"></i>${
            property.location || "Location not specified"
          }
        </p>
        <div class="property-features mb-3">
          ${
            property.bedrooms
              ? `<span><i class="fas fa-bed me-1"></i> ${property.bedrooms} Beds</span>`
              : ""
          }
          ${
            property.bathrooms
              ? `<span><i class="fas fa-bath me-1"></i> ${property.bathrooms} Baths</span>`
              : ""
          }
          ${
            property.area
              ? `<span><i class="fas fa-ruler-combined me-1"></i> ${property.area} sq.ft</span>`
              : ""
          }
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-primary">${
            property.price ? formatPrice(property.price) : "Contact for price"
          }</h6>
          <a href="/property-details.php?id=${
            property.id
          }" class="btn btn-sm btn-outline-primary">View Details</a>
        </div>
      </div>
    </div>
  `;

  return col;
}

/**
 * Show no results message
 */
function showNoResults() {
  const searchResultsContainer = document.getElementById(
    "searchResultsContainer"
  );
  if (!searchResultsContainer) return;

  searchResultsContainer.innerHTML = `
    <div class="col-12 text-center py-5">
      <i class="fas fa-search fa-3x mb-3 text-muted"></i>
      <h4>No properties found</h4>
      <p class="text-muted">Try adjusting your search criteria</p>
    </div>
  `;

  const loadMoreBtn = document.getElementById("loadMoreResults");
  if (loadMoreBtn) {
    loadMoreBtn.style.display = "none";
  }

  updateLoadingState(false);
}

/**
 * Show error message
 * @param {string} message - Error message to display
 */
function showError(message) {
  const searchResultsContainer =
    document.getElementById("searchResultsContainer") ||
    document.getElementById("searchResults");
  if (!searchResultsContainer) return;

  searchResultsContainer.innerHTML = `
    <div class="col-12">
      <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        ${message}
      </div>
    </div>
  `;

  updateLoadingState(false);
}

/**
 * Load more results
 */
async function loadMoreResults() {
  if (isLoading || !hasMore) return;

  const loadMoreBtn = document.getElementById("loadMoreResults");
  if (loadMoreBtn) {
    loadMoreBtn.disabled = true;
    loadMoreBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
  }

  try {
    currentPage++;
    currentSearchParams.page = currentPage;

    const response = await fetchProperties(currentSearchParams);

    if (
      response.success &&
      response.properties &&
      response.properties.length > 0
    ) {
      displaySearchResults(response);
    }
  } catch (error) {
    console.error("Error loading more results:", error);
    showError("Failed to load more results. Please try again.");
  } finally {
    if (loadMoreBtn) {
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = "Load More Results";
    }
  }
}

/**
 * Update loading state
 * @param {boolean} loading - Whether the app is in a loading state
 */
function updateLoadingState(loading) {
  isLoading = loading;

  // You can add additional loading state UI updates here
  const loaders = document.querySelectorAll(".loading-spinner");
  loaders.forEach((loader) => {
    loader.style.display = loading ? "inline-block" : "none";
  });
}

/**
 * Format price with commas
 * @param {number|string} price - The price to format
 * @returns {string} Formatted price with ₹ symbol
 */
function formatPrice(price) {
  if (!price) return "₹0";
  const num = parseInt(price);
  return isNaN(num) ? "₹0" : "₹" + num.toLocaleString("en-IN");
}

// Global variables
let map;
let markers = [];
let currentView = "list"; // 'list' or 'map'

// Initialize map
function initMap() {
  if (map) return; // Don't reinitialize if map already exists

  // Default to India view
  map = L.map("propertyMap").setView([20.5937, 78.9629], 5);

  // Add OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(map);

  // Add scale control
  L.control.scale({ imperial: false, metric: true }).addTo(map);
}

// Update map with property markers
function updateMap(properties) {
  // Clear existing markers
  markers.forEach((marker) => map.removeLayer(marker));
  markers = [];

  // Add new markers
  properties.forEach((property) => {
    if (property.latitude && property.longitude) {
      const marker = L.marker([
        parseFloat(property.latitude),
        parseFloat(property.longitude),
      ]).addTo(map).bindPopup(`
                    <div class="map-popup">
                        <h6>${property.title || "Property"}</h6>
                        <p class="mb-1"><strong>${formatPrice(
                          property.price
                        )}</strong></p>
                        <p class="mb-1 text-muted small">${
                          property.bedrooms || 0
                        } Beds | ${property.bathrooms || 0} Baths</p>
                        <a href="property-details.php?id=${
                          property.id
                        }" class="btn btn-sm btn-primary w-100 mt-2">View Details</a>
                    </div>
                `);

      markers.push(marker);
    }
  });

  // Fit map to bounds if we have markers
  if (markers.length > 0) {
    const group = new L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.1));
  }
}

// Toggle between list and map view
function toggleView(view) {
  if (isLoading) return;

  currentView = view;
  const listView = document.getElementById("listView");
  const mapView = document.getElementById("mapView");
  const listBtn = document.getElementById("listViewBtn");
  const mapBtn = document.getElementById("mapViewBtn");
  const viewToggleButtons = document.getElementById("viewToggleButtons");

  if (!listView || !mapView || !listBtn || !mapBtn) return;

  if (view === "map") {
    listView.classList.add("d-none");
    mapView.classList.remove("d-none");
    listBtn.classList.remove("active");
    mapBtn.classList.add("active");

    // Initialize map if not already done
    initMap();

    // Update map with current properties
    const properties = Array.from(
      document.querySelectorAll(".property-card")
    ).map((card) => ({
      id: card.dataset.id,
      title: card.dataset.title,
      price: card.dataset.price,
      bedrooms: card.dataset.bedrooms,
      bathrooms: card.dataset.bathrooms,
      thumbnail: card.querySelector(".property-thumbnail")?.src || "",
      latitude: card.dataset.latitude,
      longitude: card.dataset.longitude,
    }));

    updateMap(properties);
  } else {
    listView.classList.remove("d-none");
    mapView.classList.add("d-none");
    listBtn.classList.add("active");
    mapBtn.classList.remove("active");
  }

  // Store the current view preference
  localStorage.setItem("propertySearchView", view);
}

// Handle window resize for map
function handleResize() {
  if (map && currentView === "map") {
    setTimeout(() => {
      map.invalidateSize();
    }, 100);
  }
}

// Show save search modal
function showSaveSearchModal() {
  const modal = new bootstrap.Modal(document.getElementById("saveSearchModal"));
  const searchNameInput = document.getElementById("searchName");

  // Clear previous input
  searchNameInput.value = "";

  // Show modal
  modal.show();

  // Focus on input
  searchNameInput.focus();
}

// Save search to database
async function saveSearch(name) {
  try {
    const searchForm = document.getElementById("propertySearchForm");
    const formData = new FormData(searchForm);
    const searchParams = {};

    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
      if (value) {
        searchParams[key] = value;
      }
    }

    // Add current view state
    searchParams.view = currentView;

    const response = await fetch("/api/save_search.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        name: name,
        params: searchParams,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to save search");
    }

    // Show success message
    showToast("Search saved successfully", "success");

    // Reload saved searches
    loadSavedSearches();

    return data;
  } catch (error) {
    console.error("Error saving search:", error);
    showToast(error.message || "Failed to save search", "danger");
    throw error;
  }
}

// Load user's saved searches
async function loadSavedSearches() {
  try {
    const savedSearchesList = document.getElementById("savedSearchesList");

    // Show loading state
    savedSearchesList.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="fas fa-spinner fa-spin me-2"></i> Loading saved searches...
            </div>`;

    const response = await fetch("/api/get_saved_searches.php");
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to load saved searches");
    }

    if (data.data.length === 0) {
      savedSearchesList.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <i class="far fa-bookmark me-2"></i> No saved searches found
                </div>`;
      return;
    }

    // Render saved searches
    savedSearchesList.innerHTML = data.data
      .map(
        (search) => `
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h6 class="mb-1">${escapeHtml(search.name)}</h6>
                    <small class="text-muted">${formatDate(
                      search.updated_at
                    )}</small>
                </div>
                <div class="d-flex mt-2">
                    <button class="btn btn-sm btn-outline-primary me-2 load-search" data-id="${
                      search.id
                    }">
                        <i class="fas fa-search me-1"></i> Load
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-search" data-id="${
                      search.id
                    }">
                        <i class="far fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        `
      )
      .join("");

    // Add event listeners to load buttons
    document.querySelectorAll(".load-search").forEach((button) => {
      button.addEventListener("click", loadSavedSearch);
    });

    // Add event listeners to delete buttons
    document.querySelectorAll(".delete-search").forEach((button) => {
      button.addEventListener("click", deleteSavedSearch);
    });
  } catch (error) {
    console.error("Error loading saved searches:", error);
    const savedSearchesList = document.getElementById("savedSearchesList");
    savedSearchesList.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-circle me-2"></i>
                Failed to load saved searches. Please try again later.
            </div>`;
  }
}

// Load a saved search
async function loadSavedSearch(event) {
  try {
    const searchId = event.currentTarget.dataset.id;
    const response = await fetch(`/api/get_saved_search.php?id=${searchId}`);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to load search");
    }

    const search = data.data;
    const searchParams = search.search_params;

    // Set form values
    const form = document.getElementById("propertySearchForm");

    // Reset form
    form.reset();

    // Set each form field from saved search params
    Object.entries(searchParams).forEach(([key, value]) => {
      const input = form.querySelector(`[name="${key}"]`);
      if (input) {
        if (input.type === "checkbox" || input.type === "radio") {
          input.checked = true;
        } else {
          input.value = value;
        }
      }
    });

    // Update price range slider if it exists
    if (
      typeof updatePriceRangeDisplay === "function" &&
      searchParams.min_price &&
      searchParams.max_price
    ) {
      // This assumes you have a price range slider with specific IDs
      const minPrice = parseInt(searchParams.min_price) || 0;
      const maxPrice = parseInt(searchParams.max_price) || 10000000;
      updatePriceRangeDisplay(minPrice, maxPrice);
    }

    // Submit the form to perform the search
    currentPage = 1;
    hasMoreResults = true;
    await fetchProperties();

    // Switch to the saved view if specified
    if (
      searchParams.view &&
      (searchParams.view === "list" || searchParams.view === "map")
    ) {
      toggleView(searchParams.view);
    }

    // Show success message
    showToast(`Loaded search: ${search.name}`, "success");
  } catch (error) {
    console.error("Error loading search:", error);
    showToast(error.message || "Failed to load search", "danger");
  }
}

// Delete a saved search
async function deleteSavedSearch(event) {
  if (!confirm("Are you sure you want to delete this saved search?")) {
    return;
  }

  try {
    const searchId = event.currentTarget.dataset.id;
    const response = await fetch(`/api/delete_search.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: searchId }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to delete search");
    }

    // Remove the search item from the UI
    event.currentTarget.closest(".list-group-item").remove();

    // Show success message
    showToast("Search deleted successfully", "success");
  } catch (error) {
    console.error("Error deleting search:", error);
    showToast(error.message || "Failed to delete search", "danger");
  }
}

// Helper function to show toast messages
function showToast(message, type = "info") {
  const toastContainer = document.getElementById("toastContainer");
  const toastId = "toast-" + Date.now();
  const toast = document.createElement("div");

  toast.className = `toast align-items-center text-white bg-${type} border-0`;
  toast.role = "alert";
  toast.setAttribute("aria-live", "assertive");
  toast.setAttribute("aria-atomic", "true");
  toast.id = toastId;

  toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

  toastContainer.appendChild(toast);

  const bsToast = new bootstrap.Toast(toast, {
    autohide: true,
    delay: 5000,
  });

  bsToast.show();

  // Remove the toast from DOM after it's hidden
  toast.addEventListener("hidden.bs.toast", () => {
    toast.remove();
  });
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
  return unsafe
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Helper function to format date
function formatDate(dateString) {
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// Initialize view toggle buttons
function initViewToggleButtons() {
  const listBtn = document.getElementById("listViewBtn");
  const mapBtn = document.getElementById("mapViewBtn");

  if (listBtn) {
    listBtn.addEventListener("click", (e) => {
      e.preventDefault();
      toggleView("list");
    });
  }

  if (mapBtn) {
    mapBtn.addEventListener("click", (e) => {
      e.preventDefault();
      toggleView("map");
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  // Initialize load more button
  const loadMoreBtn = document.getElementById("loadMoreResults");
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", (e) => {
      e.preventDefault();
      loadMoreResults();
    });
  }

  // Initialize price range slider
  initPriceRangeSlider();

  // Initialize property search
  initPropertySearch();

  // Initialize view toggle buttons
  initViewToggleButtons();

  // Initialize map if map container exists
  if (document.getElementById("map")) {
    initMap();
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Initialize save search button if it exists
  const saveSearchBtn = document.getElementById("saveSearchBtn");
  if (saveSearchBtn) {
    // Show save search button when there are search results
    const searchResults = document.getElementById("searchResults");
    const searchResultsHeader = document.getElementById("searchResultsHeader");
    const savedSearchesSection = document.getElementById(
      "savedSearchesSection"
    );

    // Show/hide save search button based on search results
    const observer = new MutationObserver(() => {
      const hasResults = searchResults && searchResults.children.length > 0;
      saveSearchBtn.classList.toggle("d-none", !hasResults);

      // Show saved searches section when there are search results
      if (savedSearchesSection) {
        savedSearchesSection.classList.toggle("d-none", !hasResults);

        // Load saved searches when the section is shown
        if (hasResults && !savedSearchesSection.dataset.loaded) {
          loadSavedSearches();
          savedSearchesSection.dataset.loaded = "true";
        }
      }
    });

    // Start observing the search results container
    if (searchResults) {
      observer.observe(searchResults, { childList: true, subtree: true });
    }

    // Add click event to save search button
    saveSearchBtn.addEventListener("click", showSaveSearchModal);
  }

  // Handle save search form submission
  const saveSearchForm = document.getElementById("saveSearchForm");
  const confirmSaveBtn = document.getElementById("confirmSaveSearch");

  if (confirmSaveBtn) {
    confirmSaveBtn.addEventListener("click", async () => {
      const searchName = document.getElementById("searchName")?.value.trim();
      if (!searchName) {
        showToast("Please enter a name for your search", "warning");
        return;
      }

      try {
        await saveSearch(searchName);

        // Hide the modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("saveSearchModal")
        );
        if (modal) {
          modal.hide();
        }

        // Clear the input
        if (document.getElementById("searchName")) {
          document.getElementById("searchName").value = "";
        }

        // Reload saved searches
        loadSavedSearches();
      } catch (error) {
        console.error("Error saving search:", error);
      }
    });

    // Handle Enter key in search name input
    const searchNameInput = document.getElementById("searchName");
    if (searchNameInput) {
      searchNameInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          confirmSaveBtn.click();
        }
      });
    }
  }

  // Initialize property search if on search page
  const searchForm = document.getElementById("propertySearchForm");
  if (searchForm) {
    // Initialize form submission
    searchForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      currentPage = 1;
      hasMoreResults = true;
      await fetchProperties();

      // Scroll to results
      const resultsSection = document.getElementById("searchResultsSection");
      if (resultsSection) {
        resultsSection.scrollIntoView({ behavior: "smooth" });
      }
    });

    // Toggle advanced search
    const toggleBtn = document.getElementById("toggleAdvancedSearch");
    const advancedOptions = document.getElementById("advancedSearchOptions");

    if (toggleBtn && advancedOptions) {
      toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        const isHidden =
          advancedOptions.style.display === "none" ||
          !advancedOptions.style.display;
        advancedOptions.style.display = isHidden ? "block" : "none";
        toggleBtn.classList.toggle("active", isHidden);

        // Toggle icon
        const icon = toggleBtn.querySelector("i");
        if (icon) {
          icon.className = isHidden
            ? "fas fa-chevron-up me-1"
            : "fas fa-sliders-h me-1";
        }

        // Store the advanced search state
        localStorage.setItem("advancedSearchOpen", isHidden ? "true" : "false");
      });

      // Restore advanced search state
      if (localStorage.getItem("advancedSearchOpen") === "true") {
        advancedOptions.style.display = "block";
        toggleBtn.classList.add("active");
        const icon = toggleBtn.querySelector("i");
        if (icon) {
          icon.className = "fas fa-chevron-up me-1";
        }
      }
    }

    // Initialize load more button
    const loadMoreBtn = document.getElementById("loadMoreResults");
    if (loadMoreBtn) {
      loadMoreBtn.addEventListener("click", loadMoreResults);
    }

    // Initialize view toggle buttons
    const listViewBtn = document.getElementById("listViewBtn");
    const mapViewBtn = document.getElementById("mapViewBtn");
    const viewToggleButtons = document.getElementById("viewToggleButtons");

    if (listViewBtn && mapViewBtn && viewToggleButtons) {
      listViewBtn.addEventListener("click", () => toggleView("list"));
      mapViewBtn.addEventListener("click", () => toggleView("map"));

      // Restore the last view preference
      const savedView = localStorage.getItem("propertySearchView") || "list";
      toggleView(savedView);

      // Show toggle buttons when there are results
      viewToggleButtons.classList.remove("d-none");
    }

    // Handle window resize
    window.addEventListener("resize", handleResize);

    // Clean up event listeners when unmounting
    return () => {
      window.removeEventListener("resize", handleResize);
    };
  }
});
