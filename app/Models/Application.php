<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends Model
{
    use HasFactory, HasMedia;

    const MEDIA_DOCUMENT_COLLECTION = 'application-documents';
    const MEDIA_SIGN_COLLECTION = 'application-signs';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'name',
        'act_type',
        'recipient_office',
        'submission_date',
        'status', // New status column
    ];

    /**
     * Each application belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Many firmatarios can be attached to an application.
     */
    public function firmatarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'application_firmatario', 'application_id', 'firmatario_id');
    }

    public function document()
    {
        return $this->media()->collection(self::MEDIA_DOCUMENT_COLLECTION);
    }

    public function sign()
    {
        return $this->media()->collection(self::MEDIA_SIGN_COLLECTION);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
        ];
    }
}

