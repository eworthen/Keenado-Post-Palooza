<?php

namespace app\models;

use \WP_Query;
use \Exception;

class KeenadoPostGrid {
    private $atts;

    public function __construct($atts = []) {
        $this->atts = shortcode_atts(
            array(
                'title_font_family'      => 'font-arial',
                'title_font_color'       => '#000000',
                'description_font_family'=> 'font-arial',
                'description_font_color' => '#454545',
                'bg_color'               => '#ffffff',
                'posts_per_page'         => '3',
                'category'               => '',
            ),
            $atts
        );
    }

    public function render() {
        try {
            $output = '';
            // Arguments for WP_Query
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $args = array(
                'post_type' => 'post', // Default to 'post' if no post_type is passed in $atts
                'posts_per_page' => intval($this->atts['posts_per_page']),
                'paged' => $paged, // Ensure pagination works
            );
        
            // If a category is provided, filter posts by category
            if (!empty($atts['category'])) {
                $args['category_name'] = sanitize_title($this->atts['category']);
            }
        
            // The Query
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                // Grid container with responsive columns and max-width for cards
                $output .= '<div class="post-grid grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">';
        
                while ($query->have_posts()) {
                    $query->the_post();
        
                    // Post item with max-width of 360px and responsive behavior
                    $output .= '<div onclick="window.location.href=\'' . esc_url(get_the_permalink()) . '\'" class="post-item block border border-gray-200 shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 h-full flex flex-col" style="background-color:' . esc_attr($this->atts['bg_color']) . '; max-width: 360px; cursor:pointer;">';

        
                    // Post Thumbnail
                    if (has_post_thumbnail()) {
                        $output .= '<div style="width: 100%; height: 16rem; position: relative; overflow: hidden; background-color: #e2e8f0;">
                                        <img src="' . get_the_post_thumbnail_url(null, 'full') . '" alt="' . get_the_title() . '" style="position: absolute; top: 50%; left: 50%; width: 100%; height: auto; transform: translate(-50%, -50%); min-height: 100%;">
                                    </div>';
                    } else {
                        // Placeholder for posts without thumbnails
                        $output .= '<div style="width: 100%; height: 16rem; position: relative; overflow: hidden; background-color: #f7fafc; border: 1px solid #cbd5e0; display: flex; align-items: center; justify-content: center;">
                                        <span style="text-align: center; color: #4a5568;">Please add a featured image</span>
                                    </div>';
                    }
        
                    // Post Content
                    $output .= '<div class="p-4 flex flex-col flex-grow">';
        
                    // Post Title
                    $output .= '<h2 class="post-title text-lg font-bold ' . esc_attr($this->atts['title_font_family']) . ' mb-2 line-clamp-2" title="' . get_the_title() . '" style="color:' . esc_attr($this->atts['title_font_color']) . ';">' . get_the_title() . '</h2>';
        
                    // Post Excerpt
                    $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
                    $output .= '<div class="post-description ' . esc_attr($this->atts['description_font_family']) . ' mb-4" title="' . esc_html($excerpt) . '" style="color:' . esc_attr($this->atts['description_font_color']) . ';">' . esc_html($excerpt) . '</div>';
        
                    $output .= '</div>'; // End of post content
        
                    // Read More Button
                    $output .= '<div class="p-4 mt-auto">
                                    <div class="bg-blue-600 text-white text-xs font-semibold py-2 px-4 rounded hover:bg-green-600 inline-block">VIEW POST &rsaquo;</div>
                                </div>';
        
                    // Post Author and Date with separator and icons
                    $output .= '<div class="border-t border-gray-300 mt-2 py-2 px-4 text-xs text-gray-600 flex justify-between items-center">';
        
                    // Author with icon
                    $output .= '<span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A10.97 10.97 0 0112 15c2.733 0 5.254 1.04 7.121 2.804M12 12a4 4 0 100-8 4 4 0 000 8z" />
                                    </svg>
                                    <span class="font-semibold text-gray-800">' . get_the_author() . '</span>
                                </span>';
        
                    // Date with icon
                    $output .= '<span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-6 8h6m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-semibold text-gray-800">' . get_the_date() . '</span>
                                </span>';
        
                    $output .= '</div>';
        
                    $output .= '</div>'; // End of post-item
                }

                // Reset Post Data (Important)
                wp_reset_postdata();
        
                $output .= '</div>'; // End of post-grid
        
                // Pagination below the post grid
                $pagination = paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'mid_size' => 2,
                    'prev_text' => '&laquo;',  // Left double chevron
                    'next_text' => '&raquo;',  // Right double chevron
                    'type' => 'array',  // Output pagination as an array for customization
                ));
        
                // Display pagination with styling using TailwindCSS
                if ($pagination) {
                    $output .= '<div class="pagination mt-8 text-center">
                                <ul class="flex justify-center space-x-4 text-sm">';

                    foreach ($pagination as $page_link) {
                        // Extract the page number from the link or default to 1
                        preg_match('/page\/(\d+)/', $page_link, $matches); // Adjusted for common permalink structure
                        $page_number = isset($matches[1]) ? $matches[1] : 1;
                
                        // Check if the link is the current page (non-clickable)
                        if (strpos($page_link, 'current') !== false) {
                            // Apply styling to the current page (non-clickable, centered, outlined)
                            $output .= '<li>'
                                        . str_replace(
                                            '<span', 
                                            '<span class="block px-3 py-2 border border-gray text-black rounded"', 
                                            $page_link
                                        )
                                    . '</li>';
                        } else {
                            // Append TailwindCSS classes to the clickable links
                            $output .= '<li>'
                                        . str_replace(
                                            '<a',
                                            '<a class="block px-3 py-2 bg-gray-200 rounded hover:bg-blue-500 hover:text-white" data-page="' . esc_attr($page_number) . '"',
                                            $page_link
                                        )
                                    . '</li>';
                        }
                    }
        
                    $output .= '</ul>';
                    $output .= '</div>';
                }
        
            } else {
                // If no posts are found, display a message
                $output = '<p>No posts found.</p>';
            }
        
        } catch (Exception $e) {
            // Capture and display any error that occurs
            error_log($e->getMessage());
            $output = '<p>An error occurred: ' . esc_html($e->getMessage()) . '</p>';
        }

        wp_reset_postdata();
        return $output;
    }
}

?>