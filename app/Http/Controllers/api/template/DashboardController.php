<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function dashboardInfo()
    {


        $locale = App::getLocale();

        // Fetch data using a stored procedure
        $results = $this->fetchDashboardData($locale);

        // Map the results
        $documentCountByStatus = $results[0] ?? [];
        $documentTypePercentages = $results[1] ?? [];
        // $documentTypeSixMonths = $results[3] ?? [];
        $documentUrgencyCounts = $results[2] ?? [];
        $monthlyDocumentCounts = $results[3] ?? [];

        if ($documentUrgencyCounts) {
        }
        // Process monthly document counts
        $monthlyData = $this->processMonthlyData($monthlyDocumentCounts);


        // Process document type percentages
        $documentTypeData = $this->processDocumentTypePercentages($documentTypePercentages);


        return response()->json([
            'statuses' => $documentCountByStatus,
            'documentTypePercentages' => $documentTypeData,
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

        if ($monthlyDocumentCounts == []) {
            $monthlyDocumentCounts = ['document_count' => [0], 'month' => ['January']];
        }
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

        return [$monthNamesArray, $monthCountsArray];
    }


    private function processDocumentTypePercentages(array $documentTypePercentages): array
    {
        $documentTypeNames = array_column($documentTypePercentages, 'document_type_name');
        $percentages = array_column($documentTypePercentages, 'percentage');

        return [$documentTypeNames, array_map('floatval', $percentages)];
    }
}
