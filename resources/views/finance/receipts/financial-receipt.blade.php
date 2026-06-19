<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال مالي</title>
    <style>
        @page {
            size: 210mm 148mm; /* نصف A4 بالطول */
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.3;
            color: #000;
            background: white;
            width: 194mm; /* عرض الصفحة ناقص الهوامش */
            height: 132mm; /* ارتفاع الصفحة ناقص الهوامش */
        }
        
        .receipt {
            width: 100%;
            height: 100%;
            border: 2px solid #000;
            padding: 12px;
            background: white;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            flex-shrink: 0;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        
        .company-info {
            text-align: center;
            flex: 1;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .receipt-details {
            text-align: right;
            font-size: 12px;
        }
        
        .receipt-number {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .receipt-date {
            color: #666;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        
        .left-section,
        .right-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .label {
            font-weight: bold;
            min-width: 100px;
            font-size: 13px;
        }
        
        .value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding: 4px 6px;
            min-height: 24px;
            font-size: 13px;
            background: #f9f9f9;
        }
        
        .amount-section {
            border: 2px solid #000;
            padding: 12px;
            margin: 10px 0;
            text-align: center;
            background: #f8f9fa;
            flex-shrink: 0;
        }
        
        .amount-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .amount-value {
            font-size: 20px;
            font-weight: bold;
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 8px;
            background: white;
            color: #2c5530;
        }
        
        .amount-words {
            font-size: 12px;
            border: 1px solid #000;
            padding: 6px;
            min-height: 35px;
            text-align: right;
            background: white;
            line-height: 1.4;
        }
        
        .footer {
            border-top: 2px solid #000;
            padding-top: 10px;
            flex-shrink: 0;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-around;
            margin: 20px 0 10px 0;
        }
        
        .signature-box {
            text-align: center;
            width: 120px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 25px;
            padding-top: 5px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .contact-info {
            text-align: center;
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .receipt {
                border: 2px solid #000;
            }
            
            @page {
                size: 210mm 148mm;
                margin: 8mm;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        /* تأكد من عدم تجاوز المحتوى */
        .receipt * {
            max-width: 100%;
            word-wrap: break-word;
        }
    </style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Changa:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
        body, .pc-sidebar, .pc-header, .card, .btn, .dropdown-item, .nav-link, h1, h2, h3, h4, h5, h6 {
            font-family: 'Changa', sans-serif;
        }
</style>

</head>
<body>
    <button class="print-button no-print" onclick="window.print()">طباعة الإيصال</button>
    
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('logo-primary.png') }}" alt="شعار الشركة" class="logo">
            </div>
            
            <div class="company-info">
                <div class="company-name">نظام أثر الالكتروني</div>
                <div class="receipt-title">إيصال  {{$type}}</div>
            </div>
            
            <div class="receipt-details">
                <div class="receipt-number">رقم: {{ $receiptNumber ?? '000001' }}</div>
                <div class="receipt-date">{{ $date ?? now()->format('Y/m/d') }}</div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="right-section">
                <div class="row">
                    <span class="label">استلمت من:</span>
                    <span class="value">{{ $payeeName ?? '' }}</span>
                </div>

                <div class="row">
                    <span class="label">وذلك عن:</span>
                    <span class="value">{{ $description ?? '' }}</span>
                </div>

                <div class="row">
                    <span class="label">نوع المعاملة:</span>
                    <span class="value">{{ $transactionTypeName ?? '' }}</span>
                </div>
            </div>
            
            <div class="left-section">
                @if (!is_null($studentPayment))
                <div class="row">
                    <span class="label"> الباقي  :</span>
                    @php
                        $totalRemaining = $studentPayment->student
                            ->installments()
                            ->get()
                            ->sum(function($inst) {
                                return max(0, (float)$inst->amount_due - (float)$inst->paid_amount);
                            });
                    @endphp
                    <span class="value">{{ $totalRemaining }}</span>
                </div>
                @endif

                @if (!is_null($studentPayment))
                <div class="row">
                    <span class="label">المرحلة :</span>
                    <span class="value">{{ $studentPayment->student->currentEnrollment?->stage->name }} - {{ $studentPayment->student->currentEnrollment?->grade->name }}</span>
                </div>
                @endif

                

                <div class="row">
                    <span class="label">طريقة الدفع:</span>
                    <span class="value">
                        {{ $paymentMethod ?? '' }}
                    </span>
                </div>                
            </div>
        </div>

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="amount-label">المبلغ المستلم</div>
            <div class="amount-value">{{ $formattedAmount ?? '0.00' }} دينار ليبي</div>
            <div class="amount-words">
                {{ $amountInWords ?? 'المبلغ بالكلمات' }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">توقيع المستلم</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">توقيع المحاسب</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">الختم</div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <div>ليبيا</div>
                <div>ت: {{ '092000000' }}  | {{ 'info@athar.ly' }}</div>
            </div>
        </div>
    </div>

    <script>
        // Print function
        function printReceipt() {
            window.print();
        }
        
        // تأكد من الحجم عند التحميل
        window.addEventListener('load', function() {
            // تعديل حجم النافذة لتناسب حجم الإيصال
            if (window.location.search.includes('print=1')) {
                window.print();
            }
        });
    </script>
</body>
</html>