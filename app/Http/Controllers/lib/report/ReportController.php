<?php

namespace App\Http\Controllers\lib\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\Report\PdfGeneratorTrait;

class ReportController extends Controller
{
    use PdfGeneratorTrait;

    public function testReport(Request $req)
    {

        // $this->postreport($req);
        $this->tablecontent($req);
    }
}
