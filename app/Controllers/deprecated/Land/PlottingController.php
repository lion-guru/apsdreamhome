<?php

namespace App\Controllers\Land;

use App\Services\Land\PlottingService;
use App\Services\Auth\AuthenticationService;
use App\Core\ViewRenderer;

/**
 * Plotting Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class PlottingController
{
    private $plottingService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->plottingService = new PlottingService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new ViewRenderer();
    }

    /**
     * Show plotting dashboard
     */
    public function dashboard($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access plotting dashboard'];
            $this->redirect('/login');
            return;
        }

        // Get plotting statistics
        $statsResult = $this->plottingService->getPlottingStats();

        $data = [
            'title' => 'Plotting Dashboard - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'stats' => $statsResult['success'] ? $statsResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('land/dashboard', $data);
    }

    /**
     * Show land acquisitions
     */
    public function landAcquisitions($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access land acquisitions'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'farmer_id' => $request['get']['farmer_id'] ?? null,
            'location' => $request['get']['location'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->plottingService->getLandAcquisitions($filters, $limit, $offset);

        $data = [
            'title' => 'Land Acquisitions - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'acquisitions' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('land/acquisitions', $data);
    }

    /**
     * Show add land acquisition form
     */
    public function addLandAcquisition($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to add land acquisition'];
            $this->redirect('/login');
            return;
        }

        $data = [
            'title' => 'Add Land Acquisition - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('land/add_acquisition', $data);
    }

    /**
     * Handle add land acquisition
     */
    public function handleAddLandAcquisition($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'farmer_id' => intval($request['post']['farmer_id'] ?? 0),
            'land_area' => floatval($request['post']['land_area'] ?? 0),
            'land_area_unit' => trim($request['post']['land_area_unit'] ?? 'sqft'),
            'location' => trim($request['post']['location'] ?? ''),
            'village' => trim($request['post']['village'] ?? ''),
            'tehsil' => trim($request['post']['tehsil'] ?? ''),
            'district' => trim($request['post']['district'] ?? ''),
            'state' => trim($request['post']['state'] ?? ''),
            'acquisition_date' => $request['post']['acquisition_date'] ?? date('Y-m-d'),
            'acquisition_cost' => floatval($request['post']['acquisition_cost'] ?? 0),
            'payment_status' => trim($request['post']['payment_status'] ?? 'pending'),
            'land_type' => trim($request['post']['land_type'] ?? 'agricultural'),
            'soil_type' => trim($request['post']['soil_type'] ?? ''),
            'water_source' => trim($request['post']['water_source'] ?? ''),
            'electricity_available' => isset($request['post']['electricity_available']),
            'road_access' => isset($request['post']['road_access']),
            'documents' => json_decode($request['post']['documents'] ?? '[]', true) ?? [],
            'remarks' => trim($request['post']['remarks'] ?? ''),
            'status' => trim($request['post']['status'] ?? 'active'),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        $result = $this->plottingService->addLandAcquisition($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/land/acquisitions');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/land/acquisition/add');
        }

        return $result;
    }

    /**
     * Show plots
     */
    public function plots($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access plots'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'plot_status' => $request['get']['plot_status'] ?? null,
            'plot_type' => $request['get']['plot_type'] ?? null,
            'land_acquisition_id' => $request['get']['land_acquisition_id'] ?? null,
            'corner_plot' => $request['get']['corner_plot'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->plottingService->getPlots($filters, $limit, $offset);

        $data = [
            'title' => 'Plots - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'plots' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('land/plots', $data);
    }

    /**
     * Show add plot form
     */
    public function addPlot($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to add plot'];
            $this->redirect('/login');
            return;
        }

        // Get land acquisitions for dropdown
        $acquisitionsResult = $this->plottingService->getLandAcquisitions(['status' => 'active'], 1000, 0);

        $data = [
            'title' => 'Add Plot - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'acquisitions' => $acquisitionsResult['success'] ? $acquisitionsResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('land/add_plot', $data);
    }

    /**
     * Handle add plot
     */
    public function handleAddPlot($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'land_acquisition_id' => intval($request['post']['land_acquisition_id'] ?? 0),
            'plot_area' => floatval($request['post']['plot_area'] ?? 0),
            'plot_area_unit' => trim($request['post']['plot_area_unit'] ?? 'sqft'),
            'plot_type' => trim($request['post']['plot_type'] ?? 'residential'),
            'dimensions_length' => floatval($request['post']['dimensions_length'] ?? 0) ?: null,
            'dimensions_width' => floatval($request['post']['dimensions_width'] ?? 0) ?: null,
            'corner_plot' => isset($request['post']['corner_plot']),
            'park_facing' => isset($request['post']['park_facing']),
            'road_facing' => isset($request['post']['road_facing']),
            'current_price' => floatval($request['post']['current_price'] ?? 0),
            'base_price' => floatval($request['post']['base_price'] ?? 0) ?: null,
            'plc_amount' => floatval($request['post']['plc_amount'] ?? 0),
            'other_charges' => floatval($request['post']['other_charges'] ?? 0),
            'total_price' => floatval($request['post']['total_price'] ?? 0) ?: null,
            'remarks' => trim($request['post']['remarks'] ?? ''),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        $result = $this->plottingService->addPlot($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/land/plots');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/land/plot/add');
        }

        return $result;
    }

    /**
     * Show plot bookings
     */
    public function bookings($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access bookings'];
            $this->redirect('/login');
            return;
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'customer_id' => $request['get']['customer_id'] ?? null,
            'associate_id' => $request['get']['associate_id'] ?? null
        ];

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $result = $this->plottingService->getPlotBookings($filters, $limit, $offset);

        $data = [
            'title' => 'Plot Bookings - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'bookings' => $result['success'] ? $result['data'] : [],
            'filters' => $filters,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('land/bookings', $data);
    }

    /**
     * Show book plot form
     */
    public function bookPlot($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to book plot'];
            $this->redirect('/login');
            return;
        }

        $plotId = $request['get']['plot_id'] ?? null;

        // Get available plots
        $plotsResult = $this->plottingService->getPlots(['plot_status' => 'available'], 1000, 0);

        $data = [
            'title' => 'Book Plot - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'plots' => $plotsResult['success'] ? $plotsResult['data'] : [],
            'selected_plot_id' => $plotId,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('land/book_plot', $data);
    }

    /**
     * Handle book plot
     */
    public function handleBookPlot($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'plot_id' => intval($request['post']['plot_id'] ?? 0),
            'customer_id' => intval($request['post']['customer_id'] ?? 0),
            'associate_id' => intval($request['post']['associate_id'] ?? 0) ?: null,
            'booking_type' => trim($request['post']['booking_type'] ?? 'direct'),
            'booking_amount' => floatval($request['post']['booking_amount'] ?? 0),
            'total_amount' => floatval($request['post']['total_amount'] ?? 0),
            'payment_plan' => trim($request['post']['payment_plan'] ?? 'lump_sum'),
            'installment_period' => intval($request['post']['installment_period'] ?? 0) ?: null,
            'installment_amount' => floatval($request['post']['installment_amount'] ?? 0) ?: null,
            'payment_method' => trim($request['post']['payment_method'] ?? ''),
            'transaction_id' => trim($request['post']['transaction_id'] ?? ''),
            'booking_date' => $request['post']['booking_date'] ?? date('Y-m-d'),
            'status' => trim($request['post']['status'] ?? 'pending'),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        $result = $this->plottingService->bookPlot($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/land/bookings');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect('/land/plot/book');
        }

        return $result;
    }

    /**
     * Show add payment form
     */
    public function addPayment($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to add payment'];
            $this->redirect('/login');
            return;
        }

        $bookingId = $request['get']['booking_id'] ?? null;

        $data = [
            'title' => 'Add Payment - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'booking_id' => $bookingId,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? [],
            'old_input' => $_SESSION['old_input'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old_input']);

        return $this->viewRenderer->render('land/add_payment', $data);
    }

    /**
     * Handle add payment
     */
    public function handleAddPayment($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'booking_id' => intval($request['post']['booking_id'] ?? 0),
            'amount' => floatval($request['post']['amount'] ?? 0),
            'payment_date' => $request['post']['payment_date'] ?? date('Y-m-d'),
            'payment_method' => trim($request['post']['payment_method'] ?? ''),
            'transaction_id' => trim($request['post']['transaction_id'] ?? ''),
            'installment_number' => intval($request['post']['installment_number'] ?? 0) ?: null,
            'payment_status' => trim($request['post']['payment_status'] ?? 'completed'),
            'receipt_number' => trim($request['post']['receipt_number'] ?? ''),
            'bank_reference' => trim($request['post']['bank_reference'] ?? ''),
            'remarks' => trim($request['post']['remarks'] ?? '')
        ];

        $result = $this->plottingService->addBookingPayment($data);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            $this->redirect('/land/bookings');
        } else {
            $_SESSION['errors'] = [$result['message']];
            $_SESSION['old_input'] = $data;
            $this->redirect("/land/payment/add?booking_id={$data['booking_id']}");
        }

        return $result;
    }

    /**
     * Get land acquisitions (AJAX)
     */
    public function getLandAcquisitions($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'farmer_id' => $request['get']['farmer_id'] ?? null,
            'location' => $request['get']['location'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->plottingService->getLandAcquisitions($filters, $limit, $offset);
    }

    /**
     * Get plots (AJAX)
     */
    public function getPlots($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $filters = [
            'plot_status' => $request['get']['plot_status'] ?? null,
            'plot_type' => $request['get']['plot_type'] ?? null,
            'land_acquisition_id' => $request['get']['land_acquisition_id'] ?? null,
            'corner_plot' => $request['get']['corner_plot'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->plottingService->getPlots($filters, $limit, $offset);
    }

    /**
     * Get plot bookings (AJAX)
     */
    public function getPlotBookings($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $filters = [
            'status' => $request['get']['status'] ?? null,
            'customer_id' => $request['get']['customer_id'] ?? null,
            'associate_id' => $request['get']['associate_id'] ?? null
        ];

        $limit = min(max(intval($request['get']['limit'] ?? 20), 1), 100);
        $offset = max(0, intval($request['get']['offset'] ?? 0));

        return $this->plottingService->getPlotBookings($filters, $limit, $offset);
    }

    /**
     * Get plotting statistics (AJAX)
     */
    public function getPlottingStats($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->plottingService->getPlottingStats();
    }

    /**
     * Add land acquisition (AJAX)
     */
    public function addLandAcquisitionAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'farmer_id' => intval($request['post']['farmer_id'] ?? 0),
            'land_area' => floatval($request['post']['land_area'] ?? 0),
            'land_area_unit' => trim($request['post']['land_area_unit'] ?? 'sqft'),
            'location' => trim($request['post']['location'] ?? ''),
            'village' => trim($request['post']['village'] ?? ''),
            'tehsil' => trim($request['post']['tehsil'] ?? ''),
            'district' => trim($request['post']['district'] ?? ''),
            'state' => trim($request['post']['state'] ?? ''),
            'acquisition_date' => $request['post']['acquisition_date'] ?? date('Y-m-d'),
            'acquisition_cost' => floatval($request['post']['acquisition_cost'] ?? 0),
            'payment_status' => trim($request['post']['payment_status'] ?? 'pending'),
            'land_type' => trim($request['post']['land_type'] ?? 'agricultural'),
            'soil_type' => trim($request['post']['soil_type'] ?? ''),
            'water_source' => trim($request['post']['water_source'] ?? ''),
            'electricity_available' => isset($request['post']['electricity_available']),
            'road_access' => isset($request['post']['road_access']),
            'documents' => json_decode($request['post']['documents'] ?? '[]', true) ?? [],
            'remarks' => trim($request['post']['remarks'] ?? ''),
            'status' => trim($request['post']['status'] ?? 'active'),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        return $this->plottingService->addLandAcquisition($data);
    }

    /**
     * Add plot (AJAX)
     */
    public function addPlotAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'land_acquisition_id' => intval($request['post']['land_acquisition_id'] ?? 0),
            'plot_area' => floatval($request['post']['plot_area'] ?? 0),
            'plot_area_unit' => trim($request['post']['plot_area_unit'] ?? 'sqft'),
            'plot_type' => trim($request['post']['plot_type'] ?? 'residential'),
            'dimensions_length' => floatval($request['post']['dimensions_length'] ?? 0) ?: null,
            'dimensions_width' => floatval($request['post']['dimensions_width'] ?? 0) ?: null,
            'corner_plot' => isset($request['post']['corner_plot']),
            'park_facing' => isset($request['post']['park_facing']),
            'road_facing' => isset($request['post']['road_facing']),
            'current_price' => floatval($request['post']['current_price'] ?? 0),
            'base_price' => floatval($request['post']['base_price'] ?? 0) ?: null,
            'plc_amount' => floatval($request['post']['plc_amount'] ?? 0),
            'other_charges' => floatval($request['post']['other_charges'] ?? 0),
            'total_price' => floatval($request['post']['total_price'] ?? 0) ?: null,
            'remarks' => trim($request['post']['remarks'] ?? ''),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        return $this->plottingService->addPlot($data);
    }

    /**
     * Book plot (AJAX)
     */
    public function bookPlotAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'plot_id' => intval($request['post']['plot_id'] ?? 0),
            'customer_id' => intval($request['post']['customer_id'] ?? 0),
            'associate_id' => intval($request['post']['associate_id'] ?? 0) ?: null,
            'booking_type' => trim($request['post']['booking_type'] ?? 'direct'),
            'booking_amount' => floatval($request['post']['booking_amount'] ?? 0),
            'total_amount' => floatval($request['post']['total_amount'] ?? 0),
            'payment_plan' => trim($request['post']['payment_plan'] ?? 'lump_sum'),
            'installment_period' => intval($request['post']['installment_period'] ?? 0) ?: null,
            'installment_amount' => floatval($request['post']['installment_amount'] ?? 0) ?: null,
            'payment_method' => trim($request['post']['payment_method'] ?? ''),
            'transaction_id' => trim($request['post']['transaction_id'] ?? ''),
            'booking_date' => $request['post']['booking_date'] ?? date('Y-m-d'),
            'status' => trim($request['post']['status'] ?? 'pending'),
            'created_by' => $this->authService->getCurrentUser()['id']
        ];

        return $this->plottingService->bookPlot($data);
    }

    /**
     * Add payment (AJAX)
     */
    public function addPaymentAjax($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $data = [
            'booking_id' => intval($request['post']['booking_id'] ?? 0),
            'amount' => floatval($request['post']['amount'] ?? 0),
            'payment_date' => $request['post']['payment_date'] ?? date('Y-m-d'),
            'payment_method' => trim($request['post']['payment_method'] ?? ''),
            'transaction_id' => trim($request['post']['transaction_id'] ?? ''),
            'installment_number' => intval($request['post']['installment_number'] ?? 0) ?: null,
            'payment_status' => trim($request['post']['payment_status'] ?? 'completed'),
            'receipt_number' => trim($request['post']['receipt_number'] ?? ''),
            'bank_reference' => trim($request['post']['bank_reference'] ?? ''),
            'remarks' => trim($request['post']['remarks'] ?? '')
        ];

        return $this->plottingService->addBookingPayment($data);
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
