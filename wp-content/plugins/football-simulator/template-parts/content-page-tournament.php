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
        $current_week_matches = get_posts([
            'numberposts'   => -1,
            'post_type'     => 'matches',
            'meta_key'      => 'match_week',
            'meta_value'    => $current_week
        ]);
        $current_teams = [];
        foreach ($current_week_matches as $current_week_match) {
            $current_teams[] = get_post($current_week_match->match_home_team);
            $current_teams[] = get_post($current_week_match->match_away_team);
        }
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
            <?php if ($current_week < 4) { ?>
                <button class="btn btn-primary next_week"><?php echo __('Следующая неделя', 'textdomain'); ?></button>
                <button type="button" class="btn btn-primary play_all_games"><?php echo __('Проиграть все матчи', 'textdomain'); ?></button>
            <?php } else { ?>
                <button type="button" class="btn btn-primary new_tournament"><?php echo __('Начать новый турнир', 'textdomain'); ?></button>
            <?php } ?>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col"><?php echo __('Команда', 'textdomain'); ?></th>
                    <th scope="col">PTS</th>
                    <th scope="col">P</th>
                    <th scope="col">W</th>
                    <th scope="col">D</th>
                    <th scope="col">L</th>
                    <th scope="col">GD</th>
                    <th scope="col"><?php echo __('Результаты матчей', 'textdomain'); ?></th>
                </tr>
                </thead>

                <tbody>
                <?php
                $teams_info = fs_get_teams_info($current_teams, $current_week);
                foreach ($teams_info as $i => $team_info) {
                ?>
                <tr>
                    <th><?php echo $team_info['post']->post_title;?></th>
                    <td><?php echo $team_info['pts'] ?></td>
                    <td><?php echo $current_week ?></td>
                    <td><?php echo $team_info['w'] ?></td>
                    <td><?php echo $team_info['d'] ?></td>
                    <td><?php echo $team_info['l'] ?></td>
                    <td><?php echo $team_info['goad_diff'] ?></td>
                    <?php if ($i == 0) { ?>
                    <td rowspan="<?php echo count($current_teams); ?>">
                        <?php foreach ($current_week_matches as $match) {
                            $home_team = get_post($match->match_home_team);
                            $away_team = get_post($match->match_away_team);

                            echo $home_team->post_title . ' ' .  $match->match_home_team_goals . ' - ' .
                                $match->match_away_team_goals . ' ' . $away_team->post_title . '<br>';
                        } ?>
                    </td>
                    <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>


    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
<style>
    body {
        background-image: url('<?php echo $thumb['0'];?>');
    }
</style>
