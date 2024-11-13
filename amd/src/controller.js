define(['jquery'], function($) {
    return {
        init: function() {
            try {
                $.getScript("../blocks/ideal_users_map/amd/src/map.js")
                    .done(function(script, textStatus) {
                        if (typeof render_map === 'function') {
                            render_map();
                        } else {
                            console.error('La función render_map no está definida');
                        }
                    })
                    .fail(function(jqxhr, settings, exception) {
                        console.error('Error al cargar map.js: ', exception);
                    });
            } catch (error) {
                console.error('Error inesperado al intentar cargar map.js: ', error);
            }
        }
    };
});
