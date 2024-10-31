<?php

namespace PMProdSync;

class Core
{
    /**
     * @var Product
     */
    private $product;

    public function __construct()
    {
        add_action('admin_notices', [$this, 'general_admin_notice']);
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_footer', [$this, 'footer_styles_and_script']);

        // Adding keywords field to products page
        add_action( 'woocommerce_product_options_general_product_data', [$this, 'woocommerce_product_keywords_field'] );
        add_action( 'woocommerce_process_product_meta', [$this, 'woocommerce_product_keywords_field_save'] );

        add_action( 'woocommerce_update_product', [$this, 'action_woocommerce_update_product'], 10, 1 );
        add_action( 'wp_trash_post', [$this, 'action_woocommerce_delete_product'], 10, 1 );

        // Managing woocommerce fields
        add_filter('manage_edit-product_columns', [$this, 'add_sync_status_field_to_products_table']);
        add_filter('manage_product_posts_custom_column', [$this, 'set_values_to_sync_status_field_to_products_table']);

        // Handling ajax call
        add_action( 'wp_ajax_' . PMWOOPS_PROD_SYNC_ACTION, [$this, 'handle_product_sync_action'] );
        add_action( 'wp_ajax_' . PMWOOPS_PROD_DOWNLOAD_CSV_ACTION, [$this, 'handle_download_products_csv_action'] );

        // Loading product object
        $this->product = new Product();
    }

    public static function init() : Core
    {
        return new static();
    }

    public function is_woocommerce_activated() : bool
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    public function add_notification( $type, $message ) {
        if ($type == 'success') {
            set_transient(get_current_user_id() . PMWOOPS_NOTIFICATION_SUCCESS_KEY,
                __($message, 'pm-prod-sync')
            );
        } else if ($type == 'error') {
            set_transient(get_current_user_id() . PMWOOPS_NOTIFICATION_ERROR_KEY,
                __($message, 'pm-prod-sync')
            );
        }
    }

    public function general_admin_notice()
    {
        if ( ! $this->is_woocommerce_activated()) {
            echo '<div class="notice notice-error is-dismissible">
                 <p>' . __('Price Meter Products Sync requires woocommerce plugin', 'pm-prod-sync') . '</p>
             </div>';
        }
        if ( empty(get_option(PMWOOPS_TOKEN))) {
            echo '<div class="notice notice-error">
                 <p>' . __('Price Meter Sync API Token not set.', 'pm-prod-sync') . '</p>
             </div>';
        }

        $successMessage = get_transient( get_current_user_id() . PMWOOPS_NOTIFICATION_SUCCESS_KEY );
        $errorMessage = get_transient( get_current_user_id() . PMWOOPS_NOTIFICATION_ERROR_KEY );

        if ( $successMessage ) {
            delete_transient( get_current_user_id() . PMWOOPS_NOTIFICATION_SUCCESS_KEY );

            printf( '<div class="%1$s"><p>%2$s</p></div>',
                'notice notice-success is-dismissible',
                'Price Meter Product Sync - ' . esc_html($successMessage)
            );
        }
        if ( $errorMessage ) {
            delete_transient( get_current_user_id() . PMWOOPS_NOTIFICATION_ERROR_KEY );

            printf( '<div class="%1$s"><p>%2$s</p></div>',
                'notice notice-error is-dismissible',
                'Price Meter Product Sync - ' . esc_html($errorMessage)
            );
        }
    }

    public function footer_styles_and_script()
    {
        include_once PMWOOPS_ROOT_SRC . '/assets/style.html';
    }

    public function register_menu()
    {
        $this->store_settings();

        add_submenu_page('woocommerce', __('Price Meter Products Sync', 'pm-prod-sync'),
            __('Price Meter Products Sync', 'pm-prod-sync'), 'manage_options', PMWOOPS_PLUGIN_SLUG,
            [$this, 'add_plugin_admin_settings_page'], 100);
    }

    public function add_plugin_admin_settings_page()
    {
        include_once PMWOOPS_ROOT_VIEW . '/settings.php';
    }

    private function store_settings()
    {
        if ( ! isset($_POST['save_pm_prod_sync_form'])) {
            return;
        }

        check_admin_referer(PMWOOPS_PLUGIN_SLUG, '_wpnonce_' . PMWOOPS_PLUGIN_SLUG);

        $endpoint = sanitize_text_field($_POST['pm_woo_prod_sync_token']);

        if (empty($endpoint)) {
            \WC_Admin_Settings::add_error(__('Please provide valid Price Meter endpoint', 'pm-prod-sync'));

        } else {
            update_option(PMWOOPS_TOKEN, esc_attr($endpoint));
            \WC_Admin_Settings::add_message(__('Price Meter token has been updated successfully', 'pm-prod-sync'));
        }
    }

