<?php

namespace App\Http\Controllers\api\template;

use \Log;
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
     $locale ='ps';
        

     $documentTypePercentages = DB::select('CALL GetDocumentTypePercentages(?)', [$locale]);
$documentCountLastSixMonths = DB::select('CALL GetDocumentCountLastSixMonths(?)', [$locale]);
$documentUrgencyCounts = DB::select('CALL GetDocumentUrgencyCounts(?)', [$locale]);
$monthlyDocumentCounts = DB::select('CALL GetMonthlyDocumentCounts(?)', [$locale]);

return [
    $documentTypePercentages,
    $documentCountLastSixMonths,
    $documentUrgencyCounts,
    $monthlyDocumentCounts
];

}







}

