<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * GET /help
     * Help & support page — FAQs + WhatsApp button + ticket list / new ticket.
     */
    public function help(Request $request)
    {
        $customerId = Auth::guard('customer')->id();
        $tickets = $customerId
            ? SupportTicket::with('lastMessage')
                ->where('customer_id', $customerId)
                ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
                ->limit(5)
                ->get()
            : collect();

        $faqs = $this->faqs();

        $storeName = \App\Models\StoreSetting::getStoreName();
        $whatsappNumber = \App\Models\StoreSetting::getValue('whatsapp_number') ?: \App\Models\StoreSetting::getValue('wa_number', '');
        $whatsappText   = \App\Models\StoreSetting::getValue('whatsapp_default_text') ?: \App\Models\StoreSetting::getValue('wa_message', 'Hi, I need help with my order');

        return view('shop.help', [
            'storeName'       => $storeName,
            'faqs'            => $faqs,
            'tickets'         => $tickets,
            'whatsappNumber'  => $whatsappNumber,
            'whatsappText'    => $whatsappText,
        ]);
    }

    /**
     * GET /account/support
     * Full ticket list (chat-style).
     */
    public function index()
    {
        $customerId = Auth::guard('customer')->id();
        $tickets = SupportTicket::with('lastMessage')
            ->where('customer_id', $customerId)
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(15)
            ->withQueryString();

        $storeName = \App\Models\StoreSetting::getStoreName();
        return view('shop.support.index', [
            'storeName' => $storeName,
            'tickets'   => $tickets,
        ]);
    }

    /**
     * POST /account/support
     * Create a new ticket (first message included).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'    => 'required|string|min:3|max:200',
            'category'   => 'required|in:general,order,payment,return,other',
            'order_code' => 'nullable|string|max:50',
            'message'    => 'required|string|min:3|max:5000',
        ]);

        $customerId = Auth::guard('customer')->id();

        $ticket = DB::transaction(function () use ($data, $customerId) {
            $t = SupportTicket::create([
                'customer_id'     => $customerId,
                'subject'         => $data['subject'],
                'category'        => $data['category'],
                'order_code'      => $data['order_code'] ?? null,
                'status'          => 'awaiting_admin',
                'last_message_at' => now(),
            ]);

            SupportMessage::create([
                'ticket_id'   => $t->id,
                'sender_id'   => $customerId,
                'sender_role' => 'customer',
                'message'     => $data['message'],
            ]);

            return $t;
        });

        return redirect()->route('shop.support.show', ['ticket' => $ticket->id])
            ->with('success', 'Your ticket has been submitted. We’ll get back to you shortly.');
    }

    /**
     * GET /account/support/{ticket}
     * Chat-style thread.
     */
    public function show(int $ticket)
    {
        $ticketModel = SupportTicket::with(['messages.sender', 'customer'])
            ->where('id', $ticket)
            ->where('customer_id', Auth::guard('customer')->id())
            ->firstOrFail();

        $storeName = \App\Models\StoreSetting::getStoreName();
        return view('shop.support.show', [
            'storeName' => $storeName,
            'ticket'    => $ticketModel,
        ]);
    }

    /**
     * POST /account/support/{ticket}/reply
     */
    public function reply(Request $request, int $ticket)
    {
        $ticketModel = SupportTicket::where('id', $ticket)
            ->where('customer_id', Auth::guard('customer')->id())
            ->firstOrFail();
        abort_if($ticketModel->isClosed(), 403, 'Ticket is closed.');

        $data = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        SupportMessage::create([
            'ticket_id'   => $ticketModel->id,
            'sender_id'   => Auth::guard('customer')->id(),
            'sender_role' => 'customer',
            'message'     => $data['message'],
        ]);

        $ticketModel->status          = 'awaiting_admin';
        $ticketModel->last_message_at = now();
        $ticketModel->save();

        return redirect()->route('shop.support.show', ['ticket' => $ticketModel->id])
            ->with('success', 'Reply sent.');
    }

    /**
     * POST /account/support/{ticket}/close
     */
    public function close(int $ticket)
    {
        $ticketModel = SupportTicket::where('id', $ticket)
            ->where('customer_id', Auth::guard('customer')->id())
            ->firstOrFail();
        $ticketModel->status    = 'closed';
        $ticketModel->closed_at = now();
        $ticketModel->closed_by = Auth::guard('customer')->id();
        $ticketModel->save();
        return back()->with('success', 'Ticket closed.');
    }

    /**
     * Default FAQ content. In a real product this would live in a model.
     */
    protected function faqs(): array
    {
        return [
            [
                'q' => 'How do I track my order?',
                'a' => 'Go to My Orders from the account menu, open the order, and view the live tracking timeline. We update it at every stage — placed, accepted, dispatched, delivered.',
            ],
            [
                'q' => 'Can I cancel my order?',
                'a' => 'Yes, you can cancel an order from the order detail page as long as it hasn’t been dispatched yet. After dispatch, please use the Returns flow if needed.',
            ],
            [
                'q' => 'How do returns and refunds work?',
                'a' => 'For returnable products, open the order detail page, click Return next to the item, choose a reason, and submit. Our team will review and arrange pickup. Refunds are credited to the original payment method.',
            ],
            [
                'q' => 'What payment methods are supported?',
                'a' => 'UPI (GPay, PhonePe, Paytm, BHIM, etc.), bank transfer, and selected cards. Cash on delivery is available in select pincodes.',
            ],
            [
                'q' => 'How do coupons work?',
                'a' => 'Apply an active coupon at checkout. If your order subtotal meets the minimum, the discount is applied instantly. Bonuses from previous orders are also issued as coupons that never expire.',
            ],
            [
                'q' => 'I didn’t receive an invoice — what should I do?',
                'a' => 'Your invoice is emailed to you the moment payment is confirmed. You can also download it any time from My Orders → View order → Download Invoice.',
            ],
        ];
    }
}
