<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
?>

<script>
    <?php include WP_PLUGIN_DIR . '/football-simulator/assets/js/fs-tournament-page.js';?>
</script>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="entry-content">
        <?php
        the_content();

        $all_teams = get_posts([
            'post_type'   => 'teams',
            'post_status' => 'publish'
        ]);

        $status = get_post_meta($post->ID, 'tour_status', true);
        $current_week = get_post_meta($post->ID, 'tour_current_week', true);
        ?>

        <input type="hidden" class="current_week" value="<?php echo $current_week ?>">

        <?php if ($status == 'not_started') { ?>
            <div class="container">
                <div class="row">
                    <label for="team-select">Select 4 teams:</label>
                </div>
                <div class="row">
                    <select name="teams" class="team-select form-select" multiple>
                        <?php
                        foreach ($all_teams as $team) {
                            echo '<option value="'. $team->ID .'">' . $team->post_title . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="row">
                    <button class="btn btn-primary start-tournament"><?php echo __('Старт турнира', 'textdomain'); ?></button>
                </div>
            </div>
        <?php } else { ?>
            <?php if ($status != 'completed') { ?>
                <button class="btn btn-primary next_week"><?php echo __('Следующая неделя', 'textdomain'); ?></button>
                <button type="button" class="btn btn-primary play_all_games"><?php echo __('Проиграть все матчи', 'textdomain'); ?></button>
            <?php } ?>
            <button type="button" class="btn btn-primary new_tournament"><?php echo __('Начать новый турнир', 'textdomain'); ?></button>
            <div class="tables-js">
                <?php include('content-page-tournament-table.php'); ?>
            </div>
        <?php } ?>


    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
<style>
    body {
        background-image: url('<?php echo $thumb['0'];?>');
    }
</style>
