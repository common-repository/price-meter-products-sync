# Pricemeter Products PHP API
Price Meter PHP API to manage store specific products

## Requirement
* PHP >= 7.2

## Installation
`composer require ict-master-pk/pricemeter-products-php`
### For PHP >= 5.6
`composer require ict-master-pk/pricemeter-products-php:dev-guzzle_655`

## API Token
* Get API token from `My Stores > <Your Store> > Import Settings`
* From API import option, you'll get the **API Endpoint**
* i.e, https://pricemeter.pk/notify_store/<api_token>

## Usage
```php
$product = new \Pricemeter\Model\Product($apiToken);
$product = $product->fill([
    'id' => 1,
    'sku' => 'dummy',
    'upc' => "some upc",
    'title' => 'Dummy Product title',
    'brand' => 'Dummy brandings',
    'category' => 'Computers > Battery',
    'price' => 350.4,
    'discounted_price' => 300.2,
    'image_url' => 'https://dummystore.pk/detail_img.jpg',
    'url' => 'https://dummystore.pk/q/dummy',
    'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    'availability' => 1,
    'rating' => 4.5,
    'keywords' => 'dummy, products, items'
]);

# To Insert
$product->insert();

# To Update
$product->update();

# To Delete
$product->delete();
# OR do this in more convenient way as below
(new \Pricemeter\Model\Product($apiToken))->fill([
    'id' => 1
])->delete();
```