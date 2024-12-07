<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    //


    public function reportContain()
    {
          $locale = App::getLocale();
     $locale ='ps';
        
   // Use a raw database connection to handle multiple result sets
    $results = [];
    DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

    $pdo = DB::getPdo();
    $stmt = $pdo->prepare('CALL ReportSelectionContain(:locale)');
    $stmt->bindParam(':locale', $locale);
    $stmt->execute();

    do {
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($resultSet) {
            $results[] = $resultSet;
        }
    } while ($stmt->nextRowset());

    // Map results to named keys
    return response()->json([
        'statuses' => $results[0] ?? [],
        'sources' => $results[1] ?? [],
        'urgencies' => $results[2] ?? [],
        'document_types' => $results[3] ?? [],
        'destinations' => $results[4] ?? [],
    ]);

    }
}
