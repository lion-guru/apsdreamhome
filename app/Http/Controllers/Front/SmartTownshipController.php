<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use Exception;

/**
 * SmartTownshipController
 * Public Smart Township pages: installation guide + service packages.
 * Static marketing/info content served as first-class pages with a CTA to
 * the nearest colony detail page and the inquiry desk.
 */
class SmartTownshipController extends BaseController
{
    /**
     * GET /smart-township
     * Public installation guide for smart township plots.
     */
    public function installationGuide()
    {
        $guideSteps = [
            [
                'title' => 'Phase 1: Foundation',
                'steps' => [
                    'Site survey and infrastructure readiness check',
                    'Plot boundary + utility conduit marking',
                    'Underground cable trenching for power + data',
                    'Foundation slab sign-off with APS engineering team',
                ],
            ],
            [
                'title' => 'Phase 2: Infrastructure',
                'steps' => [
                    'Smart meter installation and DISCOM connection',
                    'Main distribution board with IoT-ready terminals',
                    'Underground fiber-to-home drop pull',
                    'Wi-Fi coverage planning for the plot + house',
                ],
            ],
            [
                'title' => 'Phase 3: Security',
                'steps' => [
                    'CCTV camera points (entry + blind corners)',
                    'Smart locks on main and side doors',
                    'Motion + door sensors with mobile alerts',
                    'Colony gate integration: your vehicle gets recognized',
                ],
            ],
            [
                'title' => 'Phase 4: Automation',
                'steps' => [
                    'Smart bulbs + dimmable remotes "best when paired with rooms"',
                    'Solar water heater + inverter integration',
                    'Split AC + fan scheduling from the app',
                    'Garden irrigation timer',
                ],
            ],
        ];

        return $this->render('iot/installation_guide', [
            'page_title' => 'Smart Township Installation Guide',
            'guide_steps' => $guideSteps,
        ]);
    }

    /**
     * GET /smart-township/packages
     * Public smart township package pricing.
     */
    public function packages()
    {
        $packages = [
            'basic' => [
                'name' => 'Smart Home Basic',
                'price' => 49999,
                'devices' => [
                    'Smart Video Doorbell',
                    '2x Smart Cameras',
                    'Smart Lock (main door)',
                    'Motion Sensor Pack',
                ],
                'features' => [
                    'Mobile app control',
                    '2-year warranty',
                    'Dedicated colony account manager',
                ],
                'support' => 'Standard support — 9 AM to 6 PM, Monday to Saturday',
            ],
            'premium' => [
                'name' => 'Smart Home Premium',
                'price' => 149999,
                'devices' => [
                    'Smart Video Doorbell',
                    '6x Smart Cameras',
                    'Smart Lock (all doors)',
                    'Motion Sensor Pack',
                    'Smart Thermostat',
                    'Smart Lighting Switches (6x)',
                ],
                'features' => [
                    'Voice assistant integration',
                    'Solar generation monitoring',
                    'Priority support with on-site visit',
                ],
                'support' => 'Priority support — 7 AM to 9 PM, 7 days a week',
            ],
            'elite' => [
                'name' => 'Smart Home Elite',
                'price' => 299999,
                'devices' => [
                    'Everything in Premium',
                    'Whole-house automation hub',
                    'Smart irrigation controller',
                    'Solar backup with automatic failover',
                    'Building management dashboard',
                ],
                'features' => [
                    'Custom scenes + automation scripts',
                    'Quarterly on-site health check',
                    'Dedicated support line for 3 years',
                ],
                'support' => 'Dedicated support — 24/7, on-site visits included',
            ],
        ];

        return $this->render('iot/service_packages', [
            'page_title' => 'Smart Township Packages',
            'packages' => $packages,
        ]);
    }
}
