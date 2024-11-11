<?php

namespace App\Http\Controllers\api\template;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\app\destination\DestinationStoreRequest;
use App\Models\Destination;
use App\Models\DestinationType;
use App\Models\Translate;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class DestinationController extends Controller
{
    public function destinations()
    {
        try {
            $locale = App::getLocale();
            $tr = [];
            if ($locale === LanguageEnum::default->value)
                $tr = Destination::with(['type']) // Eager load relationships
                    ->select("name", 'id', 'created_at as createdAt', 'color', 'destination_type_id')->orderBy('id', 'desc')->get();
            else {
                $tr = $this->translations($locale);
            }
            return response()->json($tr, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('statuses error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destination($id)
    {
        try {
            $destination = Destination::find($id);
            if ($destination) {
                // Get type based on current locale
                $type = DestinationType::select('name', 'id', 'created_at')
                    ->find($destination->destination_type_id);
                if (!$type) {
                    return response()->json([
                        'message' => __('app_translation.destination_type_not_found')
                    ], 404, [], JSON_UNESCAPED_UNICODE);
                }
                $data = [
                    "id" => $destination->id,
                    "en" => $destination->name,
                    "color" => $destination->color,
                    "type" => [
                        "id" => $type->id,
                        "name" => $this->getTranslationWithNameColumn($type, DestinationType::class),
                        "createdAt" => $type->created_at,
                    ],
                ];
                $translations = Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Destination::class)->get();
                foreach ($translations as $translation) {
                    $data[$translation->language_name] = $translation->value;
                }
                return response()->json([
                    'destination' =>  $data,
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('Urgency error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(DestinationStoreRequest $request)
    {
        $payload = $request->validated();
        try {
            $destinationType = DestinationType::find($payload['destination_type_id']);
            if (!$destinationType) {
                return response()->json([
                    'message' => __('app_translation.destination_type_not_found')
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }
            // 1. Create
            $destination = Destination::create([
                "name" => $payload["english"],
                "color" => $payload["color"],
                "destination_type_id" => $destinationType->id,
            ]);
            if ($destination) {
                // 1. Translate
                $this->TranslateFarsi($payload["farsi"], $destination->id, Destination::class);
                $this->TranslatePashto($payload["pashto"], $destination->id, Destination::class);
                // Get local
                $locale = App::getLocale();
                if ($locale === LanguageEnum::default->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'destination' => [
                            "id" => $destination->id,
                            "name" => $destination->name,
                            "color" => $destination->color,
                            "type" => [
                                "id" => $destinationType->id,
                                "name" => $destinationType->name,
                                "createdAt" => $destinationType->created_at,
                            ],
                            "createdAt" => $destination->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else if ($locale === LanguageEnum::pashto->value) {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'destination' => [
                            "id" => $destination->id,
                            "name" => $payload["pashto"],
                            "color" => $destination->color,
                            "type" => [
                                "id" => $destinationType->id,
                                "name" => $this->getTranslationWithNameColumn($destinationType, DestinationType::class),
                                "createdAt" => $destinationType->created_at,
                            ],
                            "createdAt" => $destination->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json([
                        'message' => __('app_translation.success'),
                        'destination' => [
                            "id" => $destination->id,
                            "name" => $payload["farsi"],
                            "color" => $destination->color,
                            "type" => [
                                "id" => $destinationType->id,
                                "name" => $this->getTranslationWithNameColumn($destinationType, DestinationType::class),
                                "createdAt" => $destinationType->created_at,
                            ],
                            "createdAt" => $destination->created_at
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }

                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(DestinationStoreRequest $request)
    {
        $payload = $request->validated();
        // This validation not exist in UrgencyStoreRequest
        $request->validate([
            "id" => "required"
        ]);
        try {
            // 1. Find
            $destination = Destination::find($request->id);
            $type = DestinationType::find($request->destination_type_id);
            if ($destination && $type) {
                $locale = App::getLocale();
                // 1. Update
                $destination->name = $payload['english'];
                $destination->color = $payload['color'];
                $destination->destination_type_id  = $type->id;
                $destination->save();
                $translations =
                    Translate::where("translable_id", "=", $destination->id)
                    ->where('translable_type', '=', Destination::class)->get();
                foreach ($translations as $translation) {
                    if ($translation->language_name === LanguageEnum::farsi->value) {
                        $translation->value = $payload['farsi'];
                    } else if ($translation->language_name === LanguageEnum::pashto->value) {
                        $translation->value = $payload['pashto'];
                    }
                    $translation->save();
                }
                if ($locale === LanguageEnum::pashto->value) {
                    $destination->name = $payload['pashto'];
                } else if ($locale === LanguageEnum::farsi->value) {
                    $destination->name = $payload['farsi'];
                }
                return response()->json([
                    'message' => __('app_translation.success'),
                    'destination' => [
                        "id" => $destination->id,
                        "color" => $destination->color,
                        "name" => $destination->name,
                        "createdAt" => $destination->created_at,
                        "type" => [
                            "id" => $type->id,
                            "name" => $this->getTranslationWithNameColumn($type, DestinationType::class),
                            "createdAt" => $type->created_at
                        ]
                    ],
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy($id)
    {
        try {
            $destination = Destination::find($id);
            if ($destination) {
                // 1. Delete Translation
                Translate::where("translable_id", "=", $id)
                    ->where('translable_type', '=', Destination::class)->delete();
                $destination->delete();
                return response()->json([
                    'message' => __('app_translation.success'),
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    // Utils
    private function translations($locale)
    {
        // Fetch destinations with their translations and the related destination type translations
        $query = Destination::whereHas('translations', function ($query) use ($locale) {
            // Filter the translations for each destination by locale
            $query->where('language_name', '=', $locale);
        })
            ->with([
                'translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for Destination filtered by locale
                        ->where('language_name', '=', $locale);
                },
                'type.translations' => function ($query) use ($locale) {
                    // Eager load only the 'value' column for translations filtered by locale
                    $query->select('id', 'value', 'created_at', 'translable_id')
                        // Eager load the translations for DestinationType filtered by locale
                        ->where('language_name', '=', $locale);
                }
            ])
            ->select('id', 'color', 'destination_type_id', 'created_at')
            ->get();

        // Process results and include the translations of DestinationType within each Destination
        // Transform the collection
        $query->each(function ($destination) {
            // Get the translated values for the destination
            $destinationTranslation = $destination->translations->first();

            // Set the transformed values for the destination
            $destination->id = $destination->id;
            $destination->name = $destinationTranslation->value;  // Translated name
            $destination->color = $destination->color;  // Translated color
            $destination->createdAt = $destination->created_at;

            // Get the translated values for the related DestinationType
            $destinationTypeTranslation = $destination->type->translations->first();

            // Add the related DestinationType translation
            $type = [
                "id" => $destination->destination_type_id,
                "name" => $destinationTypeTranslation->value,  // Translated name of the type
                "createdAt" => $destinationTypeTranslation->created_at
            ];
            unset($destination->type);  // Remove destinationType relation
            $destination->type = $type;

            // Remove unnecessary data from the destination object
            unset($destination->translations);  // Remove translations relation
            unset($destination->created_at);  // Remove translations relation
            unset($destination->destination_type_id);  // Remove translations relation
        });
        return $query;
    }
}
