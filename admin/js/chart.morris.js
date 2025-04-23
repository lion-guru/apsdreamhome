$(document).ready(function() {
    // Sales Overview Chart - Load data dynamically
    if($('#morrisArea').length > 0) {
        // Fetch sales data from server
        $.ajax({
            url: 'ajax/get-chart-data.php',
            type: 'GET',
            data: { chart: 'sales_overview' },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Morris.Area({
                        element: 'morrisArea',
                        data: response.data || [
                            { y: '2019', a: 0, b: 0 },
                            { y: '2020', a: 150, b: 45 },
                            { y: '2021', a: 60, b: 150 },
                            { y: '2022', a: 180, b: 140 },
                            { y: '2023', a: 100, b: 115 },
                            { y: '2024', a: 175, b: 150 }
                        ],
                        xkey: 'y',
                        ykeys: ['a', 'b'],
                        labels: ['Total Sales', 'Total Revenue'],
                        lineColors: ['#2962ff', '#4fc3f7'],
                        lineWidth: '3px',
                        resize: true,
                        redraw: true
                    });
                } else {
                    console.error('Failed to load sales data:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // Order Status Chart - Load data dynamically
    if($('#morrisLine').length > 0) {
        // Fetch order data from server
        $.ajax({
            url: 'ajax/get-chart-data.php',
            type: 'GET',
            data: { chart: 'order_status' },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Morris.Line({
                        element: 'morrisLine',
                        data: response.data || [
                            { y: 'Jan', a: 80, b: 40 },
                            { y: 'Feb', a: 120, b: 65 },
                            { y: 'Mar', a: 100, b: 35 },
                            { y: 'Apr', a: 75, b: 45 },
                            { y: 'May', a: 100, b: 75 },
                            { y: 'Jun', a: 90, b: 20 },
                            { y: 'Jul', a: 130, b: 85 },
                            { y: 'Aug', a: 140, b: 70 },
                            { y: 'Sep', a: 110, b: 40 },
                            { y: 'Oct', a: 170, b: 95 },
                            { y: 'Nov', a: 120, b: 55 },
                            { y: 'Dec', a: 150, b: 70 }
                        ],
                        xkey: 'y',
                        ykeys: ['a', 'b'],
                        labels: ['Total Orders', 'Completed Orders'],
                        lineColors: ['#1b5a90','#ff9d00'],
                        lineWidth: '3px',
                        resize: true,
                        redraw: true
                    });
                } else {
                    console.error('Failed to load order data:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
})