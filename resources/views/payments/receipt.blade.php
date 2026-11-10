<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Receipt | Puihaha</title>

    <link rel="stylesheet" href="{{ asset('css/receipt.css') }}">
</head>

<body>
    <div class="receipt-actions">
        <a href="{{ route('transactions.show', $payment->transaction) }}">
            Back to transaction
        </a>

        <button type="button" onclick="window.print()">
            Print / Save as PDF
        </button>
    </div>

    <main class="receipt">
        <header class="receipt-header">
            <div>
                <p class="brand">Puihaha</p>
                <p>E-Learning Platform</p>
                <p>Membership Management and Transaction Processing System</p>
            </div>

            <h1>Payment Receipt</h1>
        </header>

        <dl class="receipt-details">
            <div>
                <dt>Receipt number</dt>
                <dd>{{ $payment->receipt_no }}</dd>
            </div>

            <div>
                <dt>Payment date</dt>
                <dd>
                    {{ $payment->paid_at->timezone('Asia/Manila')->format('F j, Y · g:i A') }}
                </dd>
            </div>

            <div>
                <dt>Member</dt>
                <dd>
                    {{ $payment->transaction->member->first_name }}
                    {{ $payment->transaction->member->last_name }}
                </dd>
            </div>

            <div>
                <dt>Membership number</dt>
                <dd>{{ $payment->transaction->member->membership_no }}</dd>
            </div>

            <div>
                <dt>Transaction reference</dt>
                <dd>{{ $payment->transaction->reference_no }}</dd>
            </div>

            <div>
                <dt>Transaction type</dt>
                <dd>{{ ucfirst($payment->transaction->type) }}</dd>
            </div>
        </dl>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount received</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        {{ $payment->transaction->level_name }} membership
                        <small>
                            {{ $payment->transaction->category_name }}
                            · {{ $payment->transaction->validity_months }} months
                        </small>
                    </td>

                    <td class="amount">
                        PHP {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <dl class="receipt-details">
            <div>
                <dt>Payment method</dt>
                <dd>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</dd>
            </div>

            <div>
                <dt>Payment reference</dt>
                <dd>{{ $payment->external_reference ?: '—' }}</dd>
            </div>

            <div>
                <dt>Recorded by</dt>
                <dd>{{ $payment->recordedBy?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt>Notes</dt>
                <dd class="notes">{{ $payment->notes ?: '—' }}</dd>
            </div>
        </dl>

        <footer>
            This receipt acknowledges the payment amount shown above.
            Membership approval is recorded separately.
        </footer>
    </main>
</body>

</html>