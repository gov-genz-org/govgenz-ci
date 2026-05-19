<?php

declare(strict_types=1);

namespace App\Database\Support;

/**
 * Contenu par défaut — pages mentions légales / politique cookies (FR + EN).
 * Titre / sur-titre / chapô : champs CMS hero_* (pas le corps HTML).
 */
final class CmsLegalMentionsBodies
{
    /**
     * @return array{hero_overline: string, hero_title: string, hero_lead: string}
     */
    public static function heroFr(): array
    {
        return [
            'hero_overline' => 'INFORMATIONS LÉGALES',
            'hero_title'    => 'Mentions légales',
            'hero_lead'     => 'Cette page présente l’éditeur du site, l’hébergement, l’usage des cookies et le traitement des données personnelles lié aux formulaires publics.',
        ];
    }

    /**
     * @return array{hero_overline: string, hero_title: string, hero_lead: string}
     */
    public static function heroEn(): array
    {
        return [
            'hero_overline' => 'LEGAL INFORMATION',
            'hero_title'    => 'Legal notice & privacy',
            'hero_lead'     => 'This page describes the site publisher, hosting, cookie use and how personal data from public forms is handled.',
        ];
    }

    public static function fr(): string
    {
        $join = site_url('join');

        return self::wrapBody(<<<HTML
            <h2>Éditeur du site</h2>
            <p>
                Le site <strong>GoV Gen Z Madagascar</strong> est édité par le mouvement citoyen <strong>GoV Gen Z</strong> (programme Paikady Taninjanaka).
            </p>
            <p>
                Contact général : <a href="mailto:contact@govgenz.org">contact@govgenz.org</a><br>
                Candidatures : <a href="{$join}">formulaire Rejoindre</a> ou <a href="mailto:apps@govgenz.org">apps@govgenz.org</a>
            </p>

            <h2>Directeur de la publication</h2>
            <p>Le comité exécutif de GoV Gen Z Madagascar, joignable à l’adresse <a href="mailto:contact@govgenz.org">contact@govgenz.org</a>.</p>

            <h2>Hébergement</h2>
            <p>
                Le site est hébergé par un prestataire d’hébergement web. Les coordonnées complètes de l’hébergeur peuvent être complétées ou mises à jour dans
                <strong>Administration → Pages</strong> (slug <code>mentions-legales</code>).
            </p>

            <h2>Propriété intellectuelle</h2>
            <p>
                Les textes, visuels, logos et éléments graphiques du site sont protégés. Toute reproduction ou représentation, totale ou partielle, sans autorisation écrite préalable est interdite.
            </p>

            <h2>Cookies et mesure d’audience</h2>
            <p>
                Lors de votre première visite, un bandeau vous permet d’<strong>accepter</strong> ou de <strong>refuser</strong> les cookies de mesure d’audience.
            </p>
            <p>Si vous acceptez, nous pouvons utiliser <strong>Google Analytics 4</strong> pour collecter des statistiques agrégées et anonymisées, notamment :</p>
            <ul>
                <li>nombre de visiteurs et de sessions ;</li>
                <li>pages consultées ;</li>
                <li>type d’appareil et navigateur ;</li>
                <li>provenance du trafic (moteurs de recherche, liens externes, accès direct).</li>
            </ul>
            <p>
                Ces données servent à comprendre l’usage du site et à l’améliorer. Elles ne sont pas revendues à des tiers à des fins publicitaires.
                L’adresse IP peut être traitée de manière anonymisée conformément aux réglages de mesure choisis.
            </p>
            <p>
                Vous pouvez retirer votre consentement à tout moment en supprimant les cookies du site dans les paramètres de votre navigateur, puis en rechargeant la page : le bandeau réapparaîtra.
            </p>
            <p>
                Pour en savoir plus sur les cookies Google Analytics :
                <a href="https://policies.google.com/technologies/cookies" rel="noopener noreferrer">politique cookies Google</a>.
            </p>

            <h2>Données transmises via les formulaires</h2>
            <p>En utilisant les formulaires publics (par exemple <strong>Rejoindre</strong>, financement ou apport matériel sur un projet), vous nous transmettez volontairement des données d’identification et de contact (nom, e-mail, téléphone, message, secteurs choisis, etc.).</p>
            <p>Ces informations sont utilisées uniquement pour :</p>
            <ul>
                <li>traiter votre demande ou candidature ;</li>
                <li>vous recontacter dans ce cadre ;</li>
                <li>assurer le suivi interne par l’équipe GoV Gen Z.</li>
            </ul>
            <p>Elles ne sont pas cédées à des tiers commerciaux sans votre accord.</p>

            <h2>Durée de conservation</h2>
            <p>
                Les messages reçus via les formulaires sont conservés le temps nécessaire au traitement de la demande et au suivi associé, puis archivés ou supprimés selon les besoins opérationnels de l’équipe.
            </p>

            <h2>Vos droits</h2>
            <p>
                Conformément à la réglementation applicable en matière de protection des données personnelles, vous pouvez demander l’accès, la rectification ou l’effacement de vos données, ainsi que la limitation ou l’opposition à certains traitements, en écrivant à
                <a href="mailto:contact@govgenz.org">contact@govgenz.org</a> en précisant l’objet de votre demande et, si possible, le formulaire concerné.
            </p>

            <h2>Liens externes</h2>
            <p>Le site peut contenir des liens vers des sites tiers. GoV Gen Z n’est pas responsable du contenu ni des pratiques de confidentialité de ces sites.</p>

            <h2>Mise à jour</h2>
            <p class="muted">Dernière mise à jour indicative : mai 2026. Le contenu de cette page peut être modifié à tout moment via l’administration du site.</p>
HTML);
    }

