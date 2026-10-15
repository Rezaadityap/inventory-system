<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            width: 78px;
        }

        .info-colon {
            width: 10px;
        }

        .paid-stamp {
            margin-top: 8px;
            border: 2px solid #14804a;
            color: #14804a;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            padding: 5px 0;
            letter-spacing: 2px;
        }

        .paid-stamp .paid-date {
            display: block;
            font-size: 9px;
            font-weight: normal;
            letter-spacing: 0;
            margin-top: 2px;
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

        .totals-table {
            margin-top: 10px;
        }

        .totals-table td {
            font-size: 10.5px;
            padding: 3px 6px;
        }

        .totals-table td.label {
            text-align: right;
        }

        .totals-table td.value {
            text-align: right;
            width: 130px;
        }

        .totals-table tr.grand td {
            font-weight: bold;
            font-size: 11.5px;
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

        .footnote {
            margin-top: 14px;
            font-size: 9px;
            color: #555;
            text-align: center;
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

    <div class="title">INVOICE PEMBAYARAN</div>
    <div class="subtitle">{{ $invoice->invoice_number }}</div>

    <table>
        <tr>
            <td style="width: 52%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama</td>
                        <td class="info-colon">:</td>
                        <td>{{ $invoice->customer?->customer_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat</td>
                        <td class="info-colon">:</td>
                        <td>{{ $invoice->customer?->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Telp</td>
                        <td class="info-colon">:</td>
                        <td>{{ $invoice->customer?->phone_number ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 48%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="info-label">No Transaksi</td>
                        <td class="info-colon">:</td>
                        <td>{{ $invoice->transaction?->transaction_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Invoice</td>
                        <td class="info-colon">:</td>
                        <td>{{ optional($invoice->created_at)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jatuh Tempo</td>
                        <td class="info-colon">:</td>
                        <td>{{ optional($invoice->due_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Bayar</td>
                        <td class="info-colon">:</td>
                        <td>{{ optional($invoice->paid_at)->translatedFormat('d F Y, H:i') }}</td>
                    </tr>
                </table>
                <div class="paid-stamp">
                    LUNAS
                    <span class="paid-date">
                        {{ optional($invoice->paid_at)->translatedFormat('d F Y, H:i') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    @php
        $items = $invoice->transaction?->items ?? collect();
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
                <th style="width: 30%;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->waste?->waste_name }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="center">{{ $unitLabels[$item->waste?->unit] ?? $item->waste?->unit }}</td>
                    <td class="right">Rp. {{ number_format((int) $item->price, 0, ',', '.') }}</td>
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

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp. {{ number_format((int) $invoice->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if ((int) $invoice->discount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="value">- Rp. {{ number_format((int) $invoice->discount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ((int) $invoice->tax > 0)
            <tr>
                <td class="label">PPN</td>
                <td class="value">Rp. {{ number_format((int) $invoice->tax, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="label">Total Dibayar</td>
            <td class="value">Rp. {{ number_format((int) $invoice->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-label">Disetujui</div>
                <div class="signature-date">
                    {{ optional($invoice->transaction?->approved_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">({{ $invoice->transaction?->approver?->name ?? '-' }})</div>
            </td>
            <td class="signature-gap"></td>
            <td>
                <div class="signature-label">Diterbitkan</div>
                <div class="signature-date">
                    {{ optional($invoice->created_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">(PT. Surya Saputra Gemilang)</div>
            </td>
            <td class="signature-gap"></td>
            <td>
                <div class="signature-label">Pembayar</div>
                <div class="signature-date">
                    {{ optional($invoice->paid_at)->translatedFormat('d F Y, H:i') }}</div>
                <div class="signature-name">({{ $invoice->customer?->customer_name ?? '-' }})</div>
            </td>
        </tr>
    </table>

    <div class="footnote">
        Dokumen ini dicetak otomatis oleh sistem dan sah tanpa tanda tangan basah.
    </div>
</body>

</html>
