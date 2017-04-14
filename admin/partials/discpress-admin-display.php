<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.imbeard.com
 * @since      1.0.0
 *
 * @package    Discpress
 * @subpackage Discpress/admin/partials
 */

$options = get_option($this->plugin_name);
$username = $options['username'];
$consumerKey = $options['consumerKey'];
$consumerSecret = $options['consumerSecret'];

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    
    <form method="post" name="discpress_options" action="options.php">

        <?php
            settings_fields($this->plugin_name);
            do_settings_sections($this->plugin_name);
        ?>

        <p><?php _e("In order for the plugin to work you have to insert your discogs username, then you will be able to sync your Discogs collection with your WordPress website.<br />To download images, create a Discogs app within your account at this link: <a href='https://www.discogs.com/settings/developers' target='_blank'>https://www.discogs.com/settings/developers</a>.<br />After the creation insert your Consumer Key and Conusmer Secret into the fields below, then authenticate.<br />After that you can bulk download the cover images for your records.", "discpress-pro"); ?></p>

        <fieldset>
            <label for="<?php echo $this->plugin_name; ?>-username">
                <span><?php esc_attr_e('Your Discogs username:', $this->plugin_name); ?></span>
                <input class="discpress-input" type="text" id="<?php echo $this->plugin_name; ?>-username" name="<?php echo $this->plugin_name; ?>[username]" value="<?php if(!empty($username)) echo $username; ?>"/>
            </label>
        </fieldset>

        <fieldset>
            <label for="<?php echo $this->plugin_name; ?>-consumerKey">
                <span><?php esc_attr_e('Your Discogs App consumer Key:', $this->plugin_name); ?></span>
                <input class="discpress-input" type="text" id="<?php echo $this->plugin_name; ?>-consumerKey" name="<?php echo $this->plugin_name; ?>[consumerKey]" value="<?php if(!empty($consumerKey)) echo $consumerKey; ?>"/>
            </label>
        </fieldset>       

        <fieldset>
            <label for="<?php echo $this->plugin_name; ?>-consumerSecret">
                <span><?php esc_attr_e('Your Discogs App consumer Secret:', $this->plugin_name); ?></span>
                <input class="discpress-input" type="text" id="<?php echo $this->plugin_name; ?>-consumerSecret" name="<?php echo $this->plugin_name; ?>[consumerSecret]" value="<?php if(!empty($consumerSecret)) echo $consumerSecret; ?>"/>
            </label>
        </fieldset>

        <?php submit_button('Save settings', 'primary', 'submit', TRUE); ?>

    </form>

    <?php if(!empty($username)) : ?>
        <div class="discpress-block">
            <p class="submit">
                <button id="syncCollectionBtn" class="button button-primary"><?php _e("Sync your collection", "discpress"); ?></button>
            </p>
            <progress class="discpress-progress" id="syncCollectionProgress" value="" max=""></progress>
            <p id="syncCollectionResponse"></p>
        </div>
    <?php endif; ?>

    <?php if(!empty($consumerKey) && !empty($consumerSecret) && !empty($username) && !get_option('discpress_access_token')) : ?>
        <hr />
        <div class="discpress-block">
            <p><?php _e("Authenticate to download images.", "discpress"); ?></p>
            <form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
                <input type="hidden" name="action" value="discpressAuthenticate" />
                <?php submit_button('Authenticate', 'primary', 'submit', TRUE); ?>
            </form>
        </div>
    <?php endif; ?>

    <?php if(get_option('discpress_access_token')) : ?>
        <hr />
        <p><?php _e("Authentication successful!", "discpress"); ?></p>
    <?php endif; ?>
    
    <?php
        if(get_option('discpress_access_token')) :
            $the_query = new WP_Query( array( 'post_type' => 'record', 'posts_per_page' => 1, 'fields' => 'ids' ) );
            $style = 'style="display: none;"';
            if($the_query->post_count > 0) {
                $style = '';
            }
    ?>
        <div id="syncImagesBlock" class="discpress-block" <?php echo $style; ?>>
            <p><?php _e("Download your Discogs collection images and attach them to your records as thumbnails.<br />Depending on the size of your collection, this may take some time, but you can stop the process anytime and resume it later.", "discpress"); ?></p>
            <p class="submit">
                <button id="syncImagesBtn" class="button button-primary"><?php _e("Sync images", "discpress"); ?></button>
            </p>
            <progress class="discpress-progress" id="syncImagesProgress" value="" max=""></progress>
            <p id="syncImagesResponse"></p>
        </div>
    <?php endif; ?>

    <hr />

    <div class="discpress-clearfix">

        <p class="discpress-madeby">Made by <a target="_blank" href="http://www.imbeard.com/">imbeard</a></p>

        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="discpress-donate-form">
            <p><?php _e("Help us improve this plugin", "discpress"); ?></p>
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="FV2P4BFDDUTFJ" />
            <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate" />
            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
        </form>
        
    </div>

</div>