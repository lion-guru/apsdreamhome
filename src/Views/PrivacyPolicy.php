<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
///code
?>
<!DOCTYPE html>
<html lang="en">

<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- Meta Tags -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<link rel="shortcut icon" href="images/favicon.ico">

<!--	Fonts
    ========================================================-->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!--	Css Link
    ========================================================-->
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap-slider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/jquery-ui.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/layerslider.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/color.css', 'css'); ?>" id="color-change">
<link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
<link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">

<!--	Title
    =========================================================-->
<title>APS Dream Homes</title>
</head>
<body>

<!--	Page Loader
============================================================= -->
<div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
    <div class="d-flex justify-content-center y-middle position-relative">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
</div>



<div id="page-wrapper">
    <div class="row">
        <!--	Header One -->
        <!--	Header start  -->
        <?php 
$page_title = "Privacy Policy";
$meta_description = "Read APS Dream Homes' privacy policy to understand how we collect, use, and protect your personal information.";

require_once __DIR__ . '/includes/templates/new_header.php';
?>
        <!--	Header end  -->

        <!--	Banner   
        <div class="banner-full-row page-banner" style="background-image:url('<?php echo get_asset_url('breadcromb.jpg', 'images'); ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0 float-md-right"><b>Legal</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Legal</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div> --->
         <!--	Banner   --->
 
       
            

            <div class="full-row">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2 class="text-secondary double-down-line text-center mb-5">Privacy Policy</h2>
                            <div data-custom-class="body">
                                <div><strong><span data-custom-class="title">PRIVACY POLICY</span></strong></div>
                                <div><strong><span data-custom-class="subtitle">Last updated April 26, 2022</span></strong></div>
                                <div style="line-height: 1.5;">
                                    <span data-custom-class="body_text">
                                        This Privacy Notice for APS DREAM HOMES PVT LTD ('we', 'us', or 'our'), describes how and why we might access, collect, store, use, and/or share ('process') your personal information when you use our services ('Services'), including when you:
                                    </span>
                                </div>
                                <ul>
                                    <li data-custom-class="body_text">Visit our website at <a href="http://www.apsdreamhomes.com" target="_blank" data-custom-class="link">http://www.apsdreamhomes.com</a></li>
                                    <li data-custom-class="body_text">Use our services including plot, home, villa, farm house, and rental properties.</li>
                                    <li data-custom-class="body_text">Engage with us in other related ways, including any sales, marketing, or events.</li>
                                </ul>
                                <div style="line-height: 1.5;">
                                    <strong>Questions or concerns?</strong> Reading this Privacy Notice will help you understand your privacy rights and choices. We are responsible for making decisions about how your personal information is processed. If you do not agree with our policies and practices, please do not use our Services. If you still have any questions or concerns, please contact us at <a href="mailto:apsdreamhomes44@gmail.com" data-custom-class="link">apsdreamhomes44@gmail.com</a>.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>SUMMARY OF KEY POINTS</strong>
                                    <span data-custom-class="body_text">This summary provides key points from our Privacy Notice, but you can find out more details about any of these topics by clicking the link following each key point or by using our table of contents below to find the section you are looking for.</span>
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>What personal information do we process?</strong> When you visit, use, or navigate our Services, we may process personal information depending on how you interact with us and the Services, the choices you make, and the products and features you use.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>Do we process any sensitive personal information?</strong> We do not process sensitive personal information.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>Do we collect any information from third parties?</strong> We may collect information from public databases, marketing partners, social media platforms, and other outside sources.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>How do we process your information?</strong> We process your information to provide, improve, and administer our Services, communicate with you, for security and fraud prevention, and to comply with law.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>In what situations and with which parties do we share personal information?</strong> We may share information in specific situations and with specific third parties.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>How do we keep your information safe?</strong> We have adequate organizational and technical processes and procedures in place to protect your personal information.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>What are your rights?</strong> Depending on where you are located geographically, the applicable privacy law may mean you have certain rights regarding your personal information, including the right to access, correct, or delete your personal data.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>How do you exercise your rights?</strong> The easiest way to exercise your rights is by visiting <a href="https://apsdreamhomes.com/contact.php" target="_blank" data-custom-class="link">https://apsdreamhomes.com/contact.php</a>, or by contacting us.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>Want to learn more about what we do with any information we collect?</strong> Review the Privacy Notice in full.
                                </div>
                                <div style="line-height: 1.5;">
                                    <strong>TABLE OF CONTENTS</strong>
                                    <ol>
                                        <li><a data-custom-class="link" href="#infocollect">WHAT INFORMATION DO WE COLLECT?</a></li>
                                        <li><a data-custom-class="link" href="#infouse">HOW DO WE PROCESS YOUR INFORMATION?</a></li>
                                        <li><a data-custom-class="link" href="#whoshare">WHEN AND WITH WHOM DO WE SHARE YOUR PERSONAL INFORMATION?</a></li>
                                        <li><a data-custom-class="link" href="#cookies">DO WE USE COOKIES AND OTHER TRACKING TECHNOLOGIES?</a></li>
                                        <li><a data-custom-class="link" href="#ai">DO WE OFFER ARTIFICIAL INTELLIGENCE-BASED PRODUCTS?</a></li>
                                        <li><a data-custom-class="link" href="#sociallogins">HOW DO WE HANDLE YOUR SOCIAL LOGINS?</a></li>
                                        <li><a data-custom-class="link" href="#inforetain">HOW LONG DO WE KEEP YOUR INFORMATION?</a></li>
                                        <li><a data-custom-class="link" href="#infosafe">HOW DO WE KEEP YOUR INFORMATION SAFE?</a></li>
                                        <li><a data-custom-class="link" href="#privacyrights">WHAT ARE YOUR PRIVACY RIGHTS?</a></li>
                                        <li><a data-custom-class="link" href="#DNT">CONTROLS FOR DO-NOT-TRACK FEATURES</a></li>
                                        <li><a data-custom-class="link" href="#policyupdates">DO WE MAKE UPDATES TO THIS NOTICE?</a></li>
                                        <li><a data-custom-class="link" href="#contact">HOW CAN YOU CONTACT US ABOUT THIS NOTICE?</a></li>
                                        <li><a data-custom-class="link" href="#request">HOW CAN YOU REVIEW, UPDATE, OR DELETE THE DATA WE COLLECT FROM YOU?</a></li>
                                    </ol>
                                </div>
                                <div id="infocollect" style="line-height: 1.5;">
                                    <strong>1. WHAT INFORMATION DO WE COLLECT?</strong>
                                    <div style="line-height: 1.5;">
                                        <strong>Personal information you disclose to us:</strong> We collect personal information that you voluntarily provide to us when you register on the Services, express an interest in obtaining information about us or our products and Services, when you participate in activities on the Services, or otherwise when you contact us.
                                    </div>
                                    <ul>
                                        <li>Names</li>
                                        <li>Phone numbers</li>
                                        <li>Email addresses</li>
                                        <li>Mailing addresses</li>
                                        <li>Job titles</li>
                                        <li>Usernames</li>
                                        <li>Passwords</li>
                                        <li>Contact preferences</li>
                                        <li>Contact or authentication data</li>
                                        <li>Billing addresses</li>
                                        <li>Debit/credit card numbers</li>
                                    </ul>
                                    <div id="sensitiveinfo" style="line-height: 1.5;">
                                        <strong>Sensitive Information:</strong> We do not process sensitive information.
                                    </div>
                                    <div style="line-height: 1.5;">
                                        <strong>Social Media Login Data:</strong> We may provide you with the option to register with us using your existing social media account details. If you choose to register in this way, we will collect certain profile information about you from the social media provider.
                                    </div>
                                </div>
                                <div id="infouse" style="line-height: 1.5;">
                                    <strong>2. HOW DO WE PROCESS YOUR INFORMATION?</strong>
                                    <div style="line-height: 1.5;">
                                        We process your information to provide, improve, and administer our Services, communicate with you, for security and fraud prevention, and to comply with law.
                                    </div>
                                </div>
                                <div id="whoshare" style="line-height: 1.5;">
                                    <strong>3. WHEN AND WITH WHOM DO WE SHARE YOUR PERSONAL INFORMATION?</strong>
                                    <div style="line-height: 1.5;">
                                        We may share information in specific situations described in this section and/or with the following third parties:
                                    </div>
                                    <ul>
                                        <li>Business Transfers</li>
                                        <li>When we use Google Maps Platform APIs</li>
                                        <li>Business Partners</li>
                                    </ul>
                                </div>
                                <div id="cookies" style="line-height: 1.5;">
                                    <strong>4. DO WE USE COOKIES AND OTHER TRACKING TECHNOLOGIES?</strong>
                                    <div style="line-height: 1.5;">
                                        We may use cookies and other tracking technologies to collect and store your information. You can control cookies through your browser settings.
                                    </div>
                                </div>
                                <div id="ai" style="line-height: 1.5;">
                                    <strong>5. DO WE OFFER ARTIFICIAL INTELLIGENCE-BASED PRODUCTS?</strong>
                                    <div style="line-height: 1.5;">
                                        We offer products, features, or tools powered by artificial intelligence, machine learning, or similar technologies.
                                    </div>
                                </div>
                                <div id="sociallogins" style="line-height: 1.5;">
                                    <strong>6. HOW DO WE HANDLE YOUR SOCIAL LOGINS?</strong>
                                    <div style="line-height: 1.5;">
                                        If you choose to register or log in to our Services using a social media account, we may have access to certain information about you.
                                    </div>
                                </div>
                                <div id="inforetain" style="line-height: 1.5;">
                                    <strong>7. HOW LONG DO WE KEEP YOUR INFORMATION?</strong>
                                    <div style="line-height: 1.5;">
                                        We will only keep your personal information for as long as it is necessary for the purposes set out in this Privacy Notice.
                                    </div>
                                </div>
                                <div id="infosafe" style="line-height: 1.5;">
                                    <strong>8. HOW DO WE KEEP YOUR INFORMATION SAFE?</strong>
                                    <div style="line-height: 1.5;">
                                        We aim to protect your personal information through a system of organizational and technical security measures.
                                    </div>
                                </div>
                                <div id="privacyrights" style="line-height: 1.5;">
                                    <strong>9. WHAT ARE YOUR PRIVACY RIGHTS?</strong>
                                    <div style="line-height: 1.5;">
                                        You may review, change, or terminate your account at any time, depending on your country, province, or state of residence. This may include:
                                        <ul>
                                            <li>The right to access your personal data.</li>
                                            <li>The right to rectify inaccurate personal data.</li>
                                            <li>The right to erase your personal data.</li>
                                            <li>The right to restrict processing of your personal data.</li>
                                            <li>The right to data portability.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="DNT" style="line-height: 1.5;">
                                    <strong>10. CONTROLS FOR DO-NOT-TRACK FEATURES</strong>
                                    <div style="line-height: 1.5;">
                                        Information regarding Do-Not-Track features and how you can opt-out of tracking.
                                    </div>
                                </div>
                                <div id="policyupdates" style="line-height: 1.5;">
                                    <strong>11. DO WE MAKE UPDATES TO THIS NOTICE?</strong>
                                    <div style="line-height: 1.5;">
                                        We may update this privacy policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.
                                    </div>
                                </div>
                                <div id="contact" style="line-height: 1.5;">
                                    <strong>12. HOW CAN YOU CONTACT US ABOUT THIS NOTICE?</strong>
                                    <div style="line-height: 1.5;">
                                        If you have questions or comments about this notice, you may contact us at <a href="mailto:apsdreamhomes44@gmail.com" data-custom-class="link">apsdreamhomes44@gmail.com</a>.
                                    </div>
                                </div>
                                <div id="request" style="line-height: 1.5;">
                                    <strong>13. HOW CAN YOU REVIEW, UPDATE, OR DELETE THE DATA WE COLLECT FROM YOU?</strong>
                                    <div style="line-height: 1.5;">
                                        You can review, update, or delete your personal information by contacting us.
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions Section -->
                            <h2 class="text-secondary double-down-line text-center mb-5 mt-5">Terms and Conditions</h2>
                            <div data-custom-class="body">
                                <div><strong><span data-custom-class="title">TERMS AND CONDITIONS</span></strong></div>
                                <div><strong><span data-custom-class="subtitle">Last updated April 26, 2022</span></strong></div>
                                <div style="line-height: 1.5;">
                                    <span data-custom-class="body_text">
                                        These Terms and Conditions govern your use of our website and services. By accessing or using our services, you agree to be bound by these terms. If you do not agree with any part of these terms, you must not use our services.
                                    </span>
                                </div>
                               <strong>1. Acceptance of Terms</strong>
                        <div>By using our services, you agree to these Terms and Conditions and our Privacy Policy. If you do not agree, you must discontinue using our services.</div>
                    </div>
                    <div>
                        <strong>2. Services Provided</strong>
                        <div>We provide real estate services including but not limited to property sales, rentals, and management. We strive to ensure the accuracy of information provided but make no guarantees.</div>
                    </div>
                    <div>
                        <strong>3. User Responsibilities</strong>
                        <div>You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.</div>
                    </div>
                    <div>
                        <strong>4. Property Listings</strong>
                        <div>All property listings are subject to availability and may change without notice. We reserve the right to modify or discontinue any property listing at any time.</div>
                    </div>
                    <div>
                        <strong>5. Payment Terms</strong>
                        <div>Payment for services must be made in accordance with the pricing structure outlined on our website. All payments are non-refundable unless otherwise stated.</div>
                    </div>
                    <div>
                        <strong>6. Limitation of Liability</strong>
                        <div>In no event shall APS DREAM HOMES PVT LTD be liable for any direct, indirect, incidental, special, consequential, or punitive damages arising from your use of our services.</div>
                    </div>
                    <div>
                        <strong>7. Governing Law</strong>
                        <div>These Terms and Conditions shall be governed by and construed in accordance with the laws of India. Any disputes arising under or in connection with these terms shall be subject to the exclusive jurisdiction of the courts located in India.</div>
                    </div>
                    <div>
                        <strong>8. Changes to Terms</strong>
                        <div>We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting on our website. Your continued use of our services after changes are posted constitutes your acceptance of the new terms.</div>
                    </div>
                    <div>
                        <strong>9. Contact Information</strong>
                        <div>If you have any questions about these Terms and Conditions, please contact us at <a href="mailto:apsdreamhomes44@gmail.com">apsdreamhomes44@gmail.com</a>.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
                                    
                        </div>
                </div>
           
        <!--	Footer   start-->
        <?php 
