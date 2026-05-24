<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Controllers\BaseController;
use App\Models\ProjectContributionModel;
use App\Models\ProjectProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Traitement du formulaire de contribution (budget / matériel) sur une fiche projet.
 */
final class ProjectContributionSubmitter
{
    public static function submit(BaseController $controller, string $slug): RedirectResponse|ResponseInterface
    {
        helper(['locale', 'language', 'project', 'url']);
        $locale = SiteContext::locale();
        $slug   = trim($slug, '/');

        $project = model(ProjectProjectModel::class)->findPublishedBySlug($slug, $locale);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $showFundBudget   = project_has_financial_funding($project);
        $showFundMaterial = project_has_material_needs($project);
        $offerMaterial    = $showFundMaterial || $showFundBudget;
        if (! $showFundBudget && ! $showFundMaterial) {
            throw PageNotFoundException::forPageNotFound();
        }

        $type = self::resolveContributionType($controller, $showFundBudget, $offerMaterial);

        $errors     = [];
        $fields     = [];
        $donorName  = '';
        $donorEmail = '';
        $contact    = '';
        $title      = trim((string) ($project['title'] ?? $slug));

        $insertRow = [
            'project_id'        => (int) ($project['id'] ?? 0) ?: null,
            'project_slug'      => $slug,
            'project_title'     => $title,
            'locale'            => $locale,
            'contribution_type' => $type,
            'ip_address'        => $controller->request->getIPAddress(),
            'status'            => ProjectContributionModel::STATUS_NEW,
            'created_at'        => date('Y-m-d H:i:s'),
        ];

        if ($type === 'budget') {
            [$errors, $fields, $donorName, $donorEmail, $contact, $insertRow] = self::processBudget(
                $controller,
                $insertRow,
            );
        } else {
            [$errors, $fields, $donorName, $donorEmail, $contact, $insertRow] = self::processMaterial(
                $controller,
                $insertRow,
            );
        }

        $redirectUrl = project_public_url($slug) . '#project-fund';

        if ($errors !== []) {
            return redirect()->to($redirectUrl)
                ->withInput()
                ->with('fund_errors', array_values($errors));
        }

        $model = model(ProjectContributionModel::class);
        $model->insert($insertRow);
        $newId = (int) $model->getInsertID();
        $adminValidationUrl = $newId > 0
            ? site_url('admin/project-contributions') . '?status=new#contrib-row-' . $newId
            : site_url('admin/project-contributions') . '?status=new';

        ProjectContributionNotifier::send($type, $title, $slug, array_merge($fields, [
            'donor_name'  => $donorName,
            'contact'     => $contact,
            'donor_email' => $donorEmail,
        ]), $adminValidationUrl, $locale);

        if ($donorEmail !== '' && filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
            $ackSummary = [];
            foreach ($fields as $label => $value) {
                $ackSummary[] = ['label' => $label, 'value' => (string) $value];
            }
            FormSubmissionAckMailer::sendProjectFund(
                $donorEmail,
                $donorName,
                $title,
                $type,
                $ackSummary,
                $locale,
            );
        }

        return redirect()->to($redirectUrl)->with('fund_success', lang('Projects.fund_form_success'));
    }

    private static function resolveContributionType(
        BaseController $controller,
        bool $showFundBudget,
        bool $offerMaterial,
    ): string {
        $type = strtolower(trim((string) $controller->request->getPost('contribution_type')));
        if ($type === 'budget' && ! $showFundBudget) {
            $type = 'material';
        }
        if ($type === 'material' && ! $offerMaterial) {
            $type = 'budget';
        }
        if ($type !== 'budget' && $type !== 'material') {
            $type = $showFundBudget ? 'budget' : 'material';
        }

        return $type;
    }

