<?php
/*
Plugin Name: RFID-USER
Plugin URI: http://mmhf.com.bd
Description: A rfid operations plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar/
License: GPL2
*/

add_action('admin_menu', 'addRFIDUSERAdminPageContent');
function addRFIDUSERAdminPageContent() {
    add_menu_page('RFID USER', 'RFID USER', 'manage_categories' ,__FILE__, 'rfidUserAdminPage', 'dashicons-wordpress');
    add_submenu_page( __FILE__, 'RFID USER UPDATE', 'RFID USER UPDATE', 'manage_categories', 'rfid-user-edit', 'rfidUserEditAdminPage');
}
function rfidUserAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    $user_trip_table_name = $wpdb->prefix . 'users_trips';
    ?>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
    <!-- scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            var dataTable = $('#rfiduser-grid').DataTable({
                "responsive": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "<?php echo get_bloginfo('url');?>/wp-admin/admin-ajax.php", // json datasource
                    data: {action: 'getRfidUser'},
                    type: 'post',  // method  , by default get
                },
                error: function () {  // error handling
                    $(".rfiduser-grid-error").html("");
                    $("#rfiduser-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#rfiduser-grid_processing").css("display", "none");

                }

            });
        });

        function get_updated_trip(id, user_id, gea_subscription_id, gea_customer_id){
            //alert(id+'||'+gea_subscription_id+'||'+gea_customer_id);
            $.post("<?php echo admin_url('admin-ajax.php');?>", { action: 'get_updated_trip', user_id: user_id, gea_subscription_id: gea_subscription_id, gea_customer_id: gea_customer_id }, function(output) {
                //location.reload();
                //alert(output);
                output = output.substring(0, output.length - 1);
                var result = output.split('|');
                //alert(result[1]);
                //alert($("#last_updated_"+id).html());
                $('#last_updated_'+id).html(result[0]);
                $('#trip_info_'+id).html(result[1]);
            });
        }
    </script>
    
    <div class="wrap">
        <h2>RFID Installed User List</h2><br><br>
        <table id="rfiduser-grid" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Mobile</th>
                    <th>Vehicle Type</th>
                    <th>Registration No.</th>
                    <th width="25%">GEA Product Type ID</th>
                    <th width="25%">GEA Product Code</th>
                    <th width="25%">GEA Customer ID</th>
                    <th width="25%">GEA Subscription ID</th>
                    <th width="25%">RFID Sticker No.</th>
                    <th width="25%">Total Trip</th>
                    <th width="25%">Last Updated</th>
                    <th width="25%">Actions</th>
                </tr>
            </thead>
        </table>

    </div>
    <?php
}

