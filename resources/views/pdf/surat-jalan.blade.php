<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Surat Jalan {{ $shipment->shipment_number }}</title>
    <style>
        @page {
            margin: 22px 28px;
        }

        body {
            font-family: "Helvetica", Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #1c3f8f;
        }

        .company-detail {
            font-size: 9.5px;
            color: #333;
            line-height: 1.4;
        }

        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 1.5px 0;
            font-size: 10.5px;
            vertical-align: top;
        }

        .info-label {
            width: 70px;
        }

        .info-colon {
            width: 10px;
        }

        .items-table {
            margin-top: 10px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
        }

        .items-table th {
            text-align: center;
            background-color: #f0f0f0;
        }

        .items-table td.center {
            text-align: center;
        }

        .items-table td.right {
            text-align: right;
        }

        .items-table td.empty-row {
            height: 20px;
        }

        .grand-total-label {
            text-align: right;
            font-weight: bold;
        }

        .grand-total-value {
            font-weight: bold;
        }

        .signature-table {
            margin-top: 26px;
        }

        .signature-table td {
            width: 30%;
            text-align: center;
            font-size: 10.5px;
            vertical-align: top;
            border: 1px solid #000;
            padding: 8px 6px 12px;
        }

        .signature-table td.signature-gap {
            width: 5%;
            border: none;
        }

        .signature-label {
            font-weight: bold;
        }

        .signature-date {
            font-weight: bold;
            font-size: 11px;
            margin: 18px 0;
        }

        .signature-name {
            margin-top: 18px;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td style="width: 70px;">
                @if ($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 60px;">
                @endif
            </td>
            <td>
                <div class="company-name">PT. SURYA SAPUTRA GEMILANG</div>
                <div class="company-detail">
                    Ruko Dharmawangsa III Blok. D-08 Grand Taruma<br>
                    Desa Sukamakmur, Kec. Telukjambe Timur, Karawang, Jawa Barat<br>
                    Telp. +62 267 8414 432 / +62 81-1118-4011&nbsp;&nbsp;Email: info@suryasaputragemilang.co.id
                </div>
            </td>
        </tr>
    </table>

    <div class="title">SURAT JALAN PENJUALAN</div>
    <div class="subtitle">{{ $shipment->shipment_number }}</div>

    <table>
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama</td>
                        <td class="info-colon">:</td>
                        <td>{{ $shipment->transaction?->customer?->customer_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat</td>
                        <td class="info-colon">:</td>
                        <td>{{ $shipment->transaction?->customer?->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Telp</td>
                        <td class="info-colon">:</td>
                        <td>{{ $shipment->transaction?->customer?->phone_number ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="info-label">No Kendaraan</td>
                        <td class="info-colon">:</td>
                        <td>{{ $shipment->vehicle_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Sopir</td>
                        <td class="info-colon">:</td>
                        <td>{{ $shipment->driver?->user?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal</td>
                        <td class="info-colon">:</td>
                        <td>{{ optional($shipment->received_at)->translatedFormat('d F Y, H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @php
        $items = $shipment->transaction?->items ?? collect();
        $unitLabels = ['Kilogram' => 'Kg', 'Gram' => 'Gram', 'Miligram' => 'Mg'];
        $minRows = 8;
    @endphp

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 40%;">Nama Barang</th>
                <th style="width: 12%;">Qty</th>
                <th style="width: 12%;">Satuan</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->waste?->waste_name }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="center">{{ $unitLabels[$item->waste?->unit] ?? $item->waste?->unit }}</td>
                    <td>{{ $item->note_item }}</td>
                </tr>
            @endforeach
            @for ($i = $items->count(); $i < $minRows; $i++)
                <tr>
                    <td class="empty-row">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-label">Dibuat</div>
                <div class="signature-date">
                    {{ optional($shipment->created_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">({{ $shipment->shipper?->name ?? '-' }})</div>
            </td>
            <td class="signature-gap"></td>
            <td>
                <div class="signature-label">Disetujui</div>
                <div class="signature-date">
                    {{ optional($shipment->transaction?->approved_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">({{ $shipment->transaction?->approver?->name ?? '-' }})</div>
            </td>
            <td class="signature-gap"></td>
            <td>
                <div class="signature-label">Penerima</div>
                <div class="signature-date">
                    {{ optional($shipment->received_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">({{ $shipment->receiver?->name ?? '-' }})</div>
            </td>
        </tr>
    </table>
</body>

</html>
