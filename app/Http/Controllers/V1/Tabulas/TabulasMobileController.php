<?php

namespace App\Http\Controllers\V1\Tabulas;

class TabulasMobileController
{

    /**
     * @return mixed
     */
    public function commissioni()
    {
        return $this->_pathToJson(storage_path('jsons/mobile/commissioni.json'));
    }

    /**
     * @return mixed
     */
    public function ultimiatti()
    {
        return $this->_pathToJson(storage_path('jsons/mobile/ultimiatti.json'));
    }

    /**
     * @return mixed
     */
    public function ultimdossier()
{
    // Read the JSON file (adjust the path as needed)
    $json = file_get_contents(storage_path('jsons/mobile/ultimdossier.json'));
    $data = json_decode($json, true);

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
        return $this->_pathToJson(storage_path('jsons/mobile/webtv.json'));
    }

    /**
     * @return mixed
     */
    public function ebook()
    {
        return $this->_pathToJson(storage_path('jsons/mobile/ebook.json'));
    }

    /**
     * @return mixed
     */
    public function guidemanuali()
    {
        return $this->_pathToJson(storage_path('jsons/mobile/guidemanuali.json'));
    }

    /**
     * @return mixed
     */
    public function servizi()
    {
        return $this->_pathToJson(storage_path('jsons/mobile/servizi.json'));
    }

    /**
     * @param string $path
     * @return mixed
     */
    private function _pathToJson(string $path)
    {
        return json_decode(file_get_contents($path), true);
    }
}
