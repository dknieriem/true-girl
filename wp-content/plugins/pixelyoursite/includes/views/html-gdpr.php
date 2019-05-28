<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<h2 class="section-title">GDPR Settings</h2>

<div class="card card-static">
	<div class="card-header">
		General
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col">
				<p>According to GDPR (EU regulation), you'll have to inform and gather consent from your website
                    visitors about the way you use their private data.</p>
                <p>PixelYourSite implements by default 3 different tracking codes: the Facebook pixel, Google Analytics,
                    and the Pinterest tag. Chances are that you have other scripts or third-party cookies running on
                    your website (embedded videos, ad networks, chats, etc).</p>
                <p>We suggest you globally manage cookie consent with a dedicated solution. On this page, we will list a
                    few of them and we will also offer "filters" that developers can make use of.</p>
                <p>It's also important to understand what each network does and to inform your users accordingly.</p>
                <p class="mb-4">For more information about PixelYourSite and GDPR visit our <a
                            href="https://www.pixelyoursite.com/gdpr-cookie-compliance?utm_source=pixelyoursite-free-plugin&utm_medium=plugin&utm_campaign=free-plugin-gdpr" target="_blank">dedicated
                        page</a>.</p>
                
                <h3>Facebook Pixel</h3>
                <p>It is used for Facebook Ads and Facebook Analytics. It does use private data and you will need to ask
                    for prior consent.</p>
                <p class="mb-4">Facebook also has also implemented flexible ways for their users to see and control how
                    the pixel is used o partner websites. Inform your users about it: <a target="_blank"
                    href="https://www.facebook.com/ads/preferences/?entry_product=ad_settings_screen">
                    https://www.facebook.com/ads/preferences/?entry_product=ad_settings_screen</a></p>

                <h3>Google Analytics</h3>
                <p>By default, Google Analytics doesn't track private data. This implementation will probably not
                    require prior consent. If you turn on Google Analytics Advertising features, Remarketing features,
                    or if you link your Analytics account to your AdSense account, you will start to send personal data.
                    In this case, prior consent is required.</p>
                <p class="mb-4">Inform your users that they can control their privacy settings from here:
                    <a target="_blank" href="https://adssettings.google.com/authenticated">https://adssettings.google.com/authenticated</a></p>

                <h3>Google Ads</h3>
                <p>It is used for Google Ads. It does use private data and you will need to ask for prior consent.</p>
                <p class="mb-4">Inform your users that they can control their privacy settings from here:
                    <a target="_blank" href="https://adssettings.google.com/authenticated">https://adssettings.google.com/authenticated</a>
                </p>
                
                <h3>Pinterest Tag</h3>
                <p>It is used for Pinterest ads and does use private data that can identify your users. You will need to
                    ask for prior consent.</p>
                <p class="mb-0">Inform your users that they can control privacy settings from here:
                    <a target="_blank" href="https://www.pinterest.com/settings/">https://www.pinterest.com/settings/</a></p>
			</div>
		</div>
	</div>
</div>

<!-- Prior Consent -->
<div class="card card-static">
	<div class="card-header">
		Cookie Consent Integrations
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_facebook_prior_consent_enabled' ); ?>
				<h4 class="switcher-label">Enable the Facebook Pixel tracking before consent is capture (this might not
                    be GDPR compliant)</h4>
			</div>
		</div>
        <div class="row mt-3">
            <div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_analytics_prior_consent_enabled' ); ?>
                <h4 class="switcher-label">Enable Google Analytics tracking before consent is capture (if your Google
                    Analytics has advertising or remarketing features enabled, this might not be GDPR compliant)</h4>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_google_ads_prior_consent_enabled' ); ?>
                <h4 class="switcher-label">Enable Google Ads tracking before consent is capture (if your Google
                    Ads has advertising or remarketing features enabled, this might not be GDPR compliant)</h4>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_pinterest_prior_consent_enabled' ); ?>
                <h4 class="switcher-label">Enable the Pinterest Tag tracking before consent is capture (this might not
                    be GDPR compliant)</h4>
            </div>
        </div>
	</div>
</div>