    public static function en(): string
    {
        $join = site_url('en/join');

        return self::wrapBody(<<<HTML
            <h2>Site publisher</h2>
            <p>
                The <strong>GoV Gen Z Madagascar</strong> website is published by the citizen movement <strong>GoV Gen Z</strong> (Paikady Taninjanaka programme).
            </p>
            <p>
                General contact: <a href="mailto:contact@govgenz.org">contact@govgenz.org</a><br>
                Applications: <a href="{$join}">Join form</a> or <a href="mailto:apps@govgenz.org">apps@govgenz.org</a>
            </p>

            <h2>Publication director</h2>
            <p>The GoV Gen Z Madagascar executive committee, reachable at <a href="mailto:contact@govgenz.org">contact@govgenz.org</a>.</p>

            <h2>Hosting</h2>
            <p>
                The site is hosted by a web hosting provider. Full hosting provider details can be completed or updated in
                <strong>Admin → Pages</strong> (slug <code>mentions-legales</code>).
            </p>

            <h2>Intellectual property</h2>
            <p>
                Texts, visuals, logos and graphic elements on this site are protected. Any reproduction or representation, in whole or in part, without prior written permission is prohibited.
            </p>

            <h2>Cookies and audience measurement</h2>
            <p>
                On your first visit, a banner lets you <strong>accept</strong> or <strong>decline</strong> analytics cookies.
            </p>
            <p>If you accept, we may use <strong>Google Analytics 4</strong> to collect aggregated, anonymised statistics, including:</p>
            <ul>
                <li>visitor and session counts;</li>
                <li>pages viewed;</li>
                <li>device and browser type;</li>
                <li>traffic source (search engines, external links, direct access).</li>
            </ul>
            <p>
                This helps us understand how the site is used and improve it. Data is not sold to third parties for advertising purposes.
                IP addresses may be processed in anonymised form according to the measurement settings in use.
            </p>
            <p>
                You can withdraw consent at any time by clearing this site’s cookies in your browser settings and reloading the page; the banner will appear again.
            </p>
            <p>
                More about Google Analytics cookies:
                <a href="https://policies.google.com/technologies/cookies" rel="noopener noreferrer">Google cookie policy</a>.
            </p>

            <h2>Data submitted via forms</h2>
            <p>When you use public forms (for example <strong>Join us</strong>, project funding or material contribution), you voluntarily send identification and contact details (name, email, phone, message, selected sectors, etc.).</p>
            <p>We use this information only to:</p>
            <ul>
                <li>process your request or application;</li>
                <li>contact you in that context;</li>
                <li>allow internal follow-up by the GoV Gen Z team.</li>
            </ul>
            <p>It is not sold to commercial third parties without your agreement.</p>

            <h2>Retention</h2>
            <p>
                Messages received via forms are kept as long as needed to handle the request and related follow-up, then archived or deleted according to operational needs.
            </p>

            <h2>Your rights</h2>
            <p>
                Under applicable data protection law, you may request access, rectification or erasure of your data, or restriction or objection to certain processing, by writing to
                <a href="mailto:contact@govgenz.org">contact@govgenz.org</a> with the subject of your request and, if possible, the form concerned.
            </p>

            <h2>External links</h2>
            <p>The site may link to third-party websites. GoV Gen Z is not responsible for their content or privacy practices.</p>

            <h2>Updates</h2>
            <p class="muted">Indicative last update: May 2026. This page may be changed at any time via the site administration.</p>
HTML);
    }

