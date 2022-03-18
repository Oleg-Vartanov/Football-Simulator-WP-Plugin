jQuery(document).ready(function($) {
    $(".start-tournament").click(function(e) {
        if ($("select option:selected").length != 4) {
            $(".team-select").val("");
            alert('4 Teams must me selected');
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
                location.reload();
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
                show_table(table_response);
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
                    show_table(table_response);
                });
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
                location.reload();
            }
        });
    });

    function show_table(table_response) {
        if (table_response.tournament_status == 'completed') {
            $('.next_week').hide();
            $('.play_all_games').hide();
        }
        $('.current_week').val(table_response.current_week);
        $('.tables-js').append(table_response.content);
    }
});