<?php

namespace App\Http\Controllers;

use App\Notifications\InventoryAlert;
use Illuminate\Http\Request;

// Notification Center. Every query goes through the signed-in user's own
// notifications, so nobody can read or change another account's alerts.
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->input('filter') === 'unread' ? 'unread' : 'all';

        $categories = InventoryAlert::CATEGORIES;
        if (! $user->isAdmin()) {
            unset($categories['staff']);
        }

        $category = array_key_exists((string) $request->input('category'), $categories)
            ? $request->input('category')
            : null;

        $notifications = ($filter === 'unread' ? $user->unreadNotifications() : $user->notifications())
            ->when($category, fn ($query) => $query->where('data->category', $category))
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'category' => $category,
            'categories' => $categories,
            'unreadCount' => $user->unreadNotifications()->count(),
            'totalCount' => $user->notifications()->count(),
        ]);
    }

    // Marks the notification as read and opens the related record
    public function open(Request $request, string $notification)
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // Only follow links inside this app
        $isLocal = is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//');

        return redirect()->to($isLocal ? $url : route('notifications.index'));
    }

    public function markAsRead(Request $request, string $notification)
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $count = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', $count > 0
            ? "{$count} " . str('notification')->plural($count) . ' marked as read.'
            : 'There were no unread notifications.');
    }
}