<!-- Cookiebot -->
<div class="card card-static">
    <div class="card-header">
        <?php if ( ! isCookiebotPluginActivated() ) : ?>
            Cookiebot <span class="text-danger">[not detected]</span>
        <?php else: ?>
            Cookiebot <span class="text-success">[detected]</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>This is a complete premium solution that also offers a free plan for websites with under 100 pages.
                    For implementation, we suggest you follow their documentation.</p>
                <p class="mb-0">Website: <a href="https://cookiebot.com" target="_blank">https://cookiebot.com</a></p>
                <p class="mb-0">Plugin: <a href="https://wordpress.org/plugins/cookiebot/" target="_blank">https://wordpress.org/plugins/cookiebot/</a></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
			    <?php PYS()->render_switcher_input( 'gdpr_cookiebot_integration_enabled', false,
				    ! isCookiebotPluginActivated() ); ?>
                <h4 class="switcher-label">Enable Cookiebot integration</h4>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-4">
                <label class="label-inline">Facebook Pixel consent category:</label>
            </div>
            <div class="col-4">
                <?php PYS()->render_text_input( 'gdpr_cookiebot_facebook_consent_category',
                    'Enter consent category', ! isCookiebotPluginActivated() ); ?>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-4">
                <label class="label-inline">Google Analytics consent category:</label>
            </div>
            <div class="col-4">
			    <?php PYS()->render_text_input( 'gdpr_cookiebot_analytics_consent_category',
                    'Enter consent category', ! isCookiebotPluginActivated() ); ?>
            </div>
            <div class="col-4">
                * If you have advertising features enabled, enter "marketing"
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-4">
                <label class="label-inline">Google Ads consent category:</label>
            </div>
            <div class="col-4">
			    <?php PYS()->render_text_input( 'gdpr_cookiebot_google_ads_consent_category',
				    'Enter consent category', ! isCookiebotPluginActivated() ); ?>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-4">
                <label class="label-inline">Pinterest Tag consent category:</label>
            </div>
            <div class="col-4">
			    <?php PYS()->render_text_input( 'gdpr_cookiebot_pinterest_consent_category',
                    'Enter consent category', ! isCookiebotPluginActivated() ); ?>
            </div>
        </div>
    </div>
</div>

<!-- Ginger – EU Cookie Law -->
<div class="card card-static">
    <div class="card-header">
		<?php if ( ! isGingerPluginActivated() ) : ?>
            Ginger – EU Cookie Law <span class="text-danger">[not detected]</span>
		<?php else: ?>
            Ginger – EU Cookie Law <span class="text-success">[detected]</span>
		<?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>This free plugin offers an interesting cookie consent integration with the possibility to turn OFF
                    cookies before consent is given.</p>
                <p class="mb-0">Plugin: <a href="https://wordpress.org/plugins/ginger/" target="_blank">https://wordpress.org/plugins/ginger/</a>
                </p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
			    <?php PYS()->render_switcher_input( 'gdpr_ginger_integration_enabled', false,
				    ! isGingerPluginActivated() ); ?>
                <h4 class="switcher-label">Enable Ginger – EU Cookie Law integration</h4>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Notice -->
<div class="card card-static">
    <div class="card-header">
		<?php if ( ! isCookieNoticePluginActivated() ) : ?>
            Cookie Notice <span class="text-danger">[not detected]</span>
		<?php else: ?>
            Cookie Notice <span class="text-success">[detected]</span>
		<?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Free plugin with various features, including the option to store negative consent.</p>
                <p class="mb-0">Plugin: <a href="https://wordpress.org/plugins/cookie-notice/" target="_blank">https://wordpress.org/plugins/cookie-notice/</a>
                </p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_cookie_notice_integration_enabled', false,
					! isCookieNoticePluginActivated() ); ?>
                <h4 class="switcher-label">Cookie Notice integration</h4>
            </div>
        </div>
    </div>
</div>

<!-- Cookie Law Info -->
<div class="card card-static">
    <div class="card-header">
		<?php if ( ! isCookieLawInfoPluginActivated() ) : ?>
            GDPR Cookie Consent <span class="text-danger">[not detected]</span>
		<?php else: ?>
            GDPR Cookie Consent <span class="text-success">[detected]</span>
		<?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Free plugin useful features, including the option to store negative consent.</p>
                <p class="mb-0">Plugin: <a href="https://wordpress.org/plugins/cookie-law-info/" target="_blank">https://wordpress.org/plugins/cookie-law-info/</a>
                </p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
				<?php PYS()->render_switcher_input( 'gdpr_cookie_law_info_integration_enabled', false,
					! isCookieLawInfoPluginActivated() ); ?>
                <h4 class="switcher-label">GDPR Cookie Consent integration</h4>
            </div>
        </div>
    </div>
</div>

<div class="card card-static">
    <div class="card-header">
        Note
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>These solutions are not perfect or easy to implement especially for a non-technical person. Contact
                    THEIR support if you need any help. The free plugins might not cover every aspect of the GDPR
                    legislation.</p>
                <p class="mb-0">We are aware of the shortcomings and we try to offer more easy to use integrations in
                    the feature.</p>
            </div>
        </div>
    </div>
</div>

<!-- API -->
<div class="card card-static">
    <div class="card-header">
        For Developers
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col">
                <?php PYS()->render_switcher_input( 'gdpr_ajax_enabled' ); ?>
                <h4 class="switcher-label">Enable AJAX filter values update</h4>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p>Use <code>pys_gdpr_ajax_enabled</code>filter to by-pass option above.</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <p>Use following filters to control each pixel:
                    <code>pys_disable_by_gdpr</code>, <code>pys_disable_facebook_by_gdpr</code>,
                    <code>pys_disable_analytics_by_gdpr</code>, <code>pys_disable_google_ads_by_gdpr</code>
                    or <code>pys_disable_pinterest_by_gdpr</code>.
                </p>
                <p class="mb-0">First filter will disable all pixels, other can be used to disable particular pixel.
                    Simply pass <code>TRUE</code> value to disable a pixel.
                </p>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="row justify-content-center">
	<div class="col-4">
		<button class="btn btn-block btn-save">Save Settings</button>
	</div>
</div>