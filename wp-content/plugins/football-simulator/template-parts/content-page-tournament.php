<?php
/**
 * @var array<WP_Post> $all_teams All teams posts
 * @var string $thumb Background image
 */
?>

<script>
    <?php include WP_PLUGIN_DIR . '/football-simulator/assets/js/fs-tournament-page.js';?>
</script>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="entry-content">

        <?php the_content(); ?>

        <input type="hidden" class="current_week" value="<?php echo $post->tour_current_week ?>">

        <?php if ($post->tour_status == 'in_progress') { ?>
            <button class="btn btn-primary next_week">
                <?php echo __('Сгенерировать неделю', 'textdomain'); ?>
            </button>
            <button type="button" class="btn btn-primary play_all_games"><?php echo __('Сгенерировать весь турнир', 'textdomain'); ?></button>
        <?php } ?>
        <?php if ($post->tour_status != 'not_started') { ?>
            <button type="button" class="btn btn-primary new_tournament"><?php echo __('Начать новый турнир', 'textdomain'); ?></button>
        <?php } ?>
        <div class="tables-js">
            <?php if ($post->tour_status == 'not_started') {
                require __DIR__ . '/content-page-tournament-team-select.php';
            } elseif ($post->tour_current_week == 0) {
                require __DIR__ . '/content-page-tournament-table.php';
            } ?>
        </div>
    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<style>
    body {
        background-image: url('<?php echo $thumb;?>');
    }
</style>
