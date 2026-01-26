<?php  
/* 
Template Name: Account
*/
 
    the_post();
    get_header();
    if(is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = ( array )$user->roles;
        //$trip_table_name = $wpdb->prefix . "users_trips";
        //$trip_retrieve_data = $wpdb->get_row( "SELECT $trip_table_name.* FROM $trip_table_name WHERE $trip_table_name.user_id = 200 AND $trip_table_name.user_info_id=15" );
        //$total_trip = 2;
        //if(!empty($trip_retrieve_data)) {
            //echo $trip_retrieve_data->total_trip;
        //}
        if($roles[0] == 'accounts') {
            ?>
            <!-- Latest compiled and minified CSS -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
                  integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
                  crossorigin="anonymous">
            <!-- Optional theme -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
                  integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp"
                  crossorigin="anonymous">
            <link rel="stylesheet" href="https://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#example').DataTable({
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        dom: 'Bflrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    $('#example1').DataTable({
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        dom: 'Bflrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    $('#example3').DataTable({
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        dom: 'Bflrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    $('#example4').DataTable({
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        dom: 'Bflrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    $('#example5').DataTable({
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        dom: 'Bflrtip',
                        buttons: [
                            'copy', 'csv', 'excel', 'pdf', 'print'
                        ]
                    });

                    $("#main").removeClass("all_colors").addClass("all_colors myAccClass");
                });
                $(function () {
                    $("#datepicker1").datepicker({dateFormat: 'yy-mm-dd'});
                    $("#datepicker2").datepicker({dateFormat: 'yy-mm-dd'});
                    $("#datepicker3").datepicker({dateFormat: 'yy-mm-dd'});
                    $("#datepicker4").datepicker({dateFormat: 'yy-mm-dd'});
                    $("#datepicker5").datepicker({dateFormat: 'yy-mm-dd'});
                    $("#datepicker6").datepicker({dateFormat: 'yy-mm-dd'});
                });
            </script>
            <br><br>
            <div class='container'>
                <h2>Recharge Report</h2>
                <div id="search-options" class="col-md-12">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="#" class="form-horizontal" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Report Date</label>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['report_from_date'])) ? $_POST['report_from_date'] : ''; ?>"
                                                   name="report_from_date" id="datepicker1">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block"> Select from date</span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['report_to_date'])) ? $_POST['report_to_date'] : ''; ?>"
                                                   name="report_to_date" id="datepicker2">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block">Select to date </span>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="pgw_name" class="form-control">
                                            <option value="">Select PGW</option>
                                            <option value="bkash" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'bkash'){echo 'selected';}; ?>>bkash</option>
                                            <option value="city bank" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'city bank'){echo 'selected';}; ?>>city bank</option>
                                            <option value="nagad" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'nagad'){echo 'selected';}; ?>>nagad</option>
                                            <option value="dbbl rocket" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'dbbl rocket'){echo 'selected';}; ?>>dbbl rocket</option>
                                            <option value="dbbl nexus" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'dbbl nexus'){echo 'selected';}; ?>>dbbl nexus</option>
                                            <option value="upay" <?php if(!empty($_POST['pgw_name']) && $_POST['pgw_name'] == 'upay'){echo 'selected';}; ?>>upay</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="submit" class="btn blue" value="Search">
                                        <button type="reset" class="btn default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>

                <?php
                if (!empty($_POST['report_from_date']) && !empty($_POST['report_to_date'])) {
                    //echo get_current_user_id();
                    //echo '<br>';
                    //echo base64_decode($_REQUEST['data']);
                    $table_name = $wpdb->prefix . 'users_info';
                    $user_table_name = $wpdb->prefix . 'users';
                    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                    $recharge_table_name = $wpdb->prefix . 'users_recharge_histories';
                    $query = "SELECT * FROM $recharge_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['report_from_date'] . "' AND '" . $_POST['report_to_date'] . "'";
                    if(!empty($_POST['pgw_name']))
                        $query.= " AND pgw_name = '".$_POST['pgw_name']."'";

                    $query.= " GROUP BY pgw_transaction_id";
                    $recharge_history_result = $wpdb->get_results($query);
                    //echo "SELECT * FROM $recharge_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['report_from_date'] . "' AND '" . $_POST['report_to_date'] . "' GROUP BY pgw_transaction_id";
                    //exit;
                    if (!empty($recharge_history_result)) {
                        ?>
                        <table class="wp-list-table widefat striped" id="example" border="1">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Vehicle Type</th>
                                <th>Registration No.</th>
                                <th>GEA Customer ID</th>
                                <th>GEA Subscription ID</th>
                                <th>Buy Trips</th>
                                <th>Bonus Trips</th>
                                <th>Total Trips</th>
                                <th>Bonus Amount</th>
                                <th>Amount</th>
                                <th>PGW</th>
                                <th>Transaction ID</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $trip_package = explode('_', $recharge_history_result->packages);
                            foreach ($recharge_history_result as $thisResult) {
                                $rest_query = "";
                                if(!empty($thisResult->user_info_id))
                                    $rest_query = " id=".$thisResult->user_info_id;
                                elseif(!empty($thisResult->gea_subscription_id))
                                    $rest_query = " gea_subscription_id=".$thisResult->gea_subscription_id;

                                //echo "SELECT * FROM $table_name WHERE user_id=" . $thisResult->user_id . " AND (id=".$thisResult->user_info_id." OR gea_subscription_id=" . $thisResult->gea_subscription_id .")";
                                $info_result = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id=" . $thisResult->user_id . " AND ".$rest_query);
                                $user_result = $wpdb->get_row("SELECT * FROM $user_table_name WHERE ID=" . $thisResult->user_id);
                                $tariff_result = $wpdb->get_row("SELECT * FROM $tariff_table_name WHERE vehicle_type='" . $info_result->vehicle_type . "'");
                                if(!empty($thisResult->packages)){
                                    $trip_package = explode('_', $thisResult->packages);
                                    $buy_trips = $thisResult->trips - ($trip_package[1] * 2);
                                    $bonus_trips = $trip_package[1] * 2;
                                    $total_trips = $thisResult->trips;
                                    $pay_amount = ($thisResult->trips * $tariff_result->toll_rates) - ($trip_package[1] * 2 * $tariff_result->toll_rates);
                                    $bonus_amount = $trip_package[1] * 2 * $tariff_result->toll_rates;
                                }
                                else{
                                    $buy_trips = $thisResult->trips;
                                    $bonus_trips = 0;
                                    $total_trips = $thisResult->trips;
                                    $pay_amount = $thisResult->trips * $tariff_result->toll_rates;
                                    $bonus_amount = 0;
                                }

                                $pgw_name = $thisResult->pgw_name;
                                if($thisResult->platform=='bkash_app')
                                    $pgw_name = 'bkash_app';
                
                                if($thisResult->platform=='nagad_app')
                                    $pgw_name = 'nagad_app';
                
                
                                echo '
              <tr>
                <td>' . $user_result->display_name . '</td>
                <td>' . $user_result->user_login . '</td>
                <td>' . $info_result->vehicle_type . '</td>
                <td>' . $info_result->vehicle_reg_no . '</td>
                <td>' . $info_result->gea_customer_id . '</td>
                <td>' . $info_result->gea_subscription_id . '</td>
                <td>' . $buy_trips . '</td>
                <td>' . $bonus_trips . '</td>
                <td>' . $total_trips . '</td>
                <td>' . $bonus_amount . '</td>
                <td>' . $pay_amount . '</td>
                <td>' . $pgw_name . '</td>
                <td>' . $thisResult->pgw_transaction_id . '</td>
                <td>' . date('Y-m-d', strtotime($thisResult->date_inserted)) . '</td>
              </tr>
                ';
                            }
                            ?>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>
                        <?php
                    } else
                        echo '<br><br><center>No data found</center>';
                }
                ?>
                <div style="clear:both;"></div>
                <br>
                <hr>
                <br>
                <h2>Monthly Collection Report (Portal)</h2>
                <div id="search-options" class="col-md-12">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="#" class="form-horizontal" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Report Year-Month</label>
                                    <div class="col-md-2">
                                        <select name="monthly_portal_year" class="form-control">
                                            <?php
                                                for($i=date('Y');$i>=(date('Y')-5);$i--){
                                                    echo '<option value="'.$i.'"';
                                                    if(!empty($_POST['monthly_portal_year']) && $_POST['monthly_portal_year'] == $i){
                                                        echo 'selected';
                                                    }
                                                    echo '>'.$i.'</option>';
                                                }
                                            ?>
                                        </select>
                                        <span class="help-block"> Select Year</span>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="monthly_portal_month" class="form-control">
                                            <option value="1" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '1'){echo 'selected';} ?>>January</option>
                                            <option value="2" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '2'){echo 'selected';} ?>>February</option>
                                            <option value="3" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '3'){echo 'selected';} ?>>March</option>
                                            <option value="4" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '4'){echo 'selected';} ?>>April</option>
                                            <option value="5" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '5'){echo 'selected';} ?>>May</option>
                                            <option value="6" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '6'){echo 'selected';} ?>>June</option>
                                            <option value="7" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '7'){echo 'selected';} ?>>July</option>
                                            <option value="8" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '8'){echo 'selected';} ?>>August</option>
                                            <option value="9" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '9'){echo 'selected';} ?>>September</option>
                                            <option value="10" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '10'){echo 'selected';} ?>>October</option>
                                            <option value="11" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '11'){echo 'selected';} ?>>November</option>
                                            <option value="12" <?php if(!empty($_POST['monthly_portal_month']) && $_POST['monthly_portal_month'] == '12'){echo 'selected';} ?>>December</option>
                                        </select>
                                        <span class="help-block">Select Month </span>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="monthly_pgw_name" class="form-control">
                                            <option value="">Select PGW</option>
                                            <option value="bkash" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'bkash'){echo 'selected';} ?>>bkash</option>
                                            <option value="city bank" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'city bank'){echo 'selected';} ?>>city bank</option>
                                            <option value="nagad" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'nagad'){echo 'selected';}; ?>>nagad</option>
                                            <option value="dbbl rocket" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'dbbl rocket'){echo 'selected';} ?>>dbbl rocket</option>
                                            <option value="dbbl nexus" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'dbbl nexus'){echo 'selected';} ?>>dbbl nexus</option>
                                            <option value="upay" <?php if(!empty($_POST['monthly_pgw_name']) && $_POST['monthly_pgw_name'] == 'upay'){echo 'selected';} ?>>upay</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="submit" class="btn blue" value="Search">
                                        <button type="reset" class="btn default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
                <?php
                if (!empty($_POST['monthly_portal_year']) && !empty($_POST['monthly_portal_month'])) {
                    //echo get_current_user_id();
                    //echo '<br>';
                    //echo base64_decode($_REQUEST['data']);
                    $table_name = $wpdb->prefix . 'users_info';
                    $user_table_name = $wpdb->prefix . 'users';
                    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                    $recharge_table_name = $wpdb->prefix . 'users_recharge_histories';
                    $year_month = $_POST['monthly_portal_year'].'-'.$_POST['monthly_portal_month'].'-01';
                    $query = "SELECT pgw_name, SUM(pgw_amount) AS total_collection, SUM(trips) AS total_trip FROM $recharge_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m') = '".date('Y-m', strtotime($year_month))."'";
                    if(!empty($_POST['monthly_pgw_name']))
                        $query.= " AND pgw_name = '".$_POST['monthly_pgw_name']."'";

                    $query.= " GROUP BY pgw_name";

                    $recharge_history_result = $wpdb->get_results($query);
                    //exit;
                    if (!empty($recharge_history_result)) {
                        ?>
                        <table class="wp-list-table widefat striped" id="example3" border="1">
                            <thead>
                            <tr>
                                <th>Portal</th>
                                <th>Total Trips</th>
                                <th>Total Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                            foreach ($recharge_history_result as $thisResult) {
                
                                echo '
              <tr>
                <td>' . $thisResult->pgw_name . '</td>
                <td align="right">' . $thisResult->total_trip . '</td>
                <td align="right">' . number_format($thisResult->total_collection) . '</td>
              </tr>
                ';
                            }
                            ?>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>
                        <?php
                    } else
                        echo '<br><br><center>No data found</center>';
                }
                ?>
                <div style="clear:both;"></div>
                <br>
                <hr>
                <br>
                <h2>Monthly Collection Report (RFID)</h2>
                <div id="search-options" class="col-md-12">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="#" class="form-horizontal" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Report Year-Month</label>
                                    <div class="col-md-2">
                                        <select name="monthly_rfid_year" class="form-control">
                                            <?php
                                                for($i=date('Y');$i>=(date('Y')-5);$i--){
                                                    echo '<option value="'.$i.'"';
                                                    if(!empty($_POST['monthly_rfid_year']) && $_POST['monthly_rfid_year'] == $i){
                                                        echo 'selected';
                                                    }
                                                    echo '>'.$i.'</option>';
                                                }
                                            ?>
                                        </select>
                                        <span class="help-block"> Select Year</span>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="monthly_rfid_month" class="form-control">
                                            <option value="1" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '1'){echo 'selected';} ?>>January</option>
                                            <option value="2" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '2'){echo 'selected';} ?>>February</option>
                                            <option value="3" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '3'){echo 'selected';} ?>>March</option>
                                            <option value="4" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '4'){echo 'selected';} ?>>April</option>
                                            <option value="5" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '5'){echo 'selected';} ?>>May</option>
                                            <option value="6" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '6'){echo 'selected';} ?>>June</option>
                                            <option value="7" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '7'){echo 'selected';} ?>>July</option>
                                            <option value="8" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '8'){echo 'selected';} ?>>August</option>
                                            <option value="9" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '9'){echo 'selected';} ?>>September</option>
                                            <option value="10" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '10'){echo 'selected';} ?>>October</option>
                                            <option value="11" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '11'){echo 'selected';} ?>>November</option>
                                            <option value="12" <?php if(!empty($_POST['monthly_rfid_month']) && $_POST['monthly_rfid_month'] == '12'){echo 'selected';} ?>>December</option>
                                        </select>
                                        <span class="help-block">Select Month </span>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="monthly_rfid_pgw_name" class="form-control">
                                            <option value="">Select PGW</option>
                                            <option value="bkash" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'bkash'){echo 'selected';} ?>>bkash</option>
                                            <option value="city bank" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'city bank'){echo 'selected';} ?>>city bank</option>
                                            <option value="nagad" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'nagad'){echo 'selected';}; ?>>nagad</option>
                                            <option value="dbbl rocket" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'dbbl rocket'){echo 'selected';} ?>>dbbl rocket</option>
                                            <option value="dbbl nexus" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'dbbl nexus'){echo 'selected';} ?>>dbbl nexus</option>
                                            <option value="upay" <?php if(!empty($_POST['monthly_rfid_pgw_name']) && $_POST['monthly_rfid_pgw_name'] == 'upay'){echo 'selected';} ?>>upay</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="submit" class="btn blue" value="Search">
                                        <button type="reset" class="btn default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
                <?php
                if (!empty($_POST['monthly_rfid_year']) && !empty($_POST['monthly_rfid_month'])) {
                    //echo get_current_user_id();
                    //echo '<br>';
                    //echo base64_decode($_REQUEST['data']);
                    $table_name = $wpdb->prefix . 'users_info';
                    $user_table_name = $wpdb->prefix . 'users';
                    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                    $recharge_table_name = $wpdb->prefix . 'users_recharge_histories';
                    $year_month = $_POST['monthly_rfid_year'].'-'.$_POST['monthly_rfid_month'].'-01';
                    $query = "SELECT gea_subscription_id, SUM(pgw_amount) AS total_collection, SUM(trips) AS total_trip, pgw_name FROM $recharge_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m') = '".date('Y-m', strtotime($year_month))."'";
                    if(!empty($_POST['monthly_rfid_pgw_name']))
                        $query.= " AND pgw_name = '".$_POST['monthly_rfid_pgw_name']."'";

                    $query.= " GROUP BY gea_subscription_id, pgw_name";

                    $recharge_history_result = $wpdb->get_results($query);
                    //exit;
                    if (!empty($recharge_history_result)) {
                        ?>
                        <table class="wp-list-table widefat striped" id="example4" border="1">
                            <thead>
                            <tr>
                                <th>Subscription ID</th>
                                <th>Portal</th>
                                <th>Total Trips</th>
                                <th>Total Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                            foreach ($recharge_history_result as $thisResult) {
                
                                echo '
              <tr>
                <td>' . $thisResult->gea_subscription_id . '</td>
                <td>' . $thisResult->pgw_name . '</td>
                <td align="right">' . $thisResult->total_trip . '</td>
                <td align="right">' . number_format($thisResult->total_collection) . '</td>
              </tr>
                ';
                            }
                            ?>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>
                        <?php
                    } else
                        echo '<br><br><center>No data found</center>';
                }
                ?>
                <div style="clear:both;"></div>
                <br>
                <hr>
                <br>
                <h2>Process Fee Report</h2>
                <div id="search-options" class="col-md-12">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="<?php echo $action; ?>" class="form-horizontal" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Report Date</label>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['feereport_from_date'])) ? $_POST['feereport_from_date'] : ''; ?>"
                                                   name="feereport_from_date" id="datepicker3">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block"> Select from date</span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['feereport_to_date'])) ? $_POST['feereport_to_date'] : ''; ?>"
                                                   name="feereport_to_date" id="datepicker4">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block">Select to date </span>
                                    </div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="submit" class="btn blue" value="Search">
                                        <button type="reset" class="btn default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>

                <?php
                if (!empty($_POST['feereport_from_date']) && !empty($_POST['feereport_to_date'])) {
                    //echo get_current_user_id();
                    //echo '<br>';
                    //echo base64_decode($_REQUEST['data']);
                    $table_name = $wpdb->prefix . 'users_info';
                    $user_table_name = $wpdb->prefix . 'users';
                    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                    $process_fee_table_name = $wpdb->prefix . 'user_process_fees';
                    $process_fee_result = $wpdb->get_results("SELECT * FROM $process_fee_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['feereport_from_date'] . "' AND '" . $_POST['feereport_to_date'] . "' GROUP BY pgw_transaction_id");
                    if (!empty($process_fee_result)) {
                        ?>
                        <table class="wp-list-table widefat striped" id="example1" border="1">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Vehicle Type</th>
                                <th>Registration No.</th>
                                <th>GEA Customer ID</th>
                                <th>GEA Subscription ID</th>
                                <th>Amount</th>
                                <th>PGW</th>
                                <th>Transaction ID</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($process_fee_result as $thisResult) {
                                $info_result = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id=" . $thisResult->user_id . " AND id=" . $thisResult->user_info_id);
                                $user_result = $wpdb->get_row("SELECT * FROM $user_table_name WHERE ID=" . $thisResult->user_id);
                                $tariff_result = $wpdb->get_row("SELECT * FROM $tariff_table_name WHERE vehicle_type='" . $info_result->vehicle_type . "'");
                                echo '
              <tr>
                <td>' . $user_result->display_name . '</td>
                <td>' . $user_result->user_login . '</td>
                <td>' . $info_result->vehicle_type . '</td>
                <td>' . $info_result->vehicle_reg_no . '</td>
                <td>' . $info_result->gea_customer_id . '</td>
                <td>' . $info_result->gea_subscription_id . '</td>
                <td>' . $thisResult->process_amount . '</td>
                <td>' . $thisResult->pgw_name . '</td>
                <td>' . $thisResult->pgw_transaction_id . '</td>
                <td>' . date('Y-m-d', strtotime($thisResult->date_inserted)) . '</td>
              </tr>
                ';
                            }
                            ?>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <?php
                    } else
                        echo '<br><br><center>No data found</center>';
                }
                ?>
                <div style="clear:both;"></div>
                <br>
                <hr>
                <br>
                <h2>RFID Subscription Balance</h2>
                <div id="search-options" class="col-md-12">
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="<?php echo $action; ?>" class="form-horizontal" method="post">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Report Date</label>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['balancereport_from_date'])) ? $_POST['balancereport_from_date'] : ''; ?>"
                                                   name="balancereport_from_date" id="datepicker5">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block"> Select from date</span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" readonly
                                                   value="<?php echo (!empty($_POST['balancereport_to_date'])) ? $_POST['balancereport_to_date'] : ''; ?>"
                                                   name="balancereport_to_date" id="datepicker6">
                                        </div>
                                        <!-- /input-group -->
                                        <span class="help-block">Select to date </span>
                                    </div>
                                </div>
                                <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="submit" class="btn blue" value="Search">
                                        <button type="reset" class="btn default">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>

                <?php
                if (!empty($_POST['balancereport_from_date']) && !empty($_POST['balancereport_to_date'])) {
                    //echo get_current_user_id();
                    //echo '<br>';
                    //echo base64_decode($_REQUEST['data']);
                    $table_name = $wpdb->prefix . 'users_info';
                    $user_table_name = $wpdb->prefix . 'users';
                    $vehicle_types = $wpdb->get_results("SELECT DISTINCT vehicle_type FROM $table_name WHERE TRIM(vehicle_type) != ''");
                    $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                    $trip_deductions_table_name = $wpdb->prefix . 'user_trip_deductions';
                    $recharge_histories_table_name = $wpdb->prefix . 'users_recharge_histories';
                    if (!empty($vehicle_types)) {
                        ?>
                        <table class="wp-list-table widefat striped" id="example5" border="1">
                            <thead>
                            <tr>
                                <th>Vehicle Class</th>
                                <th>Opening Balance as on <?php echo $_POST['balancereport_from_date'];?></th>
                                <th>RFID Recharge During the Year</th>
                                <th>RFID Used During the Year</th>
                                <th>Unused Balance as on <?php echo $_POST['balancereport_to_date'];?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($vehicle_types as $thisResult) {
				if(!is_numeric($thisResult->vehicle_type)){
                                $recharge_result = $wpdb->get_row("SELECT SUM(pgw_amount) total_recharge FROM $recharge_histories_table_name WHERE gea_subscription_id IN (SELECT gea_subscription_id FROM $table_name WHERE vehicle_type = '".$thisResult->vehicle_type."') AND DATE_FORMAT(date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['balancereport_from_date'] . "' AND '" . $_POST['balancereport_to_date'] . "'");
                                $tariff_result = $wpdb->get_row("SELECT * FROM $tariff_table_name WHERE vehicle_type='" . $thisResult->vehicle_type . "'");
                                $deduction_result = $wpdb->get_row("SELECT (COUNT(trips) * $tariff_result->toll_rates) AS total_used  FROM $trip_deductions_table_name WHERE user_info_id IN (SELECT DISTINCT id FROM $table_name WHERE vehicle_type = '".$thisResult->vehicle_type."') AND DATE_FORMAT(gea_passage_time, '%Y-%m-%d') BETWEEN '" . $_POST['balancereport_from_date'] . "' AND '" . $_POST['balancereport_to_date'] . "'");
//echo "SELECT (SUM(trips) * $tariff_result->toll_rates) AS total_used  FROM $trip_deductions_table_name WHERE user_info_id IN (SELECT user_info_id FROM $table_name WHERE vehicle_type = '".$thisResult->vehicle_type."') AND DATE_FORMAT(gea_passage_time, '%Y-%m-%d') BETWEEN '" . $_POST['balancereport_from_date'] . "' AND '" . $_POST['balancereport_to_date'] . "'";                                
if($thisResult->vehicle_type == 'Bus'){
	$add = 0;
        $op = 2140340;
}
else if($thisResult->vehicle_type == 'Car'){
        $add = 0;
        $op = 1954010;
}
else if($thisResult->vehicle_type == 'Jeep'){
        $add = 0;
	$op = 1085890;
}
else if($thisResult->vehicle_type == 'Minibus'){
        $add = 0;
	$op = 229119;
}
else if($thisResult->vehicle_type == 'Microbus'){
        $add = 0;
        $op = 549970;
}
else if($thisResult->vehicle_type == 'Pick up'){
        $add = 0;
        $op = 94950;
}
else{
	$add = 0;
	$op = 0;
}

echo '
              <tr>
                <td>' . $thisResult->vehicle_type . '</td>
                <td align="right">' . number_format($op, 2) . '</td>
                <td align="right">' . number_format(($recharge_result->total_recharge + $add), 2) . '</td>
                <td align="right">' . number_format($deduction_result->total_used, 2) . '</td>
                <td align="right">'. number_format((($recharge_result->total_recharge + $add + $op) - $deduction_result->total_used), 2) .'</td>
              </tr>
                ';
                            } }
                            ?>
                            </tbody>
                        </table>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <?php
                    } else
                        echo '<br><br><center>No data found</center>';
                }
                ?>

            </div>
            <br>
            <br>
            <br>
            <br>
<?php
        }
        else{
            echo '<br><br><center><h2>Not Authorize</h2></center><br><br>';
            echo '
            <script>
                jQuery(document).ready(function () {
                    jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
                });
            </script>
            ';
        }

    }
    else{
        // If we're here, they aren't logged in, show them a message
        $defaults = array(
            // message show to non-logged in users
            'msg'    => __('You must login to see this content.', 'wpAyub'),
            // Login page link
            'link'   => site_url('my-account'),
            // login link anchor text
            'anchor' => __('Login.', 'wpAyub')
        );
        $args = wp_parse_args($args, $defaults);

        $msg = sprintf(
            '<aside class="login-warning">%s <a href="%s">%s</a></aside>',
            esc_html($args['msg']),
            esc_url($args['link']),
            esc_html($args['anchor'])
        );
        echo '<br><br><center>'.$msg.'</center><br><br>';
        echo '
        <script>
            jQuery(document).ready(function () {
                jQuery( "#main" ).removeClass( "all_colors" ).addClass( "all_colors myAccClass" );
            });
        </script>
        ';
    }
    get_footer();
?>

