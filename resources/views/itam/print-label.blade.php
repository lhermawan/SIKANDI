<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label QR Aset: {{ $asset->asset_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .label-card {
            width: 340px;
            border: 2px solid #000;
            background: #fff;
            padding: 12px;
            box-sizing: border-box;
            border-radius: 6px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 11px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 9px;
            margin: 2px 0 0 0;
            font-weight: normal;
        }
        .body-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .qr-box {
            width: 110px;
            height: 110px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-box svg {
            width: 100%;
            height: 100%;
        }
        .info {
            flex: 1;
            font-size: 10px;
            line-height: 1.35;
        }
        .asset-code {
            font-size: 13px;
            font-weight: bold;
            font-family: monospace;
            display: block;
            margin-bottom: 4px;
        }
        .footer {
            border-top: 1px solid #ccc;
            margin-top: 8px;
            padding-top: 4px;
            font-size: 8px;
            text-align: center;
            color: #555;
        }
        .print-btn {
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-btn {
                display: none;
            }
            .label-card {
                border: 2px solid #000;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Cetak Label Ini (Ctrl + P)</button>

    <div class="label-card">
        <div class="header">
            <h1>PEMERINTAH KABUPATEN CIAMIS</h1>
            <h2>DISKOMINFO &bull; SIKANDI ASSET MANAGEMENT</h2>
        </div>

        <div class="body-content">
            <div class="qr-box">
                {!! $qrCodeSvg !!}
            </div>
            <div class="info">
                <span class="asset-code">{{ $asset->asset_number }}</span>
                <strong>{{ $asset->name }}</strong><br>
                <span>Merk: {{ $asset->brand ?? '-' }}</span><br>
                <span>SN: {{ $asset->serial_number ?? '-' }}</span><br>
                <span>OPD: {{ $asset->organization->code }}</span>
            </div>
        </div>

        <div class="footer">
            Pindai QR Code untuk verifikasi status & spesifikasi teknis CI &bull; SIKANDI v1.0
        </div>
    </div>
</body>
</html>
