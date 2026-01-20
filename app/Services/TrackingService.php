<?php

namespace App\Services;

use App\Models\Setting;

class TrackingService
{
    // ==================== META PIXEL METHODS ====================

    /**
     * Check if Meta Pixel is enabled
     */
    public static function isMetaPixelEnabled(): bool
    {
        return (bool) Setting::getSetting('meta_pixel_enabled', false);
    }

    /**
     * Get Meta Pixel ID
     */
    public static function getMetaPixelId(): ?string
    {
        $pixelId = Setting::getSetting('meta_pixel_id', '');

        return ! empty($pixelId) ? $pixelId : null;
    }

    /**
     * Check if specific event is enabled
     */
    public static function isEventEnabled(string $eventName): bool
    {
        $key = 'meta_event_'.strtolower(str_replace(' ', '_', $eventName));

        return (bool) Setting::getSetting($key, true);
    }

    /**
     * Generate Meta Pixel base script
     */
    public static function getBaseScript(): string
    {
        if (! self::isMetaPixelEnabled()) {
            return '';
        }

        $pixelId = self::getMetaPixelId();
        if (! $pixelId) {
            return '';
        }

        return "
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript>
<img height=\"1\" width=\"1\" style=\"display:none\"
src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\"/>
</noscript>
<!-- End Meta Pixel Code -->
";
    }

    /**
     * Generate event tracking script
     */
    public static function getEventScript(string $eventName, array $params = []): string
    {
        if (! self::isMetaPixelEnabled() || ! self::isEventEnabled($eventName)) {
            return '';
        }

        $pixelId = self::getMetaPixelId();
        if (! $pixelId) {
            return '';
        }

        $paramsJson = ! empty($params) ? json_encode($params) : '{}';

        return "<script>fbq('track', '{$eventName}', {$paramsJson});</script>";
    }

    /**
     * Generate inline tracking code (for use in JS event handlers)
     */
    public static function getInlineTrackingCode(string $eventName, array $params = []): string
    {
        if (! self::isMetaPixelEnabled() || ! self::isEventEnabled($eventName)) {
            return '';
        }

        $pixelId = self::getMetaPixelId();
        if (! $pixelId) {
            return '';
        }

        $paramsJson = ! empty($params) ? json_encode($params) : '{}';

        return "if(typeof fbq === 'function'){fbq('track', '{$eventName}', {$paramsJson});}";
    }

    // ==================== GOOGLE ANALYTICS METHODS ====================

    /**
     * Check if Google Analytics is enabled
     */
    public static function isGoogleAnalyticsEnabled(): bool
    {
        return (bool) Setting::getSetting('google_analytics_enabled', false);
    }

    /**
     * Get Google Analytics Measurement ID
     */
    public static function getGoogleAnalyticsId(): ?string
    {
        $measurementId = Setting::getSetting('google_analytics_id', '');

        return ! empty($measurementId) ? $measurementId : null;
    }

    /**
     * Check if specific Google event is enabled
     */
    public static function isGoogleEventEnabled(string $eventName): bool
    {
        $key = 'google_event_'.strtolower(str_replace(' ', '_', $eventName));

        return (bool) Setting::getSetting($key, true);
    }

    /**
     * Generate Google Analytics base script (gtag.js)
     */
    public static function getGoogleBaseScript(): string
    {
        if (! self::isGoogleAnalyticsEnabled()) {
            return '';
        }

        $measurementId = self::getGoogleAnalyticsId();
        if (! $measurementId) {
            return '';
        }

        return "
<!-- Google tag (gtag.js) -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$measurementId}\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$measurementId}');
</script>
<!-- End Google Analytics -->
";
    }

    /**
     * Generate Google Analytics event tracking script
     */
    public static function getGoogleEventScript(string $eventName, array $params = []): string
    {
        if (! self::isGoogleAnalyticsEnabled() || ! self::isGoogleEventEnabled($eventName)) {
            return '';
        }

        $measurementId = self::getGoogleAnalyticsId();
        if (! $measurementId) {
            return '';
        }

        $paramsJson = ! empty($params) ? json_encode($params) : '{}';

        return "<script>if(typeof gtag === 'function'){gtag('event', '{$eventName}', {$paramsJson});}</script>";
    }

    /**
     * Generate inline Google tracking code (for use in JS event handlers)
     */
    public static function getGoogleInlineTrackingCode(string $eventName, array $params = []): string
    {
        if (! self::isGoogleAnalyticsEnabled() || ! self::isGoogleEventEnabled($eventName)) {
            return '';
        }

        $measurementId = self::getGoogleAnalyticsId();
        if (! $measurementId) {
            return '';
        }

        $paramsJson = ! empty($params) ? json_encode($params) : '{}';

        return "if(typeof gtag === 'function'){gtag('event', '{$eventName}', {$paramsJson});}";
    }

    /**
     * Get all available tracking events (Meta Pixel)
     */
    public static function getAvailableEvents(): array
    {
        return [
            'page_view' => 'PageView',
            'initiate_checkout' => 'InitiateCheckout',
            'add_payment_info' => 'AddPaymentInfo',
            'purchase' => 'Purchase',
            'viewbookings' => 'ViewBookings',
            'bookingrescheduled' => 'BookingRescheduled',
            'viewtransactions' => 'ViewTransactions',
            'viewpaymentpage' => 'ViewPaymentPage',
        ];
    }

    /**
     * Get Google Analytics event mappings
     */
    public static function getGoogleEvents(): array
    {
        return [
            'page_view' => 'page_view',
            'begin_checkout' => 'begin_checkout',
            'add_payment_info' => 'add_payment_info',
            'purchase' => 'purchase',
            'view_bookings' => 'view_bookings',
            'booking_rescheduled' => 'booking_rescheduled',
            'view_transactions' => 'view_transactions',
            'view_payment_page' => 'view_payment_page',
        ];
    }
}
