<?php

namespace app\models;

use \WP_Query;
use \Exception;

class KeenadoPostGrid {
    private $atts;

    public function __construct($atts = []) {
        $this->atts = shortcode_atts(
            array(
                'title_font_family'       => 'font-arial',
                'title_font_color'        => '#000000',
                'description_font_family' => 'font-arial',
                'description_font_color'  => '#454545',
                'bg_color'                => '#ffffff',
                'posts_per_page'          => '3',
                'category'                => '',
                'grid_id'                 => uniqid('post_grid_'),
                'paged'                   => 1, 
            ),
            $atts
        );
    }

    public function render() {
        try {
            $output = '';
            $grid_id = $this->atts['grid_id'];

           $paged = isset($this->atts['paged']) ? (int) $this->atts['paged'] : 1;

            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => intval($this->atts['posts_per_page']),
                'paged'          => $paged,
            );

            if (!empty($this->atts['category'])) {
                $args['category_name'] = sanitize_title($this->atts['category']);
            }

            $query = new WP_Query($args);

            $output .= "<div class='post-palooza-grid' data-grid-id='" . esc_attr($grid_id) . "' data-layout='vertical'>";

            if ($query->have_posts()) {
                $output .= '<div class="post-grid grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">';

                while ($query->have_posts()) {
                    $query->the_post();

                    $output .= '<div onclick="window.location.href=\'' . esc_url(get_the_permalink()) . '\'" class="post-item border border-gray-200 shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full flex flex-col" style="background-color:' . esc_attr($this->atts['bg_color']) . '; max-width: 360px; cursor:pointer;">';

                    // Thumbnail or placeholder
                    if (has_post_thumbnail()) {
                        $output .= '<div style="width: 100%; height: 16rem; position: relative; overflow: hidden; background-color: #e2e8f0;">
                            <img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="' . get_the_title() . '" style="position: absolute; top: 50%; left: 50%; width: 100%; height: auto; transform: translate(-50%, -50%); min-height: 100%;">
                        </div>';
                    } else {
                        $output .= '<div style="width: 100%; height: 16rem; position: relative; overflow: hidden; background-color: #f7fafc; border: 1px solid #cbd5e0; display: flex; align-items: center; justify-content: center;">
                            <span style="text-align: center; color: #4a5568;">Please add a featured image</span>
                        </div>';
                    }

                    // Post Content
                    $output .= '<div class="p-4 flex flex-col flex-grow">';
                    $output .= '<h2 class="post-title text-lg font-bold ' . esc_attr($this->atts['title_font_family']) . ' mb-2 line-clamp-2" title="' . get_the_title() . '" style="color:' . esc_attr($this->atts['title_font_color']) . ';">' . get_the_title() . '</h2>';

                    $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
                    $output .= '<div class="post-description ' . esc_attr($this->atts['description_font_family']) . ' mb-4" title="' . esc_html($excerpt) . '" style="color:' . esc_attr($this->atts['description_font_color']) . ';">' . esc_html($excerpt) . '</div>';
                    $output .= '</div>';

                    $output .= '<div class="p-4 mt-auto">
                        <div class="bg-blue-600 text-white text-xs font-semibold py-2 px-4 rounded hover:bg-green-600 inline-block">VIEW POST &rsaquo;</div>
                    </div>';

                    $output .= '<div class="border-t border-gray-300 mt-2 py-2 px-4 text-xs text-gray-600 flex justify-between items-center">';
                    $output .= '<span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A10.97 10.97 0 0112 15c2.733 0 5.254 1.04 7.121 2.804M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                        <span class="font-semibold text-gray-800">' . get_the_author() . '</span>
                    </span>';
                    $output .= '<span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-6 8h6m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-semibold text-gray-800">' . get_the_date() . '</span>
                    </span>';
                    $output .= '</div>';

                    $output .= '</div>'; // end post-item
                }

                wp_reset_postdata();
                $output .= '</div>'; // end post-grid

                // Pagination
                $pagination = paginate_links(array(
                    'total'      => $query->max_num_pages,
                    'current'    => max(1, $paged),
                    'mid_size'   => 2,
                    'prev_text'  => '&laquo;',
                    'next_text'  => '&raquo;',
                    'type'       => 'array',
                ));

                if ($pagination) {
                    $output .= '<div class="pagination mt-8 text-center">
                        <ul class="flex justify-center space-x-4 text-sm">';

                    foreach ($pagination as $page_link) {
                        preg_match('/page\/(\d+)/', $page_link, $matches);
                        $page_number = isset($matches[1]) ? $matches[1] : 1;

                        if (strpos($page_link, 'current') !== false) {
                            $output .= '<li>' . str_replace(
                                '<span',
                                '<span class="block px-3 py-2 border border-gray text-black rounded"',
                                $page_link
                            ) . '</li>';
                        } else {
                            $output .= '<li>' . str_replace(
                                '<a',
                                '<a class="block px-3 py-2 bg-gray-200 rounded hover:bg-blue-500 hover:text-white" data-page="' . esc_attr($page_number) . '" data-grid-id="' . esc_attr($grid_id) . '"',
                                $page_link
                            ) . '</li>';
                        }
                    }

                    $output .= '</ul></div>';
                }
            } else {
                $output .= '<p>No posts found.</p>';
            }

            $output .= '</div>'; // close post-palooza-grid

        } catch (Exception $e) {
            error_log($e->getMessage());
            $output = '<p>An error occurred: ' . esc_html($e->getMessage()) . '</p>';
        }

        wp_reset_postdata();
        return $output;
    }
}
