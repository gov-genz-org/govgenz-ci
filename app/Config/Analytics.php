<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Analytics extends BaseConfig
{
    /** Active le bandeau cookies + le chargement conditionnel de GA4 sur le site public. */
    public bool $enabled = false;

    /** ID de mesure GA4 (ex. G-XXXXXXXXXX). */
    public string $ga4MeasurementId = '';

    /**
     * Slug CMS de la page politique cookies / confidentialité (lien « En savoir plus »).
     * Vide = pas de lien.
     */
    public string $privacyPageSlug = 'mentions-legales';

    public function __construct()
    {
        parent::__construct();

        $this->enabled = filter_var(env('analytics.enabled', false), FILTER_VALIDATE_BOOLEAN);

        $id = trim((string) env('analytics.ga4MeasurementId', ''));
        if ($id !== '') {
            $this->ga4MeasurementId = $id;
        }

        $slug = trim((string) env('analytics.privacyPageSlug', ''));
        if ($slug !== '') {
            $this->privacyPageSlug = $slug;
        }
    }
}
