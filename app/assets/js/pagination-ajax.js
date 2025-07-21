jQuery(function ($) {
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();

        const $link = $(this);
        const page = $link.data('page');
        const gridId = $link.data('grid-id');
        const $grid = $(`[data-grid-id="${gridId}"]`);

        if (!$grid.length || !page || !gridId) {
            console.error('Pagination error: Missing gridId or page.');
            return;
        }

        const layout = $grid.data('layout') || 'vertical'; // Read from data-layout attribute

        $.ajax({
            url: keenado_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'keenado_pagination',
                page: page,
                grid_id: gridId,
                layout: layout,
            },
            beforeSend: () => {
                $grid.addClass('loading');
            },
            success: (response) => {
                if (response.success) {
                    $grid.replaceWith(response.data);
                } else {
                    console.warn('Pagination response error:', response.data);
                    alert(response.data || 'An error occurred while loading posts.');
                }
            },
            complete: () => {
                $grid.removeClass('loading');
            },
        });
    });
});
