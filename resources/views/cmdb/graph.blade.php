@extends('layouts.app')

@section('title', 'CMDB Graph Topology')

@section('content')
<div class="space-y-4">
    <!-- Header & Filter Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-0.5">
                <a href="{{ route('cmdb.index') }}" class="hover:underline">CMDB</a>
                <span>/</span>
                <span class="text-white font-medium">Graph Topology</span>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Topologi & Relasi Visual CMDB</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-500/30">Interactive</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Filter Type -->
            <select id="filterType" onchange="loadGraphData()" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-xl text-slate-200">
                <option value="">Semua Tipe CI</option>
                @foreach($ciTypes as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>

            <!-- Reset View Button -->
            <button type="button" onclick="fitGraph()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700 transition" title="Reset Zoom & Center">
                Reset View
            </button>

            <!-- Toggle Physics -->
            <button type="button" id="physicsBtn" onclick="togglePhysics()" class="px-3 py-1.5 bg-blue-600/20 text-blue-300 border border-blue-500/30 rounded-xl hover:bg-blue-600/30 transition">
                Stabilkan Node
            </button>

            <a href="{{ route('cmdb.relationships') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-medium transition shadow">
                + Kelola Relasi
            </a>
        </div>
    </div>

    <!-- Graph Canvas & Sidebar Inspector -->
    <div class="relative bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden h-[650px] flex shadow-2xl">
        <!-- Interactive Canvas Container -->
        <div id="cmdbNetwork" class="flex-1 w-full h-full cursor-grab active:cursor-grabbing"></div>

        <!-- Floating Legend -->
        <div class="absolute bottom-4 left-4 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl p-3 text-[11px] space-y-1.5 shadow-xl pointer-events-auto">
            <span class="font-bold text-white block text-[10px] uppercase tracking-wider mb-1">Status Node CI</span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-slate-300">Active / UP</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-slate-300">Warning / Maintenance</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-slate-300">Down / Incident</span>
            </div>
            <p class="text-[9px] text-slate-500 pt-1 border-t border-slate-800">Klik node untuk melihat detail inspector &rarr;</p>
        </div>

        <!-- Node Inspector Slide-over Panel -->
        <div id="inspectorPanel" class="w-80 bg-slate-900/95 backdrop-blur-xl border-l border-slate-800 p-5 hidden flex-col justify-between overflow-y-auto z-20 shadow-2xl">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Node Inspector</span>
                    <button type="button" onclick="closeInspector()" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-500 block">Kode CI</span>
                        <span id="inspectCode" class="font-mono text-sm font-bold text-white">--</span>
                    </div>

                    <div>
                        <span class="text-[10px] text-slate-500 block">Nama CI</span>
                        <p id="inspectName" class="font-semibold text-slate-200 text-sm">--</p>
                    </div>

                    <div id="inspectExtra" class="space-y-2 pt-2 border-t border-slate-800 text-slate-300">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800">
                <a id="inspectLink" href="#" class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium text-xs flex items-center justify-center gap-2 transition">
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
    let physicsEnabled = true;
    let graphDataCache = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadGraphData();
    });

    function loadGraphData() {
        const type = document.getElementById('filterType').value;
        let url = `{{ route('cmdb.graph.data') }}?`;
        if (type) url += `type=${type}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                graphDataCache = data;
                renderGraph(data);
            })
            .catch(err => {
                console.error('Failed to load CMDB graph:', err);
            });
    }

    function renderGraph(data) {
        const container = document.getElementById('cmdbNetwork');
        const nodes = new vis.DataSet(data.nodes);
        const edges = new vis.DataSet(data.edges);

        const options = {
            nodes: {
                borderWidth: 2,
                shadow: true,
                font: {
                    color: '#ffffff',
                    face: 'Plus Jakarta Sans',
                    size: 12
                }
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 0.7 } },
                color: { color: '#64748b', highlight: '#38bdf8' },
                font: { color: '#94a3b8', size: 10, align: 'middle' },
                smooth: { type: 'continuous' }
            },
            physics: {
                enabled: true,
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -50,
                    centralGravity: 0.01,
                    springLength: 120,
                    springConstant: 0.08
                },
                stabilization: { iterations: 150 }
            },
            interaction: {
                hover: true,
                dragNodes: true,
                zoomView: true,
                dragView: true
            }
        };

        network = new vis.Network(container, { nodes, edges }, options);

        // Click event on node
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = nodes.get(nodeId);
                showInspector(node);
            } else {
                closeInspector();
            }
        });
    }

    function showInspector(node) {
        const panel = document.getElementById('inspectorPanel');
        panel.classList.remove('hidden');
        panel.classList.add('flex');

        document.getElementById('inspectCode').textContent = node.ci_code || 'CI-' + node.id;
        document.getElementById('inspectName').textContent = node.label.replace(node.ci_code + '\n', '');
        document.getElementById('inspectLink').href = node.url;
        document.getElementById('inspectExtra').innerHTML = `
            <div class="p-2 rounded bg-slate-950/60 border border-slate-800 text-[11px]">
                ${node.title || 'Informasi status CI'}
            </div>
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

    function togglePhysics() {
        physicsEnabled = !physicsEnabled;
        if (network) {
            network.setOptions({ physics: { enabled: physicsEnabled } });
        }
        const btn = document.getElementById('physicsBtn');
        if (physicsEnabled) {
            btn.textContent = 'Stabilkan Node';
            btn.className = 'px-3 py-1.5 bg-blue-600/20 text-blue-300 border border-blue-500/30 rounded-xl hover:bg-blue-600/30 transition';
        } else {
            btn.textContent = 'Aktifkan Fisika';
            btn.className = 'px-3 py-1.5 bg-slate-800 text-slate-400 border border-slate-700 rounded-xl hover:bg-slate-700 transition';
        }
    }
</script>
@endpush
