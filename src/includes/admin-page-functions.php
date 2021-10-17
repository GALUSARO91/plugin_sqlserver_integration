<?php


function remote_db_user_primary_key_update( $user_id ){
    // check that the current user have the capability to edit the $user_id
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
  
    // create/update user meta for the $user_id
    return update_user_meta(
        $user_id,
        'remote-db-user-primary-key',
        $_POST['remote-db-user-primary-key']
    );
}

function remote_db_plugin_register_settings(){

    register_setting('remote_db_plugin', 'ssh_host',['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'ssh_port',['type' => 'string','default' => '22']);
    register_setting('remote_db_plugin', 'ssh_user', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'ssh_local_port', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'ssh_remote_host', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'ssh_remote_port', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'ssh_connection_string', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'remote_db', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'db_username', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'db_password', ['type' => 'string','default' => '']);
    register_setting('remote_db_plugin', 'db_conecction_string', ['type' => 'string','default' => '']);

add_settings_section(
      'ssh_handler_section',
      'SSH Bridge info', 
      'ssh_info_section_cb', 
      'remote_db_plugin');

add_settings_section(
      'remote_db_connector_section',
      'Remote db connector', 
      'remote_db_connector_section_cb', 
      'remote_db_plugin');

add_settings_field(
      'ssh_host', //identificador del campo agregardo
      'Host para puente SSH', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'ssh_handler_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'ssh_host',
        'input_type' => 'text'

      ]);

add_settings_field(
        'ssh_port', //identificador del campo agregardo
        'Port para puente SSH', //nombre del campo que aparecera en el website
        'remotedbplugin_get_input_text_field', //funcion que se ejecutara
        'remote_db_plugin', //identificador de ajustes registrados en el plugin
        'ssh_handler_section', // identificador de la seccion a la que pertenece
        [
          'label_for' => 'ssh_port',
          'input_type' => 'text'
  
        ]);

add_settings_field(
      'ssh_user', //identificador del campo agregardo
      'Usuario para puente SSH', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'ssh_handler_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'ssh_user',
        'input_type' => 'text'

      ]);

add_settings_field(
      'ssh_local_port', //identificador del campo agregardo
      'Puerto local para redireccion', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'ssh_handler_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'ssh_local_port',
        'input_type' => 'text'

      ]);

add_settings_field(
        'ssh_remote_host', //identificador del campo agregardo
        'host remoto para redireccion', //nombre del campo que aparecera en el website
        'remotedbplugin_get_input_text_field', //funcion que se ejecutara
        'remote_db_plugin', //identificador de ajustes registrados en el plugin
        'ssh_handler_section', // identificador de la seccion a la que pertenece
        [
          'label_for' => 'ssh_remote_host',
          'input_type' => 'text'
  
        ]);

add_settings_field(
        'ssh_remote_port', //identificador del campo agregardo
        'Puerto remoto para redireccion', //nombre del campo que aparecera en el website
        'remotedbplugin_get_input_text_field', //funcion que se ejecutara
        'remote_db_plugin', //identificador de ajustes registrados en el plugin
        'ssh_handler_section', // identificador de la seccion a la que pertenece
        [
            'label_for' => 'ssh_remote_port',
            'input_type' => 'text'
    
        ]);


add_settings_field(
      'ssh_connection_string', //identificador del campo agregardo
      'Cadena de coneccion puente SSH', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'ssh_handler_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'ssh_connection_string',
        'input_type' => 'text'

      ]);

add_settings_field(
        'remote_db', //identificador del campo agregardo
        'Nombre de la base de datos remota', //nombre del campo que aparecera en el website
        'remotedbplugin_get_input_text_field', //funcion que se ejecutara
        'remote_db_plugin', //identificador de ajustes registrados en el plugin
        'remote_db_connector_section', // identificador de la seccion a la que pertenece
        [
          'label_for' => 'remote_db',
          'input_type' => 'text'
  
        ]);

add_settings_field(
      'db_username', //identificador del campo agregardo
      'Usuario para coneccion de base de datos', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'remote_db_connector_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'db_username',
        'input_type' => 'text'

      ]);

add_settings_field(
      'db_password', //identificador del campo agregardo
      'Contraseña para coneccion de base de datos', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'remote_db_connector_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'db_password',
        'input_type' => 'text'

      ]);

add_settings_field(
      'db_conecction_string', //identificador del campo agregardo
      'Cadena de coneccion a bases de datos', //nombre del campo que aparecera en el website
      'remotedbplugin_get_input_text_field', //funcion que se ejecutara
      'remote_db_plugin', //identificador de ajustes registrados en el plugin
      'remote_db_connector_section', // identificador de la seccion a la que pertenece
      [
        'label_for' => 'db_conecction_string',
        'input_type' => 'text'

      ]);

}

function remote_db_plugin_admin_page(){

add_menu_page(
    'Remote db plugin',// Page title
    'Remote db plugin',// Menu title
    'manage_options',//capabilities
    'remote_db_plugin',//menu slug
    'remote_db_plugin_options_page_html',//function to display info
    '',
    20
  );
}
?>