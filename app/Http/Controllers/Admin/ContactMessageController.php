<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\View\Components\Actions;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
        }

        return view('admin.pages.contact-messages.index');
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.pages.contact-messages.show', compact('contactMessage'));
    }

    protected function data()
    {
        return ContactMessage::query()
            ->latest()
            ->get()
            ->map(function ($contactMessage) {
                $contactMessage->name = '<strong>' . e($contactMessage->name) . '</strong>';
                $contactMessage->email = '<a href="mailto:' . e($contactMessage->email) . '">' . e($contactMessage->email) . '</a>';
                $contactMessage->subject = e($contactMessage->subject);
                $contactMessage->status = $this->statusBadge($contactMessage->status);
                $contactMessage->received_at = $contactMessage->created_at?->format('d M, Y h:i A');
                $contactMessage->actions = (new Actions([
                    'model' => $contactMessage,
                    'resource' => 'contact-messages',
                    'buttons' => [
                        'basic' => [
                            'view' => true,
                            'edit' => false,
                            'delete' => false,
                        ],
                    ],
                ]))->render()->render();

                return $contactMessage;
            })
            ->toArray();
    }

    protected function statusBadge(string $status): string
    {
        $color = match ($status) {
            'new' => 'warning',
            'read' => 'info',
            'replied' => 'success',
            default => 'secondary',
        };

        return '<span class="badge bg-label-' . $color . '">' . e(ucfirst($status)) . '</span>';
    }
}
