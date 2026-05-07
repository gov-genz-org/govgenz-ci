<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\VolunteerApplicationModel;

class Join extends BaseController
{
    /** @var list<string> */
    public const SECTOR_KEYS = [
        'communication',
        'mobilisation',
        'juridique',
        'tech',
        'autre',
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

        $model = model(VolunteerApplicationModel::class);
        $model->insert([
            'sector'     => $this->request->getPost('sector'),
            'full_name'  => $this->request->getPost('full_name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone') ?: null,
            'message'    => $this->request->getPost('message') ?: null,
            'ip_address' => $this->request->getIPAddress(),
            'status'     => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        helper(['locale']);

        return redirect()->to(localized_site_url('join'))->with('message', lang('Site.join_success'));
    }

    /**
     * @return array<string, string>
     */
    public static function sectorLabels(): array
    {
        return [
            'communication' => lang('Site.sector_communication'),
            'mobilisation'  => lang('Site.sector_mobilisation'),
            'juridique'     => lang('Site.sector_juridique'),
            'tech'          => lang('Site.sector_tech'),
            'autre'         => lang('Site.sector_autre'),
        ];
    }
}
