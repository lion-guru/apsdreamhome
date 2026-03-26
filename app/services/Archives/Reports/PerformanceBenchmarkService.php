{
    "timestamp": "2026-02-23 17:33:21",
    "system_info": {
        "php_version": "8.2.12",
        "server_software": "Unknown",
        "memory_limit": "512M",
        "max_execution_time": "0",
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
            "avg_response_time": 104.12,
            "min_response_time": 59.8,
            "max_response_time": 161.66,
            "p95_response_time": 144.29,
            "requests_per_second": 9.6
        },
        "\/properties": {
            "iterations": 5,
            "successful_requests": 5,
            "failed_requests": 0,
            "avg_response_time": 122.96,
            "min_response_time": 86.61,
            "max_response_time": 184.09,
            "p95_response_time": 174.11,
            "requests_per_second": 8.13
        },
        "\/auth\/login": {
            "iterations": 3,
            "successful_requests": 3,
            "failed_requests": 0,
            "avg_response_time": 128.42,
            "min_response_time": 60.13,
            "max_response_time": 192.91,
            "p95_response_time": 186.84,
            "requests_per_second": 7.79
        }
    },
    "database_performance": {
        "error": "Database connection not available"
    },
    "cache_performance": {
        "small": {
            "data_size": 100,
            "avg_write_time": 0.86,
            "avg_read_time": 7.27,
            "writes_per_second": 1158.87,
            "reads_per_second": 137.64
        },
        "medium": {
            "data_size": 10000,
            "avg_write_time": 1.04,
            "avg_read_time": 18.22,
            "writes_per_second": 965.27,
            "reads_per_second": 54.9
        },
        "large": {
            "data_size": 100000,
            "avg_write_time": 1.36,
            "avg_read_time": 21.39,
            "writes_per_second": 733.83,
            "reads_per_second": 46.74
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
            "memory_peak": 2382728,
            "execution_time": 0,
            "memory_formatted": "0 B"
        },
        "load_config": {
            "memory_used": 9536,
            "memory_peak": 2382728,
            "execution_time": 0.91,
            "memory_formatted": "9.31 KB"
        },
        "load_database": {
            "memory_used": 1192,
            "memory_peak": 2382728,
            "execution_time": 0,
            "memory_formatted": "1.16 KB"
        },
        "load_cache": {
            "memory_used": 3416,
            "memory_peak": 2382728,
            "execution_time": 10.78,
            "memory_formatted": "3.34 KB"
        }
    },
    "filesystem_performance": {
        "1024b": {
            "file_size": "1 KB",
            "avg_write_time": 1.1,
            "avg_read_time": 0.29,
            "writes_per_second": 910.18,
            "reads_per_second": 3503.72
        },
        "10240b": {
            "file_size": "10 KB",
            "avg_write_time": 4.14,
            "avg_read_time": 9.26,
            "writes_per_second": 241.35,
            "reads_per_second": 108.01
        },
        "102400b": {
            "file_size": "100 KB",
            "avg_write_time": 2.26,
            "avg_read_time": 2.49,
            "writes_per_second": 442.52,
            "reads_per_second": 402.22
        }
    },
    "load_testing": {
        "1_concurrent": {
            "concurrent_users": 1,
            "total_time": 173.42,
            "avg_response_time": 173.42,
            "successful_responses": 1,
            "failed_responses": 0,
            "success_rate": 100
        },
        "5_concurrent": {
            "concurrent_users": 5,
            "total_time": 878.44,
            "avg_response_time": 175.69,
            "successful_responses": 5,
            "failed_responses": 0,
            "success_rate": 100
        },
        "10_concurrent": {
            "concurrent_users": 10,
            "total_time": 1956.15,
            "avg_response_time": 195.61,
            "successful_responses": 10,
            "failed_responses": 0,
            "success_rate": 100
        },
        "25_concurrent": {
            "concurrent_users": 25,
            "total_time": 5437.4,
            "avg_response_time": 217.5,
            "successful_responses": 24,
            "failed_responses": 1,
            "success_rate": 96
        }
    }
}