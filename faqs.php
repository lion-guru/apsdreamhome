<?php
/**
 * APS Dream Home - Frequently Asked Questions
 * Common questions and answers about our services
 */

require_once 'core/functions.php';

// Include header
require_once 'includes/templates/header.php';
?>

<!-- Page Header -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3">Frequently Asked Questions</h1>
                <p class="lead mb-4">Find answers to common questions about our real estate services</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="about.php" class="text-white">About</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">FAQs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Common Questions</h2>
                <p class="lead text-muted">Everything you need to know about working with APS Dream Home</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">

                    <!-- General Questions -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-question-circle me-2"></i>General Questions
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    How long has APS Dream Home been in business?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    APS Dream Home has been serving customers in Gorakhpur and surrounding areas for over 8 years. We have established ourselves as a trusted name in real estate with a proven track record of successful projects and satisfied customers.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    What areas do you serve?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We primarily serve Gorakhpur and surrounding areas in Uttar Pradesh. We also have projects in Lucknow, Varanasi, and Allahabad. Our expertise covers residential, commercial, and plot developments across these regions.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    Are you licensed and registered?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, APS Dream Home is a fully licensed and registered real estate company. We comply with all local regulations and maintain proper documentation for all our projects. Our registration and licensing information is available upon request.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Related -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-home me-2"></i>Property Related
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    What types of properties do you offer?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer a wide range of properties including residential apartments, independent houses, villas, commercial spaces, and plots. Our portfolio includes both ready-to-move-in properties and under-construction projects.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    How do I know if a property is legally cleared?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    All our properties undergo thorough legal verification. We provide complete documentation including title deeds, approved building plans, occupancy certificates, and other necessary legal clearances. Our legal team ensures every property meets all regulatory requirements.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                    Can I visit the property before purchasing?
                                </button>
                            </h2>
                            <div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Absolutely! We encourage site visits for all our properties. Our sales team can arrange property visits at convenient times. We believe in complete transparency and want you to see exactly what you're investing in.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Payment -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-money-bill-wave me-2"></i>Pricing & Payment
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading7">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                    What is included in the property price?
                                </button>
                            </h2>
                            <div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Property prices typically include the base cost of the property, registration fees, and basic amenities. Additional costs like stamp duty, legal fees, and maintenance charges (if applicable) are separate and will be clearly explained during the buying process.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading8">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                    Do you offer home loans or financing options?
                                </button>
                            </h2>
                            <div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we have tie-ups with major banks and financial institutions. Our team can help you with home loan applications, documentation, and getting the best interest rates. We guide you through the entire financing process.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading9">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                    Is there a booking amount required?
                                </button>
                            </h2>
                            <div id="collapse9" class="accordion-collapse collapse" aria-labelledby="heading9" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, a refundable booking amount is typically required to reserve a property. This amount varies by property value and will be adjusted against the total payment once the sale is finalized. All booking amounts are fully refundable if you decide not to proceed.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-cogs me-2"></i>Services
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading10">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                    Do you provide property management services?
                                </button>
                            </h2>
                            <div id="collapse10" class="accordion-collapse collapse" aria-labelledby="heading10" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer comprehensive property management services including tenant screening, rent collection, maintenance coordination, and property inspections. Our property management team ensures your investment is well-maintained and profitable.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading11">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                    Can you help with property registration and legal formalities?
                                </button>
                            </h2>
                            <div id="collapse11" class="accordion-collapse collapse" aria-labelledby="heading11" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Absolutely! We provide end-to-end legal assistance including property registration, documentation, stamp duty payment, and liaison with government authorities. Our legal team ensures all paperwork is completed accurately and efficiently.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading12">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                    Do you offer interior design services?
                                </button>
                            </h2>
                            <div id="collapse12" class="accordion-collapse collapse" aria-labelledby="heading12" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we have partnered with experienced interior designers who can help transform your new property into your dream home. We offer customized interior design packages that suit various budgets and preferences.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- After Sales -->
                    <div class="mb-4">
                        <h3 class="h5 mb-3 text-primary">
                            <i class="fas fa-headset me-2"></i>After Sales Support
                        </h3>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading13">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse13" aria-expanded="false" aria-controls="collapse13">
                                    What kind of after-sales support do you provide?
                                </button>
                            </h2>
                            <div id="collapse13" class="accordion-collapse collapse" aria-labelledby="heading13" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We provide comprehensive after-sales support including property handover assistance, defect rectification (within warranty period), maintenance guidance, and ongoing customer support. Our relationship with customers continues long after the sale is completed.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading14">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse14" aria-expanded="false" aria-controls="collapse14">
                                    Is there a warranty on construction quality?
                                </button>
                            </h2>
                            <div id="collapse14" class="accordion-collapse collapse" aria-labelledby="heading14" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we provide structural warranty on all our construction projects. The warranty period varies by project type and will be specified in your sale agreement. We also offer extended warranty options for additional peace of mind.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading15">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse15" aria-expanded="false" aria-controls="collapse15">
                                    How can I contact customer support?
                                </button>
                            </h2>
                            <div id="collapse15" class="accordion-collapse collapse" aria-labelledby="heading15" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can contact our customer support team through multiple channels:
                                    <ul class="mt-2">
                                        <li>Phone: +91-7007444842</li>
                                        <li>Email: support@apsdreamhome.com</li>
                                        <li>Visit our office in Gorakhpur</li>
                                        <li>Through our website contact form</li>
                                    </ul>
                                    Our support team is available Monday to Saturday, 9:00 AM to 7:00 PM.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="mb-3">Still have questions?</h3>
                <p class="lead text-muted mb-4">Our expert team is here to help you with any questions or concerns</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="tel:+917007444842" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone-alt me-2"></i>Call Now
                    </a>
                    <a href="mailto:info@apsdreamhome.com" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-envelope me-2"></i>Email Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.accordion-button {
    font-weight: 600;
    color: #333;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.accordion-button:not(.collapsed) {
    background-color: #667eea;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.accordion-button:focus {
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.25);
    border-color: #667eea;
}

.accordion-body {
    background-color: white;
    border: 1px solid #dee2e6;
    border-top: none;
    padding: 20px;
    line-height: 1.6;
}

.accordion-item {
    margin-bottom: 10px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.section-title {
    color: #333;
    font-weight: 700;
    margin-bottom: 20px;
}
</style>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
