<?php
$$page_title = 'Team Genealogy - APS Dream Home';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-white">Team Genealogy Explorer 🌳</h1>
                    <p class="text-white-50 mb-0">Visualize and manage your growing network</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-outline-light btn-sm" onclick="zoomIn()"><i class="bi bi-zoom-in"></i></button>
                    <button class="btn btn-outline-light btn-sm" onclick="zoomOut()"><i class="bi bi-zoom-out"></i></button>
                    <button class="btn btn-outline-light btn-sm" onclick="resetZoom()"><i class="bi bi-arrows-fullscreen"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="glass-card genealogy-container p-0 overflow-hidden" style="height: 700px; position: relative;">
                <div id="tree-loading" class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25" style="z-index: 10;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="genealogy-tree" class="w-100 h-100"></div>
                
                <!-- Tree Controls Overlay -->
                <div class="tree-overlay position-absolute bottom-0 end-0 p-3">
                    <div class="glass-card p-2 d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-2 small text-white">
                            <span class="badge bg-primary rounded-circle p-1" style="width: 12px; height: 12px;"></span> Active
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-white">
                            <span class="badge bg-danger rounded-circle p-1" style="width: 12px; height: 12px;"></span> Inactive
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .genealogy-container {
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(15, 23, 42, 0.6) !important;
        backdrop-filter: blur(12px);
    }

    .node circle {
        fill: #fff;
        stroke: var(--primary-color);
        stroke-width: 3px;
        transition: all 0.3s ease;
    }

    .node text {
        font: 12px 'Inter', sans-serif;
        fill: #fff;
    }

    .link {
        fill: none;
        stroke: rgba(255, 255, 255, 0.15);
        stroke-width: 2px;
    }

    .node--internal text {
        text-shadow: 0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff;
    }

    .node-card {
        cursor: pointer;
    }

    .node-name {
        font-weight: 600;
        fill: #fff;
    }

    .node-rank {
        font-size: 10px;
        fill: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
    }

    /* Glass Card shared with dashboards */
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
    }
</style>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    let svg, g, zoom, root;
    const width = document.getElementById('genealogy-tree').clientWidth;
    const height = 700;

    document.addEventListener('DOMContentLoaded', () => {
        fetch('/api/mlm/tree')
            .then(response => response.json())
            .then(data => {
                document.getElementById('tree-loading').style.display = 'none';
                if (data.success && data.network_tree) {
                    initTree(data.network_tree);
                } else {
                    document.getElementById('genealogy-tree').innerHTML = 
                        '<div class="text-center text-white py-5">No hierarchy found. Start building your team!</div>';
                }
            });
    });

    function initTree(data) {
        svg = d3.select("#genealogy-tree")
            .append("svg")
            .attr("width", "100%")
            .attr("height", "100%")
            .attr("viewBox", [0, 0, width, height]);

        g = svg.append("g");

        zoom = d3.zoom()
            .scaleExtent([0.5, 3])
            .on("zoom", (event) => {
                g.attr("transform", event.transform);
            });

        svg.call(zoom);

        const tree = d3.tree().size([width - 100, height - 200]);
        root = d3.hierarchy(data);
        tree(root);

        // Links
        g.selectAll(".link")
            .data(root.links())
            .enter().append("path")
            .attr("class", "link")
            .attr("d", d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y + 50));

        // Nodes
        const node = g.selectAll(".node")
            .data(root.descendants())
            .enter().append("g")
            .attr("class", d => "node" + (d.children ? " node--internal" : " node--leaf"))
            .attr("transform", d => `translate(${d.x},${d.y + 50})`)
            .on("click", (event, d) => {
                console.log("Node clicked:", d.data);
            });

        // Node Circle
        node.append("circle")
            .attr("r", 25)
            .style("fill", d => d.data.status === 'active' ? '#2962ff' : '#64748b')
            .style("stroke", "rgba(255,255,255,0.2)")
            .style("stroke-width", "4px");

        // Node Image (Initial instead of real image for now)
        node.append("text")
            .attr("dy", "0.35em")
            .attr("text-anchor", "middle")
            .attr("fill", "#fff")
            .attr("font-weight", "bold")
            .text(d => d.data.name.charAt(0));

        // Node Label
        const label = node.append("g")
            .attr("transform", "translate(0, 45)");

        label.append("text")
            .attr("class", "node-name")
            .attr("text-anchor", "middle")
            .text(d => d.data.name);

        label.append("text")
            .attr("class", "node-rank")
            .attr("text-anchor", "middle")
            .attr("dy", "1.2em")
            .text(d => d.data.type);

        // Center the tree
        resetZoom();
    }

    function zoomIn() {
        svg.transition().call(zoom.scaleBy, 1.2);
    }

    function zoomOut() {
        svg.transition().call(zoom.scaleBy, 0.8);
    }

    function resetZoom() {
        svg.transition().duration(750).call(
            zoom.transform,
            d3.zoomIdentity.translate(50, 50).scale(0.8)
        );
    }
</script>
