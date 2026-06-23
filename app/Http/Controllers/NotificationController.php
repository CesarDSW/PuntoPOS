<?php

namespace App\Http\Controllers;

use App\Models\CompanySettings;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function topbar()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $query = $this->visibleNotificationsQuery($user);

        $unreadCount = (clone $query)
            ->where('is_read', false)
            ->count();

        $notifications = $query
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->notification_id,
                    'type_code' => $notification->type_code,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => (bool) $notification->is_read,
                    'action_url' => $notification->action_url,
                    'created_at' => optional($notification->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no válido.',
            ], 401);
        }

        $updated = $this->visibleNotificationsQuery($user)
            ->where('notification_id', $id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'Notificación no encontrada.',
                'id_recibido' => $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Notificación marcada como leída.',
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no válido.',
            ], 401);
        }

        $updated = $this->visibleNotificationsQuery($user)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
            'updated' => $updated,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no válido.',
            ], 401);
        }

        $deleted = $this->visibleNotificationsQuery($user)
            ->where('notification_id', $id)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'Notificación no encontrada o ya fue eliminada.',
                'id_recibido' => $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Notificación eliminada correctamente.',
            'deleted' => $deleted,
        ]);
    }

    public function deleteRead()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no válido.',
            ], 401);
        }

        $deleted = $this->visibleNotificationsQuery($user)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'message' => "Se eliminaron {$deleted} notificación(es) leída(s).",
            'deleted' => $deleted,
        ]);
    }

    private function visibleNotificationsQuery($user)
    {
        $enabledTypes = [];

        if (!$user->isDeveloper() && $user->company_idfk) {
            $settings = CompanySettings::where('company_idfk', $user->company_idfk)->first();

            if ($settings) {
                if ($settings->notify_low_stock) {
                    $enabledTypes[] = 'LOW_STOCK';
                }

                if ($settings->notify_out_of_stock) {
                    $enabledTypes[] = 'OUT_OF_STOCK';
                }

                if ($settings->notify_sale_cancelled) {
                    $enabledTypes[] = 'SALE_CANCELLED';
                }

                if ($settings->notify_sale_pending ?? false) {
                    $enabledTypes[] = 'SALE_PENDING';
                }

                if ($settings->notify_sale_completed ?? false) {
                    $enabledTypes[] = 'SALE_COMPLETED';
                }
            }
        }

        return SystemNotification::query()
            ->where(function ($query) use ($user, $enabledTypes) {
                $query->where('target_user_idfk', $user->userr_id);

                if (!$user->isDeveloper() && $user->company_idfk && !empty($enabledTypes)) {
                    $query->orWhere(function ($companyQuery) use ($user, $enabledTypes) {
                        $companyQuery
                            ->whereNull('target_user_idfk')
                            ->where('company_idfk', $user->company_idfk)
                            ->whereIn('type_code', $enabledTypes);
                    });
                }
            });
    }
}