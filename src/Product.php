<?php

namespace PMProdSync;


class Product
{
    /**
     * @var \Pricemeter\Model\Product
     */
    private $product;

    public function __construct()
    {
        $apiToken = get_option(PMWOOPS_TOKEN);
        $this->product = new \Pricemeter\Model\Product($apiToken, \Pricemeter\Model\Cms::WP);
    }

    public function convert_woo_product_by_id($product_id)
    {
        return $this->convert_woo_product(wc_get_product($product_id));
    }

    public function convert_woo_product(\WC_Product $wooProd)
    {
        $this->product->fill(self::get_product_array($wooProd));

        return $this;
    }

    public static function get_product_array(\WC_Product $wooProd) : array
    {
        $categories = [];

        foreach ($wooProd->get_category_ids() as $category_id) {
            $term = get_term_by('id', $category_id, 'product_cat');
            $categories[] = $term->name;
        }

        $imageObj = wp_get_attachment_image_src(get_post_thumbnail_id( $wooProd->get_id() ), 'full');

        return [
            'id' => $wooProd->get_id(),
            'sku' => $wooProd->get_sku(),
            'upc' => '',
            'title' => $wooProd->get_title(),
            'brand' => get_bloginfo('name'),
            'category' => implode(' > ', $categories),
            'price' => $wooProd->get_regular_price(),
            'discounted_price' => $wooProd->get_sale_price(),
            'image_url' => $imageObj[0] ?? '',
            'url' => get_permalink( $wooProd->get_id() ),
            'description' => $wooProd->get_description(),
            'availability' => $wooProd->is_in_stock() ? 1 : 0,
            'rating' => $wooProd->get_average_rating(),
            'keywords' => get_post_meta($wooProd->get_id(), PMWOOPS_PROD_KEYWORDS_FIELD, true),
        ];
    }

    public function updateOrInsert($product_id)
    {
        $product = $this->convert_woo_product_by_id($product_id);
        $isInserted = get_post_meta($product_id, PMWOOPS_PROD_SYNC_META_FIELD, true);

        return $product->syncByStatus($isInserted == true ? PMWOOPS_STATUS_UPDATE : PMWOOPS_STATUS_INSERT);
    }

    public function deleteById($product_id)
    {
        $this->product->id = $product_id;

        return $this->syncByStatus(PMWOOPS_STATUS_DELETE);
    }

    public function syncByStatus($status)
    {
        try {
            $response = [
                'status' => false
            ];

            if ($status == PMWOOPS_STATUS_INSERT) {
                $response = $this->product->insert();

                if ($response['status'] == true) {
                    update_post_meta($this->product->id, PMWOOPS_PROD_SYNC_META_FIELD, true);

                } elseif (isset($response['message']) && trim($response['message']) == 'Product already exists with given id.') {
                    // Update sync status by marking it as already synced there and then try again syncing product
                    update_post_meta($this->product->id, PMWOOPS_PROD_SYNC_META_FIELD, true);

                    // Resend update request
                    $response = $this->updateOrInsert($this->product->id);
                }
            } elseif ($status == PMWOOPS_STATUS_UPDATE) {
                $response = $this->product->update();

                if ($response['status'] == true) {
                    update_post_meta($this->product->id, PMWOOPS_PROD_SYNC_META_FIELD, true);
                }
            } elseif ($status == PMWOOPS_STATUS_DELETE) {
                $response = $this->product->delete();

                if ($response['status'] == true) {
                    delete_post_meta($this->product->id, PMWOOPS_PROD_SYNC_META_FIELD);
                }
            }

            // Formatting response array message
            if (isset($response['message']) && is_array($response['message'])) {
                $response['message'] = implode(' ', array_map(function ($value) {
                    return is_array($value) ? reset($value) : $value;
                }, $response['message']));
            }

            return $response;

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error occurred while making sync call as below ' . PHP_EOL . $e->getMessage()
            ];
        }
    }
}