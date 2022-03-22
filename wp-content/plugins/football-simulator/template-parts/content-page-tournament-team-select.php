<?php
/**
 * @var array<WP_Post> $all_teams List of all teams
 */
?>
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