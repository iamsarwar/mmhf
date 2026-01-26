<?php
/*
Plugin Name: RFID
Plugin URI: http://mmhf.com.bd
Description: A rfid operations plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar/
License: GPL2
*/

add_action('admin_menu', 'addRFIDAdminPageContent');
function addRFIDAdminPageContent() {
    add_menu_page('RFID', 'RFID', 'manage_categories' ,__FILE__, 'rfidAdminPage', 'dashicons-wordpress');
}
function rfidAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    ?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <div class="wrap">
        <h2>RFID Operations</h2><br><br>
        <table class="wp-list-table widefat striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Mobile</th>
                <th>NID</th>
                <th>Vehicle Type</th>
                <th>Registration No.</th>
                <th>GEA Product Type ID</th>
                <th>GEA Product CODE</th>
                <th>GEA Customer ID</th>
                <th>GEA Subscription ID</th>
                <th>RFID Sticker No.</th>
                <th>Installation Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $wpdb->get_results("SELECT UI.id, UI.user_id, UI.nid, UI.vehicle_type, UI.vehicle_reg_no, U.user_login, U.display_name FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.rfid_sticker_no='' AND U.user_login <> ''");
            foreach ($result as $print) {
                echo "
              <tr>
                <td>$print->display_name</td>
                <td>$print->user_login<input type='hidden' id='mobile_$print->id' value='$print->user_login'></td>
                <td>$print->nid</td>
                <td>$print->vehicle_type</td>
                <td>$print->vehicle_reg_no</td>
                <td><input type='text' id='gea_product_type_id_$print->id'> </td>
                <td><input type='text' id='gea_product_id_$print->id'> </td>
                <td><input type='text' id='gea_customer_id_$print->id'> </td>
                <td><input type='text' id='gea_subscription_id_$print->id'> </td>
                <td><input type='text' id='rfid_$print->id'> </td>
                <td><input type='text' readonly id='install_$print->id'> </td>
                <td><button type='button' onclick=\"set_rfid('$print->id')\">UPDATE</button> | <button type='button' onclick=\"delete_rfid('$print->id')\">Delete</button></a></td>
              </tr>
                <script>
                    $( function() {
                        $( '#install_$print->id' ).datepicker({dateFormat: 'yy-mm-dd'});
                    } );
                </script>
            ";
            }
            ?>
            </tbody>
        </table>
        <br>
        <br>
    </div>
    <script>
	    function set_rfid(info_id) {
		    //alert(info_id);
		    var gea_product_type_id = document.getElementById('gea_product_type_id_'+info_id).value;
		    //alert(gea_product_type_id);
		    var gea_product_id = document.getElementById('gea_product_id_'+info_id).value;
		    //alert(gea_product_id);
		    var gea_customer_id = document.getElementById('gea_customer_id_'+info_id).value;
		    //alert(gea_customer_id);
		    var gea_subscription_id = document.getElementById('gea_subscription_id_'+info_id).value;
		    //alert(gea_subscription_id);
		    var rfid_sticker_no = document.getElementById('rfid_'+info_id).value;
		    //alert(rfid_sticker_no);
		    var install_date = document.getElementById('install_'+info_id).value;
		    //alert(install_date);
		    var mobile_no = document.getElementById('mobile_'+info_id).value;
		    //alert(mobile_no);
            if(gea_product_type_id == "")
                alert('Please enter GEA product type id.');
            else if(gea_product_id == '')
                alert('Please enter GEA product code.'); 
            else if(gea_customer_id == '')
                alert('Please enter GEA customer id.');
            else if(gea_subscription_id == '')
                alert('Please enter GEA subscription id.');
	    else if(rfid_sticker_no == '')
                alert('Please enter sticker no.');
            else if(install_date == '')
                alert('Please enter installation date');
            else {
                var crm = confirm("Are you sure?");
                if (crm == true) {
                    //alert(info_id);
                    //alert(rfid_sticker_no);
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'rfid_sticker',
                        info_id: info_id,
                        gea_product_type_id: gea_product_type_id,
                        gea_product_id: gea_product_id,
                        gea_customer_id: gea_customer_id,
                        gea_subscription_id: gea_subscription_id,
                        rfid_sticker_no: rfid_sticker_no,
                        install_date: install_date,
                        mobile_no: mobile_no
                    }, function (output) {
                        alert('Successfully updated.');
                        location.reload();
                    });
                }
            }
        }

        function delete_rfid(info_id){
            var crm = confirm("Are you sure to delete?");
            if (crm == true) {
                //alert(info_id);
                //alert(rfid_sticker_no);
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'rfid_delete',
                    info_id: info_id,
                }, function (output) {
                    alert('Successfully deleted.');
                    location.reload();
                });
            }
         }
    </script>
    <?php
}

add_action( 'wp_ajax_nopriv_rfid_delete', 'rfid_delete' );
add_action( 'wp_ajax_rfid_delete', 'rfid_delete' );
function rfid_delete(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    if(!empty($_POST['info_id']))
        $wpdb->delete( $table_name, array( 'id' => $_POST['info_id'] ) );

    exit;
}

