<?php

namespace App\Http\Controllers\V1\Tabulas;

use App\Services\TabulasApiService;

class TabulasKioskController
{
    protected TabulasApiService $apiService;

    public function __construct(TabulasApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @return mixed
     */
    public function assemblea()
    {
        // Get data from API
        $data = $this->apiService->getAssemblea();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch assemblea data'], 503);
        }

        // Extract specific nodes
        $docNodes = $data['docNodes'] ?? [];

        $response = [
            'ordine_del_giorno' => null,
            'calendario' => null,
            'comunicati' => [],
        ];

        foreach ($docNodes as $node) {
            $name = $node['name'] ?? '';

            // Handle "Ordine del giorno"
            if (stripos($name, 'Ordine del giorno') !== false) {
                $response['ordine_del_giorno'] = $this->parseSimpleHtmlNode($node);
            }
            // Handle "Calendario dei Lavori"
            elseif (stripos($name, 'Calendario dei Lavori') !== false) {
                $response['calendario'] = $this->parseSimpleHtmlNode($node);
            }
            // Handle "Comunicati di seduta" (Requires splitting)
            elseif (stripos($name, 'Comunicati di seduta') !== false) {
                $response['comunicati'] = $this->parseComunicati($node);
            }
        }

        return response()->json($response);
    }

    /**
     * Extracts basic HTML content from a node.
     * Used for OdG and Calendario.
     * * @param array $node
     * @return array
     */
    private function parseSimpleHtmlNode($node)
    {
        $content = $node['docContentStreamContent'] ?? '';

        // Remove the NOSEARCH comment if present, to clean up the start
        $content = str_replace('<!-- /NOSEARCH -->', '', $content);

        return [
            'name' => $node['name'] ?? '',
            'html' => trim($content)
        ];
    }

    /**
     * Parses the "Comunicati di seduta" node.
     * Splits content by <HR class="defrss"> and extracts titles (H3).
     * * @param array $node
     * @return array
     */
    private function parseComunicati($node)
    {
        $fullContent = $node['docContentStreamContent'] ?? '';

        if (empty($fullContent)) {
            return [];
        }

        // Split by the specific HR tag used in the JSON
        // Using preg_split for case-insensitive matching just in case
        $chunks = preg_split('/<HR class="defrss">/i', $fullContent);

        $parsedComunicati = [];

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) {
                continue;
            }

            // Load into DOM to extract title (H3) and body safely
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            // Hack to handle UTF-8 correctly in loadHTML
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $chunk);
            libxml_clear_errors();
            $xpath = new \DOMXPath($dom);

            // Extract Title from H3
            $h3Nodes = $xpath->query('//h3');
            $title = "-";
            if ($h3Nodes->length > 0) {
                $title = trim($h3Nodes->item(0)->textContent);
            }

            // The rest of the content is the body.
            // We can return the raw chunk, or remove the H3 if we want just the body.
            // For now, let's return the full chunk as 'html' and the extracted title separately.

            $parsedComunicati[] = [
                'title' => $title,
                'html'  => $chunk // The chunk contains the H3 + the HTML body
            ];
        }

        return $parsedComunicati;
    }

    /**
     * @return mixed
     */
    public function commperm()
    {
        $data = $this->apiService->getCommPerm();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch commperm data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function giuntealtrecomm()
    {
        $data = $this->apiService->getGiunteAltreComm();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch giuntealtrecomm data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function bicamedeleg()
    {
        $data = $this->apiService->getBicameDeleg();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch bicamedeleg data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function webtv()
    {
        $data = $this->apiService->getKioskWebtv();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch webtv data'], 503);
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function pillolevideo()
    {
        $data = $this->apiService->getPilloleVideo();

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch pillolevideo data'], 503);
        }

        return $data;
    }
}
