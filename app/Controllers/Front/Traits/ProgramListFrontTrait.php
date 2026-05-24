<?php

declare(strict_types=1);

namespace App\Controllers\Front\Traits;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Comportements partagés des listes programme (projets, positions).
 */
trait ProgramListFrontTrait
{
    protected function rejectProgramListJsonFilter(IncomingRequest $request, ResponseInterface $response): ?ResponseInterface
    {
        $accept = $request->getHeaderLine('Accept');
        $xhr    = $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
        if ($xhr || str_contains($accept, 'application/json')) {
            return null;
        }

        return $response->setStatusCode(406)->setJSON(['ok' => false, 'error' => 'json']);
    }

    /**
     * @param array<string, mixed>|null $listPage
     */
    protected function programListMainExtraClass(?array $listPage): string
    {
        helper('cms');
        $mainExtra = $listPage !== null ? cms_layout_main_class($listPage['layout_key'] ?? null) : 'ggz-layout-full';
        if (trim($mainExtra) === '') {
            return 'ggz-layout-full';
        }

        return $mainExtra;
    }
}
