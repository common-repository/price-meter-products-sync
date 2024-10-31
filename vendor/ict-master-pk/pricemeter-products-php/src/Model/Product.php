<?php

namespace Pricemeter\Model;

use GuzzleHttp\Exception\GuzzleException;
use Pricemeter\Request;

class Product
{
    /**
     * Price Meter API endpoint
     * @var string
     */
    private $endpoint;

    /**
     * @var mixed|null
     */
    private $cms = "";

    private $data = [];

    /**
     * List of all fields to be filled or required for API
     * @var string[]
     */
    public $fillable = [
        'id',
        'sku',
        'upc',
        'title',
        'brand',
        'category',
        'price',
        'discounted_price',
        'image_url',
        'url',
        'description',
        'availability',
        'rating',
        'keywords'
    ];

    public function __construct($token, $cms = "")
    {
        $this->endpoint = sprintf('notify_store/%s', $token);
        $this->cms = $cms;
    }

    /**
     * Fill product fields using array
     *
     * @param array $data
     * @return Product
     */
    public function fill(array $data) : Product
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Making PriceMeter product insert API call
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function insert()
    {
        return Request::make('POST', $this->endpoint, $this->data, $this->cms);
    }

    /**
     * Making PriceMeter product update API call
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function update()
    {
        $prodData = $this->data;
        $id = $prodData['id'];

        // Removing id from request
        unset($prodData['id']);

        return Request::make('PUT', $this->endpoint . '/' . $id, $prodData, $this->cms);
    }

    /**
     * Making PriceMeter product delete API call
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function delete()
    {
        return Request::make('DELETE', $this->endpoint . '/' . $this->data['id'], [], $this->cms);
    }

    public function __serialize() : array
    {
        return $this->data;
    }

    public function __unserialize(array $data) : void
    {
        $this->data = $data;
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        if (in_array($name, $this->fillable)) {
            $this->data[$name] = $value;
        } else {
            throw new \Exception("{$name} not in fillable");
        }
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}