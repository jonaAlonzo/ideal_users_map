<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/ideal_users_map/db/cities.php');
require_once(__DIR__.'/../../../config.php');

function xmldb_block_ideal_users_map_install()
{
    global $DB, $cities;

    // Iniciar una transacción delegada
    $transaction = $DB->start_delegated_transaction();

    try {
        foreach ($cities as $cityGroup) {
            // Insertar el grupo de registros directamente
            $DB->insert_records('ideal_users_map', $cityGroup);
        }

        // Permitir el commit de la transacción después de ejecutar todas las consultas
        $transaction->allow_commit();
    } catch (Exception $e) {
        // En caso de error, hacer rollback de la transacción
        $transaction->rollback($e);

        // Registrar el error
        error_log("Error en la instalación del plugin ideal_users_map: " . $e->getMessage());

        // Relanzar la excepción
        throw $e;  
    }
}
