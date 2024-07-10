jQuery(document).ready(function($){

    $('body').on('click', '#wc-rw-wooms-sync-button', function (e){

        e.preventDefault();
        startSpinner();
        //console.log('Clicked!');

        $.ajax({
            type: "POST",
            url: wc_rw_wooms_sync_ajax_obj.ajax_url,
            data: {
                action: "synchronise_order_action",
                order_id: getOrderIdFromUrl(),
                security: wc_rw_wooms_sync_ajax_obj.security
            },
            dataType: "json",
            encode: true
        })

            .done((response) => {
                console.log(response); //debugging
                stopSpinner();
                $('#sync_date').text(response.data.values.moy_sklad_sync_date);
                $('#sync_status').text(response.data.values.moy_sklad_sync_status);
                if(response.data.values.moy_sklad_sync_status === 'OK') {
                    $('#sync_button').remove();
                }
            })

            .fail(() =>{
                console.log('Server connection error! Please try again later!');
                alert('Server connection error! Please try again later!');
                stopSpinner();
            })
        ;

    });

});





//loader spinner start
function startSpinner(){
    jQuery('#wc-rw-opacity').addClass('opacity');
    jQuery('#wc-rw-spinner').addClass('is-active');
}

//loader spinner stop
function stopSpinner(){
    jQuery('#wc-rw-opacity').removeClass('opacity');
    jQuery('#wc-rw-spinner').removeClass('is-active');
}

// Get current order Id
function getOrderIdFromUrl(){

    let queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get('post');

}