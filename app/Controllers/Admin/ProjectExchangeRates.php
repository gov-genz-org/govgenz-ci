<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProjectExchangeRateModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectExchangeRates extends BaseController
{
    public function edit()
    {
        $config = model(ProjectExchangeRateModel::class)->getConfig();

        return view('admin/layout', [
            'title' => 'Taux de change — projets',
            'main'  => view('admin/project_exchange_rates/form', ['config' => $config]),
        ]);
    }

    public function update(): ResponseInterface
    {
        $errors = $this->validatePayload();
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        model(ProjectExchangeRateModel::class)->saveConfig($this->payloadFromPost());

        return redirect()->to(site_url('admin/project-exchange-rates'))
            ->with('message', 'Taux de change enregistrés.');
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(): array
    {
        $errors = [];
        $fields = ['usd_ariary', 'eur_ariary', 'cny_ariary', 'jpy_ariary', 'fcfa_ariary'];
        foreach ($fields as $field) {
            $raw = trim((string) $this->request->getPost($field));
            if ($raw === '' || ! is_numeric($raw) || (float) $raw <= 0) {
                $errors[$field] = 'Saisissez un nombre strictement positif.';
            }
        }

        if (trim((string) $this->request->getPost('label_year')) === '') {
            $errors['label_year'] = 'Indiquez une année ou un libellé (ex. 2026).';
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromPost(): array
    {
        return [
            'label_year'  => trim((string) $this->request->getPost('label_year')),
            'usd_ariary'  => (float) $this->request->getPost('usd_ariary'),
            'eur_ariary'  => (float) $this->request->getPost('eur_ariary'),
            'cny_ariary'  => (float) $this->request->getPost('cny_ariary'),
            'jpy_ariary'  => (float) $this->request->getPost('jpy_ariary'),
            'fcfa_ariary' => (float) $this->request->getPost('fcfa_ariary'),
        ];
    }
}
