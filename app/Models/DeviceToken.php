<?php
class DeviceToken extends Model
{
    protected $fillable = [
        'user_id', 'token', 'platform', 'last_seen_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