    /**
     * @param array<string, mixed> $insertRow
     *
     * @return array{0: list<string>, 1: array<string, string>, 2: string, 3: string, 4: string, 5: array<string, mixed>}
     */
    private static function processBudget(BaseController $controller, array $insertRow): array
    {
        $rules = [
            'budget_donor_name'    => 'required|max_length[255]',
            'budget_phone_country' => 'required|regex_match[/^\+[0-9]{1,4}$/]',
            'budget_phone_number'  => 'required|max_length[32]|regex_match[/^[0-9][0-9 .()-]{3,31}$/]',
            'budget_donor_email'   => 'permit_empty|valid_email|max_length[190]',
            'budget_amount'        => 'required|max_length[120]|regex_match[/\d/]',
            'budget_remarks'       => 'permit_empty|max_length[4000]',
        ];
        $errors = $controller->validate($rules, project_fund_validation_messages('budget'))
            ? []
            : array_values($controller->validator->getErrors());

        $donorName  = trim((string) $controller->request->getPost('budget_donor_name'));
        $donorEmail = trim((string) $controller->request->getPost('budget_donor_email'));
        $contact    = project_fund_phone_contact_from_request('budget');
        $amount     = trim((string) $controller->request->getPost('budget_amount'));
        $remarks    = trim((string) $controller->request->getPost('budget_remarks'));

        $insertRow['donor_name']  = $donorName;
        $insertRow['contact']     = $contact;
        $insertRow['donor_email'] = $donorEmail !== '' ? $donorEmail : null;
        $insertRow['amount']      = $amount !== '' ? $amount : null;
        $insertRow['remarks']     = $remarks !== '' ? $remarks : null;

        $fields = [
            lang('Projects.fund_field_name')    => $donorName,
            lang('Projects.fund_field_phone')   => $contact,
            lang('Projects.fund_field_amount')  => $amount,
            lang('Projects.fund_field_remarks') => $remarks,
        ];
        if ($donorEmail !== '') {
            $fields[lang('Projects.fund_field_email')] = $donorEmail;
        }

        return [$errors, $fields, $donorName, $donorEmail, $contact, $insertRow];
    }

    /**
     * @param array<string, mixed> $insertRow
     *
     * @return array{0: list<string>, 1: array<string, string>, 2: string, 3: string, 4: string, 5: array<string, mixed>}
     */
    private static function processMaterial(BaseController $controller, array $insertRow): array
    {
        $rules = [
            'material_donor_name'      => 'required|max_length[255]',
            'material_phone_country'   => 'required|regex_match[/^\+[0-9]{1,4}$/]',
            'material_phone_number'    => 'required|max_length[32]|regex_match[/^[0-9][0-9 .()-]{3,31}$/]',
            'material_donor_email'     => 'permit_empty|valid_email|max_length[190]',
            'material_pickup_location' => 'permit_empty|max_length[255]',
            'material_remarks'         => 'permit_empty|max_length[4000]',
        ];
        $errors = $controller->validate($rules, project_fund_validation_messages('material'))
            ? []
            : array_values($controller->validator->getErrors());

        $materialLines      = project_fund_material_lines_from_request();
        $materialLineErrors = project_fund_validate_material_lines($materialLines);
        if ($materialLineErrors !== []) {
            $errors = array_merge($errors, $materialLineErrors);
        }

        $donorName  = trim((string) $controller->request->getPost('material_donor_name'));
        $donorEmail = trim((string) $controller->request->getPost('material_donor_email'));
        $contact    = project_fund_phone_contact_from_request('material');
        $canDeliver = trim((string) $controller->request->getPost('material_can_deliver'));
        $deliveryLabel = '';
        $canDeliverDb  = null;
        if ($canDeliver === '1') {
            $deliveryLabel = lang('Projects.fund_delivery_yes');
            $canDeliverDb  = 1;
        } elseif ($canDeliver === '0') {
            $deliveryLabel = lang('Projects.fund_delivery_no');
            $canDeliverDb  = 0;
        }
        $available = trim((string) $controller->request->getPost('material_available_from'));

        $insertRow['donor_name']      = $donorName;
        $insertRow['contact']         = $contact;
        $insertRow['donor_email']     = $donorEmail !== '' ? $donorEmail : null;
        $materialStorage              = project_fund_material_storage_from_lines($materialLines);
        $insertRow['items']           = $materialStorage['items'];
        $insertRow['quantity']        = $materialStorage['quantity'];
        $insertRow['available_from']  = $available !== '' ? $available : null;
        $insertRow['pickup_location'] = trim((string) $controller->request->getPost('material_pickup_location')) ?: null;
        $insertRow['can_deliver']     = $canDeliverDb;
        $insertRow['remarks']         = trim((string) $controller->request->getPost('material_remarks')) ?: null;

        $fields = [
            lang('Projects.fund_field_name')      => $donorName,
            lang('Projects.fund_field_items')     => (string) $insertRow['items'],
            lang('Projects.fund_field_quantity')  => (string) ($insertRow['quantity'] ?? ''),
            lang('Projects.fund_field_available') => $available,
            lang('Projects.fund_field_pickup')    => (string) ($insertRow['pickup_location'] ?? ''),
            lang('Projects.fund_field_phone')     => $contact,
            lang('Projects.fund_field_delivery')  => $deliveryLabel,
            lang('Projects.fund_field_remarks')   => (string) ($insertRow['remarks'] ?? ''),
        ];
        if ($donorEmail !== '') {
            $fields[lang('Projects.fund_field_email')] = $donorEmail;
        }

        return [$errors, $fields, $donorName, $donorEmail, $contact, $insertRow];
    }
}
