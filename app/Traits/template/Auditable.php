<?php

namespace App\Traits\template;

use Carbon\Carbon;
use App\Contracts\Encryptable;
use App\Models\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
        // Listen to the 'deleting' event to capture the model's state before it's deleted
        static::deleting(function ($model) {
            $model->beforeDeleteAudit();
        });
    }

    // Store the original values before the model is deleted
    protected function beforeDeleteAudit()
    {
        // Store the original values for auditing purposes
        $this->originalValuesBeforeDelete = $this->getOriginal();
    }

    // Method to log the audit data
    protected function logAudit($event)
    {
        // Get the current user (if available)
        $user = Auth::user();
        $userType = $user ? get_class($user) : null;
        $userId = $user ? $user->id : null;

        // Get the old and new values
        $oldValues = null; // Default for 'created' and 'deleted' events
        $newValues = null; // Default for 'created' and 'deleted' events

        // For 'updated' events, get the original and changed values
        if ($event === 'updated') {
            $oldValues = $this->getOriginal(); // Get the original values before update
            $newValues = $this->getDirty();    // Get the updated values
        }

        // For 'created' events, set the new values (new model attributes)
        if ($event === 'created') {
            $newValues = $this->attributesToArray(); // Get all the attributes of the newly created model
        }

        // For 'deleted' events, use the model's attributes before deletion (no new values)
        if ($event === 'deleted') {
            $oldValues = $this->originalValuesBeforeDelete ?? $this->getOriginal(); // Access the manually stored original values
            // No new values for delete, so $newValues remains null
        }
        // Encode binary fields to base64
        if ($oldValues) {
            $oldValues = $this->encodeBinaryFields($oldValues);
        }

        if ($newValues) {
            $newValues = $this->encodeBinaryFields($newValues);
        }

        // Gather additional metadata (URL, IP address, User agent)
        $url = Request::url();
        $ipAddress = Request::ip();
        $userAgent = Request::header('User-Agent');

        // Save audit data into the 'audits' table
        Audit::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues ? json_encode($oldValues) : null, // Ensure null if no old values
            'new_values' => $newValues ? json_encode($newValues) : null, // Ensure null if no new values
            'url' => $url,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'tags' => null, // Optionally, add tags here
        ]);
    }
    // Helper function to encode binary fields to base64
    protected function encodeBinaryFields($data)
    {
        // Loop through the data and encode any binary fields to base64
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) > 0) {
                // Check if the field value is binary data by detecting any non-printable characters
                if (preg_match('/[^\x20-\x7E]/', $value)) {
                    // If it's binary data, encode it to base64
                    $data[$key] = base64_encode($value);
                }
            }
        }
        return $data;
    }
    public static function insertAudit($model, $auditable_id)
    {
        $user = Auth::user();
        $userType = $user ? get_class($user) : null;
        $userId = $user ? $user->id : null;
        $auditable_type = $model ? get_class($model) : null;

        Audit::create([
            'user_type' => $userType,  // Assuming no authenticated user for this operation
            'user_id' => $userId,
            'event' => 'created',
            'auditable_type' => $auditable_type,  // Change this to the appropriate model class
            'auditable_id' => $auditable_id,  // You can set the model's ID if needed
            'old_values' => null,
            'new_values' => json_encode(encodeBinaryFields($model->getAttributes())),
            'url' => request()->url(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'tags' => null,
        ]);
    }
    public static function insertAuditByArray($class, $modelAttributes, $auditable_id)
    {
        $user = Auth::user();
        $userType = $user ? get_class($user) : null;
        $userId = $user ? $user->id : null;

        Audit::create([
            'user_type' => $userType,  // Assuming no authenticated user for this operation
            'user_id' => $userId,
            'event' => 'created',
            'auditable_type' => $class,  // Change this to the appropriate model class
            'auditable_id' => $auditable_id,  // You can set the model's ID if needed
            'old_values' => null,
            'new_values' => json_encode(encodeBinaryFields($modelAttributes)),
            'url' => request()->url(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'tags' => null,
        ]);
    }
    // Helper function to insert encrypted data for any model
    public static function insertEncryptedData($modelClass, $attributes)
    {
        $key = config('encryption.aes_key'); // The key for encryption
        // Prepare the SQL query with AES_ENCRYPT encryption for specific fields
        $columns = [];
        $placeholders = [];
        $bindings = [];

        // Get the table name dynamically from the model class
        $table = (new $modelClass)->getTable();  // This will give you the "documents" table name

        // Check if the model implements Encryptable interface and get the encrypted fields
        if (in_array(Encryptable::class, class_implements($modelClass))) {
            // Retrieve the list of encrypted fields from the model's method if it implements Encryptable
            $fieldsToEncrypt = $modelClass::getEncryptedFields();
        } else {
            // If the model doesn't implement Encryptable, use an empty array (no encryption fields)
            $fieldsToEncrypt = [];
        }

        // Loop through the attributes and set up columns, placeholders, and bindings
        foreach ($attributes as $column => $value) {
            $columns[] = $column;

            if (in_array($column, $fieldsToEncrypt)) {
                $placeholders[] = 'AES_ENCRYPT(?, ?)'; // Encrypt the field
                $bindings[] = $value;
                $bindings[] = $key;  // The encryption key
            } else {
                $placeholders[] = '?'; // Normal field
                $bindings[] = $value;
            }
        }

        // Add timestamp fields if they don't exist
        if (!in_array('created_at', $columns)) {
            $columns[] = 'created_at';
            $placeholders[] = '?';
            $bindings[] = Carbon::now();
        }

        if (!in_array('updated_at', $columns)) {
            $columns[] = 'updated_at';
            $placeholders[] = '?';
            $bindings[] = Carbon::now();
        }

        // Prepare the insert SQL statement
        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') 
        VALUES (' . implode(', ', $placeholders) . ')';

        // Execute the query
        DB::insert($sql, $bindings);

        // Return the last inserted ID
        return DB::getPdo()->lastInsertId();
    }
    public static function updateEncryptedData($modelClass, $attributes, $id)
    {
        $key = config('encryption.aes_key'); // The key for encryption

        // Prepare the SQL query with AES_ENCRYPT encryption for specific fields
        $columns = [];
        $placeholders = [];
        $bindings = [];

        // Get the table name dynamically from the model class
        $table = (new $modelClass)->getTable();  // This will give you the "documents" table name

        // Check if the model implements Encryptable interface and get the encrypted fields
        if (in_array(Encryptable::class, class_implements($modelClass))) {
            // Retrieve the list of encrypted fields from the model's method if it implements Encryptable
            $fieldsToEncrypt = $modelClass::getEncryptedFields();
        } else {
            // If the model doesn't implement Encryptable, use an empty array (no encryption fields)
            $fieldsToEncrypt = [];
        }

        // Loop through the attributes and set up columns, placeholders, and bindings
        foreach ($attributes as $column => $value) {
            if ($column != 'id') { // Don't include 'id' in columns since it's used for the WHERE condition
                $columns[] = $column;

                if (in_array($column, $fieldsToEncrypt)) {
                    $placeholders[] = $column . ' = AES_ENCRYPT(?, ?)';
                    $bindings[] = $value;
                    $bindings[] = $key;  // The encryption key
                } else {
                    $placeholders[] = $column . ' = ?';
                    $bindings[] = $value;
                }
            }
        }

        // Add timestamp fields
        if (!in_array('updated_at', $columns)) {
            $columns[] = 'updated_at';
            $placeholders[] = 'updated_at = ?';
            $bindings[] = Carbon::now();
        }

        // Prepare the update SQL statement
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $placeholders) . ' WHERE id = ?';

        // Add the ID at the end of the bindings for the WHERE clause
        $bindings[] = $id;  // Assuming you're updating the record with this ID
        Log::debug("SQL Query: " . $bindings[3]);

        DB::beginTransaction();
        DB::update($sql, $bindings);
        DB::commit();
        // Execute the query
        // DB::update($sql, $bindings);

        return true;
    }
    // public static function updateEncryptedData($modelClass, $attributes, $id)
    // {
    //     $key = config('encryption.aes_key'); // The key for encryption

    //     // Prepare the SQL query with AES_ENCRYPT encryption for specific fields
    //     $columns = [];
    //     $placeholders = [];
    //     $bindings = [];

    //     // Get the table name dynamically from the model class
    //     $table = (new $modelClass)->getTable();  // This will give you the "documents" table name

    //     // Check if the model implements Encryptable interface and get the encrypted fields
    //     if (in_array(Encryptable::class, class_implements($modelClass))) {
    //         // Retrieve the list of encrypted fields from the model's method if it implements Encryptable
    //         $fieldsToEncrypt = $modelClass::getEncryptedFields();
    //     } else {
    //         // If the model doesn't implement Encryptable, use an empty array (no encryption fields)
    //         $fieldsToEncrypt = [];
    //     }

    //     // Loop through the attributes and set up columns, placeholders, and bindings
    //     foreach ($attributes as $column => $value) {
    //         if ($column != 'id') { // Don't include 'id' in columns since it's used for the WHERE condition
    //             $columns[] = $column;

    //             if (in_array($column, $fieldsToEncrypt)) {
    //                 $placeholders[] = $column . ' = AES_ENCRYPT(?, ?)';
    //                 $bindings[] = $value;
    //                 $bindings[] = $key;  // The encryption key
    //             } else {
    //                 $placeholders[] = $column . ' = ?';
    //                 $bindings[] = $value;
    //             }
    //         }
    //     }

    //     // Add timestamp fields
    //     if (!in_array('updated_at', $columns)) {
    //         $columns[] = 'updated_at';
    //         $placeholders[] = 'updated_at = ?';
    //         $bindings[] = Carbon::now();
    //     }

    //     // Prepare the update SQL statement
    //     $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $placeholders) . ' WHERE id = ?';

    //     // Add the ID at the end of the bindings for the WHERE clause
    //     $bindings[] = $id;  // Assuming you're updating the record with this ID

    //     // Debugging the number of bindings and their contents
    //     Log::debug("Bindings count: " . count($bindings));
    //     Log::debug("SQL Query Bindings: " . implode(", ", $bindings));

    //     // Perform the update query
    //     DB::beginTransaction();
    //     DB::update($sql, $bindings);
    //     DB::commit();

    //     return true;
    // }

    public static function selectAndDecrypt($modelClass, $id)
    {
        // Get the decryption key from the configuration
        $key = config('encryption.aes_key'); // Assuming the AES key is stored in config/encryption.php

        // Get the table name dynamically from the model class
        $table = (new $modelClass)->getTable();  // This will give you the "documents" table name

        // Check if the model implements Encryptable interface and get the encrypted fields
        if (in_array(Encryptable::class, class_implements($modelClass))) {
            // Retrieve the list of encrypted fields from the model's method if it implements Encryptable
            $fieldsToDecrypt = $modelClass::getEncryptedFields();
        } else {
            // If the model doesn't implement Encryptable, use an empty array (no encryption fields)
            $fieldsToDecrypt = [];
        }

        // Get all columns from the table (excluding encrypted ones)
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // Prepare the SQL query to select columns and decrypt specific fields
        $selectColumns = [];

        // Loop through the fields and prepare the columns for decryption
        foreach ($fieldsToDecrypt as $field) {
            // Add the AES_DECRYPT function for each encrypted field, directly using the hardcoded key
            $selectColumns[] = 'AES_DECRYPT(' . $field . ', "' . $key . '") AS ' . $field;
        }

        // Include the other columns that are not encrypted
        foreach ($columns as $column) {
            // Only add columns that are not encrypted
            if (!in_array($column, $fieldsToDecrypt)) {
                $selectColumns[] = $column;
            }
        }

        // Prepare the SQL query with placeholders for the SELECT columns and WHERE clause for the `id`
        $sql = 'SELECT ' . implode(', ', $selectColumns) . ' FROM ' . $table . ' WHERE id = ?';

        // Execute the query with the ID for the WHERE clause
        $record = DB::select($sql, [$id]);

        // If a record is found, return it with decrypted fields
        if ($record) {
            // Return the first record as an array (assuming only one result)
            return (array) $record[0];
        }

        return null; // Return null if no record is found
    }
    public static function whereAndDecrypt($modelClass, $whereField, $whereValue)
    {
        // Get the decryption key from the configuration
        $key = config('encryption.aes_key'); // Assuming the AES key is stored in config/encryption.php

        // Get the table name dynamically from the model class
        $table = (new $modelClass)->getTable();  // This will give you the "document_destinations" table name

        // Check if the model implements Encryptable interface and get the encrypted fields
        if (in_array(Encryptable::class, class_implements($modelClass))) {
            // Retrieve the list of encrypted fields from the model's method if it implements Encryptable
            $fieldsToDecrypt = $modelClass::getEncryptedFields();
        } else {
            // If the model doesn't implement Encryptable, use an empty array (no encryption fields)
            $fieldsToDecrypt = [];
        }

        // Get all columns from the table (excluding encrypted ones)
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // Prepare the SQL query to select columns and decrypt specific fields
        $selectColumns = [];

        // Loop through the fields and prepare the columns for decryption
        foreach ($fieldsToDecrypt as $field) {
            // Add the AES_DECRYPT function for each encrypted field, directly using the hardcoded key
            $selectColumns[] = 'AES_DECRYPT(' . $field . ', "' . $key . '") AS ' . $field;
        }

        // Include the other columns that are not encrypted
        foreach ($columns as $column) {
            // Only add columns that are not encrypted
            if (!in_array($column, $fieldsToDecrypt)) {
                $selectColumns[] = $column;
            }
        }

        // Build the base SQL query with the SELECT columns
        $sql = 'SELECT ' . implode(', ', $selectColumns) . ' FROM ' . $table;

        // Add the WHERE condition for the provided field and value
        if ($whereField && $whereValue) {
            $sql .= ' WHERE ' . $whereField . ' = ?';
            $bindings = [$whereValue];
        } else {
            return null; // If no where condition is provided, return null
        }

        // Execute the query with the specified bindings
        $record = DB::select($sql, $bindings);

        // If a record is found, return it as an Eloquent model instance with decrypted fields
        if ($record) {
            // Hydrate the model with the data (first record)
            $recordData = (array) $record[0];

            // Create a new instance of the model and set the attributes
            $model = new $modelClass();
            $model->setRawAttributes($recordData, true);  // Set attributes without casting
            return $model;
        }

        return null; // Return null if no record is found
    }
}
