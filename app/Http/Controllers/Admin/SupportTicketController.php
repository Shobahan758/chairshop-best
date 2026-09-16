<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(bool $today = false): View
    {
        return view('admin.support', [
            'tickets' => SupportTicket::query()->with('customerAccount')
                ->when($today, fn ($query) => $query->whereToday('created_at'))
                ->latest('updated_at')->orderByDesc('id')->paginate(20),
            'title' => $today ? 'Today SMS' : 'All SMS',
        ]);
    }

    public function today(): View
    {
        return $this->index(true);
    }

    public function destroy(SupportTicket $ticket): RedirectResponse
    {
        $ticket->delete();

        return back()->with('success', 'Message deleted successfully.');
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:5000'], 'status' => ['required', Rule::in(['open', 'answered', 'closed'])]]);
        DB::transaction(function () use ($ticket, $data): void {
            $locked = SupportTicket::query()->lockForUpdate()->findOrFail($ticket->id);
            $messages = $locked->messages;
            if (! empty($data['message'])) {
                $messages[] = ['author' => 'support', 'body' => $data['message'], 'at' => now()->toIso8601String()];
            }
            $locked->update(['messages' => $messages, 'status' => $data['status']]);
        });

        return back()->with('success', 'Ticket updated.');
    }
}
