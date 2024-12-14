<?php

namespace App\Http\Controllers;

use App\Enums\LanguageEnum;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\api\template\DashboardController;
use App\Models\AdverbType;
use App\Models\Approval;
use App\Models\Audit;
use App\Models\ColumnTranslate;
use App\Models\Contact;
use App\Models\Destination;
use App\Models\DestinationType;
use App\Models\District;
use App\Models\Document;
use App\Models\DocumentAdverb;
use App\Models\DocumentDestination;
use App\Models\DocumentDestinationNoFeedBack;
use App\Models\DocumentsEnView;
use App\Models\DocumentsFaView;
use App\Models\DocumentsPsView;
use App\Models\DocumentType;
use App\Models\Email;
use App\Models\Language;
use App\Models\ModelJob;
use App\Models\ReportGenerated;
use App\Models\RolePermission;
use App\Models\Scan;
use App\Models\Source;
use App\Models\Status;
use App\Models\Translate;
use App\Models\Urgency;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UsersEnView;
use App\Models\UsersFaView;
use App\Models\UsersPsView;
use App\Models\UsersView;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\select;
use App\Traits\template\Auditable;
use Illuminate\Support\Arr;

class TestController extends Controller
{
    public function tables()
    {
        return $tables = DB::select("
        SELECT table_name AS tableName
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_type = 'BASE TABLE'
        AND table_name NOT IN (
        'password_reset_tokens', 
        'personal_access_tokens', 
        'audits', 
        'cache', 
        'cache_locks', 
        'api_keys', 
        'column_translates', 
        'error_logs', 
        'failed_jobs', 
        'job_batches', 
        'migrations', 
        'time_units'
        )
    ");
    }
    // updated
    // created
    // deleted
    // viewed
    // Define a simple mapping of table names to model classes
    public $modelMapping = [
        'users' => User::class,
        'user_permissions' => UserPermission::class,
        'contacts' => Contact::class,
        'emails' => Email::class,
        'approvals' => Approval::class,
        'destinations' => Destination::class,
        'destination_types' => DestinationType::class,
        'report_generateds' => ReportGenerated::class,
        // Aplication
        'documents' => Document::class,
        'adverb_types' => AdverbType::class,
        'document_adverbs' => DocumentAdverb::class,
        'document_destinations' => DocumentDestination::class,
        'document_destination_no_feed_backs' => DocumentDestinationNoFeedBack::class,
        'document_types' => DocumentType::class,
        'model_jobs' => ModelJob::class,
        'scans' => Scan::class,
        'sources' => Source::class,
        'statuses' => Status::class,
        'urgencies' => Urgency::class,
    ];
    public function index(Request $request)
    {
        $results = DB::select('CALL GetUsers(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            'ps',
            '2024-11-13',    // Start date filter
            "2024-12-13",      // End date filter
            null, // Search column
            null,  // Search value
            null,         // Sort column
            'asc',        // Sort order
            3,      // Records per page
            1          // Current page
        ]);

        // The first result set contains the total records
        $totalRecords = $results[0]->total;

        // The second result set contains the paginated data
        $users = collect(array_slice($results, 1));

        // Calculate total pages
        $totalPages = ceil($totalRecords / 10);

        // Prepare pagination result
        $pagination = [
            'data' => $users,
            'total' => $totalRecords,
            'per_page' => 10,
            'current_page' => 1,
            'last_page' => $totalPages,
            'from' => ((1 - 1) * 10) + 1,
            'to' => min(1 * 10, $totalRecords),
        ];

        return $pagination;
        $tableName = "users";
        // Check if the provided table name exists in the mapping
        if (!array_key_exists($tableName, $this->modelMapping)) {
            return response()->json(['error' => 'Model not found for the provided table'], 404);
        }
        $modelClass = $this->modelMapping[$tableName];

        // Search for audits related to the provided model (auditable_type)
        $audits = Audit::where('auditable_type', $modelClass)
            ->with('user') // Eager load the user who performed the action
            ->get(['event', 'old_values', 'new_values', 'created_at', 'user_id']);

        // Map the audits to a structured JSON response
        $auditChanges = $audits->map(function ($audit) {
            return [
                'event' => $audit->event, // 'created', 'updated', or 'deleted'
                'created_at' => $audit->created_at, // Timestamp of the audit
                'old_values' => json_decode($audit->old_values), // Raw JSON of old values
                'new_values' => json_decode($audit->new_values), // Raw JSON of new values
                'user' => [
                    'id' => $audit->user ? $audit->user->id : null, // User ID
                    'username' => $audit->user ? $audit->user->username : null, // Username (or name, change based on your field)
                    'email' => $audit->user && $audit->user->email ? $audit->user->email->value : null, // User email
                ],
            ];
        });

        // Return the audit changes as raw JSON
        return response()->json($auditChanges);

        return Audit::where('auditable_type', User::class)->get();

        // $documentDestination = Auditable::whereAndDecrypt(DocumentDestination::class, 'id', 1);
        // // 3. Update deputy data
        // $documentDestination->feedback = "Doneeeeeeee";
        // // unset($documentDestination['created_at']);
        // // unset($documentDestination['updated_at']);
        // Auditable::updateEncryptedData(DocumentDestination::class, $documentDestination, $documentDestination->id,);

        // return $documentDestination;




        // Finding by id and user_type:
        //$model = Auditable::selectAndDecrypt(Document::class, 28, 'user_type', 2);
        // Finding only by user_type:
        // $model = Auditable::selectAndDecrypt(Document::class, null, 'user_type', 2);


        // $model = Auditable::selectAndDecrypt(Document::class, 1);
        $model = Auditable::whereAndDecrypt(DocumentDestination::class, 'document_id', 1);
        // $model['feedback'] = "feedback";
        // Auditable::updateEncryptedData(DocumentDestination::class, [
        //     'id' => $model['id'],
        //     'feedback' => "Naweed",
        // ], $model["id"]);
        // $record = DB::table("documents")->where('id', 22)->first();
        return dd($model);

        $doc_id = 1; // Example doc_id (replace with your actual doc_id)
        $encryption_key = config('encryption.aes_key'); // The encryption key used for AES_DECRYPT (replace with your actual key)

        // Call the stored procedure using DB::select
        $result = DB::select('CALL GetDocInfo(:doc_id, :encryption_key,:lang)', [
            'doc_id' => $doc_id,
            'encryption_key' => $encryption_key,
            'lang' => LanguageEnum::pashto->value,
        ]);
        return $result;

        $key = config('encryption.aes_key'); // The key for encryption
        $value = 'Naweed';
        $encryptedValue = DB::select('SELECT AES_ENCRYPT(?, ?) AS encrypted_value', [$value, $key]);

        // Access the encrypted value from the result set
        // Access the encrypted value from the result set
        $encryptedValue = $encryptedValue[0]->encrypted_value;


        // The result will be an array, so you may want to extract the encrypted value like so:
        return $encryptedValue;

        $documentDetails = DB::select('CALL GetDocInfo(?,?)', [1]);
        return base64_encode(openssl_random_pseudo_bytes(32));


        // Path to the user_error.log file
        $logFilePath = storage_path('logs/user_error.log');

        // Check if the file exists
        if (!File::exists($logFilePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Log file not found.'
            ], 404);
        }

