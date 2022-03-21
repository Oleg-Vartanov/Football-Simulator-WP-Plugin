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

        <?php
        the_content();
        ?>

        <input type="hidden" class="current_week" value="<?php echo $post->tour_current_week ?>">

        <!-- Team Selection Screen -->
        <?php if ($post->tour_status == 'not_started') { ?>
            <div class="container">
                <div class="row">
                    <label for="team-select"><?php echo __('Выбрать 4 команды', 'textdomain'); ?>:</label>
                </div>
                <div class="row">
                    <select name="teams" class="team-select form-select" multiple>
                        <?php foreach ($all_teams as $team) {
                            echo '<option value="'. $team->ID .'">' . $team->post_title . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="row">
                    <button class="btn btn-primary start-tournament"><?php echo __('Старт турнира', 'textdomain'); ?></button>
                </div>
            </div>

        <!-- Tournament Table Screen -->
        <?php } else { ?>
            <?php if ($post->tour_status != 'completed') { ?>
                <button class="btn btn-primary next_week">
                    <?php echo __('Симулировать неделю', 'textdomain'); ?>
                </button>
                <button type="button" class="btn btn-primary play_all_games"><?php echo __('Симулировать весь турнир', 'textdomain'); ?></button>
            <?php } ?>
            <button type="button" class="btn btn-primary new_tournament"><?php echo __('Начать новый турнир', 'textdomain'); ?></button>
            <div class="tables-js">
                <?php if ($post->tour_current_week == 0) {
                    require __DIR__ . '/content-page-tournament-table.php';
                } ?>
            </div>
        <?php } ?>


    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<style>
    body {
        background-image: url('<?php echo $thumb;?>');
    }
</style>
