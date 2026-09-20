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
                <form action="{{ route('agents.disk.delete', $agent) }}" method="POST">
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
                                    $html .= '<li class="my-1">';
                                    $html .= '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded">';
                                    $html .= '<input type="checkbox" name="paths[]" value="' . htmlspecialchars($node['path']) . '" class="rounded text-blue-600 file-checkbox">';
                                    
                                    if ($node['type'] === 'directory') {
                                        $html .= '<svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>';
                                    } else {
                                        $html .= '<svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>';
                                    }
                                    
                                    $html .= '<span class="text-sm font-medium text-gray-700 dark:text-gray-300">' . htmlspecialchars($node['name']) . '</span>';
                                    $html .= '<span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded ml-auto">' . formatBytes($node['size']) . '</span>';
                                    $html .= '</label>';
                                    
                                    if(!empty($node['children'])) {
                                        $html .= renderTree($node['children']);
                                    }
                                    $html .= '</li>';
                                }
                                $html .= '</ul>';
                                return $html;
                            }
                        @endphp
                        
                        {!! renderTree($latestScan->result) !!}
                    </div>
                    
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition" onclick="return confirm('Yakin ingin menghapus data yang dipilih secara permanen dari server?');">
                        Hapus Data Terpilih
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
