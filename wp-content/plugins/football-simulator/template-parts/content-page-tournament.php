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
        $status = $post->tour_status;
        $current_week = $post->tour_current_week;
        ?>

        <input type="hidden" class="current_week" value="<?php echo $current_week ?>">

        <!-- Team Selection Screen -->
        <?php if ($status == 'not_started') { ?>
            <div class="container">
                <div class="row">
                    <label for="team-select">Select 4 teams:</label>
                </div>
                <div class="row">
                    <select name="teams" class="team-select form-select" multiple>
                        <?php
                        $all_teams = get_posts([
                            'post_type'   => 'teams',
                            'post_status' => 'publish'
                        ]);
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

        <!-- Tournament Table Screen -->
        <?php } else { ?>
            <?php if ($status != 'completed') { ?>
                <button class="btn btn-primary next_week">
                    <?php echo __('Симулировать неделю', 'textdomain'); ?>
                </button>
                <button type="button" class="btn btn-primary play_all_games"><?php echo __('Симулировать весь турнир', 'textdomain'); ?></button>
            <?php } ?>
            <button type="button" class="btn btn-primary new_tournament"><?php echo __('Начать новый турнир', 'textdomain'); ?></button>
            <div class="tables-js"></div>
        <?php } ?>


    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
<style>
    body {
        background-image: url('<?php echo $thumb['0'];?>');
    }
</style>
