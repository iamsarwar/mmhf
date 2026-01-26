<?php
/*
Plugin Name: MISSING-CUSTOMER
Plugin URI: http://mmhf.com.bd
Description: A missing user operations plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar.com/
License: GPL2
*/

add_action('admin_menu', 'addMISSINGCUSTOMERAdminPageContent');
function addMISSINGCUSTOMERAdminPageContent() {
    add_menu_page('MISSING CUSTOMER', 'MISSING CUSTOMER', 'manage_categories' ,__FILE__, 'missingCustomerAdminPage', 'dashicons-wordpress');
}
function missingCustomerAdminPage() {
    global $wpdb;
    ?>
    <div class="wrap">
        <h2>Missing Customer</h2><br><br>
        <form method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate">
            <table class="form-table" role="presentation">
                <tr class="form-field">
                    <th scope="row"><label for="account_type">Account Type  <span class="description">(required)</span></label></th>
                    <td>
                        <select name="account_type" required class="select is_empty" id="account_type" onchange="auth_focus(this.value);">
                            <option value="Individual">Individual</option>
                            <option value="Corporate">Corporate</option>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Name(Individual/ Organization) <span class="description">(required)</span></label></th>
                    <td><input name="display_name" required type="text" id="display_name" value="" /></td>
                </tr>
                <tr class="form-field" id="auth_area" style="display:none;">
                    <th scope="row"><label for="first_name">Authorized Person’s Name</label></th>
                    <td><input type="text" value="" name="auth_person_name" id="auth_person_name" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Email</label></th>
                    <td><input name="user_email" type="text" id="user_email" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Mobile <span class="description">(required)</span></label></th>
                    <td><input name="user_login" required type="text" id="user_login" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="url">Password <span class="description">(required)</span></label></th>
                    <td><input name="user_pass" required type="password" id="user_pass" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="email">NID</label></th>
                    <td><input name="nid" type="text" id="nid" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Address</label></th>
                    <td><input name="address" type="text" id="address" value="" /></td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Vehicle Type <span class="description">(required)</span></label></th>
                    <td>
                        <select name="vehicle_type" class="select is_empty" id="vehicle_type">
                            <!--option value="2 Wheeler">2 Wheeler</option>
                            <option value="3 Wheeler">3 Wheeler</option-->
                            <option value="Car">Car</option>
                            <option value="Jeep">Jeep</option>
                            <option value="Microbus">Microbus</option>
                            <option value="Pick up">Pick up</option>
                            <option value="Minibus">Minibus</option>
                            <option value="Bus">Bus</option>
                            <option value="Truck 4 Wheeler">Truck 4 Wheeler</option>
                            <option value="Truck 6 Wheeler">Truck 6 Wheeler</option>
                            <option value="Trailer Truck">Trailer Truck</option>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row"><label for="first_name">Registration No. <span class="description">(required)</span></label></th>
                    <td><input name="vehicle_reg_no" type="text" id="vehicle_reg_no" value="" /></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="user_login">Bkash Transaction ID <span class="description">(required)</span></label></th>
                    <td><input name="pgw_transaction_id" required type="text" id="pgw_transaction_id" value="" /></td>
                </tr>
            </table>
            <p class="submit"><input type="button" onclick="missing_user_insert()" name="createuser" id="createusersub" class="button button-primary" value="Submit"  /></p>
        </form>
    </div>
    <script>
        function auth_focus(auth_val){
            if(auth_val == 'Corporate'){
                document.getElementById('auth_area').style.display='block';
            }
            else{
                document.getElementById('auth_area').style.display='none';
            }

        }

        function missing_user_insert() {
            var display_name = document.getElementById('display_name').value;
            var account_type = document.getElementById('account_type').value;
            var vehicle_type = jQuery('#vehicle_type').val();
            var address = document.getElementById('address').value;
            var nid = document.getElementById('nid').value;
            var auth_person_name = document.getElementById('auth_person_name').value;
            var user_email = document.getElementById('user_email').value;
            var pgw_transaction_id = document.getElementById('pgw_transaction_id').value;
            var user_login = document.getElementById('user_login').value;
            var vehicle_reg_no = document.getElementById('vehicle_reg_no').value;
            var user_pass = document.getElementById('user_pass').value;
            if(display_name == '')
                alert('Please enter Name.');
            else if(user_login == '')
                alert('Please enter Mobile No.');
            else if(vehicle_type == '')
                alert('Please enter Vehicle type.');
            else if(vehicle_reg_no == '')
                alert('Please enter Registration No.');
            else if(account_type == '')
                alert('Please enter account type.');
            else if(user_pass == '')
                alert('Please enter password.');
            else if(pgw_transaction_id == '')
                alert('Please enter bkash transaction id.');
            else {
                var crm = confirm("Are you sure?");
                if (crm == true) {
                    //alert(info_id);
                    //alert(rfid_sticker_no);
                    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'missing_user_insert',
                        account_type: account_type,
                        display_name: display_name,
                        user_login: user_login,
                        vehicle_type: vehicle_type,
                        vehicle_reg_no: vehicle_reg_no,
                        user_pass: user_pass,
                        pgw_transaction_id: pgw_transaction_id,
                        nid: nid,
                        user_email: user_email,
                        address: address,
                        auth_person_name: auth_person_name
                    }, function (output) {
                        if(output == 'Success0') {
                            alert('Successfully inserted.');
                            location.reload();
                        }
                        else
                            alert(output);
                    });
                }
            }
        }
    </script>
<?php
}