        // Get the contents of the user_error.log file
        $logContents = File::get($logFilePath);

        // Split the log contents into individual lines (assuming each log entry is on a separate line)
        $logLines = explode("\n", $logContents);

        $logs = [];

        // Iterate through each log line and parse it as JSON
        foreach ($logLines as $line) {
            // Skip empty lines
            if (empty($line)) continue;

            // Decode the JSON log entry
            $logData = json_decode($line, true);

            // Check if decoding was successful
            if ($logData) {
                $context = $logData['context'] ?? null;

                if ($context) {
                    // Add additional information to each log entry
                    $logs[] = [
                        'timestamp' => $context['timestamp'] ?? now()->toDateTimeString(),
                        'error_message' => $context['error_message'] ?? 'No message',
                        'user_id' => $context['user_id'] ?? 'N/A',
                        'username' => $context['username'] ?? 'N/A',
                        'ip_address' => $context['ip_address'] ?? 'N/A',
                        'method' => $context['method'] ?? 'N/A',
                        'uri' => $context['uri'] ?? 'N/A',
                    ];
                }
            }
        }

        // Return the formatted logs as JSON
        return response()->json([
            'status' => 'success',
            'logs' => $logs
        ]);

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

        // ->join('destinations', function ($join) use ($locale) {
        //     // Join based on translable_id and translable_type
        //     $join->on('translates.translable_id', '=', 'destinations.id')
        //         ->on('translates.translable_type', '=', DB::raw("'App\Models\Destination'"))
        //         ->where('translates.language_name', '=', $locale);  // Filter by language name
        // });
        // ->get();
        return $query->get();
        dd($query->toSql(), $query->getBindings());
        // return $sessionLocale;
        $userCount = User::count();
        $todayCount = User::whereDate('created_at', Carbon::today())->count();
        $activeUserCount = User::where('status', true)->count();
        $inActiveUserCount = User::where('status', false)->count();
        return response()->json([
            'counts' => [
                "active" => $userCount,
                "inActive" => $todayCount,
                "total" => $activeUserCount,
                "todayTotal" => $inActiveUserCount
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function dashboardInfo()
    {


        $locale = App::getLocale();

        // Fetch data using a stored procedure
        $results = $this->fetchDashboardData($locale);

        // Map the results
        $documentCountByStatus = $results[0] ?? [];
        $documentTypePercentages = $results[1] ?? [];
        $monthlyDocumentTypeCount = $results[2] ?? [];
        $documentTypeSixMonths = $results[3] ?? [];
        $documentUrgencyCounts = $results[4] ?? [];
        $monthlyDocumentCounts = $results[5] ?? [];

        // Process monthly document counts
        $monthlyData = $this->processMonthlyData($monthlyDocumentCounts);

        // Process grouped data for monthly type counts
        $groupedMonthlyTypeCounts = $this->groupMonthlyTypeCounts($monthlyDocumentTypeCount);

        // Process document type percentages
        $documentTypeData = $this->processDocumentTypePercentages($documentTypePercentages);

        return response()->json([
            'statuses' => $documentCountByStatus,
            'documentTypePercentages' => $documentTypeData,
            'montlyTypeCount' => $groupedMonthlyTypeCounts,
            'documenttypesixmonth' => $documentTypeSixMonths,
            'documentUrgencyCounts' => $documentUrgencyCounts,
            'monthlyDocumentCounts' => $monthlyData,
        ]);
    }

    private function fetchDashboardData(string $locale): array
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        $stmt = $pdo->prepare('CALL GetDashboardData(:locale)');
        $stmt->bindParam(':locale', $locale);
        $stmt->execute();

        $results = [];
        do {
            $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($resultSet) {
                $results[] = $resultSet;
            }
        } while ($stmt->nextRowset());

        return $results;
    }

    private function processMonthlyData(array $monthlyDocumentCounts): array
    {
        $allMonths = range(1, 12);
        $dataMap = array_column($monthlyDocumentCounts, 'document_count', 'month');

        $monthNames = [
            1 => "January",
            2 => "February",
            3 => "March",
            4 => "April",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "August",
            9 => "September",
            10 => "October",
            11 => "November",
            12 => "December",
        ];

        $monthNamesArray = [];
        $monthCountsArray = [];

        foreach ($allMonths as $monthNum) {
            $monthNamesArray[] = $monthNames[$monthNum];
            $monthCountsArray[] = $dataMap[$monthNum] ?? 0;
        }

        return ["labels" => $monthNamesArray, "data" => $monthCountsArray];
    }

    private function groupMonthlyTypeCounts(array $monthlyDocumentTypeCount): array
    {
        $allMonths = range(1, 12);

        // Initialize an empty array to hold the grouped data
        $groupedData = [];

        foreach ($monthlyDocumentTypeCount as $entry) {
            $typeName = $entry['document_type_name'];
            $month = $entry['month'];
            $count = $entry['document_count'];

            // Ensure the `document_type_name` key exists
            if (!isset($groupedData[$typeName])) {
                $groupedData[$typeName] = array_fill(0, 12, 0); // Initialize all months with 0
            }

            $groupedData[$typeName][$month - 1] += $count;
        }

        // Prepare the final array to include month-wise data for all document types
        $finalResult = [];
        foreach ($groupedData as $typeName => $monthlyData) {
            $finalResult[] = [
                'name' => $typeName,
                'data' => $monthlyData,
            ];
        }

        return $finalResult;
    }

    private function processDocumentTypePercentages(array $documentTypePercentages): array
    {
        $documentTypeNames = array_column($documentTypePercentages, 'document_type_name');
        $percentages = array_column($documentTypePercentages, 'percentage');

        return [$documentTypeNames, array_map('floatval', $percentages)];
    }
}
