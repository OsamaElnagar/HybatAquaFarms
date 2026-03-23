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
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 3px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-table th,
        .info-table td {
            padding: 5px 10px;
            border: 1px solid #ccc;
        }

        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 140px;
            text-align: right;
        }

        .info-table td {
            background-color: #fff;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-open {
            background-color: #d4edda;
            color: #155724;
        }

        .status-closed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .amount {
            font-family: 'DejaVu Sans', sans-serif;
            white-space: nowrap;
        }

        .amount-debit {
            color: #dc3545;
        }

        .amount-credit {
            color: #28a745;
        }

        .amount-bold {
            font-weight: bold;
        }

        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .entries-table th,
        .entries-table td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: right;
        }

        .entries-table th {
            background-color: #f5f5f5;
            font-weight: bold;
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
            margin-right: auto;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td {
            padding: 5px 10px;
            border: 1px solid #ccc;
        }

        .summary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .notes {
            margin-top: 10px;
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .notes-title {
            font-weight: bold;
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
            <td>{{ $entityName }}</td>
            <th>الحالة:</th>
            <td>
                @if(($statement['status'] ?? 'open') === 'open')
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
            <td class="amount amount-bold">{{ number_format($statement['closing_balance'] ?? 0) }} EGP</td>
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
                <th style="width: 80px;">التاريخ</th>
                <th style="width: 75px;">رقم القيد</th>
                <th>البيان</th>
                <th style="width: 90px;" class="text-left">مدين (EGP)</th>
                <th style="width: 90px;" class="text-left">دائن (EGP)</th>
                <th style="width: 90px;" class="text-left">الرصيد (EGP)</th>
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
                    <td class="text-center">{{ $entry['entry_number'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-left amount amount-debit">{{ $debit > 0 ? number_format($debit) : '-' }}</td>
                    <td class="text-left amount amount-credit">{{ $credit > 0 ? number_format($credit) : '-' }}</td>
                    <td class="text-left amount amount-bold">{{ number_format($runningBalance) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">لا توجد حركات</td>
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
