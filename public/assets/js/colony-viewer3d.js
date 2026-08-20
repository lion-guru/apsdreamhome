/**
 * APS Dream Home - 3D Interactive Colony Viewer
 * Uses Three.js and OrbitControls
 */

class ColonyViewer3D {
    constructor(containerId, config = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;

        // Default Config
        this.config = {
            colonyName: config.colonyName || "APS Dream City",
            gridSize: config.gridSize || 200,
            plotsCount: config.plotsCount || 100,
            roadWidth: 12,
            ...config
        };

        this.plots = [];
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        this.hoveredPlot = null;
        this.tooltip = document.getElementById('plot-tooltip') || this.createTooltip();

        this.init();
        this.animate();
    }

    createTooltip() {
        const tooltip = document.createElement('div');
        tooltip.id = 'plot-tooltip';
        tooltip.style.position = 'absolute';
        tooltip.style.background = 'rgba(15, 23, 42, 0.9)';
        tooltip.style.color = '#fff';
        tooltip.style.padding = '10px 15px';
        tooltip.style.borderRadius = '8px';
        tooltip.style.border = '1px solid #38bdf8';
        tooltip.style.fontSize = '14px';
        tooltip.style.pointerEvents = 'none';
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 0.2s';
        tooltip.style.zIndex = '1000';
        tooltip.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
        tooltip.style.transform = 'translate(-50%, -100%)';
        tooltip.style.marginTop = '-15px';
        this.container.appendChild(tooltip);
        return tooltip;
    }

    init() {
        // 1. Scene Setup
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87CEEB); // Sky blue
        this.scene.fog = new THREE.FogExp2(0x87CEEB, 0.005);

        // 2. Camera Setup (Bird's Eye)
        const aspect = this.container.clientWidth / this.container.clientHeight;
        this.camera = new THREE.PerspectiveCamera(45, aspect, 1, 1000);
        this.camera.position.set(0, 100, 150);

