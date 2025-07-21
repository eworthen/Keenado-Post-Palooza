<?php

namespace app\models;

use \WP_Query;
use \Exception;

class KeenadoHorizontalPostGrid {
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

        $query = new \WP_Query($args);

        $output .= "<div class='post-palooza-grid horizontal flex flex-col gap-y-8' data-grid-id='" . esc_attr($grid_id) . "' data-layout='horizontal'>";

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $output .= '<div onclick="window.location.href=\'' . esc_url(get_the_permalink()) . '\'" class="post-item flex flex-col md:flex-row justify-between items-stretch border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300 rounded overflow-hidden mb-4" style="background-color:' . esc_attr($this->atts['bg_color']) . '; cursor:pointer;">';

                // Image
                if (has_post_thumbnail()) {
                    $output .= '<div class="w-full md:w-1/3 h-64 relative overflow-hidden bg-gray-200 order-1 md:order-2">
                        <img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="' . get_the_title() . '" class="absolute inset-0 w-full h-full object-cover object-center">
                    </div>';
                } else {
                    $output .= '<div class="w-full md:w-1/3 h-64 flex items-center justify-center bg-gray-200 order-1 md:order-2">
                        <span class="text-gray-500">No Image Available</span>
                    </div>';
                }

                // Text content
                $output .= '<div class="p-4 flex flex-col justify-start w-full md:w-2/3 order-2 md:order-1">';

                // Category
                $category = get_the_category();
                if (!empty($category) && $category[0]->name !== 'Uncategorized') {
                    $output .= '<h3 class="post-category font-bold text-blue-600 mb-1 pl-2">' . esc_html($category[0]->name) . '</h3>';
                }

                // Title
                $output .= '<h2 class="post-title text-2xl font-bold ' . esc_attr($this->atts['title_font_family']) . ' pl-2" style="color:' . esc_attr($this->atts['title_font_color']) . ';">' . esc_html(wp_trim_words(get_the_title(), 14, '...')) . '</h2>';

                // Meta
                $output .= '<div class="post-meta flex justify-start items-center text-gray-600 text-sm gap-4 mt-2 pl-2">';
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

                // Description
                $output .= '<div class="mt-4 post-description text-base ' . esc_attr($this->atts['description_font_family']) . ' pl-2" style="color:' . esc_attr($this->atts['description_font_color']) . ';">' . esc_html(wp_trim_words(get_the_excerpt(), 20, '...')) . '</div>';

                // Button
                $output .= '<div class="p-2">
                    <div class="bg-blue-600 text-white text-xs font-semibold py-1 px-2 hover:bg-green-600 transition-colors duration-300 inline-block">VIEW POST &raquo;</div>
                </div>';

                $output .= '</div>'; // end text container
                $output .= '</div>'; // end post-item
            }

            wp_reset_postdata();

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
                $output .= '<div class="pagination mt-4 text-center">
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

        $output .= '</div>'; // close .post-palooza-grid

        return $output;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return '<p>An error occurred: ' . esc_html($e->getMessage()) . '</p>';
        }
    }
}
