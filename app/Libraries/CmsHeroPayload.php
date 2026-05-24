<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

final class CmsHeroPayload
{
    /**
     * @return array{hero_overline: ?string, hero_title: ?string, hero_lead: ?string, hero_image_id: ?int, hero_image_alt: ?string}
     */
    public static function fromPost(IncomingRequest $request): array
    {
        $emptyToNull = static function (?string $v): ?string {
            $t = trim((string) ($v ?? ''));

            return $t !== '' ? $t : null;
        };

        $rawImg = $request->getPost('hero_image_id');
        $imgId  = null;
        if ($rawImg !== null && $rawImg !== '') {
            $n = (int) $rawImg;
            $imgId = $n > 0 ? $n : null;
        }

        return [
            'hero_overline'  => $emptyToNull($request->getPost('hero_overline')),
            'hero_title'     => $emptyToNull($request->getPost('hero_title')),
            'hero_lead'      => $emptyToNull($request->getPost('hero_lead')),
            'hero_image_id'  => $imgId,
            'hero_image_alt' => $emptyToNull($request->getPost('hero_image_alt')),
        ];
    }
}
