<?php

namespace App\Http\Controllers\api\template;

use App\Enums\LanguageEnum;
use App\Models\Scan;
use App\Models\User;
use App\Models\Email;
use App\Models\Source;
use App\Models\Status;
use App\Models\Contact;
use App\Models\Urgency;
use App\Models\Approval;
use App\Models\Document;
use App\Models\ModelJob;
use App\Models\AdverbType;
use App\Models\Destination;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\DocumentAdverb;
use App\Models\UserPermission;
use App\Models\DestinationType;
use App\Models\ReportGenerated;
use App\Models\DocumentDestination;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\DocumentDestinationNoFeedBack;
use Illuminate\Support\Facades\App;

class AuditLogController extends Controller
{
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
    public function audits(Request $request, $page)
    {
        $locale = App::getLocale();
        $tr = [];
        $perPage = $request->input('per_page', 10); // Number of records per page
        $page = $request->input('page', 1); // Current page

        // Start building the query
        $query = [];

        $query = Audit::all()
            ->with('user'); // Eager load the user who performed the action

        // if ($locale === LanguageEnum::default->value) {
        //     $query = UsersEnView::query();
        // } else if ($locale === LanguageEnum::farsi->value) {
        //     $query = UsersFaView::query();
        // } else {
        //     $query = UsersPsView::query();
        // }
        // Apply date filtering conditionally if provided
        $startDate = $request->input('filters.date.startDate');
        $endDate = $request->input('filters.date.endDate');

        if ($startDate || $endDate) {
            // Apply date range filtering
            if ($startDate && $endDate) {
                $query->whereBetween('createdAt', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('createdAt', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('createdAt', '<=', $endDate);
            }
        }

        // Apply search filter if present
        $searchColumn = $request->input('filters.search.column');
        $searchValue = $request->input('filters.search.value');

        if ($searchColumn && $searchValue) {
            $allowedColumns = ['username', 'contact', 'email'];

            // Ensure that the search column is allowed
            if (in_array($searchColumn, $allowedColumns)) {
                $query->where($searchColumn, 'like', '%' . $searchValue . '%');
            }
        }

        // Apply sorting if present
        $sort = $request->input('filters.sort'); // Sorting column
        $order = $request->input('filters.order', 'asc'); // Sorting order (default is 'asc')

        // Apply sorting by provided column or default to 'created_at'
        if ($sort && in_array($sort, ['username', 'createdAt', 'status', 'job', 'destination'])) {
            $query->orderBy($sort, $order);
        } else {
            // Default sorting if no sort is provided
            $query->orderBy("createdAt", $order);
        }

        // Apply pagination (ensure you're paginating after sorting and filtering)
        $tr = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json(
            [
                "users" => $tr,
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
}
