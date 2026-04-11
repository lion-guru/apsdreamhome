<?php
/**
 * Comprehensive AI Knowledge Base Seeder
 * Seeds smart Q&A patterns for all user roles and scenarios
 */

require_once __DIR__ . '/../app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance();

echo "🤖 Seeding Comprehensive AI Knowledge Base...\n\n";

// Knowledge base data - organized by category and user role relevance
$knowledgeData = [
    // ==================== GENERAL INQUIRIES ====================
    [
        'category' => 'general',
        'question_pattern' => 'hello|hi|namaste|hey',
        'question_variations' => "hello\nhi\nhey there\nnamaste\npranam",
        'keywords' => 'hello, hi, greeting, namaste',
        'answer' => '🙏 Namaste! Welcome to APS Dream Home!\n\nMain aapki property mein madad ke liye yahan hoon.\n\nAap kya jaanna chahte hain?\n🏠 Properties\n💰 Prices\n📍 Locations\n📞 Contact karna'
    ],
    [
        'category' => 'general',
        'question_pattern' => 'aps dream home kya hai|aps kya hai|company kya karti hai',
        'question_variations' => "aps kya hai\naps dream home ka kaam kya hai\ncompany kya karti hai\nabout aps",
        'keywords' => 'aps, about, company, kaam, business',
        'answer' => '🏢 **APS Dream Home** - Premium Real Estate Developer\n\n✅ 10+ Years of Excellence\n✅ 15+ Successful Projects\n✅ 5000+ Happy Customers\n✅ Presence in Gorakhpur, Lucknow, Kushinagar, Varanasi\n\n📞 **Call: +91 92771 21112**\n🌐 **Visit: apsdreamhome.com**'
    ],
    [
        'category' => 'general',
        'question_pattern' => 'thank you|thanks|dhanyawad|shukriya',
        'question_variations' => "thanks\nthank you\ndhanyawad\nshukriya\napka bahut shukriya",
        'keywords' => 'thanks, thank you, dhanyawad',
        'answer' => '😊 **Aapka swagat hai!**\n\nKoi bhi sawaal ho toh pooch sakte hain.\n\n🏠 Hum aapke sapno ka ghar dilaane mein madad karenge!'
    ],
    [
        'category' => 'general',
        'question_pattern' => 'bye|goodbye|alvida|phir milenge',
        'question_variations' => "bye\ngoodbye\nalvida\nphir milenge\nsee you",
        'keywords' => 'bye, goodbye, alvida',
        'answer' => '👋 **Alvida!**\n\nAapka din shubh ho! 🙏\n\nProperty related koi bhi sawaal ho toh vapas aaiye.\n\n📞 **+91 92771 21112** par kabhi bhi call kar sakte hain!'
    ],
    
    // ==================== PROPERTY INQUIRIES ====================
    [
        'category' => 'property',
        'question_pattern' => 'suryoday heights kya hai|suryoday me kya hai',
        'question_variations' => "suryoday heights\nsuryoday colony\nsuryoday me kya hai\nsuryoday kaha hai",
        'keywords' => 'suryoday, heights, colony, project',
        'answer' => '🏗️ **Suryoday Heights** - Premium Residential Project\n\n📍 **Location:** Gorakhpur, Uttar Pradesh\n🏠 **Type:** Residential Plots & Villas\n📐 **Plot Sizes:** 1000 - 5000 sq ft\n💰 **Price:** ₹5.5 Lakh se shuru\n🛣️ **Status:** Ready for possession\n\n✅ Gated Community\n✅ 24/7 Security\n✅ Park & Playground\n✅ Water & Electricity\n\n📞 **Book karne ke liye call karein: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'raghunath city center kya hai|raghunath kaha hai',
        'question_variations' => "raghunath city center\nraghunath kaha hai\nraghunath nagri\nraghunath project",
        'keywords' => 'raghunath, city center, nagri, project',
        'answer' => '🏗️ **Raghunath City Center** - Commercial & Residential Hub\n\n📍 **Location:** Gorakhpur Main Road\n🏢 **Type:** Shops + Residential Flats\n📐 **Shop Size:** 200 - 1000 sq ft\n📐 **Flat Size:** 800 - 1500 sq ft\n💰 **Shop Price:** ₹15 Lakh se\n💰 **Flat Price:** ₹25 Lakh se\n\n✅ Prime Location\n✅ High Footfall\n✅ Parking Available\n✅ Lift Facility\n\n📞 **Details ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'braj radha nagri|braj radha enclave kaha hai',
        'question_variations' => "braj radha nagri\nbraj radha enclave\nbraj radha kaha hai\nbraj radha project",
        'keywords' => 'braj radha, nagri, enclave, lucknow',
        'answer' => '🏗️ **Braj Radha Enclave** - Premium Lucknow Project\n\n📍 **Location:** Lucknow, Uttar Pradesh\n🏠 **Type:** Residential Plots\n📐 **Plot Sizes:** 1500 - 4000 sq ft\n💰 **Price:** ₹8 Lakh se shuru\n\n✅ Near Airport\n✅ Highway Connected\n✅ Green Environment\n✅ Modern Amenities\n\n📞 **Visit ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'plot size kya hai|kitne size ke plot hain',
        'question_variations' => "plot size\nplot kitne bade hain\nkitne size ke plot hain\nminimum plot size",
        'keywords' => 'plot size, area, sq ft, bigha',
        'answer' => '📐 **Plot Size Options:**\n\n🔹 **Small:** 1000 sq ft (20×50)\n🔹 **Medium:** 2000 sq ft (40×50)\n🔹 **Large:** 3000 sq ft (60×50)\n🔹 **Extra Large:** 5000 sq ft (100×50)\n\n💡 **Custom sizes bhi available hain!**\n\n📞 **Requirements batayein: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'residential plot|plots available hain',
        'question_variations' => "residential plot chahiye\nkya plots available hain\nplot book karna hai\nplot lena hai",
        'keywords' => 'plot, residential, available, booking',
        'answer' => '🏠 **Haan, bahut saare plots available hain!**\n\n📍 **Available Locations:**\n• Gorakhpur - Suryoday Heights\n• Lucknow - Braj Radha Enclave\n• Kushinagar - Buddh Bihar Colony\n• Varanasi - Ganga Nagri\n\n💰 **Starting Price:** ₹4.5 Lakh\n\n📞 **Site visit ke liye call karein: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'commercial property|shop dukaan',
        'question_variations' => "commercial space\nshop chahiye\ndukaan lena hai\noffice space",
        'keywords' => 'commercial, shop, dukaan, office',
        'answer' => '🏪 **Commercial Properties Available!**\n\n📍 **Raghunath City Center, Gorakhpur**\n\n🏢 **Options:**\n• Ground Floor Shops: ₹15 Lakh se\n• First Floor Shops: ₹12 Lakh se\n• Office Spaces: ₹10 Lakh se\n• Food Court Spaces: ₹18 Lakh se\n\n✅ High Visibility\n✅ Parking Available\n✅ 24/7 Security\n\n📞 **Investment ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'property',
        'question_pattern' => 'ready to move|possession ready|immediate possession',
        'question_variations' => "ready to move\npossession ready\nimmediate possession\njaldi shifting",
        'keywords' => 'ready, possession, move, immediate',
        'answer' => '✅ **Ready to Move Projects:**\n\n🏠 **Suryoday Heights Phase 1**\n📍 Gorakhpur\n✅ Immediate Possession\n\n🏠 **Raghunath City Center**\n📍 Gorakhpur\n✅ Shops Ready\n✅ Flats Ready\n\n💰 **Special Offer:** Ready projects par 2% discount!\n\n📞 **Visit & Book: +91 92771 21112**'
    ],
    
    // ==================== PRICING & PAYMENT ====================
    [
        'category' => 'pricing',
        'question_pattern' => 'plot ka price kya hai|kitne ka hai|rate kya hai',
        'question_variations' => "plot ka price\nkitne ka plot hai\nrate kya hai\nprice list\nkimmat kya hai",
        'keywords' => 'price, rate, kimmat, cost, ₹',
        'answer' => '💰 **Plot Price List:**\n\n📍 **Gorakhpur (Suryoday Heights):**\n• 1000 sq ft - ₹5.5 Lakh\n• 2000 sq ft - ₹10.5 Lakh\n• 3000 sq ft - ₹15 Lakh\n• 5000 sq ft - ₹24 Lakh\n\n📍 **Lucknow (Braj Radha):**\n• Starting from ₹8 Lakh\n\n💡 **Flexible Payment Plans Available!**\n\n📞 **Details ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'pricing',
        'question_pattern' => 'discount hai|offer hai|sche mein kya hai',
        'question_variations' => "kya offer hai\ndiscount available hai\nscheme kya hai\noffer chal raha hai",
        'keywords' => 'discount, offer, scheme, deal',
        'answer' => '🎉 **Current Offers:**\n\n🔥 **Festive Offer:**\n• 5% Discount on Cash Payment\n• Free Registration\n• Free Mutation\n\n🔥 **Payment Plan Offer:**\n• 30% Down Payment\n• Balance in 24 EMI\n• 0% Interest for 12 months\n\n🔥 **Referral Offer:**\n• ₹10,000 per successful referral\n\n⏰ **Limited Time Only!**\n\n📞 **Offer claim karein: +91 92771 21112**'
    ],
    [
        'category' => 'pricing',
        'question_pattern' => 'emi available hai|installment mein de sakte hain',
        'question_variations' => "emi plan\ninstallment mein chahiye\nkya emi available hai\nmonthly payment",
        'keywords' => 'emi, installment, monthly, payment plan',
        'answer' => '💳 **EMI & Payment Plans:**\n\n✅ **Plan 1 - Easy EMI:**\n• 30% Down Payment\n• Balance in 36 Months EMI\n• Minimum Documentation\n\n✅ **Plan 2 - Flexi Pay:**\n• 20% Down Payment\n• 30% in 6 Months\n• 50% on Possession\n\n✅ **Plan 3 - Full Cash:**\n• 5% Discount\n• Immediate Registration\n\n🏦 **Bank Tie-ups:** HDFC, SBI, ICICI\n\n📞 **EMI calculate karein: +91 92771 21112**'
    ],
    [
        'category' => 'pricing',
        'question_pattern' => 'emi calculator|monthly payment kitna hoga',
        'question_variations' => "emi kitni banegi\nmonthly payment\nemi calculate\ninstallment amount",
        'keywords' => 'emi, calculate, monthly, installment',
        'answer' => '🧮 **EMI Calculation (Example):**\n\n💰 **Plot Price:** ₹10 Lakh\n📊 **Down Payment (30%):** ₹3 Lakh\n💳 **Loan Amount:** ₹7 Lakh\n\n📅 **EMI Options:**\n• 12 Months: ₹61,500/month\n• 24 Months: ₹32,300/month\n• 36 Months: ₹22,400/month\n• 60 Months: ₹15,200/month\n\n💡 **Lower EMI ke liye:**\n• Down Payment badhayein\n• Tenure badhayein\n\n📞 **Exact EMI ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'pricing',
        'question_pattern' => 'payment kaise karein|pay kaise karna hai',
        'question_variations' => "payment method\nkaise pay karna hai\npayment options\ncash ya online",
        'keywords' => 'payment, pay, cash, online, bank',
        'answer' => '💳 **Payment Methods:**\n\n✅ **Cash:** Office mein aake deposit karein\n\n✅ **Online Transfer:**\n• Bank: State Bank of India\n• A/c Name: APS Dream Home\n• A/c No: 1234567890\n• IFSC: SBIN0001234\n\n✅ **UPI:**\n• UPI ID: apsdream@upi\n\n✅ **Cheque/DD:**\n• "APS Dream Home" ke naam par\n\n✅ **Credit/Debit Card:**\n• Office par swipe machine available\n\n📞 **Payment help: +91 92771 21112**'
    ],
    [
        'category' => 'pricing',
        'question_pattern' => 'extra charges|hidden cost|registry charge',
        'question_variations' => "koi extra charge hai\nhidden cost hai\nregistry kitne ki hai\nadditional charges",
        'keywords' => 'extra, hidden, charge, registry, cost',
        'answer' => '📋 **Additional Charges (Transparent):**\n\n✅ **Registry Charges:**\n• 1% Stamp Duty\n• 1% Registration Fee\n• Legal Fee: ₹5,000\n\n✅ **Maintenance Deposit:**\n• ₹50/sq ft (One time)\n\n✅ **Other:**\n• Mutation: ₹2,000\n• Electricity Connection: As per actual\n\n💡 **NO HIDDEN CHARGES!**\n💡 **Sab kuch likhit mein diya jayega**\n\n📞 **Breakdown ke liye: +91 92771 21112**'
    ],
    
    // ==================== LOCATION & MAPS ====================
    [
        'category' => 'location',
        'question_pattern' => 'suryoday kaha hai|location kya hai|address batao',
        'question_variations' => "kaha hai\naddress kya hai\nlocation batao\nkaha par hai",
        'keywords' => 'location, address, kaha, where',
        'answer' => '📍 **Project Locations:**\n\n🏠 **Suryoday Heights**\n📍 Rambagh, Gorakhpur\n🗺️ **Landmark:** Near Railway Station\n\n🏠 **Raghunath City Center**\n📍 Gorakhpur Main Road\n🗺️ **Landmark:** City Centre Mall ke paas\n\n🏠 **Braj Radha Enclave**\n📍 Lucknow, Airport Road\n\n🏠 **Buddh Bihar Colony**\n📍 Kushinagar\n\n🗺️ **Google Map:** apsdreamhome.com/locations\n\n📞 **Site visit: +91 92771 21112**'
    ],
    [
        'category' => 'location',
        'question_pattern' => 'nearby kya hai|pass mein kya hai|aspaas kya hai',
        'question_variations' => "aspaas kya hai\nnearby facilities\npass mein kya hai\naspar ki suvidha",
        'keywords' => 'nearby, aspaas, facilities, pass',
        'answer' => '🏙️ **Nearby Facilities:**\n\n🚉 **Transport:**\n• Railway Station - 2 km\n• Bus Stand - 1 km\n• Airport - 15 km\n\n🏥 **Hospitals:**\n• District Hospital - 3 km\n• Apollo Clinic - 2 km\n\n🏫 **Schools:**\n• St. Joseph School - 1.5 km\n• Kendriya Vidyalaya - 2 km\n\n🛒 **Markets:**\n• City Mall - 1 km\n• Weekly Market - Walking Distance\n\n🙏 **Temples:**\n• Gorakhnath Mandir - 5 km\n\n📞 **Visit karein: +91 92771 21112**'
    ],
    [
        'category' => 'location',
        'question_pattern' => 'site visit|ghumna hai|dekhna hai',
        'question_variations' => "site visit karna hai\nplot dekhna hai\nghumne aana hai\nvisit schedule",
        'keywords' => 'visit, site, dekhna, ghumna',
        'answer' => '🚗 **Free Site Visit!**\n\n✅ **Schedule:**\n• Monday to Saturday\n• 10 AM to 6 PM\n• Sunday by appointment\n\n✅ **Facilities:**\n• Free Pickup (within 10km)\n• Project Expert Guide\n• Site Map & Brochure\n• Refreshments\n\n✅ **What to Bring:**\n• Identity Proof\n• Payment Capacity Documents\n• Family Members\n\n📞 **Visit book karein: +91 92771 21112**'
    ],
    [
        'category' => 'location',
        'question_pattern' => 'connectivity|road|highway|transport',
        'question_variations' => "road connectivity\nhighway se dur\ntransport facility\nkaise pahunche",
        'keywords' => 'connectivity, road, highway, transport',
        'answer' => '🛣️ **Excellent Connectivity!**\n\n🚗 **Road:**\n• NH-28 se 500m\n• State Highway connected\n• 30ft Internal Roads\n\n🚆 **Rail:**\n• Gorakhpur Junction - 2 km\n• Daily trains to all major cities\n\n✈️ **Air:**\n• Gorakhpur Airport - 15 km\n• Daily flights to Delhi, Mumbai\n\n🚌 **Bus:**\n• Roadways Bus Stand - 1 km\n• Private buses available\n\n📞 **Directions ke liye: +91 92771 21112**'
    ],
    
    // ==================== BOOKING & REGISTRATION ====================
    [
        'category' => 'booking',
        'question_pattern' => 'book kaise karein|plot book karna hai|booking process',
        'question_variations' => "plot book karna hai\nkaise book karein\nbooking ka process\nbook karna hai",
        'keywords' => 'book, booking, process, registration',
        'answer' => '📝 **Plot Booking Process:**\n\n**Step 1:** Site Visit & Plot Selection\n**Step 2:** Application Form Fill\n**Step 3:** Token Money (₹11,000)\n**Step 4:** Agreement Signing\n**Step 5:** Payment as per plan\n\n📋 **Documents Required:**\n• Aadhar Card\n• PAN Card\n• Passport Size Photo\n• Address Proof\n• Income Proof (for EMI)\n\n⏱️ **Processing Time:** Same day booking!\n\n📞 **Book now: +91 92771 21112**'
    ],
    [
        'category' => 'booking',
        'question_pattern' => 'token money|booking amount|initial payment',
        'question_variations' => "token money kitni hai\nbooking amount\ninitial deposit\nshuru mein kitna dena hai",
        'keywords' => 'token, booking, initial, advance',
        'answer' => '💰 **Booking Amount:**\n\n🎯 **Token Money:** ₹11,000\n\n✅ **Includes:**\n• Plot Blocking\n• Application Processing\n• Legal Verification\n• Documentation\n\n✅ **Adjustment:**\n• Token money total price mein adjust hogi\n• Refundable (terms apply)\n• 7 days ki validity\n\n📞 **Token pay karein: +91 92771 21112**'
    ],
    [
        'category' => 'booking',
        'question_pattern' => 'documents kya lagenge|kagaz kya chahiye',
        'question_variations' => "documents kya chahiye\nkagaz kya lagenge\nkaunse documents\nrequired documents",
        'keywords' => 'documents, kagaz, required, papers',
        'answer' => '📄 **Required Documents:**\n\n✅ **Identity Proof (Any 1):**\n• Aadhar Card\n• Voter ID\n• Passport\n• Driving License\n\n✅ **Address Proof:**\n• Aadhar Card\n• Electricity Bill\n• Ration Card\n\n✅ **Income Proof (for EMI):**\n• Salary Slip / Form 16\n• Bank Statement (6 months)\n• ITR (for business)\n\n✅ **Photos:**\n• Passport size - 4 photos\n\n📞 **Verification ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'booking',
        'question_pattern' => 'agreement|registry|mutation|legal process',
        'question_variations' => "agreement kya hoga\nregistry kab hogi\nmutation kaise hoga\nlegal process",
        'keywords' => 'agreement, registry, mutation, legal',
        'answer' => '⚖️ **Legal Process:**\n\n📜 **Step 1: Sale Agreement**\n• Full payment ke baad\n• Terms & Conditions\n• Both parties signature\n\n📜 **Step 2: Registry**\n• Sub-Registrar office\n• Stamp duty & registration fee\n• Same day completion\n\n📜 **Step 3: Mutation**\n• Tehsil mein application\n• Name transfer in records\n• 30-45 days process\n\n💼 **Our Legal Team helps at every step!**\n\n📞 **Legal help: +91 92771 21112**'
    ],
    [
        'category' => 'booking',
        'question_pattern' => 'cancellation|wapas lena|refund policy',
        'question_variations' => "cancel karna hai\nwapas lena hai\nrefund milega\ncancellation policy",
        'keywords' => 'cancel, refund, wapas, policy',
        'answer' => '🔄 **Cancellation & Refund Policy:**\n\n✅ **Before Agreement:**\n• 100% Token Money Refund\n• Within 15 days of booking\n\n✅ **After Agreement:**\n• 50% of paid amount\n• Within 30 days\n• Subject to terms\n\n❌ **Not Refundable:**\n• After registry completion\n• Processing fees\n• Legal charges\n\n📋 **Process:**\n• Written application\n• Original documents return\n• 15 working days processing\n\n📞 **Queries ke liye: +91 92771 21112**'
    ],
    
    // ==================== HOME LOAN ====================
    [
        'category' => 'loan',
        'question_pattern' => 'home loan|loan available hai|bank loan',
        'question_variations' => "home loan chahiye\nkya loan available hai\nbank loan\nplot loan",
        'keywords' => 'loan, home, bank, finance',
        'answer' => '🏦 **Home Loan Available!**\n\n✅ **Bank Partners:**\n• HDFC Bank\n• State Bank of India\n• ICICI Bank\n• Axis Bank\n• Punjab National Bank\n\n✅ **Loan Features:**\n• Up to 80% of plot value\n• Interest rate: 8.5% - 11%\n• Tenure: 5 - 20 years\n• Quick approval\n\n📋 **Documents:**\n• Income proof\n• Bank statements\n• Property documents\n\n📞 **Loan apply karein: +91 92771 21112**'
    ],
    [
        'category' => 'loan',
        'question_pattern' => 'loan eligibility|kitna loan milega|qualify kaise karein',
        'question_variations' => "loan eligibility\nkitna loan mil sakta hai\nqualify kaise karein\nloan criteria",
        'keywords' => 'eligibility, qualify, criteria, loan amount',
        'answer' => '✅ **Loan Eligibility:**\n\n💼 **Salaried:**\n• Monthly income: ₹25,000+\n• Job stability: 2+ years\n• CIBIL: 650+\n• Age: 21-60 years\n\n💼 **Self-Employed:**\n• Annual income: ₹3 Lakh+\n• Business vintage: 3+ years\n• ITR: Last 2 years\n• CIBIL: 650+\n\n📊 **Loan Amount:**\n• Up to 60× monthly income\n• Or 80% of property value\n• Whichever is lower\n\n📞 **Check eligibility: +91 92771 21112**'
    ],
    [
        'category' => 'loan',
        'question_pattern' => 'interest rate|kitna byaaj|loan ka rate',
        'question_variations' => "interest rate kya hai\nkitna byaaj lagta hai\nloan interest\nrate of interest",
        'keywords' => 'interest, rate, byaaj, roi',
        'answer' => '💰 **Interest Rates:**\n\n🏦 **Bank-wise Rates (Starting):**\n\n• SBI: 8.50% - 9.25%\n• HDFC: 8.65% - 9.50%\n• ICICI: 8.75% - 9.75%\n• Axis: 8.85% - 10.00%\n• PNB: 8.70% - 9.40%\n\n📊 **Example EMI (₹10 Lakh, 10 years):**\n• At 8.5%: ₹12,400/month\n• At 9.5%: ₹13,100/month\n\n💡 **Lower rate ke liye:**\n• Good CIBIL score\n• Higher down payment\n• Existing bank customer\n\n📞 **Best rate ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'loan',
        'question_pattern' => 'loan processing|kitne din mein|approval time',
        'question_variations' => "kitne din mein loan milega\nprocessing time\napproval kitne din mein\nloan sanction",
        'keywords' => 'processing, time, approval, sanction',
        'answer' => '⏱️ **Loan Processing Time:**\n\n✅ **In-Principle Approval:**\n• Same day (online)\n• Basic documents se\n\n✅ **Full Approval:**\n• 3-5 working days\n• Complete verification ke baad\n\n✅ **Disbursement:**\n• 7-10 working days\n• Agreement signing ke baad\n\n📋 **Faster Processing ke liye:**\n• Complete documents\n• Good CIBIL score\n• Quick verification\n\n📞 **Status check: +91 92771 21112**'
    ],
    
    // ==================== LEGAL SERVICES ====================
    [
        'category' => 'legal',
        'question_pattern' => 'legal services|kanooni madad|advocate',
        'question_variations' => "legal services hai\nkanooni madad\nadvocate available\nlawyer",
        'keywords' => 'legal, kanooni, advocate, lawyer',
        'answer' => '⚖️ **Legal Services Available!**\n\n✅ **Free Services (Included):**\n• Property Verification\n• Agreement Drafting\n• Registry Assistance\n• Document Checklist\n\n✅ **Paid Services:**\n• Mutation Application\n• Title Search\n• Legal Opinion\n• NOC Assistance\n\n✅ **Our Legal Team:**\n• 10+ Years Experience\n• Local Expertise\n• All documentation handled\n\n💰 **Legal Package:** ₹15,000 - ₹25,000\n\n📞 **Legal help: +91 92771 21112**'
    ],
    [
        'category' => 'legal',
        'question_pattern' => 'registry|registration|stamp duty',
        'question_variations' => "registry process\nregistration kaise hoga\nstamp duty kitni hai\nregistry charges",
        'keywords' => 'registry, registration, stamp, duty',
        'answer' => '📝 **Registry Process:**\n\n💰 **Stamp Duty:** 7% of property value\n\n💰 **Registration Fee:** 1% of property value\n\n📋 **Process:**\n1. Document Preparation\n2. Stamp Paper Purchase\n3. Sub-Registrar Visit\n4. Biometric Verification\n5. Registration Complete\n\n⏱️ **Time Required:**\n• Preparation: 2-3 days\n• Registry: Same day\n• Certificate: 7 days\n\n💼 **We handle everything!**\n\n📞 **Registry help: +91 92771 21112**'
    ],
    [
        'category' => 'legal',
        'question_pattern' => 'title clear|saf hai|dispute to nhi',
        'question_variations' => "kya plot saf hai\ntitle clear hai\nkoi dispute to nhi\nlegal clean hai",
        'keywords' => 'title, clear, dispute, clean, saf',
        'answer' => '✅ **100% Legal Clear Title!**\n\n📋 **Our Verification:**\n• Title Search (30 years)\n• Encumbrance Certificate\n• Revenue Records Check\n• Court Case Verification\n\n✅ **Documents Provided:**\n• Title Clear Certificate\n• Legal Opinion Report\n• Encumbrance Certificate\n• Mutation Records\n\n🏦 **Bank Approved:**\n• All major banks se approved\n• Loan facility available\n• Government registered\n\n💯 **Puri transparency!**\n\n📞 **Legal report: +91 92771 21112**'
    ],
    
    // ==================== CONTACT INFORMATION ====================
    [
        'category' => 'contact',
        'question_pattern' => 'contact number|phone|call karna hai',
        'question_variations' => "phone number\ncontact details\ncall karna hai\nnumber batao",
        'keywords' => 'contact, phone, number, call',
        'answer' => '📞 **Contact APS Dream Home:**\n\n📱 **Main Helpline:**\n**+91 92771 21112**\n\n📱 **Sales:**\n**+91 92771 21113**\n\n📱 **Support:**\n**+91 92771 21114**\n\n📧 **Email:**\ninfo@apsdreamhome.com\n\n🌐 **Website:**\nwww.apsdreamhome.com\n\n📍 **Head Office:**\nGorakhpur, Uttar Pradesh\n\n⏰ **Timings:**\nMon-Sat: 10 AM - 7 PM\nSun: 11 AM - 4 PM'
    ],
    [
        'category' => 'contact',
        'question_pattern' => 'office address|kaha milega|location',
        'question_variations' => "office kaha hai\naddress batao\nkaha milega\nlocation batao",
        'keywords' => 'office, address, location, milna',
        'answer' => '🏢 **Office Locations:**\n\n📍 **Head Office - Gorakhpur:**\nAPS Dream Home\nNear Railway Station\nGorakhpur, UP - 273001\n\n📍 **City Office - Lucknow:**\nBraj Radha Enclave\nAirport Road\nLucknow, UP\n\n📍 **Branch Office - Kushinagar:**\nBuddh Bihar Colony\nMain Road, Kushinagar\n\n⏰ **Office Hours:**\nMonday-Saturday: 10 AM - 7 PM\nSunday: By Appointment\n\n📞 **Appointment ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'contact',
        'question_pattern' => 'whatsapp|message karna hai|chat karna hai',
        'question_variations' => "whatsapp number\nmessage karna hai\nchat karna hai\nwhatsapp par batao",
        'keywords' => 'whatsapp, message, chat',
        'answer' => '💬 **WhatsApp Support:**\n\n📱 **WhatsApp Number:**\n**+91 92771 21112**\n\n✅ **WhatsApp par bhejein:**\n• Property Photos\n• Documents\n• Queries\n• Appointment Request\n\n✅ **Quick Response:**\n• Business Hours: Within 1 hour\n• After Hours: Next business day\n\n👉 **Click to WhatsApp:**\nwa.me/919277121112\n\n📱 **Scan QR code on website!**'
    ],
    
    // ==================== OTHER SERVICES ====================
    [
        'category' => 'services',
        'question_pattern' => 'interior design|ghar sajwana|renovation',
        'question_variations' => "interior design chahiye\nghar sajwana hai\nrenovation karna hai\ninterior decorator",
        'keywords' => 'interior, design, renovation, decoration',
        'answer' => '🎨 **Interior Design Services!**\n\n✅ **Services:**\n• 2D/3D Design Plans\n• Complete Renovation\n• Modular Kitchen\n• False Ceiling\n• Painting & Polish\n• Furniture Design\n\n✅ **Packages:**\n• Basic: ₹500/sq ft\n• Premium: ₹1000/sq ft\n• Luxury: ₹2000/sq ft\n\n✅ **Free:**\n• Consultation\n• First Design Draft\n• Material Guide\n\n📞 **Interior expert: +91 92771 21112**'
    ],
    [
        'category' => 'services',
        'question_pattern' => 'rental agreement|kiraye ka agreement|rent',
        'question_variations' => "rental agreement chahiye\nkiraye ka kagaz\nrent agreement\nlease deed",
        'keywords' => 'rental, rent, kiraya, lease',
        'answer' => '📄 **Rental Agreement Service:**\n\n✅ **Includes:**\n• Agreement Drafting\n• Police Verification\n• Notary Service\n• Registration\n\n✅ **Charges:**\n• Drafting: ₹2,000\n• Registration: ₹1,000\n• Total: ₹3,000\n\n✅ **Documents Required:**\n• Owner: Property Papers, ID Proof\n• Tenant: ID Proof, Photo\n\n⏱️ **Same Day Service!**\n\n📞 **Agreement ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'services',
        'question_pattern' => 'property tax|tax bharna|nagarnigam',
        'question_variations' => "property tax kitna hai\ntax kaise bhare\nnagarnigam tax\nhouse tax",
        'keywords' => 'tax, property, nagarnigam, house',
        'answer' => '🏛️ **Property Tax Assistance:**\n\n✅ **Services:**\n• Tax Calculation\n• Online Payment\n• Receipt Collection\n• Tax Clearance Certificate\n\n✅ **Charges:**\n• Residential: ₹2/sq ft/year\n• Commercial: ₹5/sq ft/year\n• Our Service Fee: ₹500\n\n✅ **Benefits:**\n• No Penalty\n• Clear Records\n• Mutation Help\n\n📞 **Tax help: +91 92771 21112**'
    ],
    [
        'category' => 'services',
        'question_pattern' => 'mutation|naam transfer|records update',
        'question_variations' => "mutation karna hai\nnaam transfer karna hai\nrecords update\ntehsil ka kaam",
        'keywords' => 'mutation, transfer, naam, tehsil',
        'answer' => '📝 **Mutation (Naam Transfer) Service:**\n\n✅ **Process:**\n1. Application Form\n2. Documents Submission\n3. Tehsil Verification\n4. Mutation Entry\n5. Updated Records\n\n✅ **Documents:**\n• Sale Deed\n• NOC\n• Affidavit\n• Photos\n• Application\n\n✅ **Charges:**\n• Government Fee: ₹2,000\n• Our Service: ₹3,000\n• Total: ₹5,000\n\n⏱️ **30-45 Days Process**\n\n📞 **Mutation ke liye: +91 92771 21112**'
    ],
    
    // ==================== COMPLAINTS & SUPPORT ====================
    [
        'category' => 'complaint',
        'question_pattern' => 'complaint|shikayat|problem hai|galti',
        'question_variations' => "shikayat karna hai\ncomplaint karna hai\nproblem hai\nissue hai",
        'keywords' => 'complaint, shikayat, problem, issue',
        'answer' => '📋 **Complaint & Support:**\n\nWe apologize for any inconvenience!\n\n✅ **Register Complaint:**\n• Call: +91 92771 21112\n• Email: support@apsdreamhome.com\n• Visit: Head Office\n\n✅ **Complaint Types:**\n• Service Issues\n• Payment Problems\n• Documentation Delays\n• Staff Behavior\n• Property Issues\n\n✅ **Resolution Time:**\n• Immediate: Call issues\n• 24 hours: Email queries\n• 3-5 days: Documentation\n• 7 days: Property issues\n\n📞 **Escalation ke liye: +91 92771 21112**'
    ],
    [
        'category' => 'complaint',
        'question_pattern' => 'refund|paise wapas|money back',
        'question_variations' => "paise wapas chahiye\nrefund karna hai\nmoney back\npaise return",
        'keywords' => 'refund, wapas, money, return',
        'answer' => '💰 **Refund Request:**\n\nWe understand your concern!\n\n✅ **Refund Process:**\n1. Written Application\n2. Reason Declaration\n3. Document Submission\n4. Verification (5 days)\n5. Refund Processing (10 days)\n\n✅ **Refund Policy:**\n• Before agreement: 100%\n• After agreement: 50%\n• After registry: No refund\n\n✅ **Modes:**\n• Bank Transfer\n• Cheque\n• Cash (below ₹50,000)\n\n📞 **Refund status: +91 92771 21112**'
    ],
    [
        'category' => 'complaint',
        'question_pattern' => 'delay|late ho raha|time lag raha',
        'question_variations' => "delay ho raha hai\nlate ho raha hai\ntime kyun lag raha\njaldi karo",
        'keywords' => 'delay, late, time, slow',
        'answer' => '⏱️ **Delay Resolution:**\n\nWe sincerely apologize for the delay!\n\n✅ **Common Delays:**\n• Document Verification\n• Bank Loan Processing\n• Government Approvals\n• Registry Queue\n\n✅ **We are doing:**\n• Daily Follow-ups\n• Priority Processing\n• Direct Escalation\n• Regular Updates\n\n✅ **Compensation:**\n• Delays due to us: Penalty as per agreement\n• Genuine delays: Expedited processing\n\n📞 **Status check: +91 92771 21112**'
    ],
];

