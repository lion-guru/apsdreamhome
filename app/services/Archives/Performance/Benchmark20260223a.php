{
    "timestamp": "2026-02-23 17:39:28",
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
            "successful_requests": 9,
            "failed_requests": 1,
            "avg_response_time": 145.82,
            "min_response_time": 81.97,
            "max_response_time": 259.47,
            "p95_response_time": 227.21,
            "requests_per_second": 6.86
        },
        "\/properties": {
            "iterations": 5,
            "successful_requests": 4,
            "failed_requests": 1,
            "avg_response_time": 138.54,
            "min_response_time": 102.27,
            "max_response_time": 186.56,
            "p95_response_time": 178.66,
            "requests_per_second": 7.22
        },
        "\/auth\/login": {
            "iterations": 3,
            "successful_requests": 3,
            "failed_requests": 0,
            "avg_response_time": 127.83,
            "min_response_time": 74.51,
            "max_response_time": 202.33,
            "p95_response_time": 192.76,
            "requests_per_second": 7.82
        }
    },
    "database_performance": {
        "error": "Database connection not available"
    },
    "cache_performance": {
        "small": {
            "data_size": 100,
            "avg_write_time": 1.22,
            "avg_read_time": 6.96,
            "writes_per_second": 817.54,
            "reads_per_second": 143.66
        },
        "medium": {
            "data_size": 10000,
            "avg_write_time": 0.99,
            "avg_read_time": 20.75,
            "writes_per_second": 1010.68,
            "reads_per_second": 48.19
        },
        "large": {
            "data_size": 100000,
            "avg_write_time": 1.65,
            "avg_read_time": 22.29,
            "writes_per_second": 606.97,
            "reads_per_second": 44.87
        },
        "hit_miss_simulation": {
            "total_requests": 100,
            "cache_hits": 100,
            "cache_misses": 0,
            "hit_ratio": 100,
            "miss_ratio": 0
        }
    },
    "memory_usage": {
        "baseline": {
            "memory_used": 0,
            "memory_peak": 2383152,
            "execution_time": 0,
            "memory_formatted": "0 B"
        },
        "load_config": {
            "memory_used": 9536,
            "memory_peak": 2383152,
            "execution_time": 0.59,
            "memory_formatted": "9.31 KB"
        },
        "load_database": {
            "memory_used": 1192,
            "memory_peak": 2383152,
            "execution_time": 0,
            "memory_formatted": "1.16 KB"
        },
        "load_cache": {
            "memory_used": 3416,
            "memory_peak": 2383152,
            "execution_time": 8.39,
            "memory_formatted": "3.34 KB"
        }
    },
    "filesystem_performance": {
        "1024b": {
            "file_size": "1 KB",
            "avg_write_time": 14.02,
            "avg_read_time": 0.87,
            "writes_per_second": 71.33,
            "reads_per_second": 1143.64
        },
        "10240b": {
            "file_size": "10 KB",
            "avg_write_time": 1.35,
            "avg_read_time": 2.73,
            "writes_per_second": 741.24,
            "reads_per_second": 366.3
        },
        "102400b": {
            "file_size": "100 KB",
            "avg_write_time": 2.08,
            "avg_read_time": 6.38,
            "writes_per_second": 481.3,
            "reads_per_second": 156.62
        }
    },
    "load_testing": {
        "1_concurrent": {
            "concurrent_users": 1,
            "total_time": 101.86,
            "avg_response_time": 101.86,
            "successful_responses": 1,
            "failed_responses": 0,
            "success_rate": 100
        },
        "5_concurrent": {
            "concurrent_users": 5,
            "total_time": 827.46,
            "avg_response_time": 165.49,
            "successful_responses": 4,
            "failed_responses": 1,
            "success_rate": 80
        },
        "10_concurrent": {
            "concurrent_users": 10,
            "total_time": 1559.53,
            "avg_response_time": 155.95,
            "successful_responses": 10,
            "failed_responses": 0,
            "success_rate": 100
        },
        "25_concurrent": {
            "concurrent_users": 25,
            "total_time": 3667.35,
            "avg_response_time": 146.69,
            "successful_responses": 22,
            "failed_responses": 3,
            "success_rate": 88
        }
    }
}