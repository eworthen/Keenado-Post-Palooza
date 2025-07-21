jQuery(document).ready(function ($) {
    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        console.log(keenado_ajax);

        $.ajax({
            url: keenado_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'keenado_pagination',
                page: page,
            },
            beforeSend: function () {
                $('.post-grid').addClass('loading'); // Optional: Add a loading indicator
            },
            success: function (response) {
                if (response.success) {
                    $('.post-grid').html(response.data); // Replace grid content
                } else {
                    alert(response.data); // Display error
                }
            },
            complete: function () {
                $('.post-grid').removeClass('loading'); // Remove loading indicator
            },
        });
    });
});