    /**
     * Corps HTML seul (aperçu aide admin) — le titre/chapô se règlent dans le formulaire page.
     */
    public static function guideHtml(): string
    {
        return self::wrapBody(<<<'HTML'
            <h2>Éditeur du site</h2>
            <p>
                Le site <strong>GoV Gen Z Madagascar</strong> est édité par le mouvement citoyen <strong>GoV Gen Z</strong> (programme Paikady Taninjanaka).
            </p>
            <p>
                Contact général : <a href="mailto:contact@govgenz.org">contact@govgenz.org</a><br>
                Candidatures : <a href="/join">formulaire Rejoindre</a> ou <a href="mailto:apps@govgenz.org">apps@govgenz.org</a>
            </p>

            <h2>Hébergement</h2>
            <p>
                Complétez ici le nom, l’adresse et les coordonnées de l’hébergeur (obligatoire en France / UE pour un site public).
            </p>

            <h2>Cookies et mesure d’audience</h2>
            <p>
                Lors de la première visite, un bandeau permet d’<strong>accepter</strong> ou de <strong>refuser</strong> les cookies de mesure d’audience (Google Analytics 4, statistiques agrégées).
            </p>
            <ul>
                <li>visiteurs et pages vues ;</li>
                <li>appareils et provenance du trafic ;</li>
                <li>pas de revente à des fins publicitaires.</li>
            </ul>
            <p>
                <a href="https://policies.google.com/technologies/cookies" rel="noopener noreferrer">Politique cookies Google</a>
            </p>

            <h2>Données des formulaires</h2>
            <p>Les formulaires <strong>Rejoindre</strong> et <strong>Financer un projet</strong> collectent des données de contact utilisées uniquement pour traiter la demande et vous recontacter.</p>

            <h2>Vos droits</h2>
            <p>
                Pour exercer vos droits (accès, rectification, effacement), écrivez à
                <a href="mailto:contact@govgenz.org">contact@govgenz.org</a>.
            </p>

            <p class="muted">Dernière mise à jour : mai 2026 — modifiable à tout moment dans Administration → Pages.</p>
HTML);
    }

    /**
     * @return array<string, mixed> Ligne page factice pour l’aperçu hero (admin).
     */
    public static function guidePreviewPage(): array
    {
        $hero = self::heroFr();

        return [
            'slug'           => 'mentions-legales',
            'hero_overline'  => $hero['hero_overline'],
            'hero_title'     => $hero['hero_title'],
            'hero_lead'      => $hero['hero_lead'],
            'hero_image_id'  => null,
            'hero_image_alt' => null,
        ];
    }

    private static function wrapBody(string $prose): string
    {
        $prose = trim($prose);

        return <<<HTML
<section class="section section--legal" aria-labelledby="mentions-legales-heading">
    <div class="section__inner">
        <div class="ggz-legal-prose">
{$prose}
        </div>
    </div>
</section>
HTML;
    }
}
