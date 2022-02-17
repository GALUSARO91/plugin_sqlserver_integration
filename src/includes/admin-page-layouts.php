<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function remote_db_user_primary_key( $user ){
        ?>
        <h3>Informacion de usuario para el sistema principal</h3>
        <table class="form-table">
<!--             <tr>
                <th>
                    <label for="remote-db-user-primary-key">ID de usuario BD Remota</label>
                </th>
            </tr> -->
            <tr>    
                <td>
                <?php
                $value = !is_string($user)?esc_attr(get_user_meta($user->ID, 'remote-db-user-primary-key', true )):'';
                ?>
                    <label for="remote-db-user-primary-key">ID de usuario BD Remota</label>
                    <input type="number" class="regular-text ltr" id="remote-db-user-primary-key" name="remote-db-user-primary-key"<?php 
                    if($value != ''){
                      echo "value=$value";
                    }
                    ?> 
                    placeholder="Escribe el ID del usuario en base remota.">
<!--                     <p class="description">
                    Escribe el ID del usuario en base remota.
                    </p> -->
                </td>
            <td>
                <?php
                $value = !is_string($user)?esc_attr(get_user_meta($user->ID, 'NUM_RUC', true )):'';
                ?>
                     <label for="NUM_RUC">Numero RUC</label>
                    <input type="text" class="regular-text ltr" id="NUM_RUC" name="NUM_RUC" <?php 
                    if($value != ''){
                      echo "value=$value";
                    }
                    ?> 
                    placeholder="Escribe el numero RUC del cliente">
                  <!--   <p class="description">
                    Escribe el numero RUC del cliente.
                    </p> -->
                </td>

            </tr>
            <tr>
            <td>
                <?php
                $value = !is_string($user)?esc_attr(get_user_meta($user->ID, 'PLAZO', true )):'';
                ?>
                    <label for="PLAZO">Plazo</label>
                    <input type="number" class="regular-text ltr" id="PLAZO" name="PLAZO" <?php 
                    if($value != ''){
                      echo "value=$value";
                    }
                    ?> 
                    placeholder="Escribe el plazo de credito para el cliente">
                  <!--   <p class="description">
                    Escribe el numero RUC del cliente.
                    </p> -->
                </td>
                <td>
                <?php
                $value = !is_string($user)?esc_attr(get_user_meta($user->ID, 'PLAZO', true )):'';
                ?>
                    <label for="LIMITE">Limite de Credito</label>
                    <input type="number" class="regular-text ltr" id="limite" name="LIMITE" <?php 
                    if($value != ''){
                      echo "value=$value";
                    }
                    ?> 
                    placeholder="Escribe el limite de credito para el cliente">
                  <!--   <p class="description">
                    Escribe el numero RUC del cliente.
                    </p> -->
                </td>

            </tr>
            <?php               
                if(is_string($user)){  
                  echo '<tr><td><input type="checkbox" value=1 name="user-in-db" id="user-in-db"/>';
                  echo '<lable for="user-in-db"> Usuario ya en base de datos </lable> </td></tr>';
                }
                ?>
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