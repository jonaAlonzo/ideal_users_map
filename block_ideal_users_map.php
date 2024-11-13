<?php
    global $CFG;
    require_once(__DIR__ .'/classes/render_map.php');
    require_once(__DIR__.'/classes/consultas_db.php');


class block_ideal_users_map extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_ideal_users_map');
    }

    public function get_content() {
        global $DB,$PAGE,$CFG;
        require_once($CFG->dirroot.'/config.php');


        if ($this->content !== null) {
            return $this->content;
        }
        $PAGE->requires->js_call_amd('block_ideal_users_map/controller', 'init');

        ob_start();
        render_map_api();


        $rendered_content = ob_get_clean();
        
        $this->content = new stdClass();
        $this->content->text = $rendered_content;
        $this->content->footer = '';

        return $this->content;
    }
}
