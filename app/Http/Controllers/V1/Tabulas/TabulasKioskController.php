<?php

namespace App\Http\Controllers\V1\Tabulas;

class TabulasKioskController
{
    /**
     * @return mixed
     */
    public function assemblea()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/assemblea.json'));
    }

    /**
     * @return mixed
     */
    public function commperm()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/commperm.json'));
    }

    /**
     * @return mixed
     */
    public function giuntealtrecomm()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/giuntealtrecomm.json'));
    }

    /**
     * @return mixed
     */
    public function bicamedeleg()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/bicamedeleg.json'));
    }

    /**
     * @return mixed
     */
    public function webtv()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/webtv.json'));
    }

    /**
     * @return mixed
     */
    public function pillolevideo()
    {
        return $this->_pathToJson(storage_path('jsons/chiosco/pillolevideo.json'));
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