    public function woocommerce_product_keywords_field()
    {
        global $woocommerce, $post;
        echo '<div class="product_keywords_field">';
        woocommerce_wp_text_input(
            array(
                'id'          => PMWOOPS_PROD_KEYWORDS_FIELD,
                'label'       => __( 'Price Meter Product Keywords', 'woocommerce' ),
                'placeholder' => 'Multiple keywords must be comma separated',
                'desc_tip'    => true,
                'description' => __( 'Keywords will be used on Price Meter for better product search', 'pm-prod-sync' ),
            )
        );
        echo '</div>';
    }

    public function woocommerce_product_keywords_field_save($post_id)
    {
        $field = sanitize_text_field($_POST[PMWOOPS_PROD_KEYWORDS_FIELD]);

        if (!empty($field))
            update_post_meta($post_id, PMWOOPS_PROD_KEYWORDS_FIELD, esc_attr($field));
    }

    public function add_sync_status_field_to_products_table($columns)
    {
        $columns = array_insert_after($columns, 'featured', [
            PMWOOPS_PROD_TABLE_KEY => 'PM Sync Status'
        ]);

        return $columns;
    }

    function action_woocommerce_update_product( $product_id ) {
        // Making update or insert API call
        $response = $this->product->updateOrInsert($product_id);

        if (isset($response['status']) && $response['status'] == true) {
            $this->add_notification('success', 'Product has been synced');
        } else {
            $this->add_notification('error', (isset($response['message']) ? $response['message'] : 'Error syncing product'));
        }
    }

    function action_woocommerce_delete_product( $product_id ) {
        $prod = wc_get_product($product_id);
        if (!$prod instanceof \WC_Product) return;

        // Making delete API call
        $response = $this->product->deleteById($product_id);

        if (isset($response['status']) && $response['status'] == true) {
            $this->add_notification('success', 'Product has been deleted');
        } else {
            $this->add_notification('error', (isset($response['message']) ? $response['message'] : 'Error deleting product'));
        }
    }

    public function set_values_to_sync_status_field_to_products_table($column_name)
    {
        if ($column_name == PMWOOPS_PROD_TABLE_KEY) {
            $prodId = get_the_ID();
            $syncStatus = get_post_meta($prodId, PMWOOPS_PROD_SYNC_META_FIELD, true);

            $qry = wp_nonce_url(http_build_query([
                'action' => PMWOOPS_PROD_SYNC_ACTION,
                'product_id' => $prodId,
            ]), sprintf(PMWOOPS_PROD_NONCE_URL, $prodId), '_wpnonce_' . sprintf(PMWOOPS_PROD_NONCE_URL, $prodId));

            if ($syncStatus != true) echo '<a href="' . admin_url( 'admin-ajax.php?' . $qry ) . '">';
                echo '<span class="dashicons dashicons-' . ($syncStatus == true ? 'yes-alt pm-text-success' : 'dismiss pm-text-danger') . '"></span>';
            if ($syncStatus != true) echo '</a>';
        }
    }

    public function handle_product_sync_action()
    {
        $prodId = intval($_REQUEST['product_id']);
        check_admin_referer(sprintf(PMWOOPS_PROD_NONCE_URL, $prodId), '_wpnonce_' . sprintf(PMWOOPS_PROD_NONCE_URL, $prodId));

        $response = $this->product
            ->convert_woo_product(wc_get_product($prodId))
            ->updateOrInsert($prodId);

        if (isset($response['status'])) {
            if ($response['status'] == 1) {
                $this->add_notification('success', 'Product has been synced');
            } else {
                $this->add_notification('error', isset($response['message']) ? $response['message'] : 'Error syncing product');
            }
        }

        wp_redirect(wp_get_referer());

        die();
    }

    public function handle_download_products_csv_action()
    {
        check_admin_referer(PMWOOPS_CSV_NONCE_URL, '_wpnonce_' . PMWOOPS_CSV_NONCE_URL);

        $products = wc_get_products( array( 'limit' => -1 ) );
        $reqProducts = [
            [
                'id', 'sku', 'upc', 'title', 'brand', 'category', 'price', 'discounted_price', 'image_url', 'url', 'description', 'availability', 'rating', 'keywords'
            ]
        ];
        foreach ($products as $product) {
            $reqProducts[] = Product::get_product_array($product);
        }

        $filename = 'price-meter-woo-products-' . date('Y-m-d_H:i', strtotime('now')) . '.csv';

        $f = fopen('php://memory', 'w');
        foreach ($reqProducts as $line) {
            fputcsv($f, $line);
        }
        fseek($f, 0);

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        fpassthru($f);

        die();
    }
}