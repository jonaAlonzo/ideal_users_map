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
        // Verificar si la tabla 'ideal_users_map' existe
        if ($DB->get_manager()->table_exists('ideal_users_map')) {
            // Actualizar registros existentes
            foreach ($cities as $cityGroup) {
            foreach ($cityGroup as $city) {
                // Verificar si el registro ya existe
                $existingRecord = $DB->get_record('ideal_users_map', array('id' => $city->id));
                if ($existingRecord) {
                // Actualizar el registro existente
                $DB->update_record('ideal_users_map', $city);
                } else {
                // Insertar nuevo registro
                $DB->insert_record('ideal_users_map', $city);
                }
            }
            }
        } else {
            // Insertar el grupo de registros directamente
            foreach ($cities as $cityGroup) {
            $DB->insert_records('ideal_users_map', $cityGroup);
            }
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
