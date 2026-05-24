<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Notification e-mail — proposition de financement ou d’apport matériel sur une fiche projet.
 */
final class ProjectContributionNotifier
{
    /**
     * @param array<string, string> $fields
     */
    public static function send(
        string $contributionType,
        string $projectTitle,
        string $projectSlug,
        array $fields,
        ?string $adminValidationUrl = null,
        string $locale = 'fr',
    ): void {
        $url = $adminValidationUrl ?? '';
        FormModeratorMailer::sendFundAlert(
            $contributionType,
            $projectTitle,
            $projectSlug,
            $fields,
            $url,
            $locale,
        );
    }
}
