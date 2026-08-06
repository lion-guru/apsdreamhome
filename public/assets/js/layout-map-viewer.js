/**
 * Interactive SVG Plot Layout Viewer Module
 * Handles zooming, panning, interactive plot hover tooltips, and real-time status color updates.
 */

class PlotMapViewer {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`PlotMapViewer: Container #${containerId} not found.`);
            return;
        }

        this.options = Object.assign({
            zoomStep: 0.15,
            minZoom: 0.5,
            maxZoom: 3.0,
            onPlotClick: null,
            onPlotHover: null
        }, options);

        this.zoomLevel = 1.0;
        this.svgElement = null;
        this.plotElements = [];
        this.tooltip = null;

        this.init();
    }

    init() {
        this.svgElement = this.container.querySelector('svg');
        if (!this.svgElement) {
            console.warn('PlotMapViewer: No inline SVG element found in container.');
            return;
        }

        this.createControls();
        this.createTooltip();
        this.bindEvents();
        this.applyPlotStatusColors();
    }

    createControls() {
        const controls = document.createElement('div');
        controls.className = 'plot-map-controls position-absolute top-0 end-0 m-3 d-flex flex-column gap-2 bg-white p-2 rounded shadow-sm z-3';
        controls.innerHTML = `
            <button class="btn btn-sm btn-outline-primary btn-zoom-in" title="Zoom In"><i class="bi bi-zoom-in">+</i></button>
            <button class="btn btn-sm btn-outline-primary btn-zoom-out" title="Zoom Out"><i class="bi bi-zoom-out">-</i></button>
            <button class="btn btn-sm btn-outline-secondary btn-zoom-reset" title="Reset"><i class="bi bi-aspect-ratio">Reset</i></button>
        `;

        this.container.style.position = 'relative';
        this.container.style.overflow = 'hidden';
        this.container.appendChild(controls);

        controls.querySelector('.btn-zoom-in').addEventListener('click', () => this.zoom(this.options.zoomStep));
        controls.querySelector('.btn-zoom-out').addEventListener('click', () => this.zoom(-this.options.zoomStep));
        controls.querySelector('.btn-zoom-reset').addEventListener('click', () => this.resetZoom());
    }

    createTooltip() {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'plot-map-tooltip position-absolute bg-dark text-white p-2 rounded shadow-sm text-xs z-3 d-none pointer-events-none';
        this.container.appendChild(this.tooltip);
    }

    zoom(delta) {
        this.zoomLevel = Math.max(this.options.minZoom, Math.min(this.options.maxZoom, this.zoomLevel + delta));
        if (this.svgElement) {
            this.svgElement.style.transform = `scale(${this.zoomLevel})`;
            this.svgElement.style.transformOrigin = 'center center';
            this.svgElement.style.transition = 'transform 0.2s ease-out';
        }
    }

    resetZoom() {
        this.zoomLevel = 1.0;
        if (this.svgElement) {
            this.svgElement.style.transform = 'scale(1)';
        }
    }

    bindEvents() {
        this.plotElements = this.container.querySelectorAll('[data-plot-id], [data-plot-number]');
        this.plotElements.forEach(plot => {
            plot.style.cursor = 'pointer';
            plot.style.transition = 'fill 0.2s ease, stroke 0.2s ease';

            plot.addEventListener('mouseenter', (e) => this.handlePlotHover(e, plot));
            plot.addEventListener('mouseleave', () => this.hideTooltip());
            plot.addEventListener('click', (e) => this.handlePlotClick(e, plot));
        });
    }

    applyPlotStatusColors() {
        const colorMap = {
            'available': '#28a745', // Green
            'booked': '#dc3545',    // Red
            'hold': '#ffc107',      // Yellow
            'registered': '#007bff',// Blue
            'resell': '#6f42c1'     // Purple
        };

        this.plotElements.forEach(plot => {
            const status = (plot.getAttribute('data-status') || 'available').toLowerCase();
            const fill = colorMap[status] || '#6c757d';
            plot.setAttribute('fill', fill);
            plot.setAttribute('stroke', '#ffffff');
            plot.setAttribute('stroke-width', '1.5');
        });
    }

    handlePlotHover(event, element) {
        const plotId = element.getAttribute('data-plot-id') || 'N/A';
        const plotNum = element.getAttribute('data-plot-number') || 'N/A';
        const status = (element.getAttribute('data-status') || 'Available').toUpperCase();
        const price = element.getAttribute('data-price') ? `₹${Number(element.getAttribute('data-price')).toLocaleString('en-IN')}` : '';

        this.tooltip.innerHTML = `
            <strong>Plot #${plotNum}</strong><br>
            Status: <span class="badge bg-secondary">${status}</span><br>
            ${price ? `Price: ${price}` : ''}
        `;

        const containerRect = this.container.getBoundingClientRect();
        const mouseX = event.clientX - containerRect.left;
        const mouseY = event.clientY - containerRect.top;

        this.tooltip.style.left = `${mouseX + 15}px`;
        this.tooltip.style.top = `${mouseY + 15}px`;
        this.tooltip.classList.remove('d-none');

        if (typeof this.options.onPlotHover === 'function') {
            this.options.onPlotHover(plotId, plotNum, status, element);
        }
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.classList.add('d-none');
        }
    }

    handlePlotClick(event, element) {
        const plotId = element.getAttribute('data-plot-id');
        const plotNum = element.getAttribute('data-plot-number');
        const status = element.getAttribute('data-status');

        if (typeof this.options.onPlotClick === 'function') {
            this.options.onPlotClick(plotId, plotNum, status, element);
        }
    }
}

// Auto-initialize if [data-plot-viewer] attribute exists
document.addEventListener('DOMContentLoaded', () => {
    const viewerElement = document.querySelector('[data-plot-viewer]');
    if (viewerElement) {
        window.plotViewer = new PlotMapViewer(viewerElement.id);
    }
});
