<?php
/*
Plugin Name: Installation
Plugin URI: http://mmhf.com.bd
Description: A installation operations plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar/
License: GPL2
*/

add_action('admin_menu', 'addInstallAdminPageContent');
function addInstallAdminPageContent() {
    add_menu_page('Installation', 'Installation', 'manage_categories' ,__FILE__, 'installationAdminPage', 'dashicons-wordpress');
}
function installationAdminPage() {
    ini_set('max_execution_time', 0);
    set_time_limit(0);

    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    ?>
    <div class="wrap">
        <h2>Installation Operations</h2><br><br>
        <table class="wp-list-table widefat striped">
            <thead>
            <tr>
                <th width="25%">Mobile</th>
                <th width="25%">Vehicle Type</th>
                <th width="25%">Registration No.</th>
                <th width="25%">Dependency</th>
                <th width="25%">Actions</th>
            </tr>
            </thead>
            <tbody>
            <!-- Script -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
            <script src='https://makitweb.com/demo/dropdown_select2/select2/js/select2.min.js' type='text/javascript'></script>

            <!-- CSS -->
            <link href='https://makitweb.com/demo/dropdown_select2/select2/css/select2.min.css' rel='stylesheet' type='text/css'>

            <?php
            $result = $wpdb->get_results("SELECT UI.id, UI.user_id, UI.vehicle_type, UI.vehicle_reg_no, U.user_login FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.rfid_sticker_no <> '' AND UI.installed = 0 AND U.user_login <> ''");
            $parent_result = $wpdb->get_results("SELECT UI.id, UI.user_id, U.user_login, UI.vehicle_reg_no FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.parent_id IS NULL AND LENGTH(U.user_login) = 11 AND U.user_login <> '' AND UI.vehicle_reg_no <> '' GROUP BY U.user_login ORDER BY UI.id DESC");

	    foreach ($result as $print) {
                echo "
              <tr>
                <td width='25%'>$print->user_login</td>
                <td width='25%'>$print->vehicle_type</td>
                <td width='25%'><input type='text' id='vehicle_reg_no_$print->id' value='$print->vehicle_reg_no'></td>
                <td width='25%'>
                    <input type='checkbox' id='dependant_$print->id' onclick=\"showDepandant('$print->id')\"> Dependant?<br><br>
                    <p style='display: none;' id='parent_div_id_$print->id'>
                    <select id='parent_id_$print->id' style='width:60%'>
                        <option value=''>Select</option>
                 ";
               foreach($parent_result as $thisResult){
                    echo "<option value='".$thisResult->user_id."_".$thisResult->id."'>$thisResult->user_login</option>";
               }
                echo "
                    </select>
                    </p>
                </td>
                <td width='25%'><button type='button' onclick=\"set_install('$print->id')\">UPDATE</button></a></td>
              </tr>
            ";
        ?>
             <script>
                $(document).ready(function(){
                    // Initialize select2
                    $("#parent_id_<?php echo $print->id;?>").select2();

                });
             </script>
        <?php
            }
            ?>
            </tbody>
        </table>
        <br>
        <br>
    </div>
    <script>
        function showDepandant(info_id){
            var checkboxid = "dependant_"+info_id;
            var parent_combo = "parent_div_id_"+info_id;
            var is_checked = document.getElementById(checkboxid).checked;
            if(is_checked)
                document.getElementById(parent_combo).style.display = 'block';
            else
                document.getElementById(parent_combo).style.display = 'none';
        }

        function set_install(info_id) {
            var vehicle_reg_no = document.getElementById('vehicle_reg_no_'+info_id).value;
            var parent = jQuery('#parent_id_'+info_id).val();
            if(vehicle_reg_no == '')
                alert('Please enter vehicle registration no');
            else {
                var crm = confirm("Are you sure?");
                if (crm == true) {
                    //alert(info_id);
                    //alert(rfid_sticker_no);
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'rfid_installation',
                        info_id: info_id,
                        vehicle_reg_no: vehicle_reg_no,
                        parent: parent
                    }, function (output) {
                        alert('Successfully updated.');
                        location.reload();
                    });
                }
            }
        }
    </script>
    <?php
}

