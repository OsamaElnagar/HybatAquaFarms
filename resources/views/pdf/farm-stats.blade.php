<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>إحصائيات المزرعة - {{ $farmName }}</title>
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
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1a5f7a;
            background: linear-gradient(to bottom, #e8f4f8, #fff);
            padding: 20px;
            border-radius: 8px;
        }

        .header h1 {
            font-size: 26px;
            margin-bottom: 5px;
            color: #1a5f7a;
        }

        .header h2 {
            font-size: 18px;
            font-weight: normal;
            color: #2980b9;
        }

        .stats-table {
            width: 100%;
            border-spacing: 10px;
            margin: 20px -10px;
        }

        .stats-cell {
            width: 25%;
            background-color: #ffffff;
            border: 1px solid #e1e8ed;
            border-right: 4px solid #1a5f7a;
            padding: 15px 10px;
            border-radius: 4px;
            text-align: center;
        }

        .stats-cell.expenses { border-right-color: #34495e; }
        .stats-cell.revenue { border-right-color: #2ecc71; }
        .stats-cell.profit { border-right-color: #1a5f7a; }
        .stats-cell.margin { border-right-color: #f1c40f; }

        .stats-label {
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-value {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
        }

        .profit-positive { color: #27ae60; }
        .profit-negative { color: #e74c3c; }

        .info-section {
            margin-bottom: 25px;
            background-color: #f9fbfd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e1e8ed;
        }

        .info-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 12px;
            color: #1a5f7a;
            border-bottom: 2px solid #3498db;
            padding-bottom: 6px;
            display: inline-block;
        }

        .batches-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #e1e8ed;
        }

        .batches-table th {
            background-color: #f8fbff;
            color: #1a5f7a;
            font-weight: bold;
            text-align: center;
            padding: 12px 10px;
            border-bottom: 2px solid #3498db;
            font-size: 11px;
        }

        .batches-table td {
            padding: 10px;
            border-bottom: 1px solid #e1e8ed;
            text-align: center;
            font-size: 10px;
            color: #555;
        }

        .batches-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .amount {
            font-family: 'DejaVu Sans', sans-serif;
            white-space: nowrap;
        }

        .filter-badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #e8f4f8;
            border: 1px solid #3498db;
            border-radius: 12px;
            margin-left: 5px;
            font-size: 9px;
            color: #1a5f7a;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #e1e8ed;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $storeName }}</h1>
        <h2>تقرير إحصائيات المزرعة: {{ $farmName }}</h2>
    </div>

    <div class="info-section">
        <div class="info-title">معلومات التقرير والفلاتر</div>
        <div style="margin-top: 10px;">
            <table width="100%">
                <tr>
                    <td width="50%">
                        <strong>تاريخ الإنشاء:</strong> {{ $generatedAt }}
                    </td>
                    <td width="50%" align="left">
                        <strong>الفلاتر المستخدمة:</strong>
                        @if($filters['annual_basis'] ?? false)
                            <span class="filter-badge">أساس سنوي ({{ $filters['year'] ?? now()->year }})</span>
                        @endif
                        @if(!empty($filters['start_date']))
                            <span class="filter-badge">من: {{ $filters['start_date'] }}</span>
                        @endif
                        @if(!empty($filters['end_date']))
                            <span class="filter-badge">إلى: {{ $filters['end_date'] }}</span>
                        @endif
                        @if(!empty($filters['batch_ids']))
                            <span class="filter-badge">دفعات محددة ({{ count($filters['batch_ids']) }})</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <table class="stats-table">
        <tr>
            <td class="stats-cell expenses">
                <div class="stats-label">إجمالي التكاليف</div>
                <div class="stats-value amount">{{ number_format($stats['total_expenses']) }} EGP</div>
            </td>
            <td class="stats-cell revenue">
                <div class="stats-label">إجمالي الإيرادات</div>
                <div class="stats-value amount">{{ number_format($stats['total_revenue']) }} EGP</div>
            </td>
            <td class="stats-cell profit">
                <div class="stats-label">صافي الربح / الخسارة</div>
                <div class="stats-value amount {{ $stats['net_profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    {{ number_format($stats['net_profit']) }} EGP
                </div>
            </td>
            <td class="stats-cell margin">
                <div class="stats-label">هامش الربح</div>
                <div class="stats-value">{{ number_format($stats['profit_margin'], 2) }}%</div>
            </td>
        </tr>
    </table>

    @if(!empty($stats['other_transactions']))
        <div class="info-title" style="margin-top: 25px;">معاملات المزرعة الأخرى ({{ count($stats['other_transactions']) }} معاملة)</div>
        <table class="batches-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>البيان</th>
                    <th>الفئة</th>
                    <th>الدفعة</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['other_transactions'] as $tx)
                    <tr>
                        <td>{{ $tx['date'] }}</td>
                        <td style="text-align: right;">{{ $tx['description'] ?: 'لا يوجد بيان' }}</td>
                        <td>{{ $tx['category'] }}</td>
                        <td>{{ $tx['batch_code'] }}</td>
                        <td class="{{ $tx['type'] === 'revenue' ? 'profit-positive' : 'profit-negative' }}">
                            {{ $tx['type_label'] }}
                        </td>
                        <td class="amount">{{ number_format($tx['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <table class="batches-table">
        <thead>
            <tr>
                <th>كود الدفعة</th>
                <th>الحالة</th>
                <th>تاريخ البداية</th>
                <th>المصاريف</th>
                <th>الإيرادات</th>
                <th>الربح</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['batches'] as $batch)
                <tr>
                    <td><strong>{{ $batch['code'] }}</strong></td>
                    <td>{{ $batch['status'] }}</td>
                    <td>{{ $batch['entry_date'] }}</td>
                    <td class="amount">{{ number_format($batch['expenses']) }}</td>
                    <td class="amount">{{ number_format($batch['revenue']) }}</td>
                    <td class="amount {{ $batch['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                        {{ number_format($batch['profit']) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>تم إنشاء هذا التقرير آلياً بواسطة نظام إدارة المزارع | {{ $storeName }}</p>
    </div>
</body>

</html>
