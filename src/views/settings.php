<div class="wrap woocommerce">

    <?php echo \WC_Admin_Settings::show_messages() ?>

    <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <h1 class="wp-heading-inline">General Settings</h1>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pm_woo_prod_sync_token">Price Meter API Token</label>
                </th>
                <td class="forminp forminp-text">
                    <input name="pm_woo_prod_sync_token" id="pm_woo_prod_sync_token" type="text"
                          value="<?php echo get_option(PMWOOPS_TOKEN) ?>" placeholder="" size="40" />
                    <br><p class="description">Get API token from <code><a href="https://pricemeter.pk/my/store" target="_blank">My Stores</a> > Your Store > Import Settings</code></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="pm_woo_prod_sync_csv">Price Meter CSV</label>
                </th>
                <td class="forminp forminp-text">
                    <?php
                    $qry = wp_nonce_url(http_build_query([
                        'action' => PMWOOPS_PROD_DOWNLOAD_CSV_ACTION,
                    ]), PMWOOPS_CSV_NONCE_URL, '_wpnonce_' . PMWOOPS_CSV_NONCE_URL);
                    ?>
                    <p class="description"><a href="<?php echo admin_url('admin-ajax.php?' . $qry) ?>">Click here</a> to download products csv, which you can upload under your store at Price Meter.</p>
                </td>
            </tr>
            </tbody>
        </table>

        <p class="submit">
            <button name="save_pm_prod_sync_form" class="button-primary pm-save-button" type="submit" value="Save changes">Save
                changes
            </button>
            <?php wp_nonce_field(PMWOOPS_PLUGIN_SLUG, '_wpnonce_' . PMWOOPS_PLUGIN_SLUG); ?>
        </p>
    </form>
</div>