<?php

declare(strict_types=1);

use App\Libraries\SiteContext;

?>
<div class="footer__col">
    <h4><?= esc(lang('Site.footer_movement')) ?></h4>
    <ul>
        <li><a href="<?= esc(localized_site_url(localized_slug_from_fr('qui-sommes-nous')), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Who we are' : 'Qui sommes-nous' ?></a></li>
        <li><a href="<?= esc(localized_site_url(localized_slug_from_fr('notre-adn')), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Our DNA' : 'Notre ADN' ?></a></li>
        <li><a href="<?= esc(localized_site_url('structure'), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Structure' : 'Structure' ?></a></li>
        <li><a href="<?= esc(localized_site_url(localized_slug_from_fr('secteurs')), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Sectors' : 'Secteurs' ?></a></li>
        <li><a href="<?= esc(localized_site_url(localized_slug_from_fr('etude')), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Youth study' : 'Étude' ?></a></li>
        <li><a href="<?= esc(localized_site_url('contact'), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Contact' : 'Contact' ?></a></li>
        <li><a href="<?= esc(localized_site_url('press'), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Press' : 'Presse' ?></a></li>
        <li><a href="<?= esc(localized_site_url('join'), 'attr') ?>"><?= SiteContext::locale() === 'en' ? 'Join us' : 'Rejoindre' ?></a></li>
    </ul>
</div>
<div class="footer__col">
    <h4><?= esc(lang('Site.footer_soon')) ?></h4>
    <ul>
        <li><span class="footer__soon">declaration.govgenz.org</span></li>
        <li><span class="footer__soon">counterpoint.govgenz.org</span></li>
        <li><span class="footer__soon">projects.govgenz.org</span></li>
    </ul>
</div>
<div class="footer__col">
    <h4><?= esc(lang('Site.footer_contacts')) ?></h4>
    <ul>
        <li><a href="mailto:contact@govgenz.org">contact@govgenz.org</a></li>
        <li><a href="mailto:recruitment@govgenz.org">recruitment@govgenz.org</a></li>
        <li><a href="mailto:partnerships@govgenz.org">partnerships@govgenz.org</a></li>
        <li><a href="mailto:ethics@govgenz.org">ethics@govgenz.org</a></li>
    </ul>
</div>
