<?php
namespace block_ideal_users_map\output;

defined('MOODLE_INTERNAL') || die();
use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    public function render_map($data) {
        global $PAGE;
        $PAGE->requires->css('/templates/css/styles.css');
        return $this->render_from_template('block_ideal_users_map/map', $data);
    }
}
