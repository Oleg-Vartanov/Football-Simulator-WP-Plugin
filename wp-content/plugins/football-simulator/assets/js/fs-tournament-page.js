jQuery(document).ready(function($) {
    toggle_buttons($('.current_status').val());

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: { action: 'fs_show_tables' },
        success: function (response) {
            if ($('.current_week').val() != 0) {
                $.each(response, function (i, table_response) {
                    add_content_to_tables(table_response);
                });
            }
        }
    });

    $(document).on('change', '.goals', function() {
        if ($(this).val() < 0) {
            alert('Отрицательное число голов!');
            return false;
        }

        let data = {
            action: 'fs_edit_score',
            goals: $(this).val(),
            nonce: $(this).data("nonce"),
            team: $(this).data("team"),
            match_id: $(this).data("match-id")
        };

        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            success: function (response) {
                $('.tables-js').empty();
                $.each(response, function(i, table_response) {
                    add_content_to_tables(table_response);
                });
            }
        });
    });

    $(document).on('click', '.start-tournament', function() {
        if ($("select option:selected").length != $(".start_calc_week").val()) {
            $(".team-select").val("");
            alert('Необходимо выбрать ' + $(".start_calc_week").val() + ' команды');
            return false;
        }

        let data = {
            action: 'fs_start_tournament',
            team_ids: $('.team-select').val()
        };

        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            success: function (response) {
                $('.tables-js').empty();
                add_content_to_tables(response);
            }
        });
    });

    $(".new_tournament").click(function(e) {
        let data = {
            action: 'fs_reset_tournament'
        };

        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            success: function (response) {
                $('.tables-js').empty();
                add_content_to_tables(response);
            }
        });
    });

    $(".next_week").click(function(e) {
        let data = {
            action: 'fs_start_week',
            current_week: $('.current_week').val()
        };

        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            success: function (table_response) {
                add_content_to_tables(table_response);
            }
        });
    });

    $(".play_all_games").click(function(e) {
        let data = {
            action: 'fs_play_all_games',
            current_week: $('.current_week').val()
        };

        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            success: function (response) {
                $.each(response, function(i, table_response) {
                    add_content_to_tables(table_response);
                });
            }
        });
    });

    function add_content_to_tables(response) {
        toggle_buttons(response.tournament_status);
        $('.current_week').val(response.current_week);
        $('.tables-js').append(response.content);
    }

    function toggle_buttons(tournament_status) {
        if (tournament_status != 'in_progress') {
            $('.next_week').hide();
            $('.play_all_games').hide();
        } else {
            $('.next_week').show();
            $('.play_all_games').show();
            $('.new_tournament').show();
        }
        if (tournament_status == 'not_started') {
            $('.new_tournament').hide();
        }
    }
});