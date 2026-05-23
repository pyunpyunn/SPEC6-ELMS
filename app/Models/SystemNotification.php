<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SystemNotification extends Model
{
    public const RELATED_LEAVE_APPLICATION = 'leave_application';

    public const TYPE_LEAVE_REQUEST = 'leave_request';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'related_type',
        'related_id',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'related_id' => 'integer',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActionable(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('type', '!=', self::TYPE_LEAVE_REQUEST)
                ->orWhereNull('related_type')
                ->orWhereNull('related_id')
                ->orWhere(function (Builder $query): void {
                    $query->where('related_type', self::RELATED_LEAVE_APPLICATION)
                        ->whereIn('related_id', LeaveApplication::query()
                            ->where('status', 'pending')
                            ->select('id'));
                });
        });
    }

    public function scopeUnreadActionable(Builder $query): Builder
    {
        return $query->whereNull('read_at')->actionable();
    }

    public static function sendTo(
        User $user,
        string $title,
        string $body,
        ?string $url = null,
        string $type = 'info',
        ?string $relatedType = null,
        ?int $relatedId = null
    ): self {
        return self::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'action_url' => $url,
        ]);
    }

    /**
     * @return Collection<int, self>
     */
    public static function sendToRole(
        string $role,
        string $title,
        string $body,
        ?string $url = null,
        string $type = 'info',
        ?int $excludeUserId = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): Collection {
        return User::with('employee.departmentRecord')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $user) => $user->hasAccessRole($role) && ($excludeUserId === null || $user->id !== $excludeUserId))
            ->map(fn (User $user) => self::sendTo($user, $title, $body, $url, $type, $relatedType, $relatedId));
    }

    public static function markLeaveRequestSettled(LeaveApplication $leaveApplication): int
    {
        $leaveApplication->loadMissing(['employee', 'leaveType']);

        $legacyBodies = collect();

        if ($leaveApplication->employee && $leaveApplication->leaveType) {
            $legacyBodies->push(
                $leaveApplication->employee->full_name.' submitted a '.$leaveApplication->leaveType->name.' request for '.
                $leaveApplication->start_date->format('M d, Y').' to '.$leaveApplication->end_date->format('M d, Y').'.'
            );
            $legacyBodies->push(
                $leaveApplication->employee->full_name.' submitted a '.$leaveApplication->leaveType->name.' request.'
            );
        }

        return self::query()
            ->where('type', self::TYPE_LEAVE_REQUEST)
            ->whereNull('read_at')
            ->where(function (Builder $query) use ($leaveApplication, $legacyBodies): void {
                $query->where(function (Builder $related) use ($leaveApplication): void {
                    $related->where('related_type', self::RELATED_LEAVE_APPLICATION)
                        ->where('related_id', $leaveApplication->id);
                })
                    ->orWhere('action_url', 'like', '%/manager/approvals/'.$leaveApplication->id.'%')
                    ->when($legacyBodies->isNotEmpty(), fn (Builder $legacy) => $legacy->orWhereIn('body', $legacyBodies->unique()->values()->all()));
            })
            ->update(['read_at' => now()]);
    }
}
