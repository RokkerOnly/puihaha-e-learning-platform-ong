@extends('layouts.members')

@section('title', 'Record Payment')
@section('subtitle', 'Record a payment received for this membership transaction.')
@section('container-class', 'narrow')

@section('content')
<dl class="profile">
    <div>
        <dt>Transaction</dt>
        <dd class="transaction-reference">
            {{ $transaction->reference_no }}
        </dd>
    </div>

    <div>
        <dt>Member</dt>
        <dd>
            {{ $transaction->member->first_name }}
            {{ $transaction->member->last_name }}
        </dd>
    </div>

    <div>
        <dt>Outstanding Balance</dt>
        <dd>PHP {{ number_format($balance, 2) }}</dd>
    </div>
</dl>

<form action="{{ route('transactions.payments.store', $transaction) }}" method="POST">
    @csrf

    <input type="hidden" name="form_token" value="{{ $formToken }}">

    <div class="field">
        <label for="amount">Amount Received (PHP)</label>

        <input id="amount" type="number" name="amount" value="{{ old('amount', $balance) }}" min="0.01"
            max="{{ $balance }}" step="0.01" required>

        <p class="transaction-help">
            Enter the amount actually received. Partial payments are allowed.
        </p>
    </div>

    <div class="field">
        <label for="method">Payment Method</label>

        <select id="method" name="method" required>
            <option value="">Select a method</option>

            <option value="cash" @selected(old('method')==='cash' )>
                Cash
            </option>

            <option value="bank_transfer" @selected(old('method')==='bank_transfer' )>
                Bank Transfer
            </option>

            <option value="e_wallet" @selected(old('method')==='e_wallet' )>
                E-Wallet
            </option>
        </select>
    </div>

    <div class="field">
        <label for="external_reference">Bank / E-Wallet Reference</label>

        <input id="external_reference" type="text" name="external_reference" value="{{ old('external_reference') }}"
            maxlength="255">

        <p class="transaction-help">
            Required for bank transfers and e-wallet payments.
            Optional for cash.
        </p>
    </div>

    <div class="field">
        <label for="paid_at">Payment Date and Time — Philippines</label>

        <input id="paid_at" type="datetime-local" name="paid_at" value="{{ old(
                       'paid_at',
                       now('Asia/Manila')->format('Y-m-d\TH:i')
                   ) }}" max="{{ now('Asia/Manila')->format('Y-m-d\TH:i') }}" required>
    </div>

    <div class="field">
        <label for="notes">Notes — Optional</label>

        <textarea id="notes" name="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
    </div>

    <div class="actions">
        <button class="btn" type="submit">Save Payment</button>

        <a class="btn btn-secondary" href="{{ route('transactions.show', $transaction) }}">
            Cancel
        </a>
    </div>
</form>
@endsection