// Insert into database
$inserted = 0;
$skipped = 0;

foreach ($knowledgeData as $item) {
    try {
        // Check if similar pattern exists
        $existing = $db->fetch(
            "SELECT id FROM ai_knowledge_base WHERE question_pattern = ?",
            [$item['question_pattern']]
        );
        
        if ($existing) {
            echo "⚠️  Skipped (exists): {$item['question_pattern']}\n";
            $skipped++;
            continue;
        }
        
        $db->execute(
            "INSERT INTO ai_knowledge_base 
             (category, question_pattern, question_variations, keywords, answer, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $item['category'],
                $item['question_pattern'],
                $item['question_variations'] ?? '',
                $item['keywords'],
                $item['answer']
            ]
        );
        echo "✅ Added: {$item['category']} - {$item['question_pattern']}\n";
        $inserted++;
    } catch (Exception $e) {
        echo "❌ Error: {$item['question_pattern']} - {$e->getMessage()}\n";
    }
}

echo "\n🎉 Seeding Complete!\n";
echo "✅ Inserted: $inserted\n";
echo "⚠️  Skipped: $skipped\n";
echo "📊 Total in Knowledge Base: " . count($knowledgeData) . " patterns\n";
echo "\n🤖 AI Chatbot is now fully trained!\n";
