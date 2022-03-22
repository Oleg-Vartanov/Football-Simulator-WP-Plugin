jQuery(document).ready(function($) {
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
        if ($("select option:selected").length != 4) {
            $(".team-select").val("");
            alert('Необходимо выбрать 4 команды');
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
        if (response.tournament_status == 'completed' || response.tournament_status == 'not_started') {
            $('.next_week').hide();
            $('.play_all_games').hide();
        } else {
            $('.next_week').show();
            $('.play_all_games').show();
        }
        $('.current_week').val(response.current_week);
        $('.tables-js').append(response.content);
    }
});