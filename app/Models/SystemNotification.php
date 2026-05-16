<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SystemNotification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'type', 'action_url', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function sendTo(User $user, string $title, string $body, ?string $url = null, string $type = 'info'): self
    {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'action_url' => $url,
        ]);
    }

    /**
     * @return Collection<int, self>
     */
    public static function sendToRole(string $role, string $title, string $body, ?string $url = null, string $type = 'info'): Collection
    {
        return User::with('employee.departmentRecord')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $user) => $user->hasAccessRole($role))
            ->map(fn (User $user) => self::sendTo($user, $title, $body, $url, $type));
    }
}
