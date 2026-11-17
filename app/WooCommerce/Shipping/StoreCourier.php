<?php
declare(strict_types=1);
namespace App\WooCommerce\Shipping;

/** A real WooCommerce rate, controlled by the theme and restricted to configured cities. */
final class StoreCourier extends \WC_Shipping_Method
{
    public function __construct()
    {
        $this->id = 'rs_store_courier';
        $this->method_title = 'پیک فروشگاه';
        $this->title = (string) rs_option('shop.courier_title', 'پیک فروشگاه');
        $this->enabled = rs_option('shop.courier_enabled', false) ? 'yes' : 'no';
        $this->supports = [];
        $this->tax_status = 'none';
    }
    public function is_available($package): bool
    {
        if ($this->enabled !== 'yes' || !rs_option('shop.delivery_slots_enabled', true) || trim((string) rs_option('shop.delivery_slots', '')) === '') return false;
        $cities = array_filter(array_map(static fn($s) => \App\Support\Persian::normalize($s), explode(',', (string) rs_option('shop.courier_cities', ''))));
        $city = \App\Support\Persian::normalize((string) ($package['destination']['city'] ?? ''));
        return $city !== '' && in_array($city, $cities, true) && parent::is_available($package);
    }
    public function calculate_shipping($package = []): void
    {
        $this->add_rate(['id' => $this->id, 'label' => $this->title, 'cost' => max(0, (float) rs_option('shop.courier_cost', 0)), 'package' => $package]);
    }
}
