@extends('layouts.app')

@section('title', 'Smart CMDB Topology')

@section('content')
<div class="space-y-4">
    <!-- Header & Filter Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-0.5">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <span class="text-white font-medium">Smart Topology</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Topologi & Relasi Visual CMDB</span>
                <span class="relative flex h-3 w-3 ml-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">Live Data</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- View Mode Segmented Control -->
            <div class="flex items-center bg-slate-950 p-1 rounded-xl border border-slate-700 mr-2 shadow-inner">
                <button onclick="changeViewMode('hybrid')" id="btn-hybrid" class="px-3 py-1.5 font-bold rounded-lg bg-blue-600 text-white shadow transition">Hybrid (Cluster)</button>
                <button onclick="changeViewMode('infra')" id="btn-infra" class="px-3 py-1.5 font-bold rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">Infra Only</button>
                <button onclick="changeViewMode('service')" id="btn-service" class="px-3 py-1.5 font-bold rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">Service Map</button>
            </div>

            <select id="filterType" onchange="loadGraphData()" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 hidden md:block">
                <option value="">Semua Tipe CI</option>
                @foreach($ciTypes as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>

            <select id="filterStatus" onchange="loadGraphData()" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 hidden md:block">
                <option value="">Semua Status</option>
                <option value="active">Active (Online)</option>
                <option value="down">Down (Offline)</option>
                <option value="maintenance">Maintenance</option>
                <option value="warning">Warning</option>
            </select>

            <button type="button" onclick="fitGraph()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700 transition" title="Reset Zoom & Center">
                Reset View
            </button>
        </div>
    </div>

    <!-- Graph Canvas & Sidebar Inspector -->
    <div class="relative bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden h-[650px] flex shadow-2xl">
        
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm z-30 flex flex-col items-center justify-center hidden">
            <div class="w-12 h-12 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin mb-4"></div>
            <p class="text-blue-400 font-bold uppercase tracking-widest text-xs">Membangun Topologi Pintar...</p>
        </div>

        <!-- Interactive Canvas Container -->
        <div id="cmdbNetwork" class="flex-1 w-full h-full cursor-grab active:cursor-grabbing"></div>

        <!-- Floating Legend -->
        <div class="absolute bottom-4 left-4 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-3 text-[11px] space-y-1.5 shadow-xl pointer-events-auto">
            <span class="font-bold text-white block text-[10px] uppercase tracking-wider mb-1">Status Node CI</span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                <span class="text-slate-300">Active / UP</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-slate-300">Warning / Maintenance</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                <span class="text-slate-300">Down / Offline</span>
            </div>
            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-800">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(225,29,72,0.8)]"></span>
                <span class="text-rose-400 font-bold">COMPROMISED (Hacked)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.8)]"></span>
                <span class="text-orange-400 font-bold">AT RISK (Blast Radius)</span>
            </div>
            <p class="text-[9px] text-slate-500 pt-1 mt-1 border-t border-slate-800">Klik 2x pada kotak 📦 untuk mengekspansi klaster.</p>
        </div>

        <!-- Node Inspector Slide-over Panel -->
        <div id="inspectorPanel" class="w-80 bg-slate-900/95 backdrop-blur-xl border-l border-slate-800 p-5 hidden flex-col justify-between overflow-y-auto z-20 shadow-2xl transition-all">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Smart Inspector
                    </span>
                    <button type="button" onclick="closeInspector()" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>

                <div class="mt-4 space-y-4">
                    <!-- Title block -->
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800">
                        <span id="inspectCode" class="text-[10px] font-mono text-slate-400 block mb-1">--</span>
                        <p id="inspectName" class="font-bold text-white text-sm leading-tight">--</p>
                    </div>

                    <!-- Integration Data (Dynamic) -->
                    <div id="inspectExtra" class="space-y-3">
                        <!-- Loaded dynamically via JS -->
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 mt-6">
                <a id="inspectLink" href="#" class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium text-xs flex items-center justify-center gap-2 transition shadow-lg">
                    <span>Buka Detail Lengkap CI</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let network = null;
    let graphDataCache = null;
    let currentViewMode = 'hybrid'; // 'hybrid', 'infra', 'service'
    
    // Animation trackers
    let animationInterval = null;
    let pulseInterval = null;
    let dashOffset = 0;
    let pulseScale = 0;

    document.addEventListener('DOMContentLoaded', function() {
        loadGraphData();
    });

    function changeViewMode(mode) {
        currentViewMode = mode;
        
        // Update Buttons UI
        const modes = ['hybrid', 'infra', 'service'];
        modes.forEach(m => {
            const btn = document.getElementById('btn-' + m);
            if(m === mode) {
                btn.className = 'px-3 py-1.5 font-bold rounded-lg bg-blue-600 text-white shadow transition';
            } else {
                btn.className = 'px-3 py-1.5 font-bold rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition';
            }
        });

        // Re-render
        if(graphDataCache) {
            applyFiltersAndRender();
        }
    }

    function loadGraphData() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
        
        const type = document.getElementById('filterType').value;
        const status = document.getElementById('filterStatus').value;
        let url = `{{ route('cmdb.graph.data') }}?`;
        if (type) url += `type=${type}&`;
        if (status) url += `status=${status}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                graphDataCache = data;
                applyFiltersAndRender();
            })
            .catch(err => {
                console.error('Failed to load CMDB graph:', err);
                document.getElementById('loadingOverlay').classList.add('hidden');
            });
    }

    function applyFiltersAndRender() {
        // Clear old animations
        if(animationInterval) clearInterval(animationInterval);
        if(pulseInterval) clearInterval(pulseInterval);

        // Deep copy data to avoid mutating cache
        const nodes = JSON.parse(JSON.stringify(graphDataCache.nodes));
        const edges = JSON.parse(JSON.stringify(graphDataCache.edges));

        // Filter based on View Mode
        nodes.forEach(n => {
            if (currentViewMode === 'infra') {
                if (['website', 'application'].includes(n.type_code)) n.hidden = true;
            } else if (currentViewMode === 'service') {
                if (['router', 'switch', 'firewall'].includes(n.type_code)) n.hidden = true;
            }
        });

        renderGraph({ nodes, edges });
    }

    function renderGraph(data) {
        const container = document.getElementById('cmdbNetwork');
        const nodesDataSet = new vis.DataSet(data.nodes);
        const edgesDataSet = new vis.DataSet(data.edges);

        const options = {
            nodes: {
                borderWidth: 2,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.5)',
                    size: 10,
                    x: 2,
                    y: 2
                },
                font: {
                    face: 'Plus Jakarta Sans',
                    size: 12
                }
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 0.7 } },
                font: { color: '#94a3b8', size: 10, align: 'middle' },
                smooth: { type: 'continuous' }
            },
            physics: {
                enabled: true,
                solver: 'barnesHut',
                barnesHut: {
                    gravitationalConstant: -5000, 
                    centralGravity: 0.15,
                    springLength: 250, 
                    springConstant: 0.04,
                    damping: 0.09,
                    avoidOverlap: 0.8
                },
                stabilization: { 
                    enabled: true,
                    iterations: 30, // Unfolding effect
                    updateInterval: 10
                }
            },
            layout: {
                randomSeed: 42 // Keeps layout somewhat consistent between re-renders
            },
            interaction: {
                hover: true,
                dragNodes: true,
                zoomView: true,
                dragView: true
            }
        };

        network = new vis.Network(container, { nodes: nodesDataSet, edges: edgesDataSet }, options);

        // --- SMART CLUSTERING LOGIC ---
        if (currentViewMode === 'hybrid') {
            const servers = data.nodes.filter(n => ['server', 'database'].includes(n.type_code));
            
            servers.forEach(server => {
                // Find node IDs connected to this server
                const connectedEdges = data.edges.filter(e => e.from === server.id || e.to === server.id);
                const neighborIds = connectedEdges.map(e => e.from === server.id ? e.to : e.from);
                
                // Find which neighbors are websites
                const websitesToCluster = data.nodes.filter(n => neighborIds.includes(n.id) && n.type_code === 'website');

                // Auto-Cluster Threshold: > 2 websites on 1 server
                if (websitesToCluster.length > 2) {
                    const clusterOptions = {
                        joinCondition: function (nodeOptions) {
                            return websitesToCluster.map(w => w.id).includes(nodeOptions.id);
                        },
                        clusterNodeProperties: {
                            id: 'cluster_' + server.id,
                            label: '📦 ' + websitesToCluster.length + ' Websites\\n(Klik 2x Buka)',
                            shape: 'box',
                            color: { background: '#1e40af', border: '#60a5fa' },
                            font: { color: '#ffffff', size: 12, face: 'Plus Jakarta Sans', bold: true },
                            borderWidth: 2,
                            shadow: true
                        }
                    };
                    network.cluster(clusterOptions);
                }
            });
        }

        // --- SMART ANIMATIONS ENGINE ---
        
        // 1. Data Flow Animation
        const animatedEdges = data.edges.filter(e => e.is_active_flow || e.is_threat_flow);
        if (animatedEdges.length > 0) {
            animationInterval = setInterval(() => {
                dashOffset -= 1;
                if (dashOffset < -20) dashOffset = 0;
            }, 50);
            
            network.on("beforeDrawing", function(ctx) {
                ctx.lineDashOffset = dashOffset;
            });
        }

        // 2. Pulse Animation for Incident Nodes (Red) and Blast Radius (Orange)
        const pulseNodes = data.nodes.filter(n => n.pulse);
        if (pulseNodes.length > 0) {
            let increasing = true;
            pulseInterval = setInterval(() => {
                if (increasing) { pulseScale += 1; if (pulseScale >= 15) increasing = false; } 
                else { pulseScale -= 1; if (pulseScale <= 0) increasing = true; }
                
                const updates = pulseNodes.map(n => {
                    const colorStr = n.pulse === 'red' ? 'rgba(239, 68, 68, 0.8)' : 'rgba(249, 115, 22, 0.8)';
                    return {
                        id: n.id,
                        shadow: {
                            enabled: true,
                            color: colorStr,
                            size: 10 + pulseScale,
                            x: 0, y: 0
                        }
                    };
                });
                
                // Try catch to prevent error if node is clustered
                try { nodesDataSet.update(updates); } catch (e) {}
            }, 60);
        }

        // Interaction Events
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                
                // Ignore clicks on cluster nodes for inspector
                if (network.isCluster(nodeId)) {
                    closeInspector();
                    return;
                }

                const node = graphDataCache.nodes.find(n => n.id === nodeId);
                if(node) showInspector(node);
            } else {
                closeInspector();
            }
        });

        // Double Click to Open Clusters
        network.on("doubleClick", function (params) {
            if (params.nodes.length == 1) {
                if (network.isCluster(params.nodes[0]) == true) {
                    network.openCluster(params.nodes[0]);
                }
            }
        });

        // Auto-fit & zoom smoothly after nodes finish unpacking
        network.on("stabilizationIterationsDone", function () {
            document.getElementById('loadingOverlay').classList.add('hidden');
            network.fit({
                animation: { duration: 1000, easingFunction: "easeInOutQuad" }
            });
        });
    }

    function showInspector(node) {
        const panel = document.getElementById('inspectorPanel');
        panel.classList.remove('hidden');
        panel.classList.add('flex');

        document.getElementById('inspectCode').textContent = node.ci_code || 'CI-' + node.id;
        document.getElementById('inspectName').textContent = node.ci_name || node.label.split('\n')[0];
        document.getElementById('inspectLink').href = node.url;

        // Build Smart Metrics HTML
        let statusBadge = '';
        if(node.status === 'active') statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ONLINE</span>';
        else if(node.status === 'down') statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-500/20 text-slate-400 border border-slate-500/30">OFFLINE</span>';
        else statusBadge = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">${node.status.toUpperCase()}</span>`;

        let alertBox = '';
        if (node.has_incidents) {
            alertBox = `
            <div class="bg-rose-500/10 border border-rose-500/30 p-3 rounded-xl shadow-lg shadow-rose-900/20">
                <div class="flex items-center gap-2 text-rose-400 mb-1">
                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-xs font-bold uppercase tracking-wider">Compromised</span>
                </div>
                <p class="text-[11px] text-rose-300">Terdapat <b>${node.incident_count}</b> insiden keamanan aktif yang mengincar perangkat ini!</p>
            </div>`;
        } else if (node.is_impacted) {
            const sourcesText = node.impact_sources.join(', ');
            alertBox = `
            <div class="bg-orange-500/10 border border-orange-500/30 p-3 rounded-xl shadow-lg shadow-orange-900/20">
                <div class="flex items-center gap-2 text-orange-400 mb-1">
                    <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span class="text-xs font-bold uppercase tracking-wider">At Risk (Blast Radius)</span>
                </div>
                <p class="text-[11px] text-orange-300 leading-relaxed">Berpotensi tinggi terdampak karena terhubung langsung dengan <b>${sourcesText}</b> yang saat ini sedang diretas.</p>
            </div>`;
        }

        let agentStatusBadge = '';
        if (node.agent_status === 'active') agentStatusBadge = '<span class="text-emerald-400">🛡️ Protected (Active)</span>';
        else if (node.agent_status === 'unmanaged') agentStatusBadge = '<span class="text-slate-500">⚠️ Unmanaged</span>';
        else agentStatusBadge = `<span class="text-amber-400">🛡️ ${node.agent_status.toUpperCase()}</span>`;

        document.getElementById('inspectExtra').innerHTML = `
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="bg-slate-900/50 p-2 rounded-lg border border-slate-800">
                    <span class="text-[10px] text-slate-500 block mb-1">State</span>
                    ${statusBadge}
                </div>
                <div class="bg-slate-900/50 p-2 rounded-lg border border-slate-800">
                    <span class="text-[10px] text-slate-500 block mb-1">Network</span>
                    <span class="text-slate-300 font-mono">${node.ip || 'No IP'}</span>
                </div>
            </div>
            
            <div class="bg-slate-900/50 p-3 rounded-lg border border-slate-800 text-xs">
                <span class="text-[10px] text-slate-500 block mb-1">EDR Agent Status</span>
                ${agentStatusBadge}
            </div>

            ${alertBox}
        `;
    }

    function closeInspector() {
        const panel = document.getElementById('inspectorPanel');
        panel.classList.add('hidden');
        panel.classList.remove('flex');
    }

    function fitGraph() {
        if (network) {
            network.fit({ animation: { duration: 800, easingFunction: 'easeInOutQuad' } });
        }
    }
</script>
@endpush
