<?php

namespace App\Http\Controllers\web\generator;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use App\Traits\template\Auditable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ApiKeyController extends Controller
{
    public function index()
    {
        return view('keygenerator.dashboard');
    }

    public function key()
    {
        return view('keygenerator.generatekey');
    }

    public function load()
    {

        $apiKeys = ApiKey::all(); // Adjust this to match your model and database
        return response()->json($apiKeys);
    }
    public function store(Request $request)
    {

        // return $request;
        $request->validate([
            'name' => 'required|string|max:64',
            'directorate' => 'required|string|max:64',
            'apikey' => 'required|string',
            'ip_address' => 'ip'
        ]);
        $ipaddress = null;
        if ($request->ip_address) {
            $ipaddress = $request->ip_address;
        }
        $key = $request->apikey;
        $hashedKey = $this->hashApiKey($key);

        if ($request->id) {

            try {
                // Attempt to find the API key by its ID
                $apiKey = ApiKey::findOrFail($request->id);

                // Update the fields only if provided in the request
                $apiKey->name = $request->name ?? $apiKey->name;
                $apiKey->directorate = $request->directorate ?? $apiKey->directorate;
                $apiKey->key = $key ?? $apiKey->key;
                $apiKey->ip_address = $request->ip_address ?? $apiKey->ip_address;
                $apiKey->hashed_key = $hashedKey ?? $apiKey->hashed_key;
                // Save the updated record
                $apiKey->save();

                // Return success response
                return response()->json([
                    'api_key' => $apiKey->key,
                    'success' => 'Successfully updated API key'
                ], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // If the API key is not found, return a 404 error
                return response()->json([
                    'error' => 'API key not found'
                ], 404);
            } catch (\Exception $e) {
                // Handle any other exceptions
                return response()->json([
                    'error' => 'An error occurred while updating the API key',
                    'message' => $e->getMessage()
                ], 500);
            }
        } else {

            $apiKey = ApiKey::create([
                'name' => $request->name,
                'directorate' => $request->directorate,
                'key' => $key,
                'ip_address' => $ipaddress,
                'hashed_key' => $hashedKey,
                'is_active' => true,
            ]);
            // $insertedId = Auditable::insertEncryptedData(ApiKey::class, [
            //     'name' => $request->name,
            //     'directorate' => $request->directorate,
            //     'key' => $key,
            //     'ip_address' => $ipaddress,
            //     'hashed_key' => $hashedKey,
            //     'is_active' => true,
            // ]);
            // $$apiKey = ApiKey::find($insertedId);
            // if ($document)
            //     Auditable::insertAudit($document, $insertedId);

            return response()->json([
                'api_key' => $key,
                'success' => 'Successfuly added new api key'
            ], 201);
        }
    }

    public function revoke(Request $request)
    {

        $id = $request->id;
        $apiKey = ApiKey::findOrFail($id);

        $isactive = $apiKey->is_active;

        if ($isactive  == 1) {

            $apiKey->update(['is_active' => false]);

            return response()->json(['message' => 'API key revoked.']);
        } else {
            $apiKey->update(['is_active' => true]);

            return response()->json(['message' => 'API key revoked.']);
        }
    }

    public function edit(Request $request)
    {

        $data = ApiKey::where('id', $request->id)->first();
        return response()->json($data);
    }
    protected function hashApiKey($key)
    {
        return Hash::make($key);
    }
}
