<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .faq-section {
            padding: 80px 0;
        }
        .faq-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: none;
        }
        .faq-question {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .faq-question:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .faq-question:not(.collapsed) {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .faq-answer {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 0 0 15px 15px;
        }
        .category-section {
            margin-bottom: 40px;
        }
        .category-title {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .contact-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 20px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Frequently Asked Questions</h1>
                    <p class="lead mb-4">
                        Find answers to common questions about our properties, services, and processes.
                        If you don't find what you're looking for, feel free to contact us.
                    </p>
                    <a href="/contact" class="btn btn-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Still Need Help?
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <!-- General Questions -->
            <div class="category-section">
                <h2 class="category-title">General Questions</h2>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        <i class="fas fa-chevron-down me-2"></i>
                        What types of properties does APS Dream Homes offer?
                    </button>
                    <div id="faq1" class="collapse">
                        <div class="faq-answer">
                            <p>APS Dream Homes offers a wide range of properties including:</p>
                            <ul>
                                <li>Residential apartments and flats</li>
                                <li>Independent villas and houses</li>
                                <li>Residential plots for custom construction</li>
                                <li>Commercial properties and office spaces</li>
                                <li>Mixed-use developments</li>
                            </ul>
                            <p>All our properties are located in prime areas of Gorakhpur and surrounding regions in Eastern UP.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        <i class="fas fa-chevron-down me-2"></i>
                        How long has APS Dream Homes been in business?
                    </button>
                    <div id="faq2" class="collapse">
                        <div class="faq-answer">
                            <p>APS Dream Homes Pvt Ltd was established in 2016 and has been successfully operating for over 8 years. We have completed 15+ projects and delivered more than 500 properties to satisfied customers.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        <i class="fas fa-chevron-down me-2"></i>
                        Is APS Dream Homes a registered company?
                    </button>
                    <div id="faq3" class="collapse">
                        <div class="faq-answer">
                            <p>Yes, APS Dream Homes Pvt Ltd is a legally registered company under the Companies Act 2013. Our registration number is U70109UP2022PTC163047. We operate with complete transparency and follow all legal and regulatory requirements.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Related -->
            <div class="category-section">
                <h2 class="category-title">Property Related</h2>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                        <i class="fas fa-chevron-down me-2"></i>
                        What is the process for booking a property?
                    </button>
                    <div id="faq4" class="collapse">
                        <div class="faq-answer">
                            <p>The property booking process involves:</p>
                            <ol>
                                <li><strong>Property Selection:</strong> Choose your preferred property from our available options</li>
                                <li><strong>Documentation:</strong> Submit required documents (ID proof, address proof, etc.)</li>
                                <li><strong>Token Amount:</strong> Pay the booking amount (typically 10-20% of property value)</li>
                                <li><strong>Agreement:</strong> Sign the sale agreement and complete formalities</li>
                                <li><strong>Payment Schedule:</strong> Follow the agreed payment schedule</li>
                                <li><strong>Possession:</strong> Take possession upon completion and final payment</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                        <i class="fas fa-chevron-down me-2"></i>
                        Do you provide home loans assistance?
                    </button>
                    <div id="faq5" class="collapse">
                        <div class="faq-answer">
                            <p>Yes, we have tie-ups with leading banks and financial institutions including SBI, HDFC, ICICI, and PNB. Our experienced team will help you:</p>
                            <ul>
                                <li>Choose the best home loan option</li>
                                <li>Complete loan documentation</li>
                                <li>Coordinate with banks for faster approval</li>
                                <li>Guide you through the entire loan process</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                        <i class="fas fa-chevron-down me-2"></i>
                        What documents are required for property purchase?
                    </button>
                    <div id="faq6" class="collapse">
                        <div class="faq-answer">
                            <p>Required documents typically include:</p>
                            <h6>For Individuals:</h6>
                            <ul>
                                <li>Aadhar Card/PAN Card</li>
                                <li>Address proof (utility bill, passport)</li>
                                <li>Income proof (salary slips, IT returns)</li>
                                <li>Bank statements (last 6 months)</li>
                                <li>Passport size photographs</li>
                            </ul>
                            <h6>For Companies:</h6>
                            <ul>
                                <li>Company registration certificate</li>
                                <li>PAN Card of company</li>
                                <li>Board resolution for property purchase</li>
                                <li>Authorized signatory documents</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Related -->
            <div class="category-section">
                <h2 class="category-title">Payment & Finance</h2>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                        <i class="fas fa-chevron-down me-2"></i>
                        What are the payment options available?
                    </button>
                    <div id="faq7" class="collapse">
                        <div class="faq-answer">
                            <p>We offer flexible payment options:</p>
                            <ul>
                                <li><strong>Construction Linked Plan:</strong> Payments linked to construction milestones</li>
                                <li><strong>Down Payment Plan:</strong> Higher initial payment with lower EMIs</li>
                                <li><strong>Flexi Payment Plan:</strong> Customized payment schedule</li>
                                <li><strong>Subvention Scheme:</strong> Developer pays EMIs until possession (subject to terms)</li>
                            </ul>
                            <p>All payments can be made through cheque, online transfer, or bank loans.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                        <i class="fas fa-chevron-down me-2"></i>
                        Is GST included in the property price?
                    </button>
                    <div id="faq8" class="collapse">
                        <div class="faq-answer">
                            <p>GST is applicable on under-construction properties as per government regulations:</p>
                            <ul>
                                <li>Affordable housing (under ₹45 lakhs): 1% GST</li>
                                <li>Other properties: 5% GST</li>
                                <li>Completed properties: No GST (only stamp duty and registration)</li>
                            </ul>
                            <p>GST is calculated on the base price and is included in the total cost quoted.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                        <i class="fas fa-chevron-down me-2"></i>
                        What additional costs are involved?
                    </button>
                    <div id="faq9" class="collapse">
                        <div class="faq-answer">
                            <p>Besides the property cost, you should budget for:</p>
                            <ul>
                                <li><strong>Stamp Duty:</strong> 6-7% of property value (varies by state)</li>
                                <li><strong>Registration Charges:</strong> 1-2% of property value</li>
                                <li><strong>Legal Fees:</strong> ₹20,000 - ₹50,000 (varies)</li>
                                <li><strong>Home Loan Processing:</strong> 0.5-1% of loan amount</li>
                                <li><strong>Maintenance Charges:</strong> ₹2-5 per sq ft per month</li>
                                <li><strong>Property Tax:</strong> Annual tax as per local municipal corporation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Construction & Possession -->
            <div class="category-section">
                <h2 class="category-title">Construction & Possession</h2>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                        <i class="fas fa-chevron-down me-2"></i>
                        What is the construction quality like?
                    </button>
                    <div id="faq10" class="collapse">
                        <div class="faq-answer">
                            <p>We maintain the highest construction standards:</p>
                            <ul>
                                <li><strong>Earthquake Resistant:</strong> RCC framed structure designed for seismic zones</li>
                                <li><strong>Quality Materials:</strong> ISI certified steel, cement, and other materials</li>
                                <li><strong>Expert Supervision:</strong> Qualified engineers and architects oversee every project</li>
                                <li><strong>Regular Testing:</strong> Third-party quality checks at every construction stage</li>
                                <li><strong>Warranty:</strong> Structural warranty for specified periods</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                        <i class="fas fa-chevron-down me-2"></i>
                        When can I expect possession?
                    </button>
                    <div id="faq11" class="collapse">
                        <div class="faq-answer">
                            <p>Possession timelines depend on the project stage:</p>
                            <ul>
                                <li><strong>Ready to Move:</strong> Immediate possession (completed projects)</li>
                                <li><strong>Under Construction:</strong> 6-24 months depending on construction stage</li>
                                <li><strong>New Launch:</strong> 24-36 months for project completion</li>
                            </ul>
                            <p>We provide regular construction updates and maintain transparency about project progress.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-card">
                    <button class="faq-question collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                        <i class="fas fa-chevron-down me-2"></i>
                        What amenities are included?
                    </button>
                    <div id="faq12" class="collapse">
                        <div class="faq-answer">
                            <p>Amenities vary by project but commonly include:</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Basic Amenities:</h6>
                                    <ul>
                                        <li>24/7 Security</li>
                                        <li>Power Backup</li>
                                        <li>Water Supply</li>
                                        <li>Parking</li>
                                        <li>Lifts</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Lifestyle Amenities:</h6>
                                    <ul>
                                        <li>Swimming Pool</li>
                                        <li>Gymnasium</li>
                                        <li>Children's Play Area</li>
                                        <li>Landscaped Gardens</li>
                                        <li>Community Hall</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Need Help -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Still have questions?</h4>
                        <p class="text-muted mb-4">Our experienced team is here to help you with any queries about our properties, services, or processes.</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="/contact" class="btn btn-primary">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                            <a href="tel:+919554000001" class="btn btn-outline-primary">
                                <i class="fas fa-phone-alt me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
