<?php
namespace  App\Services\Service;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Services\Service\Cart\Item;
use App\Events\CartAdding;
use App\Events\CartAdded;
use App\Events\CartUpdating;
use App\Events\CartUpdated;
use App\Events\CartRemoving;
use App\Events\CartRemoved;
use App\Events\CartCheckouting;
use App\Events\CartCheckouted;
use App\Events\CartDestroying;
use App\Events\CartDestroyed;

class CartService
{
    /**
     * cache manager.
     *
     * @var \Illuminate\cache\cacheManager
     */
    protected $cache;
    /**
     * Event dispatcher.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $event;
    /**
     * Current cart name.
     *
     * @var string
     */
    protected $name = 'shopping_cart.default';
    /**
     * Associated model name.
     *
     * @var string
     */
    protected $model;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->cache = Cache::store('redis')->tags('shoppingCart');
    }

   /**
     * Set the current cart name.
     *
     * @param string $name Cart name name
     *
     * @return Cart
     */
    public function name($name)
    {
        $this->name = 'shopping_cart.'.$name;
        return $this;
    }
    /**
     * Associated model.
     *
     * @param string $model The name of the model
     *
     * @return Cart
     */
    public function associate($model)
    {
        if (!class_exists($model)) {
            throw new \Exception("Invalid model name '$model'.");
        }
        $this->model = $model;
        return $this;
    }
    /**
     * Get all items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->getCart();
    }
    /**
     * Add a row to the cart.
     *
     * @param int|string $id         Unique ID of the item
     * @param string     $name       Name of the item
     * @param int        $qty        Item qty to add to the cart
     * @param float      $price      Price of one item
     * @param array      $attributes Array of additional attributes, such as 'size' or 'color'...
     *
     * @return string
     */
    public function add($id, $name = null, $qty = null, $price = null, array $attributes = [])
    {
        $cart = $this->getCart();
        event(new CartAdding($this->name, [$attributes, $cart]));
        $row = $this->addRow($id, $name, $qty, $price, $attributes);
        $cart = $this->getCart();
        event(new CartAdded($this->name, [$attributes, $cart]));
        return $row;
    }
    /**
     * Update the quantity of one row of the cart.
     *
     * @param string    $rawId     The __raw_id of the item you want to update
     * @param int|array $attribute New quantity of the item|Array of attributes to update
     *
     * @return Item|bool
     */
    public function update($rawId, $attribute)
    {
        if (!$row = $this->get($rawId)) {
            throw new \Exception('Item not found.');
        }
        $cart = $this->getCart();
        event(new CartUpdating($this->name, [$row, $cart]));

        if (is_array($attribute)) {
            $raw = $this->updateAttribute($rawId, $attribute);
        } else {
            $raw = $this->updateQty($rawId, $attribute);
        }

        event(new CartUpdated($this->name, [$row, $cart]));
        return $raw;
    }
    /**
     * Remove a row from the cart.
     *
     * @param string $rawId The __raw_id of the item
     *
     * @return bool
     */
    public function remove($rawId)
    {
        if (!$row = $this->get($rawId)) {
            return true;
        }
        $cart = $this->getCart();
        event(new CartRemoving($this->name, [$row, $cart]));
        $cart->forget($rawId);
        $this->save($cart);
        event(new CartRemoved($this->name, [$row, $cart]));
        return true;
    }
    /**
     * Get a row of the cart by its ID.
     *
     * @param string $rawId The ID of the row to fetch
     *
     * @return Item
     */
    public function get($rawId)
    {
        $row = $this->getCart()->get($rawId);
        return is_null($row) ? null : new Item($row);
    }
    /**
     * Clean the cart.
     *
     * @return bool
     */
    public function destroy()
    {
        $cart = $this->getCart();
        event(new CartDestroying($this->name, $cart));
        $this->save(null);
        event(new CartDestroyed($this->name, $cart));
        return true;
    }
    /**
     * Alias of destory().
     *
     * @return bool
     */
    public function clean()
    {
        $this->destroy();
    }
    /**
     * Get the price total.
     *
     * @return float
     */
    public function total()
    {
        return $this->totalPrice();
    }
    /**
     * Return total price of cart.
     *
     * @return
     */
    public function totalPrice()
    {
        $total = 0;
        $cart = $this->getCart();
        if ($cart->isEmpty()) {
            return $total;
        }
        foreach ($cart as $row) {
            $total += $row->qty * $row->price;
        }
        return $total;
    }
    /**
     * Get the number of items in the cart.
     *
     * @param bool $totalItems Get all the items (when false, will return the number of rows)
     *
     * @return int
     */
    public function count($totalItems = true)
    {
        $items = $this->getCart();
        if (!$totalItems) {
            return $items->count();
        }
        $count = 0;
        foreach ($items as $row) {
            $count += $row->qty;
        }
        return $count;
    }
    /**
     * Get rows count.
     *
     * @return int
     */
    public function countRows()
    {
        return $this->count(false);
    }
    /**
     * Search if the cart has a item.
     *
     * @param array $search An array with the item ID and optional options
     *
     * @return array
     */
    public function search(array $search)
    {
        $rows = new Collection();
        if (empty($search)) {
            return $rows;
        }
        foreach ($this->getCart() as $item) {
            if (array_intersect_assoc($item->intersect($search)->toArray(), $search)) {
                $rows->put($item->__raw_id, $item);
            }
        }
        return $rows;
    }
    /**
     * Get current cart name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get current associated model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
    /**
     * Return whether the shopping cart is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() <= 0;
    }
    /**
     * Add row to the cart.
     *
     * @param string $id         Unique ID of the item
     * @param string $name       Name of the item
     * @param int    $qty        Item qty to add to the cart
     * @param float  $price      Price of one item
     * @param array  $attributes Array of additional options, such as 'size' or 'color'
     *
     * @return string
     */
    protected function addRow($id, $name, $qty, $price, array $attributes = [])
    {
        if (!is_numeric($qty) || $qty < 1) {
            throw new \Exception('Invalid quantity.');
        }
        if (!is_numeric($price) || $price < 0) {
            throw new \Exception('Invalid price.');
        }
        $cart = $this->getCart();
        $rawId = $this->generateRawId($id, $attributes);
        if ($row = $cart->get($rawId)) {
            $row = $this->updateQty($rawId, $row->qty + $qty);
        } else {
            $row = $this->insertRow($rawId, $id, $name, $qty, $price, $attributes);
        }
        return $row;
    }
    /**
     * Generate a unique id for the new row.
     *
     * @param string $id         Unique ID of the item
     * @param array  $attributes Array of additional options, such as 'size' or 'color'
     *
     * @return string
     */
    protected function generateRawId($id, $attributes)
    {
        ksort($attributes);
        return md5($id.serialize($attributes));
    }
    /**
     * Sync the cart to cache.
     *
     * @param \Illuminate\Support\Collection|null $cart The new cart content
     *
     * @return \Illuminate\Support\Collection
     */
    protected function save($cart)
    {  
        $this->cache->put($this->name, $cart);
        return $cart;
    }
    /**
     * Get the carts content.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getCart()
    {
        $cart = $this->cache->get($this->name);
        return $cart instanceof Collection ? $cart : new Collection();
    }
    /**
     * Update a row if the rawId already exists.
     *
     * @param string $rawId      The ID of the row to update
     * @param array  $attributes The quantity to add to the row
     *
     * @return Item
     */
    protected function updateRow($rawId, array $attributes)
    {
        $cart = $this->getCart();
        $row = $cart->get($rawId);
        foreach ($attributes as $key => $value) {
            $row->put($key, $value);
        }
        if (count(array_intersect(array_keys($attributes), ['qty', 'price']))) {
            $row->put('total', $row->qty * $row->price);
        }

        $this->save($cart);
        return $row;
    }
    /**
     * Create a new row Object.
     *
     * @param string $rawId      The ID of the new row
     * @param string $id         Unique ID of the item
     * @param string $name       Name of the item
     * @param int    $qty        Item qty to add to the cart
     * @param float  $price      Price of one item
     * @param array  $attributes Array of additional options, such as 'size' or 'color'
     *
     * @return Item
     */
    protected function insertRow($rawId, $id, $name, $qty, $price, $attributes = [])
    {
        $newRow = $this->makeRow($rawId, $id, $name, $qty, $price, $attributes);
        $cart = $this->getCart();
        $cart->put($rawId, $newRow);
        $this->save($cart);
        return $newRow;
    }
    /**
     * Make a row item.
     *
     * @param string $rawId      raw id
     * @param mixed  $id         item id
     * @param string $name       item name
     * @param int    $qty        quantity
     * @param float  $price      price
     * @param array  $attributes other attributes
     *
     * @return Item
     */
    protected function makeRow($rawId, $id, $name, $qty, $price, array $attributes = [])
    {
        return new Item(array_merge([
                                     '__raw_id' => $rawId,
                                     'id' => $id,
                                     'name' => $name,
                                     'qty' => $qty,
                                     'price' => $price,
                                     'total' => $qty * $price,
                                     '__model' => $this->model,
                                    ], $attributes));
    }
    /**
     * Update the quantity of a row.
     *
     * @param string $rawId The ID of the row
     * @param int    $qty   The qty to add
     *
     * @return Item|bool
     */
    public function updateQty($rawId, $qty)
    {
        if ($qty <= 0) {
            return $this->remove($rawId);
        }
        return $this->updateRow($rawId, ['qty' => $qty]);
    }
    /**
     * Update an attribute of the row.
     *
     * @param string $rawId      The ID of the row
     * @param array  $attributes An array of attributes to update
     *
     * @return Item
     */
    protected function updateAttribute($rawId, $attributes)
    {
        return $this->updateRow($rawId, $attributes);
    }
}