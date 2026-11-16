<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Core\ViewComposer;
use App\Support\DemoCatalog;
use App\Support\Persian;
use App\Support\ProductPresenter;
use WP_Post;
use WP_Query;

final class BlogComposer extends ViewComposer
{
    public static function views(): array
    {
        return ['blog.index', 'blog.single'];
    }

    public function with(array $data): array
    {
        if (is_singular('post')) {
            return $this->buildSingleArticle();
        }
        return $this->buildBlogArchive();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBlogArchive(): array
    {
        $activeCat = isset($_GET['bcat']) ? sanitize_text_field((string) wp_unslash($_GET['bcat'])) : 'all';
        $search = isset($_GET['bq']) ? sanitize_text_field((string) wp_unslash($_GET['bq'])) : get_search_query();
        $paged = max(1, (int) (get_query_var('paged') ?: 1));

        $publishedPosts = (int) (wp_count_posts('post')->publish ?? 0);
        $posts = [];
        $categories = ['all' => 'همه مطالب'];
        $totalPages = 1;

        if ($publishedPosts > 0) {
            foreach (get_categories(['hide_empty' => true]) as $term) {
                if ($term->slug !== 'uncategorized') {
                    $categories[$term->slug] = $term->name;
                }
            }

            $args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => 9,
                'paged' => $paged,
            ];
            if ($activeCat !== 'all' && $activeCat !== '') {
                if (is_category()) {
                    $queried = get_queried_object();
                    $args['category_name'] = $queried->slug ?? $activeCat;
                } else {
                    $args['category_name'] = $activeCat;
                }
            } elseif (is_category()) {
                $queried = get_queried_object();
                if ($queried && isset($queried->slug)) {
                    $activeCat = (string) $queried->slug;
                    $args['category_name'] = $activeCat;
                }
            }
            if ($search !== '') {
                $args['s'] = $search;
            }

            $q = new WP_Query($args);
            $totalPages = max(1, (int) $q->max_num_pages);
            foreach ($q->posts as $post) {
                $posts[] = $this->presentPost($post);
            }
        } else {
            foreach (DemoCatalog::posts() as $dp) {
                $categories[$dp['cat']] = $dp['cat'];
                if ($activeCat !== 'all' && $dp['cat'] !== $activeCat) {
                    continue;
                }
                if ($search !== '' && !str_contains(Persian::normalize($dp['title'] . ' ' . $dp['excerpt']), Persian::normalize($search))) {
                    continue;
                }
                $posts[] = [
                    'id' => $dp['id'],
                    'title' => $dp['title'],
                    'url' => rs_page_url('blog'),
                    'cat' => $dp['cat'],
                    'image' => rs_img($dp['image']),
                    'date' => $dp['date'],
                    'read' => $dp['read'],
                    'excerpt' => $dp['excerpt'],
                    'author' => 'تیم فنی رنگ صادقی',
                ];
            }
        }

        return [
            'featuredPost' => $posts[0] ?? null,
            'gridPosts' => count($posts) > 1 ? array_slice($posts, 1) : $posts,
            'allPosts' => $posts,
            'blogCategories' => $categories,
            'activeCat' => $activeCat,
            'searchQuery' => $search,
            'paged' => $paged,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSingleArticle(): array
    {
        $post = get_post(get_queried_object_id());
        if (!$post instanceof WP_Post) {
            return [];
        }

        $article = $this->presentPost($post);
        $content = apply_filters('the_content', $post->post_content);

        // استخراج خودکار سرتیترهای h2 برای فهرست مطالب (TOC)
        $toc = [];
        $idx = 0;
        $contentWithIds = (string) preg_replace_callback(
            '/<h2([^>]*)>(.*?)<\/h2>/is',
            static function (array $m) use (&$toc, &$idx): string {
                $idx++;
                $id = 'sec-' . $idx;
                $toc[] = [
                    'id' => $id,
                    'title' => wp_strip_all_tags($m[2]),
                ];
                if (str_contains($m[1], 'id=')) {
                    return $m[0];
                }
                return '<h2' . $m[1] . ' id="' . $id . '">' . $m[2] . '</h2>';
            },
            $content
        );
        $article['content'] = $contentWithIds;

        // مطالب مرتبط
        $relQuery = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'post__not_in' => [$post->ID],
            'no_found_rows' => true,
        ]);
        $relatedPosts = [];
        foreach ($relQuery->posts as $rp) {
            $relatedPosts[] = $this->presentPost($rp);
        }

        // محصولات پیشنهادی مرتبط با مقاله در ستون کناری
        $sidebarProducts = [];
        if (rs_is_wc()) {
            $pq = new WP_Query([
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 3,
                'orderby' => 'rand',
                'no_found_rows' => true,
            ]);
            foreach ($pq->posts as $prodPost) {
                $wcProd = wc_get_product($prodPost->ID);
                if ($wcProd) {
                    $sidebarProducts[] = ProductPresenter::fromWcProduct($wcProd);
                }
            }
        }
        if ($sidebarProducts === []) {
            foreach (array_slice(DemoCatalog::products(), 0, 3) as $dp) {
                $sidebarProducts[] = ProductPresenter::fromDemoArray($dp);
            }
        }

        $wpComments = get_comments([
            'post_id' => $post->ID,
            'status' => 'approve',
            'number' => 30,
        ]);

        return [
            'article' => $article,
            'toc' => $toc,
            'relatedPosts' => $relatedPosts,
            'sidebarProducts' => $sidebarProducts,
            'postComments' => $wpComments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPost(WP_Post $post): array
    {
        $cats = get_the_category($post->ID);
        $catName = !empty($cats[0]) ? $cats[0]->name : 'آموزش';
        $demoImg = (string) get_post_meta($post->ID, '_rs_demo_image', true);
        $thumb = get_the_post_thumbnail_url($post->ID, 'large')
            ?: ($demoImg !== '' ? rs_img($demoImg) : rs_img('blog/patina.webp'));
        $readMeta = (string) get_post_meta($post->ID, '_rs_read_time', true);
        $words = str_word_count(wp_strip_all_tags($post->post_content));
        $readLabel = $readMeta !== '' ? $readMeta : (rs_fa(max(3, (int) ceil($words / 180))) . ' دقیقه مطالعه');
        $tags = get_the_tags($post->ID);
        $tagNames = [];
        if (is_array($tags)) {
            foreach ($tags as $t) {
                $tagNames[] = $t->name;
            }
        }

        return [
            'id' => (string) $post->ID,
            'title' => get_the_title($post),
            'url' => (string) get_permalink($post),
            'cat' => $catName,
            'image' => (string) $thumb,
            'date' => Persian::jalaliDate((int) get_post_time('U', true, $post)),
            'read' => $readLabel,
            'excerpt' => wp_trim_words(wp_strip_all_tags($post->post_excerpt ?: $post->post_content), 28, '…'),
            'author' => get_the_author_meta('display_name', (int) $post->post_author) ?: 'تیم فنی رنگ صادقی',
            'tags' => $tagNames !== [] ? $tagNames : ['پتینه', 'اکریلیک', 'آموزش رنگ‌آمیزی'],
            'comments_count' => rs_fa((int) $post->comment_count),
        ];
    }
}
