<div class="pc_faq_author">
    <?php if ($pc_date_show == 'yes'): ?>
        <span class="pc_faq_date"><?php echo $pc_date; ?> </span>
    <?php endif; ?>
    <?php if ($pc_author_show == 'yes'): ?>
        <span class="pc_faq_author">, BY  <span class="pc_faq_author_name"><?php echo $pc_author; ?></span></span>
        <?php endif; ?>
</div>