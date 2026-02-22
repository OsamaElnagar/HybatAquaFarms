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
        <div class="section-title">💰 الخزينة والماليات</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي الرصيد</span>
                    <span class="card-value text-primary">{{ number_format($data['treasury']['balance'] ?? 0, 2) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">إجمالي المقبوضات (اليوم)</span>
                    <span class="card-value text-success">{{ number_format($data['treasury']['incoming'] ?? 0, 2) }}
                        ج.م</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي المدفوعات (اليوم)</span>
                    <span class="card-value text-danger">{{ number_format($data['treasury']['outgoing'] ?? 0, 2) }}
                        ج.م</span>
                </td>
                <td class="card"></td>
            </tr>
        </table>
    </div>

    <!-- 2. Sales Section -->
    <div class="section">
        <div class="section-title">🛒 المبيعات</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إيرادات مبيعات اليوم</span>
                    <span class="card-value text-success">{{ number_format($data['sales']['revenue'] ?? 0, 2) }}
                        ج.م</span>
                </td>
                <td class="card">
                    <span class="card-title">عدد الطلبات (اليوم)</span>
                    <span class="card-value">{{ number_format($data['sales']['orders_count'] ?? 0) }} طلب</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">الكميات المباعة (اليوم)</span>
                    <span class="card-value">{{ number_format($data['sales']['weight'] ?? 0, 2) }} كجم</span>
                </td>
                <td class="card"></td>
            </tr>
        </table>
    </div>

    <!-- 3. Harvest Section -->
    <div class="section">
        <div class="section-title">🌾 عمليات الحصاد</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي الحصاد (اليوم)</span>
                    <span class="card-value text-primary">{{ number_format($data['harvest']['weight'] ?? 0, 2) }}
                        كجم</span>
                </td>
                <td class="card">
                    <span class="card-title">عدد العمليات (اليوم)</span>
                    <span class="card-value">{{ number_format($data['harvest']['operations_count'] ?? 0) }}
                        عمليات</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4. Feed & Batches Section -->
    <div class="section">
        <div class="section-title">🐟 الأعلاف والدورات</div>
        <table class="grid">
            <tr>
                <td class="card">
                    <span class="card-title">إجمالي العلف المستهلك (اليوم)</span>
                    <span class="card-value text-warning">{{ number_format($data['feed']['consumption'] ?? 0, 2) }}
                        كجم</span>
                </td>
                <td class="card">
                    <span class="card-title">تكلفة العلف التقديرية (اليوم)</span>
                    <span class="card-value text-danger">{{ number_format($data['feed']['cost'] ?? 0, 2) }} ج.م</span>
                </td>
            </tr>
            <tr>
                <td class="card">
                    <span class="card-title">الدورات النشطة حاليا</span>
                    <span class="card-value text-primary">{{ number_format($data['feed']['active_batches'] ?? 0) }}
                        دورات</span>
                </td>
                <td class="card"></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        تم إنشاء هذا التقرير آلياً بواسطة نظام إدارة المزرعة بتاريخ {{ now()->format('Y-m-d h:i A') }}
    </div>

</body>

</html>