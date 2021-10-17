<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function remote_db_user_primary_key( $user ){
        ?>
        <h3>ID de usuario BD Remota</h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="remote-db-user-primary-key">ID de usuario BD Remota</label>
                </th>
                <td>
                    <input type="number"
                           class="regular-text ltr"
                           id="remote-db-user-primary-key"
                           name="remote-db-user-primary-key"
                           value="<?php esc_attr(get_user_meta($user->ID, 'remote-db-user-primary-key', true )); ?>"
                           <!-- FIXME: need error handler function -->
                           title="Escribe el ID del usuario en base remota.">
                    <p class="description">
                    Escribe el ID del usuario en base remota.
                    </p>
                </td>
            </tr>
        </table>
        <?php
}
function remotedbplugin_get_input_text_field ($args){
    ?>
      <?php
        $option = get_option($args['label_for']);
       ?>
    <input type= <?php
    echo esc_attr($args['input_type']);
    ?> name= <?php
    echo esc_attr($args['label_for']);
    ?> value=
    <?php echo isset($option)? $option : '';
    ?> >
    <?php
  }
function ssh_info_section_cb (){
  echo '<p><i>
      Please fill up fields below with required info.
   </i></p>';
}

function remote_db_connector_section_cb (){
  echo '<p><i>
      If you are able to install the dependences on you own, you can leave this unchecked.
   </i></p>';
}
// FIXME: Change this message
?>
<?php
function remote_db_plugin_options_page_html() {
    ?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1> 
      
      <form action="options.php" method="post">
        <?php

        settings_fields('remote_db_plugin');

        do_settings_sections('remote_db_plugin');

        submit_button('Save Settings');
        ?>
      </form>
    </div>
    <?php
}
?>