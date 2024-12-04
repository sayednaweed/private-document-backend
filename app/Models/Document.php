<?php

namespace App\Models;

use App\Contracts\Encryptable;
use App\Models\User;
use App\Traits\template\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model implements Encryptable
{
    use Auditable;
    public static function getEncryptedFields(): array
    {
        return ['summary', 'muqam_statement', 'saved_file'];  // List of fields to encrypt
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
        return $this->hasMany(DocumentDestinationNoFeed::class);
    }
    public static function createEncrypt(array $attributes = [])
    {
        // Steps:
        // 1. Select column
        // 2. Use AES_ENCRYPT(?, ?) pass key and value and validate in case of null
        $key = config('encryption.aes_key'); // The key for encryption
        DB::insert(
            'insert into documents (summary, muqam_statement, saved_file, document_number, qaid_warida_number, document_date, qaid_warida_date, document_type_id, status_id, urgency_id, source_id, reciever_user_id, old, created_at, updated_at) 
            values (AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                isset($attributes['summary']) ? $attributes['summary'] : null,
                $key,    // The key for encryption
                isset($attributes['muqam_statement']) ? $attributes['muqam_statement'] : null,
                $key,    // The key for encryption
                isset($attributes['saved_file']) ? $attributes['saved_file'] : null,
                $key,    // The key for encryption
                $attributes['document_number'],
                $attributes['qaid_warida_number'],
                $attributes['document_date'],
                $attributes['qaid_warida_date'],
                $attributes['document_type_id'],
                $attributes['status_id'],
                $attributes['urgency_id'],
                $attributes['source_id'],
                Request()->user()->id,
                $attributes['old'],
                now(),  // created_at
                now(),  // updated_at
            ]
        );
        $insertedId = DB::getPdo()->lastInsertId();
        $document = Document::find($insertedId);

        Auditable::insertAudit($document, $document->id);
        return $document;
    }
    public static function updateEncrypt(array $attributes = [], $model)
    {
        // Steps:
        // 1. Select column
        // 2. Use AES_ENCRYPT(?, ?) pass key and value and validate in case of null
        $key = config('encryption.aes_key'); // The key for encryption
        DB::update(
            'update documents set summary, muqam_statement, saved_file, document_number, qaid_warida_number, document_date, qaid_warida_date, document_type_id, status_id, urgency_id, source_id, reciever_user_id, old, created_at, updated_at , where id = ? 
            values (AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                isset($attributes['summary']) ? $attributes['summary'] : null,
                $key,    // The key for encryption
                isset($attributes['muqam_statement']) ? $attributes['muqam_statement'] : null,
                $key,    // The key for encryption
                isset($attributes['saved_file']) ? $attributes['saved_file'] : null,
                $key,    // The key for encryption
                $attributes['document_number'],
                $attributes['qaid_warida_number'],
                $attributes['document_date'],
                $attributes['qaid_warida_date'],
                $attributes['document_type_id'],
                $attributes['status_id'],
                $attributes['urgency_id'],
                $attributes['source_id'],
                Request()->user()->id,
                $attributes['old'],
                now(),  // created_at
                now(),  // updated_at
                $model->id,
            ]
        );
        Auditable::insertAudit($model, $model->id);
    }
}
