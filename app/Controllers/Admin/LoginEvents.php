<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StaffLoginEventModel;
use CodeIgniter\HTTP\ResponseInterface;

class LoginEvents extends BaseController
{
    private const EXPORT_ROW_CAP = 5000;

    public function index()
    {
        service('pager')->only(['outcome', 'q']);

        $model = model(StaffLoginEventModel::class);

        $outcome = $this->request->getGet('outcome');
        if (is_string($outcome) && in_array($outcome, ['success', 'failure'], true)) {
            $model = $model->where('outcome', $outcome);
        }

        $searchQuery = trim((string) $this->request->getGet('q'));
        if ($searchQuery !== '') {
            if (mb_strlen($searchQuery) > 120) {
                $searchQuery = mb_substr($searchQuery, 0, 120);
            }
            $model = $model->like('email_attempt', $searchQuery);
        }

        $events = $model->orderBy('id', 'DESC')->paginate(40, 'default');

        return view('admin/layout', [
            'title' => 'Journal de connexion',
            'main'  => view('admin/login_events/index', [
                'events'        => $events,
                'filterOutcome' => is_string($outcome) && in_array($outcome, ['success', 'failure'], true) ? $outcome : 'all',
                'searchQuery'   => $searchQuery,
                'pager'         => $model->pager,
            ]),
        ]);
    }

    public function exportCsv(): ResponseInterface
    {
        $model = model(StaffLoginEventModel::class);

        $outcome = $this->request->getGet('outcome');
        if (is_string($outcome) && in_array($outcome, ['success', 'failure'], true)) {
            $model = $model->where('outcome', $outcome);
        }

        $searchQuery = trim((string) $this->request->getGet('q'));
        if ($searchQuery !== '') {
            if (mb_strlen($searchQuery) > 120) {
                $searchQuery = mb_substr($searchQuery, 0, 120);
            }
            $model = $model->like('email_attempt', $searchQuery);
        }

        $rows = $model->orderBy('id', 'DESC')->findAll(self::EXPORT_ROW_CAP);

        $filename = 'connexions-staff-' . date('Y-m-d-His') . '.csv';

        $response = service('response');
        $response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $lines = [];
        $lines[] = $this->csvLine([
            'id',
            'created_at',
            'outcome',
            'detail',
            'email_attempt',
            'staff_user_id',
            'ip_address',
            'user_agent',
        ]);

        foreach ($rows as $row) {
            $lines[] = $this->csvLine([
                (string) ($row['id'] ?? ''),
                (string) ($row['created_at'] ?? ''),
                (string) ($row['outcome'] ?? ''),
                (string) ($row['detail'] ?? ''),
                (string) ($row['email_attempt'] ?? ''),
                (string) ($row['staff_user_id'] ?? ''),
                (string) ($row['ip_address'] ?? ''),
                (string) ($row['user_agent'] ?? ''),
            ]);
        }

        $body = "\xEF\xBB\xBF" . implode("\n", $lines) . "\n";

        return $response->setBody($body);
    }

    /**
     * @param list<string> $fields
     */
    private function csvLine(array $fields): string
    {
        $escaped = [];
        foreach ($fields as $f) {
            $escaped[] = '"' . str_replace('"', '""', $f) . '"';
        }

        return implode(';', $escaped);
    }
}
