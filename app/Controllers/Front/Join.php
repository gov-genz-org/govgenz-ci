<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\VolunteerJoinNotifier;
use App\Models\SectorModel;
use App\Models\VolunteerApplicationModel;

class Join extends BaseController
{
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
                'sectors' => model(SectorModel::class)->optionsForSelect(),
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
     * @param mixed $raw
     * @return list<string>
     */
    public static function normalizeSectorKeys($raw): array
    {
        $values = is_array($raw) ? $raw : (($raw === null || $raw === '') ? [] : [$raw]);
        $allowed = array_fill_keys(model(SectorModel::class)->allowedCodes(), true);
        $out = [];
        foreach ($values as $value) {
            $key = strtolower(trim((string) $value));
            if ($key === '' || ! isset($allowed[$key])) {
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
        $fromDb = model(SectorModel::class)->optionsForSelect();
        $out    = [];
        foreach ($sectorKeys as $key) {
            if (isset($fromDb[$key])) {
                $out[] = $fromDb[$key];
                continue;
            }
            $line = lang('Site.sector_' . $key);
            $out[] = is_string($line) && $line !== '' && ! str_starts_with($line, 'Site.') ? $line : $key;
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