function rfidUserEditAdminPage(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    $user_trip_table_name = $wpdb->prefix . 'users_trips';
    //echo $_REQUEST['id'];
    $result = $wpdb->get_row("SELECT UI.*, U.user_login, U.display_name FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.ID = ".$_REQUEST['id']);
    if(!empty($result)){
        $trip_result = $wpdb->get_row("SELECT UT.total_trip FROM $user_trip_table_name AS UT WHERE UT.user_id=".$result->user_id." AND UT.gea_subscription_id=".$result->gea_subscription_id);
    ?>
    <div class="wrap">
        <h2>RFID Installed User Update</h2><br><br>
        <form method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate">
            <table class="form-table" role="presentation">
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Name <span class="description">(required)</span></label></th>
                    <td><input name="display_name" type="text" id="display_name" value="<?php echo $result->display_name;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Mobile <span class="description">(required)</span></label></th>
                    <td><input name="user_login" type="text" id="user_login" value="<?php echo $result->user_login;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Vehicle Type <span class="description">(required)</span></label></th>
                    <td>
                        <select name="vehicle_type" class="select is_empty" id="vehicle_type">
                            <!--option value="2 Wheeler">2 Wheeler</option>
                            <option value="3 Wheeler">3 Wheeler</option-->
                            <option value="Car" <?php if($result->vehicle_type=='Car'){?> selected<?php }?>>Car</option>
                            <option value="Jeep" <?php if($result->vehicle_type=='Jeep'){?> selected<?php }?>>Jeep</option>
                            <option value="Microbus" <?php if($result->vehicle_type=='Microbus'){?> selected<?php }?>>Microbus</option>
                            <option value="Pick up" <?php if($result->vehicle_type=='Pick up'){?> selected<?php }?>>Pick up</option>
                            <option value="Minibus" <?php if($result->vehicle_type=='Minibus'){?> selected<?php }?>>Minibus</option>
                            <option value="Bus" <?php if($result->vehicle_type=='Bus'){?> selected<?php }?>>Bus</option>
                            <option value="Truck 4 Wheeler" <?php if($result->vehicle_type=='Truck 4 Wheeler'){?> selected<?php }?>>Truck 4 Wheeler</option>
                            <option value="Truck 6 Wheeler" <?php if($result->vehicle_type=='Truck 6 Wheeler'){?> selected<?php }?>>Truck 6 Wheeler</option>
                            <option value="Trailer Truck" <?php if($result->vehicle_type=='Trailer Truck'){?> selected<?php }?>>Trailer Truck</option>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Registration No. <span class="description">(required)</span></label></th>
                    <td><input name="vehicle_reg_no" type="text" id="vehicle_reg_no" value="<?php echo $result->vehicle_reg_no;?>" /></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="user_login">GEA Product Type ID <span class="description">(required)</span></label></th>
                    <td><input name="gea_product_type_id" type="text" id="gea_product_type_id" value="<?php echo $result->gea_product_type_id;?>" /></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="email">GEA Product Code <span class="description">(required)</span></label></th>
                    <td><input name="gea_product_id" type="text" id="gea_product_id" value="<?php echo $result->gea_product_id;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">GEA Customer ID <span class="description">(required)</span></label></th>
                    <td><input name="gea_customer_id" type="text" id="gea_customer_id" value="<?php echo $result->gea_customer_id;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="last_name">GEA Subscription ID <span class="description">(required)</span></label></th>
                    <td><input name="gea_subscription_id" type="text" id="gea_subscription_id" value="<?php echo $result->gea_subscription_id;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="url">RFID Sticker No. <span class="description">(required)</span></label></th>
                    <td><input name="rfid_sticker_no" type="text" id="rfid_sticker_no" value="<?php echo $result->rfid_sticker_no;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="url">Trips <span class="description">(required)</span></label></th>
                    <td><input name="total_trip" type="text" id="total_trip" value="<?php echo $trip_result->total_trip;?>" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="url">New Password</label></th>
                    <td><input name="user_pass" type="password" id="user_pass" value="" /></td>
                </tr>
            </table>
            <p class="submit"><input type="button" onclick="set_rfid('<?php echo $result->id;?>')" name="createuser" id="createusersub" class="button button-primary" value="Update"  /></p>
        </form>
    </div>
    <script>
        function set_rfid(info_id) {
            var display_name = document.getElementById('display_name').value;
            var user_login = document.getElementById('user_login').value;
            var vehicle_type = jQuery('#vehicle_type').val();
            var vehicle_reg_no = document.getElementById('vehicle_reg_no').value;
            var gea_product_type_id = document.getElementById('gea_product_type_id').value;
            var gea_product_id = document.getElementById('gea_product_id').value;
            var gea_customer_id = document.getElementById('gea_customer_id').value;
            var gea_subscription_id = document.getElementById('gea_subscription_id').value;
            var rfid_sticker_no = document.getElementById('rfid_sticker_no').value;
            var total_trip = document.getElementById('total_trip').value;
            var user_pass = document.getElementById('user_pass').value;
            if(display_name == '')
                alert('Please enter Name.');
            else if(user_login == '')
                alert('Please enter Mobile No.');
            else if(vehicle_type == '')
                alert('Please enter Vehicle type.');
            else if(vehicle_reg_no == '')
                alert('Please enter Registration No.');
            else if(gea_product_type_id == '')
                alert('Please enter GEA product type id.');
            else if(gea_product_id == '')
                alert('Please enter GEA product code.');
            else if(gea_customer_id == '')
                alert('Please enter GEA customer id.');
            else if(gea_subscription_id == '')
                alert('Please enter GEA subscription id.');
            else if(rfid_sticker_no == '')
                alert('Please enter sticker no.');
            else if(total_trip == '')
                alert('Please enter Total trip.');
            else {
                var crm = confirm("Are you sure?");
                if (crm == true) {
                    //alert(info_id);
                    //alert(rfid_sticker_no);
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'rfid_user_update',
                        info_id: info_id,
                        display_name: display_name,
                        user_login: user_login,
                        vehicle_type: vehicle_type,
                        vehicle_reg_no: vehicle_reg_no,
                        gea_product_type_id: gea_product_type_id,
                        gea_product_id: gea_product_id,
                        gea_customer_id: gea_customer_id,
                        gea_subscription_id: gea_subscription_id,
                        rfid_sticker_no: rfid_sticker_no,
                        user_pass: user_pass,
                        total_trip: total_trip
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
    else
        echo '<br><br><center><h2>No data found..</h2></center>';
}


add_action( 'wp_ajax_nopriv_getRfidUser', 'getRfidUser' );
add_action( 'wp_ajax_getRfidUser', 'getRfidUser' );
function getRfidUser(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    $user_trip_table_name = $wpdb->prefix . 'users_trips';

    // storing  request (ie, get/post) global array to a variable
    $requestData = $_REQUEST;
    $columns = array(
    // datatable column index  => database column name
        0 => 'user_login',
        1 => 'vehicle_type',
        2 => 'vehicle_reg_no',
        3 => 'gea_product_type_id',
        4 => 'gea_product_id',
        5 => 'gea_customer_id',
        6 => 'gea_subscription_id',
        7 => 'rfid_sticker_no'
);
    // getting total number records without any search
    $sql = "SELECT U.user_login, UI.id, UI.user_id, UI.vehicle_type, UI.vehicle_reg_no, UI.gea_product_type_id, UI.gea_product_id, UI.gea_customer_id, UI.gea_subscription_id, UI.rfid_sticker_no ";
    $sql .= " FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.installed = 1 AND U.user_login <> '' AND U.user_login > 0";
    $wpdb->get_results($sql);
    $totalData = $wpdb->num_rows;
    $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.
    $sql = "SELECT U.user_login, UI.id, UI.user_id, UI.vehicle_type, UI.vehicle_reg_no, UI.gea_product_type_id, UI.gea_product_id, UI.gea_customer_id, UI.gea_subscription_id, UI.rfid_sticker_no ";
    $sql .= " FROM $table_name AS UI LEFT JOIN $user_table_name AS U ON U.ID = UI.user_id WHERE UI.installed = 1 AND U.user_login <> '' AND U.user_login > 0";
    if (!empty($requestData['search']['value'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
        $sql .= " AND ( U.user_login LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.vehicle_type LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.vehicle_reg_no LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.gea_product_type_id LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.gea_product_id LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.gea_customer_id LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.gea_subscription_id LIKE '%" . $requestData['search']['value'] . "%' ";
        $sql .= " OR UI.rfid_sticker_no LIKE '%" . $requestData['search']['value'] . "%' )";
    }
    $wpdb->get_results($sql);
    $totalFiltered = $wpdb->num_rows; // when there is a search parameter then we have to modify total number filtered rows as per search result.
    $sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . "   " . $requestData['order'][0]['dir'] . "   LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "   ";

    $result = $wpdb->get_results($sql);

    $data = array();
    foreach($result as $print){  // preparing an array
        $trip_result = $wpdb->get_row("SELECT UT.* FROM $user_trip_table_name AS UT WHERE UT.user_id=".$print->user_id." AND UT.gea_subscription_id=".$print->gea_subscription_id);
        $nestedData = array();
        $nestedData[] = $print->user_login;
        $nestedData[] = $print->vehicle_type;
        $nestedData[] = $print->vehicle_reg_no;
        $nestedData[] = $print->gea_product_type_id;
        $nestedData[] = $print->gea_product_id;
        $nestedData[] = $print->gea_customer_id;
        $nestedData[] = $print->gea_subscription_id;
        $nestedData[] = $print->rfid_sticker_no;
        $nestedData[] = "<span id='trip_info_".$trip_result->id."'>".(!empty($trip_result->total_trip)?$trip_result->total_trip:0)."</span>";
        $nestedData[] = "<span id='last_updated_".$trip_result->id."'>".$trip_result->date_updated."</span> <a href='#' onclick='get_updated_trip(\"".$trip_result->id."\",\"".$trip_result->user_id."\",\"".$trip_result->gea_subscription_id."\",\"".$trip_result->gea_customer_id."\");return false;' class='button'>Get Update</a>";
        $nestedData[] = "<a class='button' href='".get_bloginfo('url')."/wp-admin/admin.php?page=rfid-user-edit&id=$print->id'>UPDATE</a>";

        $data[] = $nestedData;
    }

    $json_data = array(
        "draw" => intval($requestData['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
        "recordsTotal" => intval($totalData),  // total number of records
        "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
        "data" => $data   // total data array
    );

    echo json_encode($json_data);  // send data as json format
    exit;
}
