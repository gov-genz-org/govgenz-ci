<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\StaffLoginEventModel;
use CodeIgniter\HTTP\IncomingRequest;

class StaffLoginAudit
{
    public static function record(
        IncomingRequest $request,
        string $outcome,
        ?string $detail = null,
        ?int $staffUserId = null,
        ?string $emailAttempt = null,
    ): void {
        try {
            $ip = $request->getIPAddress();
            if ($ip === '') {
                $ip = '0.0.0.0';
            }
            $ua = $request->getUserAgent();
            $ua = is_string($ua) ? mb_substr($ua, 0, 512) : null;

            model(StaffLoginEventModel::class)->insert([
                'staff_user_id' => $staffUserId,
                'email_attempt' => $emailAttempt !== null ? mb_substr($emailAttempt, 0, 255) : null,
                'outcome'       => mb_substr($outcome, 0, 32),
                'detail'        => $detail !== null ? mb_substr($detail, 0, 64) : null,
                'ip_address'    => mb_substr($ip, 0, 45),
                'user_agent'    => $ua,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'StaffLoginAudit: ' . $e->getMessage());
        }
    }
}
