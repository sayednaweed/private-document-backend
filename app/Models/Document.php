<?php

namespace App\Models;

use App\Contracts\Encryptable;
use App\Models\User;
use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;

class Document extends Model implements Encryptable
{
    use Auditable;
    public static function getEncryptedFields(): array
    {
        return ['summary', 'saved_file'];  // List of fields to encrypt
    }
    protected $guarded = [];
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function scan()
    {
        return $this->belongsTo(Scan::class);
    }
    public function source()
    {
        return $this->belongsTo(Source::class);
    }
    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id', "id");
    }
    public function urgency()
    {
        return $this->belongsTo(Urgency::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentDestination()
    {
        return $this->hasMany(DocumentDestination::class);
    }
    public function documentDestinationNoFeed()
    {
        return $this->hasMany(DocumentDestinationNoFeedBack::class);
    }
}
