<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pindai Aset: {{ $asset->asset_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-4 flex flex-col items-center justify-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5">
        <!-- Badge & Identity -->
        <div class="text-center pb-4 border-b border-slate-800">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-semibold mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Aset Terverifikasi SIKANDI</span>
            </div>
            <h1 class="text-xl font-bold text-white">{{ $asset->name }}</h1>
            <p class="font-mono text-xs text-amber-400 font-bold mt-0.5">{{ $asset->asset_number }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $asset->organization->name }}</p>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-500 block">Status Siklus</span>
                <span class="font-bold text-emerald-400 uppercase">{{ $asset->lifecycle_status }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-500 block">Kondisi Fisik</span>
                <span class="font-bold text-slate-200 uppercase">{{ $asset->condition }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-500 block">Pengguna</span>
                <span class="font-semibold text-white truncate block">{{ $asset->assignedTo?->name ?? 'Belum Ditugaskan' }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800">
                <span class="text-[10px] text-slate-500 block">Lokasi</span>
                <span class="font-semibold text-slate-300 truncate block">{{ $asset->location?->name ?? 'Kantor Dinas' }}</span>
            </div>
        </div>

        <!-- Hardware & Serial Info -->
        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-500">Merk / Model</span>
                <span class="text-slate-200 font-medium">{{ $asset->brand ?? '-' }} {{ $asset->model ?? '' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Serial Number</span>
                <span class="font-mono text-slate-300">{{ $asset->serial_number ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Masa Garansi</span>
                <span class="text-slate-300">{{ $asset->warranty_expiry_date ? $asset->warranty_expiry_date->format('d M Y') : '-' }}</span>
            </div>
        </div>

        <!-- Linked CMDB CIs -->
        @if($asset->configurationItems->isNotEmpty())
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs">
                <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-2">Configuration Item (CMDB) Terhubung</span>
                <div class="space-y-2">
                    @foreach($asset->configurationItems as $ci)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono text-blue-400 font-bold">{{ $ci->ci_code }}</span>
                                <p class="text-slate-300">{{ $ci->name }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300">{{ $ci->ciType->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-2 text-center">
            <a href="{{ route('login') }}" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold block transition">
                Masuk ke Platform SIKANDI &rarr;
            </a>
            <p class="text-[10px] text-slate-500 mt-3">&copy; 2026 Bidang Persandian dan Keamanan Informasi &bull; Diskominfo Kab. Ciamis</p>
        </div>
    </div>
</body>
</html>
