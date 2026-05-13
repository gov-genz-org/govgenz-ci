<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\VolunteerJoinNotifier;
use App\Models\VolunteerApplicationModel;

class Join extends BaseController
{
    /** @var list<string> */
    public const SECTOR_KEYS = [
        'legal',
        'economy',
        'food',
        'energy',
        'water',
        'education',
        'health',
        'infrastructure',
        'digital',
        'territories',
        'environment',
        'mines',
        'security',
        'citizen',
    ];

    public function index()
    {
        helper('url');
        $multiSelectBase = base_url('assets/vendor/multi-select-dropdown-js/1.0.3/');

        $extraHead = <<<HTML
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/css/intlTelInput.css">
<link rel="stylesheet" href="{$multiSelectBase}MultiSelect.min.css">
<link rel="stylesheet" href="/assets/css/join-enhancements.css">
HTML;
        $extraScripts = <<<HTML
<script defer src="https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/js/intlTelInput.min.js"></script>
<script defer src="{$multiSelectBase}MultiSelect.min.js"></script>
<script defer src="/js/front/join-enhancements.js"></script>
HTML;

        return view('front/layout', [
            'title'           => lang('Site.join_title'),
            'main'            => view('front/join', [
                'sectors' => self::sectorLabels(),
            ]),
            'navActive'       => 'join',
            'mainExtraClass'  => 'ggz-layout-full',
            'extraHead'       => $extraHead,
            'extraScripts'    => $extraScripts,
        ]);
    }

    public function submit()
    {
        $sectorKeys = self::normalizeSectorKeys($this->request->getPost('sector'));

        $rules = [
            'full_name' => 'required|max_length[255]',
            'email'     => 'required|valid_email|max_length[190]',
            'phone_country' => 'permit_empty|regex_match[/^\+[0-9]{1,4}$/]',
            'phone_number'  => 'permit_empty|max_length[32]|regex_match[/^[0-9][0-9 .()-]{3,31}$/]',
            'message'   => 'permit_empty|max_length[8000]',
        ];

        $errors = [];
        if ($sectorKeys === []) {
            $errors['sector'] = lang('Site.join_sector_required');
        }

        if (! $this->validate($rules)) {
            $errors = array_merge($errors, $this->validator->getErrors());
        }

        $phoneNumber = trim((string) ($this->request->getPost('phone_number') ?? ''));
        $phoneCountry = trim((string) ($this->request->getPost('phone_country') ?? ''));
        if ($phoneNumber !== '' && $phoneCountry === '') {
            $errors['phone_country'] = lang('Site.join_phone_country_required');
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $fullName  = (string) $this->request->getPost('full_name');
        $emailAddr = (string) $this->request->getPost('email');
        $msgRaw    = $this->request->getPost('message');
        $phoneFull = ($phoneNumber !== '') ? trim($phoneCountry . ' ' . $phoneNumber) : null;
        $sectorCsv = implode(',', $sectorKeys);

        $model = model(VolunteerApplicationModel::class);
        $model->insert([
            'sector'     => $sectorCsv,
            'full_name'  => $fullName,
            'email'      => $emailAddr,
            'phone'      => $phoneFull,
            'message'    => $msgRaw ?: null,
            'ip_address' => $this->request->getIPAddress(),
            'status'     => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0) {
            $adminValidationUrl = site_url('admin/volunteers') . '?status=new#vol-row-' . $newId;
            VolunteerJoinNotifier::send([
                'id'                   => $newId,
                'sector_label'         => self::sectorLabelsText($sectorKeys),
                'full_name'            => $fullName,
                'email'                => $emailAddr,
                'phone'                => $phoneFull,
                'message'              => $msgRaw ? (string) $msgRaw : null,
                'ip_address'           => $this->request->getIPAddress(),
                'admin_validation_url' => $adminValidationUrl,
            ]);
        }

        helper(['locale']);

        return redirect()->to(localized_site_url('join'))->with('message', lang('Site.join_success'));
    }

    /**
     * @return array<string, string>
     */
    public static function sectorLabels(): array
    {
        return [
            'legal'          => lang('Site.sector_legal'),
            'economy'        => lang('Site.sector_economy'),
            'food'           => lang('Site.sector_food'),
            'energy'         => lang('Site.sector_energy'),
            'water'          => lang('Site.sector_water'),
            'education'      => lang('Site.sector_education'),
            'health'         => lang('Site.sector_health'),
            'infrastructure' => lang('Site.sector_infrastructure'),
            'digital'        => lang('Site.sector_digital'),
            'territories'    => lang('Site.sector_territories'),
            'environment'    => lang('Site.sector_environment'),
            'mines'          => lang('Site.sector_mines'),
            'security'       => lang('Site.sector_security'),
            'citizen'        => lang('Site.sector_citizen'),
        ];
    }

    /**
     * @param mixed $raw
     * @return list<string>
     */
    public static function normalizeSectorKeys($raw): array
    {
        $values = is_array($raw) ? $raw : (($raw === null || $raw === '') ? [] : [$raw]);
        $out = [];
        foreach ($values as $value) {
            $key = trim((string) $value);
            if ($key === '' || ! in_array($key, self::SECTOR_KEYS, true)) {
                continue;
            }
            if (! in_array($key, $out, true)) {
                $out[] = $key;
            }
        }

        return $out;
    }

    /**
     * Libellés humains pour les clés secteur, dans l’ordre de sélection.
     *
     * @param list<string> $sectorKeys
     * @return list<string>
     */
    public static function sectorLabelLines(array $sectorKeys): array
    {
        $labels = self::sectorLabels();
        $out = [];
        foreach ($sectorKeys as $key) {
            $out[] = $labels[$key] ?? $key;
        }

        return $out;
    }

    /**
     * @param list<string> $sectorKeys
     */
    public static function sectorLabelsText(array $sectorKeys): string
    {
        return implode("\n", self::sectorLabelLines($sectorKeys));
    }

}
