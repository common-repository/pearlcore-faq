<?php

function pc_faq_get_like_notice() {
    wp_nonce_field("pc_faq_like","pc_faq_like");
}
