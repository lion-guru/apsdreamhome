/**
 * Team Dashboard JavaScript
 * APS Dream Home - Team Management
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        hierarchyData: window.TEAM_HIERARCHY_DATA || [],
        earningsData: window.TEAM_EARNINGS_DATA || [],
        autoRefreshInterval: 300000, // 5 minutes
        chartColors: {
            primary: '#2962ff',
            secondary: '#4CAF50',
            warning: '#f39c12',
            info: '#17a2b8',
            danger: '#e74c3c'
        }
    };

    // Utility functions
    const Utils = {
        /**
         * Format number with Indian notation (L, Cr)
         */
        formatIndianNumber: function(num) {
            if (num >= 10000000) {
                return (num / 10000000).toFixed(2) + ' Cr';
            } else if (num >= 100000) {
                return (num / 100000).toFixed(2) + ' L';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + ' K';
            }
            return num.toString();
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount) {
            return '₹' + amount.toLocaleString('en-IN');
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        /**
         * Safe JSON parse
         */
        safeParse: function(str, fallback = []) {
            try {
                return JSON.parse(str);
            } catch (e) {
                return fallback;
            }
        }
    };

    // Hierarchy Renderer
    const HierarchyRenderer = {
        container: null,
        data: [],

        init: function(data) {
            this.data = data || CONFIG.hierarchyData;
            this.container = document.getElementById('hierarchy-chart');
            if (!this.container) return;
            this.render();
        },

        render: function() {
            if (!this.container) return;
            
            this.container.innerHTML = '';

            if (!this.data || !this.data.root) {
                this.container.innerHTML = '<div class="text-center py-5"><i class="bi bi-diagram-3 text-muted"></i><p class="text-muted mt-2">No team hierarchy data available</p></div>';
                return;
            }

            // Create root node
            const rootNode = this.createNode(this.data.root, 'root');
            this.container.appendChild(rootNode);

            // Create level containers
            for (let level = 1; level <= 4; level++) {
                if (this.data.levels && this.data.levels[level] && this.data.levels[level].length > 0) {
                    const levelContainer = document.createElement('div');
                    levelContainer.className = 'hierarchy-level mt-4';
                    levelContainer.innerHTML = `<h6 class="text-center mb-3">Level ${level}</h6>`;

                    const levelNodes = document.createElement('div');
                    levelNodes.className = 'hierarchy-nodes d-flex flex-wrap justify-content-center gap-3';

                    this.data.levels[level].forEach(member => {
                        const node = this.createNode(member, `level-${level}`);
                        levelNodes.appendChild(node);
                    });

                    levelContainer.appendChild(levelNodes);
                    this.container.appendChild(levelContainer);
                }
            }
        },

        createNode: function(member, nodeClass) {
            const node = document.createElement('div');
            node.className = `hierarchy-node ${nodeClass}`;

            const avatar = member.avatar || '/assets/images/user/default-avatar.jpg';
            const name = this.escapeHtml(member.name || 'Unknown');
            const type = this.escapeHtml(member.type || 'Member');
            const status = member.status || 'active';
            const referralCode = member.referral_code ? this.escapeHtml(member.referral_code) : '';

            node.innerHTML = `
                <img loading="lazy" src="${avatar}" alt="${name}" class="node-avatar img-fluid">
                <div class="node-name">${name}</div>
                <div class="node-type">${type}
                    <span class="node-status ${status}"></span>
                </div>
                ${referralCode ? `<small class="text-muted d-block">${referralCode}</small>` : ''}
            `;

            node.addEventListener('click', () => {
                // Navigate to member detail if URL is provided
                if (member.detail_url) {
                    window.location.href = member.detail_url;
                }
            });

            return node;
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Chart Manager
    const ChartManager = {
        charts: {},

        init: function(data) {
            this.renderEarningsChart(data || CONFIG.earningsData);
        },

        renderEarningsChart: function(data) {
            const ctx = document.getElementById('teamEarningsChart');
            if (!ctx) return;

            if (!data || data.length === 0) {
                return;
            }

            // Destroy existing chart if exists
            if (this.charts.earnings) {
                this.charts.earnings.destroy();
            }

            this.charts.earnings = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.map(item => item.month),
                    datasets: [{
                        label: 'Team Earnings (₹)',
                        data: data.map(item => item.earnings),
                        borderColor: CONFIG.chartColors.primary,
                        backgroundColor: 'rgba(41, 98, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: CONFIG.chartColors.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Team Earnings: ' + Utils.formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + Utils.formatIndianNumber(value);
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        },

        destroy: function() {
            Object.values(this.charts).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
            this.charts = {};
        }
    };

    // Progress Bar Animator
    const ProgressAnimator = {
        init: function() {
            this.animateProgressBars();
        },

        animateProgressBars: function() {
            const progressBars = document.querySelectorAll('.incentive-progress-bar');
            progressBars.forEach(bar => {
                const progress = bar.getAttribute('data-progress');
                if (progress) {
                    // Animate width
                    setTimeout(() => {
                        bar.style.setProperty('--progress-width', progress + '%');
                    }, 200);
                }
            });
        }
    };

    // Tooltip Manager (for chart tooltips)
    const TooltipManager = {
        init: function() {
            // Initialize Bootstrap tooltips if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
    };

    // Auto Refresh
    const AutoRefresh = {
        intervalId: null,

        start: function(interval) {
            this.stop();
            this.intervalId = setInterval(() => {
                this.refreshData();
            }, interval || CONFIG.autoRefreshInterval);
        },

        stop: function() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },

        refreshData: function() {
            // Placeholder for AJAX refresh
            // Could be implemented to fetch fresh data
            console.log('Auto-refresh triggered');
        }
    };

    // Main initialization
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', onDOMReady);
        } else {
            onDOMReady();
        }
    }

    function onDOMReady() {
        // Initialize progress bars
        ProgressAnimator.init();

        // Initialize hierarchy
        HierarchyRenderer.init(CONFIG.hierarchyData);

        // Initialize charts
        ChartManager.init(CONFIG.earningsData);

        // Initialize tooltips
        TooltipManager.init();

        // Start auto-refresh
        AutoRefresh.start(CONFIG.autoRefreshInterval);

        // Set up progress bar animations from data attributes
        document.querySelectorAll('.incentive-progress-bar').forEach(bar => {
            const progress = bar.getAttribute('data-progress');
            if (progress) {
                bar.style.setProperty('--progress-width', progress + '%');
            }
        });

        console.log('Team Dashboard initialized');
    }

    // Expose modules for external use
    window.TeamDashboard = {
        Config: CONFIG,
        Utils: Utils,
        HierarchyRenderer: HierarchyRenderer,
        ChartManager: ChartManager,
        ProgressAnimator: ProgressAnimator,
        AutoRefresh: AutoRefresh,
        init: init
    };

    // Auto-initialize
    init();

})();