<?php
    $current_teams = [];
    $current_week_matches = get_posts([
        'numberposts'   => -1,
        'post_type'     => 'matches',
        'meta_key'      => 'match_week',
        'meta_value'    => $current_week
    ]);
    foreach ($current_week_matches as $current_week_match) {
        $current_teams[] = get_post($current_week_match->match_home_team);
        $current_teams[] = get_post($current_week_match->match_away_team);
    }
    $teams_info = fs_get_teams_info($current_teams, $current_week);
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
            <?php if ($i == 0) { ?>
            <td class="matches-row" rowspan="<?php echo count($current_teams); ?>">
                <?php foreach ($current_week_matches as $match) {
                    $home_team = get_post($match->match_home_team);
                    $away_team = get_post($match->match_away_team);
                    ?>
                    <p>
                    <?php echo $home_team->post_title; ?>
                        <input class="goals"
                               data-team="home"
                               data-match-id="<?php echo $match->ID; ?>"
                               value="<?php echo $match->match_home_team_goals; ?>">
                        <span> - </span>
                        <input class="goals"
                               data-team="away"
                               data-match-id="<?php echo $match->ID; ?>"
                               value="<?php echo $match->match_away_team_goals; ?>">
                         <?php echo $away_team->post_title; ?>
                    </p>
                <?php } ?>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
</table>