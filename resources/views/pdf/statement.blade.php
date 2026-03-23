<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $statement['title'] ?? 'كشف حساب' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1a5f7a;
            background: linear-gradient(to bottom, #e8f4f8, #fff);
            padding: 15px;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 3px;
            color: #1a5f7a;
        }

        .header h2 {
            font-size: 15px;
            font-weight: normal;
            color: #2980b9;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .info-table th,
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #3498db;
        }

        .info-table th {
            background: linear-gradient(to bottom, #3498db, #2980b9);
            color: white;
            font-weight: bold;
            width: 140px;
            text-align: right;
        }

        .info-table td {
            background-color: #f8fbff;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-open {
            background-color: #27ae60;
            color: white !important;
        }

        .status-closed {
            background-color: #e74c3c;
            color: white !important;
        }

        .amount {
            font-family: 'DejaVu Sans', sans-serif;
            white-space: nowrap;
        }

        .amount-debit {
            color: #e74c3c;
            font-weight: 600;
        }

        .amount-credit {
            color: #27ae60;
            font-weight: 600;
        }

        .amount-bold {
            font-weight: bold;
            color: #1a5f7a;
        }

        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .entries-table th,
        .entries-table td {
            border: 1px solid #3498db;
            padding: 6px 8px;
            text-align: right;
        }

        .entries-table th {
            background: linear-gradient(to bottom, #3498db, #2980b9);
            color: white;
            font-weight: bold;
        }

        .entries-table tr:nth-child(even) {
            background-color: #f0f8ff;
        }

        .entries-table tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .entries-table tr:hover {
            background-color: #e8f4f8;
        }

        .entries-table .text-center {
            text-align: center;
        }

        .entries-table .text-left {
            text-align: left;
        }

        .summary-section {
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .summary-table {
            width: 280px;
            margin-left: auto;
            margin-right: 0;
            border-collapse: collapse;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .summary-table th,
        .summary-table td {
            padding: 8px 12px;
            border: 1px solid #1a5f7a;
        }

        .summary-table th {
            background: linear-gradient(to bottom, #1a5f7a, #0e4157);
            color: white;
            font-weight: bold;
            text-align: right;
        }

        .summary-table td {
            background-color: #f8fbff;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 2px solid #3498db;
            padding-top: 10px;
        }

        .notes {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            font-size: 10px;
        }

        .notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 3px;
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $storeName }}</h1>
        <h2>{{ $statement['title'] ?? 'كشف حساب' }} - {{ $entityName }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <th>الاسم:</th>
            <td><strong>{{ $entityName }}</strong></td>
            <th>الحالة:</th>
            <td>
                @if(in_array($statement['status'] ?? 'open', ['open', 'Open', 'OPEN']))
                    <span class="status-badge status-open">مفتوح</span>
                @else
                    <span class="status-badge status-closed">مغلق / مسوَّى</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>تاريخ الفتح:</th>
            <td>{{ $statement['opened_at'] ?? '-' }}</td>
            <th>تاريخ الإغلاق:</th>
            <td>{{ $statement['closed_at'] ?? 'لا تزال مفتوحة' }}</td>
        </tr>
        <tr>
            <th>الرصيد الافتتاحي:</th>
            <td class="amount amount-bold">{{ number_format($statement['opening_balance'] ?? 0) }} EGP</td>
            <th>الرصيد الختامي:</th>
            <td class="amount amount-bold" style="font-size: 12px;">{{ number_format($statement['closing_balance'] ?? 0) }} EGP</td>
        </tr>
    </table>

    @if(!empty($statement['notes']))
        <div class="notes">
            <div class="notes-title">ملاحظات:</div>
            <div>{{ $statement['notes'] }}</div>
        </div>
    @endif

    <table class="entries-table">
        <thead>
            <tr>
                <th style="width: 45px;">#</th>
                <th style="width: 85px;">التاريخ</th>
                <th>البيان</th>
                <th style="width: 95px;" class="text-left">مدين (EGP)</th>
                <th style="width: 95px;" class="text-left">دائن (EGP)</th>
                <th style="width: 95px;" class="text-left">الرصيد (EGP)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $statement['opening_balance'] ?? 0;
            @endphp

            @forelse($entries as $index => $entry)
                @php
                    $debit = $entry['debit'] ?? 0;
                    $credit = $entry['credit'] ?? 0;
                    $runningBalance = $runningBalance + $debit - $credit;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $entry['date'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-left amount amount-debit">{{ $debit > 0 ? number_format($debit) : '-' }}</td>
                    <td class="text-left amount amount-credit">{{ $credit > 0 ? number_format($credit) : '-' }}</td>
                    <td class="text-left amount amount-bold">{{ number_format($runningBalance) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">لا توجد حركات</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($entries))
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <th colspan="2">ملخص الكشف</th>
                </tr>
                <tr>
                    <td>إجمالي المدين:</td>
                    <td class="text-left amount amount-debit">{{ number_format(array_sum(array_column($entries, 'debit'))) }} EGP</td>
                </tr>
                <tr>
                    <td>إجمالي الدائن:</td>
                    <td class="text-left amount amount-credit">{{ number_format(array_sum(array_column($entries, 'credit'))) }} EGP</td>
                </tr>
                <tr>
                    <td>الرصيد الختامي:</td>
                    <td class="text-left amount amount-bold">{{ number_format($statement['closing_balance'] ?? $runningBalance) }} EGP</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>تم إنشاء هذا الكشف في {{ $generatedAt }} | {{ $storeName }}</p>
    </div>
</body>

</html>
