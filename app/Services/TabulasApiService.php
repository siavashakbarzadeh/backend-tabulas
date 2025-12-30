<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TabulasApiService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('tabulas.api_base_url');
        $this->timeout = config('tabulas.timeout', 30);
    }

    /**
     * Make a GET request to the Tabulas API.
     *
     * @param string $endpoint
     * @return array|null
     */
    protected function get(string $endpoint): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->accept('application/json')
                ->get($this->baseUrl . $endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Tabulas API request failed", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Tabulas API request exception", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // =============================
    // Mobile Endpoints
    // =============================

    /**
     * Get commissioni data.
     */
    public function getCommissioni(): ?array
    {
        return $this->get('/v2/tabulas/mobile/commissioni');
    }

    /**
     * Get ultimi atti data.
     */
    public function getUltimiAtti(): ?array
    {
        return $this->get('/v2/tabulas/mobile/ultimiatti');
    }

    /**
     * Get ultim dossier data.
     */
    public function getUltimDossier(): ?array
    {
        return $this->get('/v2/tabulas/mobile/ultimdossier');
    }

    /**
     * Get webtv data (mobile).
     */
    public function getMobileWebtv(): ?array
    {
        return $this->get('/v2/tabulas/mobile/webtv');
    }

    /**
     * Get ebook data.
     */
    public function getEbook(): ?array
    {
        return $this->get('/v2/tabulas/mobile/ebook');
    }

    /**
     * Get guide manuali data.
     */
    public function getGuideManuali(): ?array
    {
        return $this->get('/v2/tabulas/mobile/guidemanuali');
    }

    /**
     * Get servizi data.
     */
    public function getServizi(): ?array
    {
        return $this->get('/v2/tabulas/mobile/servizi');
    }

    // =============================
    // Kiosk Endpoints
    // =============================

    /**
     * Get assemblea data.
     */
    public function getAssemblea(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/assemblea');
    }

    /**
     * Get commissioni permanenti data.
     */
    public function getCommPerm(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/commperm');
    }

    /**
     * Get giunte altre commissioni data.
     */
    public function getGiunteAltreComm(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/giuntealtrecomm');
    }

    /**
     * Get bicamerali e delegazioni data.
     */
    public function getBicameDeleg(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/bicamedeleg');
    }

    /**
     * Get webtv data (kiosk).
     */
    public function getKioskWebtv(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/webtv');
    }

    /**
     * Get pillole video data.
     */
    public function getPilloleVideo(): ?array
    {
        return $this->get('/v2/tabulas/kiosk/pillolevideo');
    }
}
