<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use  EncryptedAttribute;
    use \OwenIt\Auditing\Auditable;
    //

    protected $fillable  = [
        'muqam_statement',
        'document_date',
        'document_number',
        'document_type_id',
        'status_id',
        'urgency_id',
        'source_id',
        'scan_id',
        'muqam_statement',
        'recieved_date_by_us',
        'summary',
        'qaid_warida_number',
        'qaid_sadira_number',
        'qaid_sadira_date',
        'saved_file',
    ];

    



    protected $encryptable = ['muqam_statement','document_number','summary','saved_file'];


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
        return $this->belongsTo(Type::class);
    }
    public function urgency()
    {
        return $this->belongsTo(Urgency::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentDestination(){
        return $this->hasMany(DocumentDestination::class);
    }
    public function documentDestinationNoFeed(){
        return $this->hasMany(DocumentDestinationNoFeed::class);
    }

}
