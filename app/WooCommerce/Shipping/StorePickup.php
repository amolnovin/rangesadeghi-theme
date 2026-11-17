<?php
declare(strict_types=1);
namespace App\WooCommerce\Shipping;

/** A store pickup rate; checkout captures the requested pickup time. */
final class StorePickup extends \WC_Shipping_Method
{
    public function __construct()
    {
        $this->id = 'rs_store_pickup';
        $this->method_title = 'تحویل حضوری از فروشگاه';
        $this->title = (string) rs_option('shop.pickup_title', 'تحویل حضوری از فروشگاه');
        $this->enabled = rs_option('shop.pickup_enabled', false) ? 'yes' : 'no';
        $this->supports = [];
        $this->tax_status = 'none';
    }
    public function is_available($package): bool
    {
        return $this->enabled === 'yes' && (bool) rs_option('shop.delivery_slots_enabled', true) && trim((string) rs_option('shop.delivery_slots', '')) !== '' && parent::is_available($package);
    }
    public function calculate_shipping($package = []): void
    {
        $this->add_rate(['id' => $this->id, 'label' => $this->title, 'cost' => max(0, (float) rs_option('shop.pickup_cost', 0)), 'package' => $package]);
    }
}
