<?php
declare(strict_types = 1);
require __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL);

// Registering Whoops
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

// Preparing and making API call
$apiToken = "";
$product = new \Pricemeter\Model\Product($apiToken);
$product = $product->fill([
    'id' => 42343,
    'sku' => 'dummy',
    'upc' => "some upc",
    'title' => 'Dummy Product title',
    'brand' => 'Dummy brandings',
    'category' => 'Computers > Battery',
    'price' => 350.4,
    'discounted_price' => 300.2,
    'image_url' => 'https://s3.ap-southeast-1.amazonaws.com/pm-storage/images/products/936/8510453468-41747_detail_img.jpg',
    'url' => 'https://radiotvcentre.pk/q/dummy',
    'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    'availability' => 1,
    'rating' => 4.5,
    'keywords' => 'dummy, products, items'
]);


try {
    // Making insert API call
    dd($product->insert());

    // Making update API call
//    dd($product->update());

    // Making delete API call
//    dd($product->delete());

} catch (\Exception $e) {
    dd($product, $e);
}