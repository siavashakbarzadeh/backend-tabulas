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
        return $this->_pathToJson(storage_path('jsons/mobile/ultimdossier.json'));
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
