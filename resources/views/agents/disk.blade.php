@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kelola Disk: {{ $agent->hostname }}</h1>
            <p class="text-gray-600 dark:text-gray-400">Agent ID: {{ $agent->agent_id }}</p>
        </div>
        <a href="{{ route('agents.show', $agent) }}" class="text-blue-600 hover:underline">&larr; Kembali ke Detail Agent</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Status Penyimpanan Terakhir</h3>
            @if($latestMetric)
                <div class="flex items-center mt-4">
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                            <div class="h-4 rounded-full {{ $latestMetric->disk_usage > 90 ? 'bg-red-600' : ($latestMetric->disk_usage > 70 ? 'bg-yellow-400' : 'bg-green-600') }}" style="width: {{ $latestMetric->disk_usage }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Terpakai: {{ $latestMetric->disk_usage }}%</p>
                    </div>
                </div>
            @else
                <p class="text-gray-500">Belum ada data metrik disk.</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 md:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pindai Ruang (Cache/Tmp)</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Minta agen untuk memindai folder cache dan temporary secara otomatis untuk menemukan file-file yang aman dihapus.</p>
            
            <form action="{{ route('agents.disk.scan', $agent) }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
                    Mulai Pindai Cache
                </button>
            </form>
        </div>
    </div>

    @if($latestScan)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-4">Hasil Pindaian Terakhir</h3>
            
            @if(in_array($latestScan->status, ['pending', 'processing']))
                <div class="flex items-center space-x-3 text-blue-600" id="scan-loading">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Agen sedang {{ $latestScan->status == 'pending' ? 'menunggu perintah' : 'memproses scan' }}... (Halaman akan otomatis dimuat ulang)</span>
                </div>
                <script>
                    setTimeout(() => { window.location.reload(); }, 5000);
                </script>
            @elseif($latestScan->status == 'completed' && $latestScan->result)
                <form id="delete-form" action="{{ route('agents.disk.delete', $agent) }}" method="POST">
                    @csrf
                    <div class="max-h-96 overflow-y-auto mb-4 border rounded p-4 dark:border-gray-700">
                        @php
                            function formatBytes($bytes, $precision = 2) { 
                                $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
                                $bytes = max($bytes, 0); 
                                $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
                                $pow = min($pow, count($units) - 1); 
                                $bytes /= pow(1024, $pow); 
                                return round($bytes, $precision) . ' ' . $units[$pow]; 
                            }
                            
                            function renderTree($nodes) {
                                $html = '<ul class="ml-4 border-l pl-2 border-gray-300 dark:border-gray-600">';
                                foreach($nodes as $node) {
                                    $hasChildren = !empty($node['children']);
                                    $html .= '<li class="my-1 node-item">';
                                    
                                    // Wrapper for the row
                                    $html .= '<div class="flex items-center space-x-2 hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded">';
                                    
                                    // Expand/Collapse toggle button
                                    if ($hasChildren) {
                                        $html .= '<button type="button" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-gray-700 transition" onclick="toggleFolder(this)">
                                                    <svg class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                  </button>';
                                    } else {
                                        $html .= '<div class="w-5 h-5"></div>'; // Spacer for alignment
                                    }

                                    // Checkbox and Label
                                    $html .= '<label class="flex items-center space-x-2 cursor-pointer flex-1">';
                                    $html .= '<input type="checkbox" name="paths[]" value="' . htmlspecialchars($node['path']) . '" class="rounded text-blue-600 file-checkbox" onchange="toggleChildren(this)">';
                                    
                                    if ($node['type'] === 'directory') {
                                        $html .= '<svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>';
                                    } else {
                                        $html .= '<svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>';
                                    }
                                    
                                    $html .= '<span class="text-sm font-medium text-gray-700 dark:text-gray-300 break-all">' . htmlspecialchars($node['name']) . '</span>';
                                    $html .= '<span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded ml-auto whitespace-nowrap">' . formatBytes($node['size']) . '</span>';
                                    $html .= '</label>';
                                    $html .= '</div>';
                                    
                                    if($hasChildren) {
                                        $html .= '<div class="children-container">';
                                        $html .= renderTree($node['children']);
                                        $html .= '</div>';
                                    }
                                    $html .= '</li>';
                                }
                                $html .= '</ul>';
                                return $html;
                            }
                        @endphp
                        
                        {!! renderTree($latestScan->result) !!}

                        <script>
                            function toggleFolder(btn) {
                                const li = btn.closest('li');
                                const container = li.querySelector(':scope > .children-container');
                                const svg = btn.querySelector('svg');
                                
                                if (container.style.display === 'none') {
                                    container.style.display = 'block';
                                    svg.classList.remove('-rotate-90');
                                } else {
                                    container.style.display = 'none';
                                    svg.classList.add('-rotate-90');
                                }
                            }
                            
                            function toggleChildren(checkbox) {
                                const li = checkbox.closest('li');
                                const container = li.querySelector(':scope > .children-container');
                                if (container) {
                                    const childCheckboxes = container.querySelectorAll('input[type="checkbox"]');
                                    childCheckboxes.forEach(cb => {
                                        cb.checked = checkbox.checked;
                                    });
                                }
                            }
                        </script>
                    </div>
                    
                    <button type="button" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition" onclick="showConfirmModal()">
                        Hapus Data Terpilih
                    </button>
                </form>

                <!-- Modal Konfirmasi Hapus -->
                <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
                        <div class="flex items-center text-red-600 mb-4">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Konfirmasi Penghapusan Permanen</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Anda akan menghapus <span id="selected-count" class="font-bold"></span> item berikut secara permanen dari server agen. Tindakan ini tidak dapat dibatalkan!</p>
                        
                        <div class="max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 rounded border dark:border-gray-700 mb-6 text-sm text-gray-700 dark:text-gray-400 font-mono">
                            <ul id="selected-list" class="list-disc pl-5 space-y-1"></ul>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">Batal</button>
                            <button type="button" onclick="submitDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow font-medium">Ya, Eksekusi Hapus</button>
                        </div>
                    </div>
                </div>

                <script>
                    function showConfirmModal() {
                        const checked = document.querySelectorAll('.file-checkbox:checked');
                        if (checked.length === 0) {
                            alert('Silakan pilih setidaknya satu data (folder/file) yang ingin dihapus terlebih dahulu.');
                            return;
                        }
                        
                        const listEl = document.getElementById('selected-list');
                        listEl.innerHTML = '';
                        document.getElementById('selected-count').textContent = checked.length;
                        
                        checked.forEach(cb => {
                            const li = document.createElement('li');
                            li.textContent = cb.value;
                            listEl.appendChild(li);
                        });
                        
                        document.getElementById('confirmModal').classList.remove('hidden');
                    }
                    
                    function closeConfirmModal() {
                        document.getElementById('confirmModal').classList.add('hidden');
                    }
                    
                    function submitDelete() {
                        document.getElementById('delete-form').submit();
                    }
                </script>

            @endif
        </div>
    @endif
</div>
@endsection
