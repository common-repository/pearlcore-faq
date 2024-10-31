<div class="pc_faq_like_button">
    <span class="count"><?php echo $pc_post_like; ?> </span>
    <?php pc_faq_get_like_notice(); ?>
    <span class="pc_faq_like_icon <?php echo $pc_count_class; ?>" post_id="<?php echo $pc_id; ?>" 
          pc_type="<?php echo $pc_count_class; ?>">
        <i class="fa fa-thumbs-o-up"></i>
    </span>
</div>