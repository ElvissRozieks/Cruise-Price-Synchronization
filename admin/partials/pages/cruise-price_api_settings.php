<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Cruise_price
 * @subpackage Cruise_price/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1>API settings</h1>
    <form method="post" action="options.php">
        <?php
            settings_fields( 'cruise-price_setting_fields' );
            do_settings_sections( 'cruise-price_setting_fields' );
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                         <label for="apikey">API KEY</label>
                    </th>
                    <td>
                        <input name="apikey" type="password" aria-describedby="tagline-description" id="apikey" value="<?php echo get_option('apikey'); ?>" class="regular-text strong">
                        <p class="description" id="tagline-description">Save securly API Key</p>
                    </td>
                </tr>
                <tr>
                    <th>
                         <label for="api-url">API Access URL</label>
                    </th>
                    <td>
                        <input name="api-url" type="text" aria-describedby="tagline-description" id="api-url" value="<?php echo get_option('api-url'); ?>" class="regular-text strong">
                        <p class="description" id="tagline-description">Access Url</p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <h1>Google KEY's</h1>
                    </th>
                </tr>
                <tr>
                    <th>
                         <label for="site-key">Google Re-captche V3 Site key</label>
                    </th>
                    <td>
                        <input name="site-key" type="text" aria-describedby="tagline-description" id="site-key" value="<?php echo get_option('site-key'); ?>" class="regular-text strong">
                        <p class="description" id="tagline-description">Site key</p>
                    </td>
                </tr>
                <tr>
                    <th>
                    <label for="secure-key">Google Re-captche V3 Secure key</label>
                    </th>
                    <td>
                        <input name="secure-key" type="password" aria-describedby="tagline-description" id="secure-key" value="<?php echo get_option('secure-key'); ?>" class="regular-text strong">
                        <p class="description" id="tagline-description">Secure key</p>
                    </td>
                </tr>
                <tr>
                    <th>
                    <label for="secure-key">Google Map API</label>
                    </th>
                    <td>
                        <input name="google-map" type="password" aria-describedby="tagline-description" id="google-map" value="<?php echo get_option('google-map'); ?>" class="regular-text strong">
                        <p class="description" id="tagline-description">Google MAP API Key</p>
                    </td>
                </tr>
            </tbody>
        </table>
            <?php submit_button('Save Api Key','primary','submit'); ?>
    </form>
    <hr>
    <table class="form-table">
        <tr>
            <th>
                <h1>Shortcode settings</h1>
            </ht>
        </tr>
        <tr>
            <th>
                    <label>Shortcode</label>
            </th>
            <td>
                <input disabled type="text" aria-describedby="tagline-description" value="[cruise_api_list]" class="regular-text strong">
                <p class="description" id="tagline-description">Use this short code to execute cruiser list</p>
            </td>
        </tr>
    </table>
</div>