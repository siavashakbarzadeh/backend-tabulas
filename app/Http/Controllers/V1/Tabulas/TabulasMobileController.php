<?php

namespace App\Http\Controllers\V1\Tabulas;

use App\Services\TabulasApiService;

class TabulasMobileController
{
    protected TabulasApiService $apiService;

    public function __construct(TabulasApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @return mixed
     */
    public function commissioni()
    {
        $data = $this->apiService->getCommissioni();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch commissioni data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function ultimiatti()
    {
        // 1. Get data from API
        $data = $this->apiService->getUltimiAtti();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch ultimi atti data'], 503);
        }

        // 2. Extract docNodes and skip "Tutti i contenuti"
        $docNodes = $data['docNodes'] ?? [];
        $filteredNodes = array_filter($docNodes, function ($node) {
            return isset($node['name']) && $node['name'] !== "Tutti i contenuti";
        });

        $parsedResults = [];

        // Simple regex patterns for date and seduta
        // Adjust to match your real patterns (e.g. date dd/mm/yyyy, seduta/e n. ###)
        $datePattern   = '/^\d{1,2}\/\d{1,2}\/\d{4}$/i';
        $sedutaPattern = '/seduta/i';

        foreach ($filteredNodes as $node) {
            // Use node "name" as the document identifier
            $documentIdentifier = $node['name'] ?? "-";

            // docContentUrl might be useful to store
            $docContentUrl = $node['docContentUrl'] ?? "-";

            // Load HTML from "docContentStreamContent"
            $htmlContent = $node['docContentStreamContent'] ?? "";
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($htmlContent);
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);

            // 3. Parse <p> elements for date, seduta, description lines
            $pNodes = $xpath->query('//p');
            $date = "-";
            $seduta = "-";
            $descriptionLines = []; // We'll collect extra lines here

            foreach ($pNodes as $p) {
                $text = trim($p->textContent);

                // Check if it matches the date pattern
                if (preg_match($datePattern, $text)) {
                    $date = $text;
                }
                // Check if it looks like a seduta
                else if (preg_match($sedutaPattern, $text)) {
                    $seduta = $text;
                }
                // Otherwise, treat as part of the description
                else {
                    // If it's not empty or doesn't match known patterns
                    if (!empty($text)) {
                        $descriptionLines[] = $text;
                    }
                }
            }

            // Combine any leftover lines into a single string
            // Adjust if you want to store them separately
            $description = implode(" | ", $descriptionLines);
            if (empty($description)) {
                $description = "-";
            }

            // 4. Extract PDF link (the <a> whose href contains ".pdf")
            $pdfNodes = $xpath->query('//a[contains(@href, ".pdf")]');
            $pdf = "-";
            if ($pdfNodes->length > 0) {
                // If you want the full HTML
                // $pdf = $dom->saveHTML($pdfNodes->item(0));

                // Or if you just want the link, e.g.:
                $pdfHref = $pdfNodes->item(0)->getAttribute('href');
                $pdf = $pdfHref ?: "-";
            }

            // Build the structured record
            $parsedResults[] = [
                'documentIdentifier' => $documentIdentifier,
                'date'               => $date,
                'seduta'             => $seduta,
                'description'        => $description,
                'pdf'                => $pdf,
                'docContentUrl'      => $docContentUrl,
            ];
        }

        // Return JSON
        return response()->json($parsedResults);
    }


    /**
     * @return mixed
     */
    public function ultimdossier()
    {
        // Get data from API
        $data = $this->apiService->getUltimDossier();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch ultim dossier data'], 503);
        }

        // Assume the main content is in the docNode named "Tutti i contenuti"
        $docNodes = $data['docNodes'] ?? [];
        $tutti = null;
        foreach ($docNodes as $node) {
            if (isset($node['name']) && $node['name'] === "Tutti i contenuti") {
                $tutti = $node;
                break;
            }
        }
        // Fallback if not found: use the first docNode
        if (!$tutti && count($docNodes) > 0) {
            $tutti = $docNodes[0];
        }

        $htmlContent = $tutti['docContentStreamContent'] ?? "";
        // Split the content into chunks by <HR class="defrss">
        $chunks = preg_split('/<HR class="defrss">/', $htmlContent);
        $dossiers = [];

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) continue;

            // Load chunk HTML into DOMDocument (suppress warnings)
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($chunk);
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);

            // Extract the document identifier from the first <h5> element
            $h5Nodes = $xpath->query('//h5');
            $documentIdentifier = $h5Nodes->length > 0 ? trim($h5Nodes->item(0)->textContent) : "-";

            // Extract servizio from the first <p><strong> element
            $servizioNodes = $xpath->query('//p/strong');
            $servizio = $servizioNodes->length > 0 ? trim($servizioNodes->item(0)->textContent) : "-";

            // Extract description from the first <p><em> element
            $descriptionNodes = $xpath->query('//p/em');
            $description = $descriptionNodes->length > 0 ? trim($descriptionNodes->item(0)->textContent) : "-";

            // Extract references from li elements within a ul having class "dossier_riferimenti"
            $refNodes = $xpath->query('//div[contains(@class, "annotazione")]//ul[contains(@class, "dossier_riferimenti")]/li');
            $riferimenti = [];
            foreach ($refNodes as $ref) {
                $riferimenti[] = trim($ref->textContent);
            }

            // Extract the date from the div with class "data" > span.annotazione
            $dateNodes = $xpath->query('//div[contains(@class, "data")]/span[contains(@class, "annotazione")]');
            $date = $dateNodes->length > 0 ? trim($dateNodes->item(0)->textContent) : "-";

            // Extract label: a tag that has ".html" in its href
            $labelNodes = $xpath->query('//p/a[contains(@href, ".html")]');
            $label = $labelNodes->length > 0 ? $dom->saveHTML($labelNodes->item(0)) : "-";

            // Extract PDF: a tag that has ".pdf" in its href
            $pdfNodes = $xpath->query('//p/a[contains(@href, ".pdf")]');
            $pdf = $pdfNodes->length > 0 ? $dom->saveHTML($pdfNodes->item(0)) : "-";

            $dossiers[] = [
                'documentIdentifier' => $documentIdentifier,
                'servizio'           => $servizio,
                'description'        => $description,
                'date'               => $date,
                'label'              => $label,
                'pdf'                => $pdf,
                'riferimenti'        => $riferimenti,
            ];
        }

        return response()->json($dossiers);
    }




    /**
     * @return mixed
     */
    public function webtv()
    {
        $data = $this->apiService->getMobileWebtv();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch webtv data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function ebook()
    {
        $data = $this->apiService->getEbook();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch ebook data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function guidemanuali()
    {
        $data = $this->apiService->getGuideManuali();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch guide manuali data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function servizi()
    {
        $data = $this->apiService->getServizi();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch servizi data'], 503);
        }

        return $data;
    }
}
