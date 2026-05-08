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
        return view('front/layout', [
            'title'           => lang('Site.join_title'),
            'main'            => view('front/join', [
                'sectors' => self::sectorLabels(),
            ]),
            'navActive'       => 'join',
            'mainExtraClass'  => 'ggz-layout-full',
        ]);
    }

    public function submit()
    {
        $rules = [
            'sector'    => 'required|in_list[' . implode(',', self::SECTOR_KEYS) . ']',
            'full_name' => 'required|max_length[255]',
            'email'     => 'required|valid_email|max_length[190]',
            'phone'     => 'permit_empty|max_length[64]',
            'message'   => 'permit_empty|max_length[8000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sectorKey = (string) $this->request->getPost('sector');
        $fullName  = (string) $this->request->getPost('full_name');
        $emailAddr = (string) $this->request->getPost('email');
        $phoneRaw  = $this->request->getPost('phone');
        $msgRaw    = $this->request->getPost('message');

        $model = model(VolunteerApplicationModel::class);
        $model->insert([
            'sector'     => $sectorKey,
            'full_name'  => $fullName,
            'email'      => $emailAddr,
            'phone'      => $phoneRaw ?: null,
            'message'    => $msgRaw ?: null,
            'ip_address' => $this->request->getIPAddress(),
            'status'     => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0) {
            $labels = self::sectorLabels();
            $adminValidationUrl = site_url('admin/volunteers') . '?status=new#vol-row-' . $newId;
            VolunteerJoinNotifier::send([
                'id'                   => $newId,
                'sector_label'         => $labels[$sectorKey] ?? $sectorKey,
                'full_name'            => $fullName,
                'email'                => $emailAddr,
                'phone'                => $phoneRaw ? (string) $phoneRaw : null,
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
}
