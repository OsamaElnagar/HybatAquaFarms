<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 8px 12px;
            border-right: 4px solid #2563eb;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            background-color: #fff;
            width: 45%;
        }

        .card-title {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .card-value {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }

        .text-success {
            color: #16a34a;
        }

        .text-danger {
            color: #dc2626;
        }

        .text-primary {
            color: #2563eb;
        }

        .text-warning {
            color: #d97706;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>{{ config('app.name', 'Hybat Aqua Farms') }}</h1>
        <p>التقرير اليومي الشامل للمزرعة</p>
        <p>التاريخ: <strong>{{ $reportDate }}</strong></p>
    </div>

    <!-- 1. Treasury Section -->
    <div class="section">
        <div class="section-title">الخزينة والماليات</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي الرصيد</span>
                    <span class="card-value text-primary">{{ number_format($data['treasury']['balance'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي المقبوضات (اليوم)</span>
                    <span class="card-value text-success">{{ number_format($data['treasury']['incoming'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي المدفوعات (اليوم)</span>
                    <span class="card-value text-danger">{{ number_format($data['treasury']['outgoing'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card"></td>
            </tr>
        </table>
    </div>

    <!-- 2. Sales Section -->
    <div class="section">
        <div class="section-title">المبيعات</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">اليوم ({{ number_format($data['sales']['today_count'] ?? 0) }} طلب)</span>
                    <span class="card-value text-success">{{ number_format($data['sales']['today_revenue'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">هذا الأسبوع</span>
                    <span class="card-value">{{ number_format($data['sales']['week_revenue'] ?? 0) }} ج.م</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">هذا الشهر ({{ number_format($data['sales']['month_count'] ?? 0) }}
                        طلب)</span>
                    <span class="card-value text-primary">{{ number_format($data['sales']['month_revenue'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">الكميات المباعة (اليوم)</span>
                    <span class="card-value">{{ number_format($data['sales']['weight'] ?? 0) }} كجم</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. Harvest Section -->
    <div class="section">
        <div class="section-title">عمليات الحصاد (ملخص الشهر)</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي عمليات الشهر</span>
                    <span class="card-value text-primary">{{ number_format($data['harvest']['month_count'] ?? 0) }}
                        عمليات</span>
                </td>
                <td class="card">
                    <span class="card-title">أوزان الشهر</span>
                    <span class="card-value">{{ number_format($data['harvest']['month_weight'] ?? 0) }} كجم</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي صناديق الشهر</span>
                    <span class="card-value">{{ number_format($data['harvest']['month_boxes'] ?? 0) }} صندوق</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي مبيعات החصاد التقديرية (الشهر)</span>
                    <span class="card-value text-success">{{ number_format($data['harvest']['month_sales'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
        </table>

        @if(isset($data['harvest']['latest']) && count($data['harvest']['latest']) > 0)
            <div style="margin-top: 15px; font-size: 13px;">
                <strong>أحدث عمليات حصاد الشهر:</strong>
                <ul style="padding-right: 20px; margin-top: 5px;">
                    @foreach($data['harvest']['latest'] as $harvest)
                        <li>
                            رقم {{ $harvest->harvest_number ?? '-' }} ({{ $harvest->harvest_date->format('Y-m-d') }}):
                            دورة <strong>{{ $harvest->harvestOperation->batch->batch_code ?? '-' }}</strong> -
                            {{ $harvest->status?->getLabel() ?? 'بدون حالة' }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 4. Feed & Batches Section -->
    <div class="section">
        <div class="section-title">الأعلاف والدورات النشطة</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">الدورات النشطة</span>
                    <span class="card-value text-primary">{{ number_format($data['batches']['count'] ?? 0) }}
                        دورات</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي الأسماك الحية المتوقعة</span>
                    <span class="card-value">{{ number_format($data['batches']['total_living'] ?? 0) }} سمكة</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي العلف المستهلك (للدورات النشطة)</span>
                    <span class="card-value text-warning">{{ number_format($data['batches']['total_feed'] ?? 0) }}
                        كجم</span>
                </td>
                <td class="card">
                    <span class="card-title">التكلفة الإجمالية (للدورات النشطة)</span>
                    <span class="card-value text-danger">{{ number_format($data['batches']['total_expenses'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
        </table>

        @if(isset($data['batches']['details']) && count($data['batches']['details']) > 0)
            <div style="margin-top: 15px; font-size: 13px;">
                <strong>تفاصيل الدورات النشطة:</strong>
                <ul style="padding-right: 20px; margin-top: 5px;">
                    @foreach($data['batches']['details'] as $batch)
                        <li style="margin-bottom: 8px;">
                            <strong>مزرعة: {{ $batch['farm_name'] }}</strong> -
                            دورة <code>{{ $batch['batch_code'] }}</code> ({{ $batch['species_name'] }})<br>
                            <span style="color: #475569;">
                                النشاط: {{ $batch['days_active'] }} يوم |
                                العدد: {{ number_format($batch['current_qty']) }} / {{ number_format($batch['initial_qty']) }}
                                (نافق: {{ $batch['mortality_rate'] }}%)<br>
                                @if($batch['current_weight_avg'] > 0)
                                    الأوزان: متوسط {{ number_format($batch['current_weight_avg']) }} جم (دخول:
                                    {{ number_format($batch['initial_weight_avg']) }} جم)<br>
                                @endif
                                الاستهلاك: {{ number_format($batch['total_feed_consumed']) }} كجم |
                                التكلفة: {{ number_format($batch['total_cycle_expenses']) }} ج.م |
                                الرصيد المتبقي: {{ number_format($batch['outstanding_balance']) }} ج.م
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 5. Feed Stock Alerts -->
    <div class="section">
        <div class="section-title">مخزون الأعلاف المركزي</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">الأصناف المتوفرة</span>
                    <span class="card-value">{{ number_format($data['feed_stock']['items_count'] ?? 0) }} صنف</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي وزن المخزون</span>
                    <span class="card-value text-primary">{{ number_format($data['feed_stock']['total_weight'] ?? 0) }}
                        كجم ({{ number_format(($data['feed_stock']['total_weight'] ?? 0) / 1000) }} طن)</span>
                </td>
            </tr>
        </table>

        @if(isset($data['feed_stock']['low_stocks']) && count($data['feed_stock']['low_stocks']) > 0)
            <div
                style="margin-top: 15px; font-size: 13px; border: 1px solid #fca5a5; background-color: #fef2f2; padding: 10px; border-radius: 6px;">
                <strong class="text-danger">تنبيهات انخفاض المخزون (أقل من 500 كجم):</strong>
                <ul style="padding-right: 20px; margin-top: 5px; margin-bottom: 0;">
                    @foreach($data['feed_stock']['low_stocks'] as $stock)
                        <li>
                            <strong>{{ $stock->feedItem->name ?? 'علف' }}</strong>
                            في {{ $stock->warehouse->name ?? 'مستودع' }}
                            ({{ $stock->warehouse->farm->name ?? 'مزرعة' }}):
                            <span class="text-danger"><strong>{{ number_format($stock->quantity_in_stock ?? 0) }}
                                    كجم</strong></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 6. Feed Issues (Latest) -->
    @if(isset($data['feed']['latest_grouped']) && count($data['feed']['latest_grouped']) > 0)
        <div class="section">
            <div class="section-title">منصرف الأعلاف (آخر يومين)</div>
            <div style="padding: 0 10px;">
                @foreach($data['feed']['latest_grouped'] as $date => $farms)
                    <div style="margin-bottom: 12px;">
                        <strong
                            style="color: #1e3a8a; border-bottom: 1px dashed #cbd5e1; display: inline-block; margin-bottom: 5px;">📅
                            {{ $date }}</strong>
                        @foreach($farms as $farmName => $items)
                            <div style="margin-bottom: 8px; padding-right: 15px;">
                                <strong>مزرعة: {{ $farmName }}</strong>
                                <ul style="padding-right: 20px; margin-top: 2px;">
                                    @foreach($items as $itemName => $quantity)
                                        <li>{{ $itemName }}: <strong class="text-primary">{{ number_format($quantity) }} كجم</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            @if(isset($data['feed']['latest_list']) && count($data['feed']['latest_list']) > 0)
                <div style="margin-top: 15px; font-size: 13px;">
                    <strong>تفاصيل أحدث عمليات الصرف:</strong>
                    <ul style="padding-right: 20px; margin-top: 5px;">
                        @foreach($data['feed']['latest_list'] as $issue)
                            <li>
                                <em>[{{ \Carbon\Carbon::parse($issue->date)->format('Y-m-d') }}]</em>
                                دورة <strong>{{ $issue->batch?->batch_code ?? 'غير متوفر' }}</strong>
                                ({{ $issue->farm?->name ?? 'غير محدد' }}):
                                {{ $issue->feedItem?->name ?? 'علف غير محدد' }} -
                                <span class="text-danger"><strong>{{ number_format($issue->quantity) }} كجم</strong></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- 7. Expenses & Advances -->
    <div class="section">
        <div class="section-title">المصروفات والسلف (خلال الشهر الجاري)</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي حركة السندات والعهد (الشهر)</span>
                    <span class="card-value text-danger">{{ number_format($data['expenses']['grand_total'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">عدد عمليات الصرف</span>
                    <span class="card-value">{{ number_format($data['expenses']['count'] ?? 0) }} حركة</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">سلف الموظفين النشطة الآن</span>
                    <span class="card-value">{{ number_format($data['advances']['count'] ?? 0) }} سلف</span>
                </td>
                <td class="card">
                    <span class="card-title">أرصدة السلف المتبقية (قائمة)</span>
                    <span class="card-value text-warning">{{ number_format($data['advances']['remaining'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
        </table>

        @if(isset($data['expenses']['latest']) && count($data['expenses']['latest']) > 0)
            <div style="margin-top: 15px; font-size: 13px;">
                <strong>(أحدث حركات الصرف) - تفاصيل المعاملات:</strong>
                <ul style="padding-right: 20px; margin-top: 5px;">
                    @foreach($data['expenses']['latest'] as $expense)
                        <li style="margin-bottom: 4px;">
                            <strong>[{{ $expense['type'] }}]</strong>
                            <span class="text-danger"><strong>{{ number_format((float) $expense['amount']) }}
                                    ج.م</strong></span>
                            | <em>{{ $expense['date']->format('Y-m-d') }}</em><br>
                            <span
                                style="color: #64748b;">{{ \Illuminate\Support\Str::limit($expense['desc'] ?? '-', 80) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 8. Cashflow -->
    <div class="section">
        <div class="section-title">التدفقات النقدية والقيود (الشهر)</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي القيود</span>
                    <span class="card-value">{{ number_format($data['cashflow']['count'] ?? 0) }} قيد</span>
                </td>
                <td class="card">
                    <span class="card-title">حجم التداول التقديري للقيود (الشهر)</span>
                    <span class="card-value text-primary">{{ number_format($data['cashflow']['volume'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
        </table>

        @if(isset($data['cashflow']['latest']) && count($data['cashflow']['latest']) > 0)
            <div style="margin-top: 15px; font-size: 13px;">
                <strong>أحدث قيود اليومية:</strong>
                <ul style="padding-right: 20px; margin-top: 5px;">
                    @foreach($data['cashflow']['latest'] as $entry)
                        <li>
                            ({{ \Carbon\Carbon::parse($entry->date)->format('Y-m-d') }}):
                            {{ \Illuminate\Support\Str::limit($entry->description ?? $entry->reference ?? 'قيد يومية', 60) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 9. External Calculations -->
    <div class="section">
        <div class="section-title">الحسابات الخارجية</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي المقبوضات (الحسابات الخارجية)</span>
                    <span
                        class="card-value text-success">{{ number_format($data['external_calculations']['total_receipts'] ?? 0) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي المدفوعات (الحسابات الخارجية)</span>
                    <span
                        class="card-value text-danger">{{ number_format($data['external_calculations']['total_payments'] ?? 0) }}
                        ج.م</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">صافي الأثر (الرصيد الكلي)</span>
                    <span
                        class="card-value {{ ($data['external_calculations']['net_balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($data['external_calculations']['net_balance'] ?? 0) }} ج.م
                    </span>
                </td>
                <td class="card">
                    <span class="card-title">حركة الشهر (مقبوض / مدفوع)</span>
                    <span class="card-value">
                        <span
                            class="text-success">{{ number_format($data['external_calculations']['month_receipts'] ?? 0) }}</span>
                        /
                        <span
                            class="text-danger">{{ number_format($data['external_calculations']['month_payments'] ?? 0) }}</span>
                        ج.م
                    </span>
                </td>
            </tr>
        </table>

        @if(isset($data['external_calculations']['accounts']) && count($data['external_calculations']['accounts']) > 0)
            <div style="margin-top: 15px; font-size: 13px;">
                <strong>أرصدة الحسابات:</strong>
                <ul style="padding-right: 20px; margin-top: 5px;">
                    @foreach($data['external_calculations']['accounts'] as $account)
                        @php
                            $balance = ($account->receipts_sum ?? 0) - ($account->payments_sum ?? 0);
                        @endphp
                        <li style="margin-bottom: 4px;">
                            <strong>{{ $account->name }}</strong>:
                            <span class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                <strong>{{ number_format($balance) }} ج.م</strong>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="footer">
        تم إنشاء هذا التقرير آلياً بواسطة نظام إدارة المزرعة بتاريخ {{ now()->format('Y-m-d h:i A') }}
    </div>

</body>

</html>