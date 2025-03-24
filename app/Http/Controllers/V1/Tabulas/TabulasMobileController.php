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
        // Read the raw dossier data from your JSON/text file
        $rawData = file_get_contents(storage_path('jsons/mobile/ultimdossier.json'));

        // Parse the raw data into structured records
        $parsedRecords = $this->parseDossierData($rawData);
        dd($parsedRecords);
        // Return the structured data as JSON
        return response()->json($parsedRecords);
    }

    protected function parseDossierData($rawData)
    {
        $records = [];
        // Split raw data into records using empty lines as separators
        $rawRecords = preg_split("/\n\s*\n/", $rawData);
        foreach ($rawRecords as $recordStr) {
            // Split record into lines and trim whitespace
            $lines = array_filter(array_map('trim', explode("\n", $recordStr)));
            if (empty($lines)) {
                continue;
            }
            // The first line is the header.
            // Split header by tabs or multiple spaces.
            $headerParts = preg_split("/\t+|\s{2,}/", $lines[0]);
            $headerParts = array_values(array_filter($headerParts, function ($p) {
                return trim($p) !== "";
            }));
            $documentIdentifier = isset($headerParts[0]) ? $headerParts[0] : "-";
            $servizio = isset($headerParts[1]) ? $headerParts[1] : "-";
            $date = isset($headerParts[2]) ? $headerParts[2] : "-";
            $label = isset($headerParts[3]) ? $headerParts[3] : "-";

            // The remaining lines are description and references.
            $description = "";
            $riferimenti = [];
            $inReferences = false;
            for ($i = 1; $i < count($lines); $i++) {
                $line = $lines[$i];
                if (strpos($line, "Riferimenti:") === 0) {
                    $inReferences = true;
                    $refLine = trim(str_replace("Riferimenti:", "", $line));
                    if ($refLine !== "") {
                        $riferimenti[] = $refLine;
                    }
                } elseif ($inReferences) {
                    $riferimenti[] = $line;
                } else {
                    $description .= ($description ? " " : "") . $line;
                }
            }

            $records[] = [
                'documentIdentifier' => $documentIdentifier,
                'servizio'           => $servizio,
                'date'               => $date,
                'label'              => $label,
                'description'        => $description,
                'riferimenti'        => $riferimenti
            ];
        }
        return $records;
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
