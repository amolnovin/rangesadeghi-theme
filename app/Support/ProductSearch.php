<?php

declare(strict_types=1);

namespace App\Support;

use WP_Query;

/** One scoped search implementation for the product archive and live suggestions. */
final class ProductSearch
{
    private const VARIANTS = [
        'ی' => ['ي', 'ى'], 'ک' => ['ك'], 'ه' => ['ة'],
        '0' => ['۰', '٠'], '1' => ['۱', '١'], '2' => ['۲', '٢'], '3' => ['۳', '٣'],
        '4' => ['۴', '٤'], '5' => ['۵', '٥'], '6' => ['۶', '٦'], '7' => ['۷', '٧'],
        '8' => ['۸', '٨'], '9' => ['۹', '٩'],
    ];

    public static function clean(string $text): string
    {
        $text = sanitize_text_field($text);
        return trim(mb_substr($text, 0, 120, 'UTF-8'));
    }

    public static function normalize(string $text): string
    {
        return Persian::toEn(Persian::normalize(str_replace('ى', 'ی', $text)));
    }

    private static function withVisibility(array $tax): array
    {
        if (!function_exists('wc_get_product_visibility_term_ids')) return $tax;
        $visibility = wc_get_product_visibility_term_ids();
        $excluded = [(int) ($visibility['exclude-from-search'] ?? 0)];
        if (get_option('woocommerce_hide_out_of_stock_items') === 'yes') $excluded[] = (int) ($visibility['outofstock'] ?? 0);
        $excluded = array_values(array_filter($excluded));
        if (!$excluded) return $tax;
        $condition = ['taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => $excluded, 'operator' => 'NOT IN'];
        return $tax ? ['relation' => 'AND', $tax, $condition] : [$condition];
    }

    /** Configure the main search query without affecting unrelated WordPress queries. */
    public static function apply(WP_Query $query, string $text, bool $relevance = true): void
    {
        $text = self::normalize(self::clean($text));
        if ($text === '') return;
        $query->set('s', $text);
        $query->set('rs_product_search', $text);
        $query->set('rs_product_search_relevance', $relevance);
        $query->set('tax_query', self::withVisibility((array) $query->get('tax_query')));
    }

    public static function query(array $args, string $text, bool $relevance = true): WP_Query
    {
        $search = self::normalize(self::clean($text));
        if ($search !== '') {
            $args['s'] = $search;
            $args['rs_product_search'] = $search;
            $args['rs_product_search_relevance'] = $relevance;
            $args['tax_query'] = self::withVisibility((array) ($args['tax_query'] ?? []));
        }
        return new WP_Query($args);
    }

    private static function normalizedColumn(string $column, string $search): string
    {
        global $wpdb;
        // A half-space in stored titles should behave like a space for phrase ranking.
        if (str_contains($search, ' ')) $column = $wpdb->prepare("REPLACE($column, %s, %s)", "\u{200C}", ' ');
        // Only normalize variants actually needed by this query to reduce database work.
        foreach (self::VARIANTS as $canonical => $variants) {
            if (!str_contains($search, (string) $canonical)) continue;
            foreach ($variants as $variant) {
                $column = $wpdb->prepare("REPLACE($column, %s, %s)", $variant, (string) $canonical);
            }
        }
        return $column;
    }

    public static function filterSearch(string $sql, WP_Query $query): string
    {
        $search = (string) $query->get('rs_product_search');
        if ($search === '') return $sql;
        global $wpdb;
        $terms = array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 10);
        if (!$terms) return $sql;
        $columns = [];
        foreach (['post_title', 'post_excerpt', 'post_content'] as $column) {
            $columns[] = self::normalizedColumn("{$wpdb->posts}.{$column}", $search);
        }
        $parts = [];
        foreach ($terms as $term) {
            $like = '%' . $wpdb->esc_like($term) . '%';
            $matches = [];
            foreach ($columns as $column) $matches[] = $wpdb->prepare("$column LIKE %s", $like);
            $parts[] = '(' . implode(' OR ', $matches) . ')';
        }
        $result = ' AND (' . implode(' AND ', $parts) . ')';
        if (!is_user_logged_in()) $result .= " AND ({$wpdb->posts}.post_password = '')";
        return $result;
    }

    public static function filterOrder(string $sql, WP_Query $query): string
    {
        $search = (string) $query->get('rs_product_search');
        if ($search === '' || !$query->get('rs_product_search_relevance')) return $sql;
        global $wpdb;
        $title = self::normalizedColumn("{$wpdb->posts}.post_title", $search);
        $sentence = $wpdb->prepare("$title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        $parts = [];
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 10) as $term) {
            $parts[] = $wpdb->prepare("$title LIKE %s", '%' . $wpdb->esc_like($term) . '%');
        }
        if (!$parts) return $sql;
        return '(CASE WHEN ' . $sentence . ' THEN 0 WHEN (' . implode(' AND ', $parts) . ") THEN 1 ELSE 2 END), {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
    }
}
