<style>
.success-container {
    max-width: 600px;
    margin: 4rem auto;
    text-align: center;
    padding: 2rem;
}

.success-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 2rem;
}

.success-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 1rem;
}

.success-message {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.property-preview {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: left;
}

.property-preview h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

.property-preview p {
    color: #666;
    margin-bottom: 0;
}

.btn-group {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-outline {
    border: 2px solid #667eea;
    color: #667eea;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Purchase Successful!</h1>
        <p class="success-message">
            Congratulations! Your purchase request for the property has been processed successfully. 
            Our agent will contact you shortly for the next steps.
        </p>
        
        <div class="property-preview">
            <h4><?php echo h($property['title']); ?></h4>
            <p><?php echo h($property['address'] ?? $property['location'] ?? 'Gorakhpur'); ?></p>
            <p><strong>Amount:</strong> ₹<?php echo number_format($property['price'] ?? 0, 0); ?></p>
        </div>
        
        <div class="btn-group">
            <a href="/user/dashboard" class="btn btn-primary">Go to Dashboard</a>
            <a href="/properties" class="btn btn-outline">Browse More</a>
        </div>
    </div>
</div>
