@extends('layouts.admin')
@section('title', $title.' — ChairGhor')
@section('content')
<div class="admin-page-heading"><div><span>MESSAGE MANAGEMENT</span><h1>{{ $title }}</h1></div><b>Total: {{ $tickets->total() }}</b></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<section class="admin-panel">
    <div class="table-responsive">
        <table class="table admin-orders-table admin-messages-table align-middle">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse($tickets as $ticket)
                    @php
                        $contact = $ticket->messages[0]['contact'] ?? [];
                        $isReplyOpen = $errors->any() && (string) old('ticket_id') === (string) $ticket->id;
                    @endphp
                    <tr>
                        <td><b>{{ $contact['name'] ?? $ticket->customerAccount->name ?? 'Guest' }}</b><small class="d-block text-muted">#{{ $ticket->id }}</small></td>
                        <td class="text-nowrap">{{ $contact['phone'] ?? $ticket->customerAccount->phone ?? '—' }}</td>
                        <td class="text-break">{{ $contact['email'] ?? $ticket->customerAccount->email ?? '—' }}</td>
                        <td><b>{{ $ticket->subject }}</b></td>
                        <td><div class="admin-message-preview">{{ $ticket->messages[0]['body'] ?? '—' }}</div></td>
                        <td class="text-nowrap">{{ $ticket->created_at->format('d M Y') }}<small class="d-block text-muted">{{ $ticket->created_at->format('h:i A') }}</small></td>
                        <td><span class="category-status {{ $ticket->status === 'closed' ? 'inactive' : 'active' }}">{{ ucfirst($ticket->status) }}</span></td>
                        <td>
                            <div class="category-actions">
                                <button type="button" class="category-action edit" data-bs-toggle="collapse" data-bs-target="#message-{{ $ticket->id }}" aria-controls="message-{{ $ticket->id }}" aria-expanded="{{ $isReplyOpen ? 'true' : 'false' }}" aria-label="View and reply to message {{ $ticket->id }}" title="View / Reply"><i class="bi bi-chat-left-text" aria-hidden="true"></i></button>
                                <form method="post" action="{{ route('admin.support.destroy', $ticket) }}" onsubmit="return confirm('Delete this message and all replies? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="category-action delete" title="Delete" aria-label="Delete message {{ $ticket->id }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="admin-message-detail-row">
                        <td colspan="8" class="p-0 border-0">
                            <div class="collapse {{ $isReplyOpen ? 'show' : '' }}" id="message-{{ $ticket->id }}">
                                <div class="admin-message-detail">
                                    <h2 class="h6">Conversation #{{ $ticket->id }} · {{ $ticket->subject }}</h2>
                                    @foreach($ticket->messages as $message)
                                        <div class="border-bottom py-3"><small class="text-muted">{{ $message['author'] === 'support' ? 'Support' : ($contact['name'] ?? $ticket->customerAccount->name ?? 'Guest') }} · {{ \Illuminate\Support\Carbon::parse($message['at'])->format('d M Y, h:i A') }}</small><p class="admin-message-body mb-0 mt-2">{{ $message['body'] }}</p></div>
                                    @endforeach
                                    <form method="post" action="{{ route('admin.support.update', $ticket) }}" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                        <label for="reply-{{ $ticket->id }}" class="form-label">Reply</label>
                                        <textarea class="form-control mb-3" id="reply-{{ $ticket->id }}" name="message" maxlength="5000" rows="3" placeholder="Write your reply…">{{ $isReplyOpen ? old('message') : '' }}</textarea>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <label for="status-{{ $ticket->id }}" class="form-label mb-0">Status</label>
                                            <select class="admin-status-select" id="status-{{ $ticket->id }}" name="status">@foreach(['open', 'answered', 'closed'] as $status)<option value="{{ $status }}" @selected($status === ($isReplyOpen ? old('status') : $ticket->status))>{{ ucfirst($status) }}</option>@endforeach</select>
                                            <button class="btn btn-admin-primary" type="submit"><i class="bi bi-send" aria-hidden="true"></i> Save Reply / Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
{{ $tickets->links() }}
@endsection
