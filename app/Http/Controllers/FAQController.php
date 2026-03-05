<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * FAQController Controller
 * Handles FAQ related operations
 */
class FAQController extends BaseController
{
    /**
     * Index method - Show FAQ page
     * @return void
     */
    public function index()
    {
        $this->render('faq/index', [
            'page_title' => 'FAQ - APS Dream Home',
            'page_description' => 'Frequently asked questions about APS Dream Home services',
            'faqs' => [
                [
                    'id' => 1,
                    'question' => 'What types of properties do you offer?',
                    'answer' => 'We offer a wide range of properties including apartments, villas, commercial spaces, plots, and independent houses across Gorakhpur, Lucknow, and other parts of Uttar Pradesh.',
                    'category' => 'General'
                ],
                [
                    'id' => 2,
                    'question' => 'How do I book a property site visit?',
                    'answer' => 'You can book a site visit by calling our customer care number, filling the contact form on our website, or visiting our nearest office.',
                    'category' => 'Booking'
                ],
                [
                    'id' => 3,
                    'question' => 'What is the process for property registration?',
                    'answer' => 'Our team handles the complete registration process including documentation, legal verification, and registration with relevant authorities.',
                    'category' => 'Legal'
                ],
                [
                    'id' => 4,
                    'question' => 'Do you provide home loan assistance?',
                    'answer' => 'Yes, we have tie-ups with major banks and financial institutions to help you get home loans at competitive interest rates.',
                    'category' => 'Finance'
                ],
                [
                    'id' => 5,
                    'question' => 'Are there any hidden charges?',
                    'answer' => 'No, we believe in complete transparency. All charges are clearly mentioned in the agreement with no hidden costs.',
                    'category' => 'Pricing'
                ],
                [
                    'id' => 6,
                    'question' => 'What after-sales support do you provide?',
                    'answer' => 'We provide complete after-sales support including property management, maintenance services, and assistance with utilities and documentation.',
                    'category' => 'Support'
                ]
            ]
        ], 'layouts/base');
    }
}
