<?php

namespace App\Traits\Report;

use App\Models\User;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

trait PdfGeneratorTrait
{
    public function tablecontent($req)
    {
        // mPDF setup code
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'amiri', // Set your default font if necessary
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);

        // Set the title of the document
        $mpdf->SetTitle('Your Document Title');

        // Create the TOC page
        $mpdf->AddPage(); // Add a new page for the TOC
        $mpdf->TOCpagebreak(); // Create a page break for the TOC

        // Write the header for the TOC
        $mpdf->WriteHTML('<h1>Table of Contents</h1>');

        // ** NOTE: The TOC needs sections to populate! **

        // Adding content sections (these will automatically be added to the TOC)
        $mpdf->AddPage();
        $mpdf->WriteHTML('<h1>Section 1</h1>'); // This will appear in the TOC
        $mpdf->WriteHTML('<p>Content for section 1...</p>');

        $mpdf->AddPage();
        $mpdf->WriteHTML('<h1>Section 2</h1>'); // This will appear in the TOC
        $mpdf->WriteHTML('<html>
<body>

     <!-- focus this and I want in same page -->
     <div class="toc">
        <div>Table of content</div>
        <tocpagebreak />
     </div>
     <!-- -------- --->

     <div style="page-break-before : always">  
       <tocentry content="1. bbbbbbbbb" />
       <div>page 2</div>
     </div>

     <div style="page-break-before : always">
       <tocentry content="2. cccccccccc" />
       <div>page 2</div>
     </div>

</body>
</html');

        // Additional content...
        $mpdf->AddPage();
        $mpdf->WriteHTML('<h1>Conclusion</h1>'); // This will appear in the TOC
        $mpdf->WriteHTML('<p>This is the conclusion...</p>');

        // Write the actual TOC after defining sections
        $mpdf->TOC(1, 'L'); // 1 = level of headings, 'L' = left alignment

        // Output the generated PDF to the browser
        return $mpdf->Output('document.pdf', 'I'); // Stream PDF to browser
    }






    public function postreport($req)
    {
        // Retrieve user data
        $data = User::select('full_name', 'username', 'email_id')->get();

        // Get the default configuration for fonts and directories
        $configVariables = new ConfigVariables();
        $fontDirs = $configVariables->getDefaults()['fontDir'];
        $fontVariables = new FontVariables();
        $fontData = $fontVariables->getDefaults()['fontdata'];

        // Initialize mPDF with custom font and watermark settings
        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [public_path('fonts/amiri')]),
            'fontdata' => $fontData + [
                'amiri' => [
                    'R' => 'Amiri-Regular.ttf',
                    'B' => 'Amiri-Bold.ttf',
                    'I' => 'Amiri-Italic.ttf',
                    'BI' => 'Amiri-BoldItalic.ttf'
                ]
            ],
            'default_font' => 'amiri',
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_bottom' => 50, // Increase bottom margin to make space for the footer
        ]);

        // Set the watermark image and opacity

        $watermarkImagePath = public_path('logo/emart.png'); // Accessing the image in public/logo

        $mpdf->SetWatermarkImage($watermarkImagePath, 0.2); // Set watermark and opacity
        $mpdf->showWatermarkImage = true; // Enable watermark

        // Set the footer with RTL support
        $footerHtml = $this->generateFooterHtml();
        $mpdf->SetFooter($footerHtml); // Apply footer to all pages
        $mpdf->defaultfooterline = 0; // Remove default footer line

        // Render and write the first part of the PDF
        $firstPartHtml = view('Report.first', compact('data'))->render();
        $mpdf->WriteHTML($firstPartHtml);

        // Write additional HTML content
        $secondPartHtml = "<div>This is the second part.</div>";
        $mpdf->WriteHTML($secondPartHtml);

        // Merge an existing PDF into the generated document
        $this->mergeExistingPdf($mpdf, storage_path('app/public/pdf/new.pdf'), $footerHtml);

        // Write the third part of the dynamic content
        $thirdPartHtml = "<div>This is the third part of the PDF.</div>";
        $mpdf->AddPage();
        $mpdf->WriteHTML($thirdPartHtml);

        // Output the generated PDF to the browser
        return $mpdf->Output('document.pdf', 'I'); // Stream PDF to browser
    }

    /**
     * Generates the footer HTML for the PDF.
     *
     * @return string
     */
    protected function generateFooterHtml()
    {
        return '
            <div style="width: 100%; text-align: right; margin: 0; padding: 0; direction: ltr; font-family: amiri; font-size: 10pt; border: none;">
                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 33%; text-align: left; border: none;">{PAGENO}</td>
                        <td style="width: 34%; text-align: center; border: none;">مابین</td>
                        <td style="width: 33%; text-align: right; border: none;">
                            وزارت صحت عامه امارت اسلامی افغانستان وزیر اکبر خان
                            <br>ریاست روابط بین الملل
                            <br>شماره تماس : ۰۷۹۸۳۲۹۳۸
                            <br>ایمیل ادرس : test@gmail.com
                        </td>
                    </tr>
                </table>
            </div>
        ';
    }

    /**
     * Merges an existing PDF into the mPDF document.
     *
     * @param \Mpdf\Mpdf $mpdf
     * @param string $pdfPath
     * @param string $footerHtml
     */
    protected function mergeExistingPdf(Mpdf $mpdf, $pdfPath, $footerHtml)
    {
        $pageCount = $mpdf->setSourceFile($pdfPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $mpdf->AddPage();
            $templateId = $mpdf->ImportPage($i);
            $mpdf->UseTemplate($templateId);
            $mpdf->SetFooter($footerHtml); // Ensure the footer is set for each page
        }
    }
}
