<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * GET /admin/support
     * Ticket inbox.
     */
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['customer', 'lastMessage'])
            ->when($request->q, function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(function ($w) use ($term) {
                    $w->where('subject', 'like', "%{$term}%")
                      ->orWhere('order_code', 'like', "%{$term}%")
                      ->orWhereHas('customer', function ($c) use ($term) {
                          $c->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('mobile', 'like', "%{$term}%");
                      });
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByRaw('CASE WHEN status = "closed" THEN 1 ELSE 0 END')
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'open'       => SupportTicket::where('status', 'open')->count(),
            'awaiting'   => SupportTicket::where('status', 'awaiting_admin')->count(),
            'closed'     => SupportTicket::where('status', 'closed')->count(),
            'all'        => SupportTicket::count(),
        ];

        return view('admin.support.index', [
            'tickets' => $tickets,
            'stats'   => $stats,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    /**
     * GET /admin/support/{ticket}
     */
    public function show(int $ticket)
    {
        $ticketModel = SupportTicket::with(['messages.sender', 'customer'])
            ->findOrFail($ticket);

        return view('admin.support.show', [
            'ticket' => $ticketModel,
        ]);
    }

    /**
     * POST /admin/support/{ticket}/reply
     */
    public function reply(Request $request, int $ticket)
    {
        $ticketModel = SupportTicket::findOrFail($ticket);
        abort_if($ticketModel->isClosed(), 403, 'Ticket is closed.');

        $data = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        $adminId = Auth::id();

        SupportMessage::create([
            'ticket_id'   => $ticketModel->id,
            'sender_id'   => $adminId,
            'sender_role' => 'admin',
            'message'     => $data['message'],
        ]);

        $ticketModel->status          = 'awaiting_customer';
        $ticketModel->last_message_at = now();
        $ticketModel->save();

        return back()->with('success', 'Reply sent.');
    }

    /**
     * POST /admin/support/{ticket}/status
     */
    public function updateStatus(Request $request, int $ticket)
    {
        $ticketModel = SupportTicket::findOrFail($ticket);
        $data = $request->validate([
            'status' => 'required|in:open,awaiting_customer,awaiting_admin,closed',
        ]);

        $ticketModel->status = $data['status'];
        if ($data['status'] === 'closed') {
            $ticketModel->closed_at = now();
            $ticketModel->closed_by = Auth::id();
        } else {
            $ticketModel->closed_at = null;
            $ticketModel->closed_by = null;
        }
        $ticketModel->save();

        return back()->with('success', 'Ticket status updated.');
    }
}
