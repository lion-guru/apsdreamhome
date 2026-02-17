<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mock server variables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('APP_NAME', 'APS Dream Home');
define('BASE_URL', 'http://localhost/');

// Load autoloader
require_once BASE_PATH . '/app/core/autoload.php';

// Load helpers
require_once BASE_PATH . '/app/Helpers/env.php';

use App\Http\Controllers\Public\PageController;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\News;
use App\Models\Career;

// Mock DB Config function to bypass App::config() if needed
// But now we have config() helper which uses App::config()
// So we need to initialize App
$app = new \App\Core\App(BASE_PATH);

// Override db_config for testing if needed
// function db_config() { ... } 
// We rely on config/database.php now via App::config()

echo "Starting Render Test...\n";

// Test 1: Service Model
echo "\nTesting Service Model...\n";
try {
    $services = Service::query()->where('status', 'active')->get();
    echo "Fetched " . count($services) . " services.\n";
    if (count($services) > 0) {
        $first = $services[0];
        echo "First Service: " . $first->title . "\n";
    }
} catch (Throwable $e) {
    echo "Error testing Service Model: " . $e->getMessage() . "\n";
}

// Test 2: TeamMember Model
echo "\nTesting TeamMember Model...\n";
try {
    $team = TeamMember::query()->where('status', 'active')->get();
    echo "Fetched " . count($team) . " team members.\n";
    if (count($team) > 0) {
        $first = $team[0];
        echo "First Team Member: " . $first->name . "\n";
    }
} catch (Throwable $e) {
    echo "Error testing TeamMember Model: " . $e->getMessage() . "\n";
}

// Test 3: PageController Rendering (Services)
echo "\nTesting PageController::services()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->services();
    $output = ob_get_clean();

    if (strpos($output, 'Our Services') !== false) {
        echo "SUCCESS: Services page rendered correctly.\n";
    } else {
        echo "FAILURE: Services page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Services page: " . $e->getMessage() . "\n";
}

// Test 4: PageController Rendering (Team)
echo "\nTesting PageController::team()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->team();
    $output = ob_get_clean();

    if (strpos($output, 'Our Team') !== false) {
        echo "SUCCESS: Team page rendered correctly.\n";
    } else {
        echo "FAILURE: Team page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Team page: " . $e->getMessage() . "\n";
}

// Test 5: PageController Rendering (News)
echo "\nTesting PageController::news()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->news();
    $output = ob_get_clean();

    if (strpos($output, 'News & Updates') !== false) {
        echo "SUCCESS: News page rendered correctly.\n";
    } else {
        echo "FAILURE: News page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering News page: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test 6: PageController Rendering (Careers)
echo "\nTesting PageController::careers()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->careers();
    $output = ob_get_clean();

    if (strpos($output, 'Join Our Team') !== false) {
        echo "SUCCESS: Careers page rendered correctly.\n";
    } else {
        echo "FAILURE: Careers page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Careers page: " . $e->getMessage() . "\n";
}

// Test 7: PageController Rendering (Home)
echo "\nTesting PageController::index() (Home)...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->index();
    $output = ob_get_clean();

    if (strpos($output, 'Home') !== false) {
        echo "SUCCESS: Home page rendered correctly.\n";
    } else {
        echo "FAILURE: Home page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Home page: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test 8: PageController Rendering (About)
echo "\nTesting PageController::about()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->about();
    $output = ob_get_clean();

    if (strpos($output, 'About Us') !== false) {
        echo "SUCCESS: About page rendered correctly.\n";
    } else {
        echo "FAILURE: About page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering About page: " . $e->getMessage() . "\n";
}

// Test 9: PageController Rendering (Contact)
echo "\nTesting PageController::contact()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->contact();
    $output = ob_get_clean();

    if (strpos($output, 'Contact Us') !== false) {
        echo "SUCCESS: Contact page rendered correctly.\n";
    } else {
        echo "FAILURE: Contact page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Contact page: " . $e->getMessage() . "\n";
}

// Test 10: PageController Rendering (Gallery)
echo "\nTesting PageController::gallery()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->gallery();
    $output = ob_get_clean();

    if (strpos($output, 'Gallery') !== false) {
        echo "SUCCESS: Gallery page rendered correctly.\n";
    } else {
        echo "FAILURE: Gallery page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Gallery page: " . $e->getMessage() . "\n";
}

// Test 11: PageController Rendering (Testimonials)
echo "\nTesting PageController::testimonials()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->testimonials();
    $output = ob_get_clean();

    if (strpos($output, 'Testimonials') !== false) {
        echo "SUCCESS: Testimonials page rendered correctly.\n";
    } else {
        echo "FAILURE: Testimonials page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Testimonials page: " . $e->getMessage() . "\n";
}

// Test 12: PageController Rendering (FAQ)
echo "\nTesting PageController::faq()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->faq();
    $output = ob_get_clean();

    if (strpos($output, 'FAQs') !== false) {
        echo "SUCCESS: FAQ page rendered correctly.\n";
    } else {
        echo "FAILURE: FAQ page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering FAQ page: " . $e->getMessage() . "\n";
}

// Test 13: PageController Rendering (Resell)
echo "\nTesting PageController::resell()...\n";
try {
    ob_start();
    $controller = new PageController();
    $controller->resell();
    $output = ob_get_clean();

    if (strpos($output, 'Resell') !== false) {
        echo "SUCCESS: Resell page rendered correctly.\n";
    } else {
        echo "FAILURE: Resell page did not contain expected title.\n";
    }
} catch (Throwable $e) {
    echo "Error rendering Resell page: " . $e->getMessage() . "\n";
}

echo "\nTest Complete.\n";
