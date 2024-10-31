(function ($, window, document, undefined) {
    'use strict';
    var faq_p_id, nonce, faq_action_type, faq_url, reject_reason;

    /**
     * Approve/ Reject Product
     */
    $.fn.faq_product_action = function () {

        var faq_data = {faq_post_id: faq_p_id, nonce: nonce, faq_action_type: faq_action_type, faq_url: faq_url, reject_reason: reject_reason};
        $.post(faq_ajax_url, {
            action: "faq_product_approve",
            type: "post",
            data: faq_data
        }).done(function (response) {
            if (response.indexOf('<') > -1) {
                location.reload();
            } else {
                var obj = jQuery.parseJSON(response);
                if (obj.status === 'success') {
                    window.location = obj.redirect;
                } else {
                    alert(obj.message);
                    return false;
                }
            }

        }).fail(function (error) {

        });

    };

//    $(document).on('click', 'a.faq_action_link', function (e) {
//        e.preventDefault();
//        reject_reason = '';
//        faq_p_id = $(this).attr('data-id');
//        nonce = $(this).attr('data-nonce');
//        faq_action_type = $(this).attr('action-type');
//        faq_url = $('#post-' + faq_p_id + ' .has-row-actions a').attr('href');
//        if (faq_action_type === 'reject') {
//            
//            
//        } else {
//            $(this).replaceWith($('<div />', {
//                'class': 'spinner is-active'
//            }));
//            $(document).faq_product_action();
//        }
//
//    });

//    $(document).on('click', '.faq_reason_submit_button', function (e) {
//        e.preventDefault();
//        reject_reason = $('.faq_reject_reason_wrapper textarea').val();
//        $(this).replaceWith($('<div />', {
//            'class': 'spinner is-active'
//        }));
//        $(document).faq_product_action();
//    });

    /**
     * On Click View Detail Button
     */
    $(document).on('click', 'a.pc_faq_approve_link', function (e) {
        e.preventDefault();
        var faq_p_id = $(this).attr('data-id');
        if ($('.faq_network_overlay').length) {
            $('.faq_network_overlay').show();
            $('.faq_network_overlay').addClass('faq_popup_close_button');
        } else {
            $('body').append('<div class="faq_network_overlay faq_popup_close_button"></div>');
        }
        $('#faq_detail_popup_' + faq_p_id).show();
        $('#faq_detail_popup_' + faq_p_id).faq_center_position(0, 0);
    });

    /**
     * On Click Popup Close Button
     */
    $(document).on('click', '.faq_popup_close_button', function (e) {
        e.preventDefault();
        if ($('.faq_network_overlay').length) {
            $('.faq_network_overlay').show();
        } else {
            $('body').append('<div class="faq_network_overlay"></div>');
        }
        $('.faq_network_overlay').hide();
        $('.faq_fields_popup').hide();
    });

    $(document).on('click', '.faq_reason_popup_close_button', function (e) {
        e.preventDefault();

        $('.faq_network_overlay').hide();
        $('.faq_reject_reason_wrapper').hide();
        $('.faq_fields_popup').hide();
    });


    /**
     * Popup Center Postion
     * 
     * @param {type} faq_top_value
     * @param {type} faq_left_value
     * @returns {book-store-admin_L1.$.fn}
     */
    $.fn.faq_center_position = function (faq_top_value, faq_left_value) {
        this.css("position", "fixed");
        this.css("top", ($(window).height() / 2) - (this.outerHeight() / 2) + faq_top_value);
        this.css("left", ($(window).width() / 2) - (this.outerWidth() / 2) + faq_left_value);
        return this;
    };


    
})(jQuery, window, document);
