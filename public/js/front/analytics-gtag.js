(function () {
  window.ggzLoadAnalytics = function (measurementId) {
    if (!measurementId || window.ggzAnalyticsLoaded) {
      return;
    }
    window.ggzAnalyticsLoaded = true;

    window.dataLayer = window.dataLayer || [];
    window.gtag = function () {
      window.dataLayer.push(arguments);
    };

    window.gtag('consent', 'default', {
      analytics_storage: 'denied',
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
    });

    window.gtag('consent', 'update', {
      analytics_storage: 'granted',
    });

    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
    document.head.appendChild(script);

    script.onload = function () {
      window.gtag('js', new Date());
      window.gtag('config', measurementId, {
        anonymize_ip: true,
        allow_google_signals: false,
        allow_ad_personalization_signals: false,
        send_page_view: true,
      });
    };
  };
})();
