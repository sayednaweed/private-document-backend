<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Translate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //



public function dashboardInfo()
{
    $locale = App::getLocale();
    $locale = 'ps'; // Example locale override

    // Calculate the total document count for the last 6 months
    $startDate = Carbon::now()->subMonths(6);

    // Common base query for Document, DocumentType, and Translates
    $documentTypeQuery = Document::join('document_types', 'documents.document_type_id', '=', 'document_types.id')
        ->leftJoin('translates', function ($join) use ($locale) {
            $join->on('translates.translable_id', '=', 'document_types.id')
                ->where('translates.translable_type', '=', 'App\Models\DocumentType')
                ->where('translates.language_name', '=', $locale);
        });

    // Document Type Percentages
    $documenttypepersentage = $documentTypeQuery
        ->selectRaw(
            "IF('$locale' != 'en' AND translates.value IS NOT NULL, translates.value, document_types.name) AS document_type_name, 
            COUNT(documents.id) AS document_count"
        )
        ->groupBy('document_types.id', 'document_types.name', 'translates.value')
        ->get();

    // Calculate total document count for percentage calculation
    $totalCount = $documenttypepersentage->sum('document_count');

    // Calculate percentages
    $percentages = $documenttypepersentage->map(function ($item) use ($totalCount) {
        $percentage = $totalCount ? ($item->document_count / $totalCount) * 100 : 0;
        $item->percentage = number_format($percentage, 2);
        return $item;
    });

    // Document Count for the Last 6 Months
    $sixmontinfo = $documentTypeQuery
        ->where('documents.created_at', '>=', $startDate)
        ->selectRaw(
            "IF('$locale' != 'en' AND translates.value IS NOT NULL, translates.value, document_types.name) AS document_type_name, 
            COUNT(documents.id) AS document_count"
        )
        ->groupBy('document_types.id', 'document_types.name', 'translates.value')
        ->get();

    // Document Urgency Counts
    $documenturgency = Document::join('urgencies', 'documents.urgency_id', '=', 'urgencies.id')
        ->leftJoin('translates', function ($join) use ($locale) {
            $join->on('translates.translable_id', '=', 'urgencies.id')
                ->where('translates.translable_type', '=', 'App\Models\Urgency')
                ->where('translates.language_name', '=', $locale);
        })
        ->selectRaw(
            "IF('$locale' != 'en' AND translates.value IS NOT NULL, translates.value, urgencies.name) AS urgency_name, 
            COUNT(documents.id) AS document_count"
        )
        ->groupBy('urgencies.id', 'urgencies.name', 'translates.value')
        ->get();

    // Monthly Document Counts for the Current Year
    $monthinfo12 = Document::selectRaw(
        "MONTHNAME(documents.created_at) AS month_name,
        MONTH(documents.created_at) AS month,
        YEAR(documents.created_at) AS year,
        COUNT(documents.id) AS document_count"
    )
        ->whereYear('documents.created_at', '=', date('Y')) // Filter by the current year
        ->groupBy('month_name', 'month', 'year')
        ->orderBy('month') // Order by the month number to keep chronological order
        ->get();

    return [
        'DocumentTypePersentage' => $percentages,
        'DocumentUrgency' => $documenturgency,
        'DocumentSixCountType' => $sixmontinfo,
        'YearMonthsDocCount' => $monthinfo12
    ];
}
  



}

