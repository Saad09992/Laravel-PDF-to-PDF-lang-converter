<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Dompdf\Dompdf;
use Mpdf\Mpdf;
use Smalot\PdfParser\Parser;

class PdfTranslationController extends Controller
{
    private $client;
    private $baseUrl = 'https://translate.google.com/translate_a/single';

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ]
        ]);
    }

    public function translatePdf(Request $request)
    {
        try {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf',
            ]);

            $file = $request->file('pdf');
            $pdfPath = $file->getRealPath();

            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            if (empty($text)) {
                return response()->json(['error' => 'Failed to extract text from PDF.'], 400);
            }

            $translatedText = $this->translateText($text);
            $translatedPdfPath = $this->generatePdf($translatedText);

            return response()->download($translatedPdfPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('PDF translation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Translation failed: ' . $e->getMessage()], 500);
        }
    }

    private function translateText($text)
    {
        try {
            $chunks = str_split($text, 1000); // Reduced chunk size for better accuracy
            $translatedChunks = [];

            // Manual mapping for common phrases
            $commonPhrases = [
                'hello' => 'ہیلو',
            ];

            // Check and replace common phrases before translating
            $text = str_ireplace(array_keys($commonPhrases), array_values($commonPhrases), $text);

            foreach ($chunks as $chunk) {
                if (!empty($translatedChunks)) {
                    sleep(2);
                }

                $chunk = mb_convert_encoding(trim($chunk), 'UTF-8', 'auto'); // Ensure proper encoding
                if (empty($chunk)) continue;

                $params = [
                    'query' => [
                        'client' => 't',
                        'sl' => 'en',
                        'tl' => 'ur',
                        'hl' => 'en',
                        'dt' => ['t', 'bd', 'ex', 'ld', 'md', 'qca', 'rw', 'rm', 'ss'],
                        'ie' => 'UTF-8',
                        'oe' => 'UTF-8',
                        'otf' => 2,
                        'ssel' => 0,
                        'tsel' => 0,
                        'q' => $chunk
                    ]
                ];

                try {
                    $response = $this->client->get($this->baseUrl, $params);
                    $result = json_decode($response->getBody(), true);

                    \Log::info('Translation response: ' . json_encode($result)); // Log the response for debugging

                    if (!empty($result[0])) {
                        $translatedText = '';
                        foreach ($result[0] as $section) {
                            if (isset($section[0])) {
                                $translatedText .= $section[0];
                            }
                        }
                        if (!empty($translatedText)) {
                            $translatedChunks[] = $translatedText;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Chunk translation failed: ' . $e->getMessage());
                    sleep(5);

                    // Try with alternative endpoint structure
                    try {
                        $params['query']['client'] = 'gtx';
                        $params['query']['dt'] = 't';
                        $response = $this->client->get($this->baseUrl, $params);
                        $result = json_decode($response->getBody(), true);

                        \Log::info('Retry translation response: ' . json_encode($result)); // Log retry response

                        if (!empty($result[0])) {
                            $translatedText = '';
                            foreach ($result[0] as $section) {
                                if (isset($section[0])) {
                                    $translatedText .= $section[0];
                                }
                            }
                            if (!empty($translatedText)) {
                                $translatedChunks[] = $translatedText;
                            }
                        }
                    } catch (\Exception $retryError) {
                        \Log::error('Retry translation failed: ' . $retryError->getMessage());
                        continue;
                    }
                }
            }

            $result = implode(' ', $translatedChunks);
            if (empty($result)) {
                throw new \Exception('Translation returned empty result');
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('Translation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generatePdf($content)
    {
        try {
            // Ensure the font file exists in the public/fonts/ directory
            $fontPath = public_path('fonts/NafeesRegular.ttf');
            if (!file_exists($fontPath)) {
                throw new \Exception("Font file not found: $fontPath");
            }

            // Create a new mPDF instance with updated configuration
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'fontDir' => [public_path('fonts')],
                'fontdata' => [
                    'nafees' => [
                        'R' => 'NafeesRegular.ttf',
                        'useOTL' => 0xFF,    // Enable OpenType Layout features
                        'useKashida' => 75,  // Enable kashida for text justification
                    ],
                ],
                'default_font' => 'nafees',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'tempDir' => storage_path('app/temp'),  // Temporary directory for font processing
            ]);

            // Enable Arabic text shaping
            $mpdf->SetDirectionality('rtl');

            // Updated HTML with proper text rendering settings
            $html = '
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>
                    @font-face {
                        font-family: "NafeesRegular";
                        src: url("' . $fontPath . '") format("truetype");
                    }
                    body {
                        font-family: "nafees", "NafeesRegular", sans-serif;
                        direction: rtl;
                        text-align: right;
                        font-size: 30px;
                        line-height: 2;
                        padding: 20px;
                    }
                    * {
                        font-family: "nafees", "NafeesRegular", sans-serif !important;
                    }
                </style>
            </head>
            <body>' . nl2br($content) . '</body>
            </html>';

            // Write the HTML content to mPDF with text processing enabled
            $mpdf->WriteHTML($html);

            // Output the PDF to a file
            $filePath = storage_path('app/public/' . uniqid('translated_') . '.pdf');
            $mpdf->Output($filePath, 'F');

            return $filePath;

        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
