<?php

namespace App\Http\Controllers;

class ErrorController
{
    public function notFound()
    {
        http_response_code(404);
        echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h4>Page Not Found</h4>
                        </div>
                        <div class="card-body text-center">
                            <h2>404 - Page Not Found</h2>
                            <p>The page you are looking for could not be found.</p>
                            <a href="http://localhost." class="btn btn-primary">Go to Homepage</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
}