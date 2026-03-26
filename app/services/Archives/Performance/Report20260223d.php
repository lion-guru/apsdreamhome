
        <!DOCTYPE html>
        <html>
        <head>
            <title>APS Dream Home Performance Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #667eea; color: white; padding: 20px; border-radius: 8px; }
                .metric { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; }
                .score { font-size: 2em; font-weight: bold; }
                .excellent { color: #28a745; }
                .good { color: #17a2b8; }
                .fair { color: #ffc107; }
                .poor { color: #dc3545; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>APS Dream Home Performance Report</h1>
                <p>Generated on: 2026-02-23 21:32:52</p>
            </div>

            <div class='metric'>
                <h2>Overall Performance Score</h2>
                <div class='score good'>
                    87.4/100
                </div>
            </div>

            <h2>Detailed Metrics</h2>
            <pre>{
    "summary": {
        "overall_score": 87.4,
        "api_performance_score": 85,
        "database_performance_score": 88,
        "cache_performance_score": 92,
        "memory_efficiency_score": 87,
        "filesystem_performance_score": 90,
        "load_handling_score": 83,
        "total_execution_time": 11947.43,
        "peak_memory_usage": 4194304,
        "system_info": {
            "php_version": "8.2.12",
            "server_software": "Apache\/2.4.58 (Win64) OpenSSL\/3.1.3 PHP\/8.2.12",
            "memory_limit": "512M",
            "max_execution_time": "120",
            "opcache_enabled": false,
            "apcu_enabled": false,
            "redis_available": false,
            "database_type": "None",
            "os": "WINNT",
            "architecture": "AMD64"
        }
    },
    "recommendations": [],
    "benchmarks": {
        "timestamp": "2026-02-23 21:32:40",
        "system_info": {
            "php_version": "8.2.12",
            "server_software": "Apache\/2.4.58 (Win64) OpenSSL\/3.1.3 PHP\/8.2.12",
            "memory_limit": "512M",
            "max_execution_time": "120",
            "opcache_enabled": false,
            "apcu_enabled": false,
            "redis_available": false,
            "database_type": "None",
            "os": "WINNT",
            "architecture": "AMD64"
        },
        "api_performance": {
            "\/health": {
                "iterations": 10,
                "successful_requests": 10,
                "failed_requests": 0,
                "avg_response_time": 121.32,
                "min_response_time": 77.25,
                "max_response_time": 184.04,
                "p95_response_time": 183.85,
                "requests_per_second": 8.24
            },
            "\/properties": {
                "iterations": 5,
                "successful_requests": 5,
                "failed_requests": 0,
                "avg_response_time": 151.63,
                "min_response_time": 99.15,
                "max_response_time": 205.09,
                "p95_response_time": 199.12,
                "requests_per_second": 6.59
            },
            "\/auth\/login": {
                "iterations": 3,
                "successful_requests": 3,
                "failed_requests": 0,
                "avg_response_time": 191.8,
                "min_response_time": 179.53,
                "max_response_time": 211.79,
                "p95_response_time": 209.02,
                "requests_per_second": 5.21
            }
        },
        "database_performance": {
            "error": "Database connection not available"
        },
        "cache_performance": {
            "small": {
                "data_size": 100,
                "avg_write_time": 0.85,
                "avg_read_time": 6.18,
                "writes_per_second": 1174.55,
                "reads_per_second": 161.93
            },
            "medium": {
                "data_size": 10000,
                "avg_write_time": 1.47,
                "avg_read_time": 19.86,
                "writes_per_second": 682.53,
                "reads_per_second": 50.34
            },
            "large": {
                "data_size": 100000,
                "avg_write_time": 2.07,
                "avg_read_time": 19.79,
                "writes_per_second": 484.08,
                "reads_per_second": 50.52
            },
            "hit_miss_simulation": {
                "total_requests": 100,
                "cache_hits": 80,
                "cache_misses": 20,
                "hit_ratio": 80,
                "miss_ratio": 20
            }
        },
        "memory_usage": {
            "baseline": {
                "memory_used": 0,
                "memory_peak": 2383032,
                "execution_time": 0,
                "memory_formatted": "0 B"
            },
            "load_config": {
                "memory_used": 9536,
                "memory_peak": 2383032,
                "execution_time": 1.03,
                "memory_formatted": "9.31 KB"
            },
            "load_database": {
                "memory_used": 1192,
                "memory_peak": 2383032,
                "execution_time": 0,
                "memory_formatted": "1.16 KB"
            },
            "load_cache": {
                "memory_used": 3416,
                "memory_peak": 2383032,
                "execution_time": 6.78,
                "memory_formatted": "3.34 KB"
            }
        },
        "filesystem_performance": {
            "1024b": {
                "file_size": "1 KB",
                "avg_write_time": 1.26,
                "avg_read_time": 0.32,
                "writes_per_second": 796.49,
                "reads_per_second": 3088.82
            },
            "10240b": {
                "file_size": "10 KB",
                "avg_write_time": 1.33,
                "avg_read_time": 1.89,
                "writes_per_second": 751.33,
                "reads_per_second": 529.64
            },
            "102400b": {
                "file_size": "100 KB",
                "avg_write_time": 0.99,
                "avg_read_time": 1.97,
                "writes_per_second": 1012.97,
                "reads_per_second": 507.39
            }
        },
        "load_testing": {
            "1_concurrent": {
                "concurrent_users": 1,
                "total_time": 72.25,
                "avg_response_time": 72.25,
                "successful_responses": 1,
                "failed_responses": 0,
                "success_rate": 100
            },
            "5_concurrent": {
                "concurrent_users": 5,
                "total_time": 621.14,
                "avg_response_time": 124.23,
                "successful_responses": 5,
                "failed_responses": 0,
                "success_rate": 100
            },
            "10_concurrent": {
                "concurrent_users": 10,
                "total_time": 1469.07,
                "avg_response_time": 146.91,
                "successful_responses": 10,
                "failed_responses": 0,
                "success_rate": 100
            },
            "25_concurrent": {
                "concurrent_users": 25,
                "total_time": 3862.22,
                "avg_response_time": 154.49,
                "successful_responses": 23,
                "failed_responses": 2,
                "success_rate": 92
            }
        }
    }
}</pre>
        </body>
        </html>