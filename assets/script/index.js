import jquery from 'jquery'

const $ = jquery;
window.$ = window.jQuery = $;


if ($('.js-search-street').length > 0) {
    $('.js-search-street').on('keyup', function () {
        if ($(this).val().length > 3) {
            let route = $(this).data('route')

            let currentRequest = null;
            currentRequest = $.ajax({
                url: route,
                data: {
                    address: $(this).val()
                },
                beforeSend: function () {
                    if (currentRequest != null) {
                        currentRequest.abort();
                    }
                },
                success: function (data) {
                    if (data.success) {
                        $('#search-street').html(data.htmlContent)
                    }
                }
            });
        }
    })
}

$('body').on('click', '.js-set-address', function () {
    let data = $(this).data();
    // REMPLIR LES CHAMPS AVEC LES DATA CORRESPONDANTES
    $('.js-search-street').val(data.street);
    $('.js-search-city').val(data.city);
    $('.js-search-postcode').val(data.zipcode);
    $('.js-search-latitude').val(data.latitude);
    $('.js-search-longitude').val(data.longitude);

    //CLEAR LA LISTE DES ADDRESS (#search-street)
    $('#search-street').empty();

})


