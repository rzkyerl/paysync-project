<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Notify all active users with one of the supplied roles.
     *
     * @param  array<int, string>  $roles
     * @param  array<string, mixed>  $data
     */
    public function notifyRoles(
        int $companyId,
        array $roles,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = [],
        ?int $exceptUserId = null,
    ): void {
        $users = User::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('role', $roles)
            ->when($exceptUserId, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->get(['id', 'company_id']);

        $this->notifyUsers($users, $type, $title, $message, $actionUrl, $data);
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  array<string, mixed>  $data
     */
    public function notifyUsers(
        Collection $users,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = [],
    ): void {
        $now = now();
        $rows = $users->unique('id')->map(fn (User $user) => [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data === [] ? null : json_encode($data, JSON_THROW_ON_ERROR),
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            UserNotification::insert($rows);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = [],
    ): UserNotification {
        return $user->userNotifications()->create([
            'company_id' => $user->company_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }
}
