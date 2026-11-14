@extends('layouts.admin')

@section('title', $invoice->invoice_number)

@section('actions')
    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-light"><i class="mdi mdi-download"></i> Download PDF</a>
    @if (! $invoice->isPaid())
        <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-email-fast-outline"></i> Send email</button>
        </form>
    @endif
    <a href="{{ route('invoices.index') }}" class="btn btn-light">Back to list</a>
@endsection

@section('content')
    <div class="detail-layout">
        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge badge-{{ $invoice->status_badge }}"><i class="mdi {{ $invoice->status_icon }}"></i> {{ $invoice->status_label }}</span>
                </div>

                <div class="form-grid mb-2">
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt>Invoice #</dt>
                            <dd>{{ $invoice->invoice_number }}</dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt>Customer</dt>
                            <dd>
                                <a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer->name }}</a>
                                <div class="text-muted small">{{ $invoice->customer->email ?: 'No email on file' }}</div>
                            </dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt>Period / Due</dt>
                            <dd>
                                {{ $invoice->period_label }}
                                · due {{ $invoice->due_at?->format('d M Y') ?? '—' }}
                            </dd>
                        </dl>
                    </div>
                    <div class="span-4">
                        <dl class="detail-dl">
                            <dt>Total</dt>
                            <dd><strong>{{ $invoice->currency }} {{ number_format((float) $invoice->total, 2) }}</strong></dd>
                        </dl>
                    </div>
                    @if ($invoice->isPaid())
                        <div class="span-8">
                            <dl class="detail-dl">
                                <dt>Paid</dt>
                                <dd>
                                    {{ $invoice->paid_at?->format('d M Y, h:i A') }}
                                    @if ($invoice->payment_method)
                                        · {{ str_replace('_', ' ', $invoice->payment_method) }}
                                    @endif
                                    @if ($invoice->payment_note)
                                        <span class="text-muted">— {{ $invoice->payment_note }}</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    @endif
                </div>

                <div class="section-label"><i class="mdi mdi-format-list-bulleted"></i> Line items</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-right">{{ $invoice->currency }} {{ number_format((float) $item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if (! $invoice->isPaid())
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><i class="mdi mdi-cash-check"></i> Mark paid</h4>
                    <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                        @csrf
                        <div class="form-grid">
                            <div class="span-12">
                                <x-ui.field name="payment_method" label="Payment method" icon="mdi-cash">
                                    <select id="payment_method" name="payment_method" class="form-control">
                                        <option value="">—</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank transfer</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="other">Other</option>
                                    </select>
                                </x-ui.field>
                            </div>
                            <div class="span-12">
                                <x-ui.field name="payment_note" label="Note">
                                    <input type="text" id="payment_note" name="payment_note" class="form-control" maxlength="500">
                                </x-ui.field>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">Mark as paid</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
