<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Membership Record | Puihaha</title>

    <link rel="stylesheet" href="{{ asset('css/receipt.css') }}">
</head>

<body>
    <div class="receipt-actions">
        <a href="{{ route('members.show', $member->id) }}">
            Back to member
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

            <h1>Membership Record</h1>
        </header>

        <dl class="receipt-details">
            <div>
                <dt>Membership number</dt>
                <dd>{{ $member->membership_no }}</dd>
            </div>

            <div>
                <dt>Full name</dt>
                <dd>{{ $member->first_name }} {{ $member->last_name }}</dd>
            </div>

            <div>
                <dt>Email address</dt>
                <dd>{{ $member->email }}</dd>
            </div>

            <div>
                <dt>Category</dt>
                <dd>{{ $member->category?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt>Membership level</dt>
                <dd>{{ $member->level?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt>Member status</dt>
                <dd>{{ $member->active ? 'Active' : 'Inactive' }}</dd>
            </div>

            <div>
                <dt>Membership started</dt>
                <dd>
                    {{ $member->membership_starts_at?->timezone('Asia/Manila')->format('F j, Y · g:i A') ?? 'No approved membership yet' }}
                </dd>
            </div>

            <div>
                <dt>Membership expires</dt>
                <dd>
                    {{ $member->membership_expires_at?->timezone('Asia/Manila')->format('F j, Y · g:i A') ?? 'No approved membership yet' }}
                </dd>
            </div>

            <div>
                <dt>Registered on</dt>
                <dd>
                    {{ $member->created_at->timezone('Asia/Manila')->format('F j, Y') }}
                </dd>
            </div>
        </dl>

        <footer>
            Generated on
            {{ now()->timezone('Asia/Manila')->format('F j, Y · g:i A') }}.
            This record reflects the member details at the time of printing.
        </footer>
    </main>
</body>

</html>