require_once __DIR__ . '/includes/templates/new_footer.php';
?>
        <!--	Footer   start-->


        <!-- Scroll to top -->
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a>
        <!-- End Scroll To top -->
    </div>
</div>
<!-- Wrapper End -->

<!--	Js Link
============================================================-->
<script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/greensock.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.transitions.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/layerslider.kreaturamedia.jquery.js', 'js'); ?>"></script>
<!--jQuery Layer Slider -->
<script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/owl.carousel.min.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/tmpl.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.dependClass-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/draggable-0.1.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/jquery.slider.js', 'js'); ?>"></script>
<script src="<?php echo get_asset_url('js/wow.js', 'js'); ?>"></script>

<script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>

<!-- css style -->
<style>
        /* General Styles */
        body {
            font-family: 'Muli', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }

        /* Container Styles */
        .full-row {
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Heading Styles */
        h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        strong {
            color: #2980b9;
        }

        /* Paragraph Styles */
        .body_text {
            margin-bottom: 15px;
        }

        /* Link Styles */
        .link {
            color: #2980b9;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
        }

        /* List Styles */
        ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        li {
            margin-bottom: 10px;
        }

        /* Table of Contents Styles */
        ol {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .full-row {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }
        }
        /* General Body Styles */
body {
    font-family: 'Muli', sans-serif;
    background-color: #f9f9f9;
    color: #333;
    line-height: 1.6;
    margin: 0;
    padding: 0;
}

/* Container Styles */
.container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

/* Heading Styles */
h2 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 20px;
}

/* Subheading Styles */
h3 {
    font-size: 24px;
    color: #2980b9;
    margin-top: 20px;
    margin-bottom: 15px;
}

/* Paragraph Styles */
p {
    margin-bottom: 15px;
}

/* List Styles */
ul, ol {
    padding-left: 20px;
    margin-bottom: 20px;
}

/* List Item Styles */
li {
    margin-bottom: 10px;
}

/* Strong Text Styles */
strong {
    color: #2980b9;
}

/* Link Styles */
a {
    color: #2980b9;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #2980b9;
    color: #fff;
    border: none;
    border-radius: 5px;
    text-align: center;
    text-decoration: none;
}

.btn:hover {
    background-color: #1a6a8a;
}

/* Responsive Styles */
@media (max-width: 768px) {
    h2 {
        font-size: 24px;
    }

    h3 {
        font-size: 20px;
    }

    .container {
        padding: 10px;
    }
}

    </style>
</body>

</html>