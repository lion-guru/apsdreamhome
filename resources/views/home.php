<?php $this->layout('layouts.app'); ?>

<?php $this->section('title'); ?>Find Your Dream Home | APS Dream Home<?php $this->endSection(); ?>

<?php $this->section('styles'); ?>
<style>
    .hero {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<?php $this->endSection(); ?>

<section class="hero text-white py-20 md:py-32">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">Find Your Dream Home Today</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Discover the perfect property that matches your lifestyle and budget from our extensive collection.</p>
        
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-4">
            <form action="/properties/search" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="location" class="sr-only">Location</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>
                        <input type="text" id="location" name="location" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Location">
                    </div>
                </div>
                <div class="w-full md:w-48">
                    <label for="type" class="sr-only">Property Type</label>
                    <select id="type" name="type" class="block w-full pl-3 pr-10 py-3 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Any Type</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="villa">Villa</option>
                        <option value="land">Land</option>
                    </select>
                </div>
                <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-md transition">
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Properties</h2>
            <div class="w-20 h-1 bg-indigo-600 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="property-card bg-white rounded-lg overflow-hidden shadow-md">
                <div class="relative">
                    <img src="https://source.unsplash.com/random/600x400?house,<?= $i ?>" alt="Property <?= $i ?>" class="w-full h-56 object-cover">
                    <div class="absolute top-4 right-4 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                        For Sale
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black to-transparent">
                        <div class="text-white font-bold text-xl">$<?= number_format(rand(200000, 1000000)) ?></div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Luxury <?= ($i === 1 ? 'Villa' : ($i % 2 === 0 ? 'Apartment' : 'House')) ?> in <?= ['New York', 'Los Angeles', 'Miami', 'Chicago', 'Houston', 'Phoenix'][$i-1] ?></h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-indigo-600 mr-1"></i>
                        <?= ($i * 2) ?> <?= ($i * 2) === 1 ? 'bedroom' : 'bedrooms' ?>, <?= ($i + 1) ?> <?= ($i + 1) === 1 ? 'bath' : 'baths' ?>, <?= rand(1000, 5000) ?> sqft
                    </p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-yellow-400">
                            <?php for ($j = 0; $j < 5; $j++): ?>
                                <i class="fas fa-star<?= $j < 4 ? ' text-yellow-400' : ' text-gray-300' ?>"></i>
                            <?php endfor; ?>
                            <span class="text-gray-600 ml-2">(<?= rand(10, 100) ?>)</span>
                        </div>
                        <a href="/properties/<?= $i ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">View Details</a>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
        
        <div class="text-center mt-10">
            <a href="/properties" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-md transition">
                View All Properties
            </a>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose Us</h2>
            <div class="w-20 h-1 bg-indigo-600 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-home text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Wide Range of Properties</h3>
                <p class="text-gray-600">Choose from thousands of properties all over the country with new properties added daily.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-hand-holding-usd text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Best Price Guarantee</h3>
                <p class="text-gray-600">We offer the best prices for properties with no hidden charges or fees.</p>
            </div>
            
            <div class="text-center p-6 bg-white rounded-lg shadow-md">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Customer Support</h3>
                <p class="text-gray-600">Our dedicated support team is available round the clock to assist you.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Clients Say</h2>
            <div class="w-20 h-1 bg-indigo-600 mx-auto"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ([
                ['name' => 'John Doe', 'role' => 'Home Buyer', 'content' => 'Found my dream home within a week of searching. The process was smooth and the team was very professional.'],
                ['name' => 'Jane Smith', 'role' => 'Investor', 'content' => 'Great platform for real estate investment. The property listings are accurate and up-to-date.'],
                ['name' => 'Robert Johnson', 'role' => 'First-time Buyer', 'content' => 'As a first-time homebuyer, I was nervous, but APS Dream Home made the process so easy and stress-free.']
            ] as $t): ?>
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <img class="h-12 w-12 rounded-full" src="https://i.pravatar.cc/150?u=<?= md5($t['name']) ?>" alt="<?= htmlspecialchars($t['name']) ?>">
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold"><?= htmlspecialchars($t['name']) ?></h4>
                        <p class="text-gray-600"><?= htmlspecialchars($t['role']) ?></p>
                    </div>
                </div>
                <p class="text-gray-700 italic">"<?= htmlspecialchars($t['content']) ?>"</p>
                <div class="mt-4 flex text-yellow-400">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-indigo-700 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Find Your Dream Home?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Join thousands of satisfied customers who found their perfect property with us.</p>
        <a href="/properties" class="inline-block bg-white text-indigo-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-md transition">
            Browse Properties
        </a>
    </div>
</section>