        // 3. Renderer Setup
        this.renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: "high-performance" });
        this.renderer.setSize(this.container.clientWidth, this.container.clientHeight);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.container.appendChild(this.renderer.domElement);

        // 4. Controls
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.maxPolarAngle = Math.PI / 2 - 0.05; // Don't go below ground
        this.controls.minDistance = 20;
        this.controls.maxDistance = 300;

        // 5. Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
        dirLight.position.set(50, 100, 50);
        dirLight.castShadow = true;
        dirLight.shadow.mapSize.width = 2048;
        dirLight.shadow.mapSize.height = 2048;
        dirLight.shadow.camera.near = 10;
        dirLight.shadow.camera.far = 300;
        dirLight.shadow.camera.left = -100;
        dirLight.shadow.camera.right = 100;
        dirLight.shadow.camera.top = 100;
        dirLight.shadow.camera.bottom = -100;
        this.scene.add(dirLight);

        // 6. Build the Environment
        this.buildEnvironment();

        // 7. Event Listeners
        window.addEventListener('resize', this.onWindowResize.bind(this));
        this.renderer.domElement.addEventListener('mousemove', this.onMouseMove.bind(this));
        this.renderer.domElement.addEventListener('click', this.onClick.bind(this));
    }

    buildEnvironment() {
        // Base Ground (Grass)
        const groundGeo = new THREE.PlaneGeometry(this.config.gridSize, this.config.gridSize);
        const groundMat = new THREE.MeshStandardMaterial({ color: 0x3b82f6 > 0 ? 0x2d5a27 : 0x2d5a27, roughness: 0.8 }); // Green
        const ground = new THREE.Mesh(groundGeo, groundMat);
        ground.rotation.x = -Math.PI / 2;
        ground.receiveShadow = true;
        this.scene.add(ground);

        // Main Road (Cross intersection)
        const roadMat = new THREE.MeshStandardMaterial({ color: 0x333333, roughness: 0.9 });
        
        const hRoadGeo = new THREE.PlaneGeometry(this.config.gridSize, this.config.roadWidth);
        const hRoad = new THREE.Mesh(hRoadGeo, roadMat);
        hRoad.rotation.x = -Math.PI / 2;
        hRoad.position.y = 0.1;
        hRoad.receiveShadow = true;
        this.scene.add(hRoad);

        const vRoadGeo = new THREE.PlaneGeometry(this.config.roadWidth, this.config.gridSize);
        const vRoad = new THREE.Mesh(vRoadGeo, roadMat);
        vRoad.rotation.x = -Math.PI / 2;
        vRoad.position.y = 0.11;
        vRoad.receiveShadow = true;
        this.scene.add(vRoad);

        // Generate Procedural Plots
        this.generatePlots();
    }

    generatePlots() {
        const plotGroup = new THREE.Group();
        const plotSizeX = 10;
        const plotSizeZ = 15;
        const gap = 1;
        
        const startX = -(this.config.gridSize/2) + 20;
        const startZ = -(this.config.gridSize/2) + 20;
        
        let plotNumber = 1;

        // 4 quadrants
        const quadrants = [
            { offsetX: 10, offsetZ: 10 },
            { offsetX: -10, offsetZ: 10 },
            { offsetX: 10, offsetZ: -10 },
            { offsetX: -10, offsetZ: -10 }
        ];

        quadrants.forEach((q, qIndex) => {
            const blockPrefix = String.fromCharCode(65 + qIndex); // A, B, C, D
            for(let row=0; row<4; row++) {
                for(let col=0; col<5; col++) {
                    const x = (q.offsetX > 0 ? q.offsetX : q.offsetX - (col*(plotSizeX+gap) + plotSizeX)) + (q.offsetX > 0 ? col*(plotSizeX+gap) : 0);
                    const z = (q.offsetZ > 0 ? q.offsetZ : q.offsetZ - (row*(plotSizeZ+gap) + plotSizeZ)) + (q.offsetZ > 0 ? row*(plotSizeZ+gap) : 0);

                    // Random Status: 60% Available, 30% Sold, 10% Hold
                    const rand = Math.random();
                    let status = 'Available';
                    let color = 0x22c55e; // Green
                    if(rand > 0.9) { status = 'Hold'; color = 0xeab308; } // Yellow
                    else if(rand > 0.6) { status = 'Sold'; color = 0xef4444; } // Red

                    const plotGeo = new THREE.BoxGeometry(plotSizeX, 0.5, plotSizeZ);
                    const plotMat = new THREE.MeshStandardMaterial({ 
                        color: color, 
                        roughness: 0.6,
                        transparent: true,
                        opacity: 0.85
                    });
                    
                    const plotMesh = new THREE.Mesh(plotGeo, plotMat);
                    plotMesh.position.set(x + (plotSizeX/2), 0.25, z + (plotSizeZ/2));
                    plotMesh.castShadow = true;
                    plotMesh.receiveShadow = true;
                    
                    // Add UserData for Raycaster
                    plotMesh.userData = {
                        id: `${blockPrefix}-${plotNumber}`,
                        type: 'plot',
                        status: status,
                        size: '1000 sqft',
                        price: status === 'Available' ? '₹15,00,000' : 'N/A',
                        originalColor: color
                    };

                    plotGroup.add(plotMesh);
                    this.plots.push(plotMesh);
                    plotNumber++;
                }
            }
        });

        this.scene.add(plotGroup);
    }

    onMouseMove(event) {
        // Calculate mouse position in normalized device coordinates (-1 to +1)
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

        // Update tooltip position
        this.tooltip.style.left = event.clientX + 'px';
        this.tooltip.style.top = event.clientY + 'px';

        this.checkIntersections();
    }

    checkIntersections() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.plots);

        if (intersects.length > 0) {
            const object = intersects[0].object;
            
            if (this.hoveredPlot !== object) {
                // Restore previous hovered object
                if (this.hoveredPlot) {
                    this.hoveredPlot.material.emissive.setHex(0x000000);
                    this.hoveredPlot.position.y = 0.25;
                }
                
                this.hoveredPlot = object;
                
                // Highlight current
                this.hoveredPlot.material.emissive.setHex(0x333333);
                this.hoveredPlot.position.y = 1.0; // Lift up slightly

                // Update Tooltip
                const data = this.hoveredPlot.userData;
                let statusBadge = '';
                if(data.status === 'Available') statusBadge = '<span style="color:#4ade80;font-weight:bold;">● Available</span>';
                if(data.status === 'Sold') statusBadge = '<span style="color:#f87171;font-weight:bold;">● Sold</span>';
                if(data.status === 'Hold') statusBadge = '<span style="color:#facc15;font-weight:bold;">● Hold</span>';

                this.tooltip.innerHTML = `
                    <div style="font-weight:bold;font-size:16px;border-bottom:1px solid #475569;padding-bottom:5px;margin-bottom:5px;">Plot ${data.id}</div>
                    <div>Status: ${statusBadge}</div>
                    <div>Size: ${data.size}</div>
                    <div>Price: ${data.price}</div>
                    <div style="margin-top:5px;font-size:11px;color:#94a3b8;">Click for details</div>
                `;
                this.tooltip.style.opacity = '1';
                document.body.style.cursor = 'pointer';
            }
        } else {
            if (this.hoveredPlot) {
                this.hoveredPlot.material.emissive.setHex(0x000000);
                this.hoveredPlot.position.y = 0.25;
                this.hoveredPlot = null;
                this.tooltip.style.opacity = '0';
                document.body.style.cursor = 'default';
            }
        }
    }

    onClick(event) {
        if(this.hoveredPlot) {
            const data = this.hoveredPlot.userData;
            if(data.status === 'Available') {
                // Focus camera on plot
                const target = this.hoveredPlot.position.clone();
                
                // Smooth camera animation using simple interpolation or GSAP if available
                if(window.gsap) {
                    gsap.to(this.controls.target, {
                        x: target.x, y: target.y, z: target.z,
                        duration: 1, ease: "power2.out"
                    });
                    gsap.to(this.camera.position, {
                        x: target.x, y: target.y + 30, z: target.z + 40,
                        duration: 1.5, ease: "power2.out"
                    });
                } else {
                    this.controls.target.copy(target);
                }
                
                // Trigger custom event for the UI
                const e = new CustomEvent('plotSelected', { detail: data });
                document.dispatchEvent(e);
            }
        }
    }

    onWindowResize() {
        if(!this.container || !this.camera || !this.renderer) return;
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    animate() {
        requestAnimationFrame(this.animate.bind(this));
        if (this.controls) this.controls.update();
        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
    }

    resetCamera() {
        if(window.gsap) {
            gsap.to(this.camera.position, { x: 0, y: 100, z: 150, duration: 1.5 });
            gsap.to(this.controls.target, { x: 0, y: 0, z: 0, duration: 1 });
        } else {
            this.camera.position.set(0, 100, 150);
            this.controls.target.set(0, 0, 0);
        }
    }
}

// Expose to window
window.ColonyViewer3D = ColonyViewer3D;
