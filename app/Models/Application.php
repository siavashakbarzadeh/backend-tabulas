<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory, HasMedia;

    const MEDIA_DOCUMENT_COLLECTION = 'application-documents';
    const MEDIA_SIGN_COLLECTION = 'application-signs';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'act_type',
        'recipient_office',
        'submission_date',
    ];

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
