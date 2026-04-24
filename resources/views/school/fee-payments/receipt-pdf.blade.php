<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receipt_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .details-table td {
            padding: 5px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right !important;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .signature-box {
            margin-top: 50px;
            float: right;
            width: 200px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>{{ $school->address }}</p>
        <p><strong>FEE RECEIPT</strong></p>
    </div>

    <table class="details-table">
        <tr>
            <td width="50%">
                <strong>Receipt No:</strong> {{ $receipt_no }}<br>
                <strong>Date:</strong> {{ $payments->first()->payment_date->format('d-M-Y') }}<br>
                <strong>Payment Mode:</strong> {{ $payments->first()->payment_mode }}<br>
                @if($payments->first()->reference_number)
                <strong>Ref No:</strong> {{ $payments->first()->reference_number }}
                @endif
            </td>
            <td width="50%" class="text-right">
                <strong>Student Name:</strong> {{ $student->full_name }}<br>
                <strong>Admission No:</strong> {{ $student->admission_no }}<br>
                <strong>Class:</strong> {{ $student->class->name ?? 'N/A' }} {{ $student->section->name ?? '' }}<br>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Fee Particulars</th>
                <th>Period</th>
                <th class="text-right">Amount Paid (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($payments as $index => $payment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $payment->fee->feeType->name }} - {{ $payment->fee->feeName->name }}</td>
                <td>{{ $payment->fee->fee_period }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @php $total += $payment->amount; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Amount:</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 30px;">
        <strong>Amount in words:</strong> {{ ucwords((new \NumberFormatter("en", \NumberFormatter::SPELLOUT))->format($total)) }} Rupees Only.
    </p>

    <div class="signature-box">
        Authorized Signatory
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        <p>This is a computer-generated receipt.</p>
    </div>

</body>
</html>
