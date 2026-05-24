<?php

declare(strict_types=1);

if (! function_exists('project_share_social_links')) {
    /**
     * Liens de partage.
     *
     * Facebook / LinkedIn / X : URL web (sharer) — le JS mobile utilise Web Share API ou onglet navigateur.
     * WhatsApp : deep link app + texte avec l’URL du projet.
     * TikTok : pas d’URL de partage officielle — copie + ouverture app (JS).
     *
     * @return array<string, array{web: string, mobile?: string, app?: string, android?: string}>
     */
    function project_share_social_links(string $title, string $projectPageUrl, string $shareQrPageUrl): array
    {
        $projectEnc = rawurlencode($projectPageUrl);
        $waText     = rawurlencode($title . ' — ' . $projectPageUrl);
        $tweetText  = rawurlencode(lang('Projects.share_qr_social_text', ['title' => $title]));
        $fbWeb      = 'https://www.facebook.com/sharer/sharer.php?u=' . $projectEnc;
        $liWeb      = 'https://www.linkedin.com/shareArticle?mini=true&url=' . $projectEnc;
        $xWeb       = 'https://twitter.com/intent/tweet?url=' . $projectEnc . '&text=' . $tweetText;

        return [
            'facebook' => [
                'web'     => $fbWeb,
                'mobile'  => $fbWeb,
                'app'     => 'fb://facewebmodal/f?href=' . rawurlencode($fbWeb),
                'android' => 'intent://www.facebook.com/sharer.php?u=' . $projectEnc . '#Intent;scheme=https;package=com.facebook.katana;end',
            ],
            'whatsapp' => [
                'web'     => 'https://api.whatsapp.com/send?text=' . $waText,
                'app'     => 'whatsapp://send?text=' . $waText,
                'android' => 'intent://send?text=' . $waText . '#Intent;scheme=whatsapp;package=com.whatsapp;end',
            ],
            'linkedin' => [
                'web'     => $liWeb,
                'mobile'  => $liWeb,
                'app'     => 'linkedin://shareArticle?mini=true&url=' . $projectEnc,
                'android' => 'intent://www.linkedin.com/shareArticle?mini=true&url=' . $projectEnc . '#Intent;scheme=https;package=com.linkedin.android;end',
            ],
            'x' => [
                'web'    => $xWeb,
                'mobile' => $xWeb,
                'app'    => 'twitter://post?message=' . rawurlencode($title . ' ' . $projectPageUrl),
            ],
            'tiktok' => [
                'web' => '',
                'app' => 'snssdk1233://',
            ],
            'email' => [
                'web' => 'mailto:?subject=' . rawurlencode(lang('Projects.share_qr_email_subject', ['title' => $title]))
                    . '&body=' . rawurlencode(lang('Projects.share_qr_email_body', ['title' => $title, 'url' => $projectPageUrl])),
            ],
        ];
    }
}

