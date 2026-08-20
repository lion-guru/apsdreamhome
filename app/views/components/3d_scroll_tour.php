<style>
/* 3D Viewer Fullscreen Layout */
.colony-viewer-wrapper {
    position: relative;
    width: 100%;
    height: 80vh;
    min-height: 600px;
    background: #0f172a;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border: 1px solid #1e293b;
}

#colony-canvas-container {
    width: 100%;
    height: 100%;
    display: block;
}

/* UI Overlay */
.viewer-ui-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 10;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
}

.viewer-header {
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(10px);
    padding: 15px 25px;
    border-radius: 8px;
    display: inline-block;
    pointer-events: auto;
    border: 1px solid rgba(255,255,255,0.1);
}

.viewer-header h3 {
    margin: 0;
    color: #fff;
    font-weight: 700;
}

.viewer-header p {
    margin: 5px 0 0 0;
    color: #94a3b8;
    font-size: 0.9rem;
}

.viewer-controls {
    display: flex;
    gap: 10px;
    pointer-events: auto;
}

.viewer-btn {
    background: rgba(15, 23, 42, 0.8);
    color: #fff;
    border: 1px solid #38bdf8;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.viewer-btn:hover {
    background: #38bdf8;
    color: #0f172a;
}

.viewer-legend {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(10px);
    padding: 15px;
    border-radius: 8px;
    pointer-events: auto;
    border: 1px solid rgba(255,255,255,0.1);
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: #e2e8f0;
    font-size: 0.9rem;
}
.legend-item:last-child { margin-bottom: 0; }
.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    margin-right: 10px;
}

/* Selected Plot Details Modal (In-canvas) */
#plot-details-panel {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 300px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    border: 1px solid #38bdf8;
    padding: 20px;
    color: #fff;
    pointer-events: auto;
    transform: translateX(120%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}
#plot-details-panel.active {
    transform: translateX(0);
}

.close-panel {
    position: absolute;
    top: 15px;
    right: 15px;
    color: #94a3b8;
    cursor: pointer;
}
.close-panel:hover { color: #fff; }
</style>

<!-- Load Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<!-- Load our custom viewer -->
<script src="<?= BASE_URL ?>/public/assets/js/colony-viewer3d.js"></script>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold mb-3">Interactive Colony Explorer</h2>
            <p class="text-muted lead">Drag to rotate, scroll to zoom. Click on any plot to view details.</p>
        </div>
    </div>

    <div class="colony-viewer-wrapper">
        <!-- The Canvas -->
        <div id="colony-canvas-container"></div>
        
        <!-- UI Overlay -->
        <div class="viewer-ui-overlay">
            <div>
                <div class="viewer-header">
                    <h3>Budha City Phase 1</h3>
                    <p>Premium Residential Plots</p>
                </div>
            </div>
            
            <div class="viewer-controls">
                <button class="viewer-btn" id="btn-reset-view"><i class="fas fa-video me-2"></i>Reset Camera</button>
            </div>
        </div>

        <!-- Legend -->
        <div class="viewer-legend">
            <h6 class="text-white mb-3 fw-bold">Map Legend</h6>
            <div class="legend-item">
                <div class="legend-color" style="background: #22c55e;"></div> Available
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #eab308;"></div> Hold
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ef4444;"></div> Sold
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #2d5a27;"></div> Parks / Green Zone
            </div>
        </div>

        <!-- Plot Details Panel -->
        <div id="plot-details-panel">
            <i class="fas fa-times close-panel" id="close-plot-panel"></i>
            <h4 id="pd-title" class="mb-3 text-info fw-bold">Plot A-1</h4>
            <div class="mb-2"><strong>Status:</strong> <span id="pd-status">Available</span></div>
            <div class="mb-2"><strong>Size:</strong> <span id="pd-size">1000 sqft</span></div>
            <div class="mb-4"><strong>Price:</strong> <span id="pd-price" class="text-success fw-bold">₹15,00,000</span></div>
            
            <button class="btn btn-primary w-100" id="pd-book-btn">Book Now</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the Viewer
    const viewer = new ColonyViewer3D('colony-canvas-container', {
        colonyName: "Budha City",
        gridSize: 200
    });

    // Reset View Button
    document.getElementById('btn-reset-view').addEventListener('click', () => {
        viewer.resetCamera();
        document.getElementById('plot-details-panel').classList.remove('active');
    });

    // Handle Plot Selection from Viewer
    document.addEventListener('plotSelected', function(e) {
        const data = e.detail;
        
        // Update panel data
        document.getElementById('pd-title').innerText = 'Plot ' + data.id;
        document.getElementById('pd-size').innerText = data.size;
        document.getElementById('pd-price').innerText = data.price;
        
        const statusEl = document.getElementById('pd-status');
        if(data.status === 'Available') {
            statusEl.innerHTML = '<span class="text-success">Available</span>';
            document.getElementById('pd-book-btn').style.display = 'block';
        } else if(data.status === 'Sold') {
            statusEl.innerHTML = '<span class="text-danger">Sold</span>';
            document.getElementById('pd-book-btn').style.display = 'none';
        } else {
            statusEl.innerHTML = '<span class="text-warning">Hold</span>';
            document.getElementById('pd-book-btn').style.display = 'none';
        }

        // Show panel
        document.getElementById('plot-details-panel').classList.add('active');
    });

    // Close Panel
    document.getElementById('close-plot-panel').addEventListener('click', () => {
        document.getElementById('plot-details-panel').classList.remove('active');
    });
});
</script>
