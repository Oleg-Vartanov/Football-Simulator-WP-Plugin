<?php
/**
 * @var array $teams_info Team's info in a current's week table
 * @var int $current_week
 * @var array<WP_Post> $current_week_matches
 */
?>
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
        <?php foreach ($teams_info as $i => $team_info) { ?>
        <tr>
            <th><?php echo $team_info['post']->post_title;?></th>
            <td><?php echo $team_info['pts'] ?></td>
            <td><?php echo $current_week ?></td>
            <td><?php echo $team_info['w'] ?></td>
            <td><?php echo $team_info['d'] ?></td>
            <td><?php echo $team_info['l'] ?></td>
            <td><?php echo $team_info['goad_diff'] ?></td>
            <?php if ($current_week != 0 && $i == 0) { ?>
            <td class="matches-row" rowspan="<?php echo count($teams_info); ?>">
                <?php foreach ($current_week_matches as $match) { ?>
                    <p>
                    <?php echo get_the_title($match->match_home_team); ?>
                        <input class="goals"
                               data-team="home"
                               data-match-id="<?php echo $match->ID; ?>"
                               value="<?php echo $match->match_home_team_goals; ?>">
                        <span> - </span>
                        <input class="goals"
                               data-team="away"
                               data-match-id="<?php echo $match->ID; ?>"
                               value="<?php echo $match->match_away_team_goals; ?>">
                        <?php echo get_the_title($match->match_away_team); ?>
                    </p>
                <?php } ?>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
</table>