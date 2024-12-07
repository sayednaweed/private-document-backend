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
    1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May",
    6 => "June", 7 => "July", 8 => "August", 9 => "September", 10 => "October",
            11 => "November", 12 => "December",
];

        $monthNamesArray = [];
        $monthCountsArray = [];

        foreach ($allMonths as $monthNum) {
            $monthNamesArray[] = $monthNames[$monthNum];
            $monthCountsArray[] = $dataMap[$monthNum] ?? 0;
        }

        return [$monthNamesArray, $monthCountsArray];
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
        'document_type_name' => $typeName,
                'monthly_data' => $monthlyData,
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
