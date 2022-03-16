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

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="entry-content">
        <?php
        the_content();

        $teams = get_posts([
            'post_type'   => 'teams',
            'post_status' => 'publish'
        ]);
        ?>

        <script>
            jQuery(document).ready(function($) {
                $("#next_week").click(function(e) {
                    if ($("select option:selected").length != 4) {
                        $("#team-select").val("");
                        alert('4 Teams must me selected');
                        return false;
                    }

                    let data = {
                        action: 'fs_select_teams',
                        team_ids: $('#team-select').val()
                    };

                    jQuery.ajax({
                        url: '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            return false;
                            console.log( response );
                        }
                    });
                });
            });
        </script>

            <div class="container">
                <div class="row">
                    <label for="team-select">Select 4 teams:</label>
                </div>
                <div class="row">
                    <select name="teams" id="team-select" class="form-select" multiple>
                        <?php
                        foreach ($teams as $team) {
                            echo '<option value="'. $team->ID .'">' . $team->post_title . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="row">
                    <button id="next_week" class="btn btn-primary">Следующая неделя</button>
                    <button id="play_all_games" type="button" class="btn btn-primary">Проиграть все матчи</button>
                </div>
            </div>
    </div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<?php $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
<style>
    body {
        background-image: url('<?php echo $thumb['0'];?>');
    }
</style>
