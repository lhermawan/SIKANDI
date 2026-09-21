<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan IKASANDI - {{ $assessment->organization->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table td.label {
            font-weight: bold;
            width: 150px;
        }
        .score-box {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
        .score-box h2 {
            margin: 0 0 10px;
            font-size: 16px;
        }
        .score-box .score {
            font-size: 32px;
            font-weight: bold;
        }
        .questions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .questions-table th, .questions-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .questions-table th {
            background-color: #eee;
            text-align: left;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .signature {
            display: inline-block;
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Cetak Laporan</button>
        <a href="{{ route('ikasandi.assessment', ['org_id' => $assessment->organization_id]) }}" style="padding: 10px 20px; font-size: 14px; text-decoration: none; color: #333; border: 1px solid #ccc; background: #eee;">Kembali</a>
    </div>

    <div class="header">
        <h1>Laporan Indeks Keamanan Informasi dan Persandian (IKASANDI)</h1>
        <p>Pemerintah Kabupaten Ciamis - Tahun {{ $assessment->year }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Instansi / OPD</td>
            <td>: {{ $assessment->organization->name }}</td>
            <td class="label">Status</td>
            <td>: {{ strtoupper($assessment->status) }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td>: {{ ucfirst($assessment->organization->category) }}</td>
            <td class="label">Tgl Verifikasi</td>
            <td>: {{ $assessment->verified_at ? $assessment->verified_at->format('d F Y') : '-' }}</td>
        </tr>
    </table>

    <div class="score-box">
        <h2>Skor Kepatuhan Final</h2>
        <div class="score">{{ $assessment->final_score }}%</div>
        <p style="margin: 5px 0 0;">
            (Skor Kuesioner: {{ $assessment->compliance_score }}% | Penalti Insiden: -{{ $assessment->penalty_score }}%)
        </p>
    </div>

    <h3>Rincian Penilaian Mandiri</h3>
    <table class="questions-table">
        <thead>
            <tr>
                <th style="width: 50%;">Kategori / Pertanyaan</th>
                <th style="width: 50%;">Jawaban</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <th colspan="2">{{ $cat->name }}</th>
                </tr>
                @foreach($cat->questions as $q)
                    @php $ans = $existingAnswers[$q->id] ?? null; @endphp
                    <tr>
                        <td>{{ $q->question_text }}</td>
                        <td>
                            @if($ans === 'compliant') Compliant (100%)
                            @elseif($ans === 'partial') Partial (50%)
                            @elseif($ans === 'non_compliant') Non-Compliant (0%)
                            @elseif($ans === 'not_applicable') N/A
                            @else Belum diisi
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Ciamis, {{ date('d F Y') }}<br>Admin Persandian / CSIRT</p>
            <div class="signature-line">
                ( ......................................... )
            </div>
        </div>
    </div>
</body>
</html>
