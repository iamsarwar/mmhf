<?php
/*
Plugin Name: REPORT
Plugin URI: http://mmhf.com.bd
Description: A reprot plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar/
License: GPL2
*/

add_action('admin_menu', 'addREPORTAdminPageContent');
function addREPORTAdminPageContent() {
    add_menu_page('REPORT', 'REPORT', 'manage_categories' ,__FILE__, 'reportAdminPage', 'dashicons-wordpress');
    //add_submenu_page( __FILE__, 'RFID USER UPDATE', 'RFID USER UPDATE', 'manage_categories', 'rfid-user-edit', 'rfidUserEditAdminPage');
}
function reportAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'users_info';
    $user_table_name = $wpdb->prefix . 'users';
    $recharge_history_table_name = $wpdb->prefix . 'users_recharge_histories';
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
        });
        $(function () {
            $("#datepicker1").datepicker({dateFormat: 'yy-mm-dd'});
            $("#datepicker2").datepicker({dateFormat: 'yy-mm-dd'});
        });
    </script>
    <div class="wrap">
        <h2>Recharge Report</h2><br><br>
        <table class="wp-list-table widefat striped" id="example" border="1">
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Vehicle Type</th>
                <th>Registration No.</th>
                <th>Customer ID</th>
                <th>Trip</th>
                <th width="15%">Date</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $wpdb->get_results("SELECT RH.*, U.display_name, U.user_login, UI.vehicle_type, UI.vehicle_reg_no, UI.gea_customer_id FROM $recharge_history_table_name AS RH LEFT JOIN $user_table_name AS U ON U.ID = RH.user_id LEFT JOIN $table_name AS UI ON UI.id = RH.user_info_id ORDER BY RH.date_inserted DESC");
            foreach ($result as $print) {
                echo "
              <tr>
                <td>$print->invoice_id</td>
                <td>$print->display_name</td>
                <td>$print->user_login</td>
                <td>$print->vehicle_type</td>
                <td>$print->vehicle_reg_no</td>
                <td>$print->gea_customer_id</td>
                <td>$print->trips</td>
                <td width='15%'>$print->date_inserted</td>
              </tr>
            ";
            }
            ?>
            </tbody>
        </table>
        <br>
        <br>
        <br>
        <br>
        <h2>Vehicle Report</h2>
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
            <?php
            if (!empty($_POST['report_from_date']) && !empty($_POST['report_to_date'])) {
                //echo get_current_user_id();
                //echo '<br>';
                //echo base64_decode($_REQUEST['data']);
                $table_name = $wpdb->prefix . 'users_info';
                $user_table_name = $wpdb->prefix . 'users';
                $tariff_table_name = $wpdb->prefix . 'tariff_rates';
                $recharge_table_name = $wpdb->prefix . 'users_recharge_histories';
                $recharge_history_result = $wpdb->get_results("SELECT $table_name.vehicle_type, SUM($recharge_table_name.trips) AS total_trip FROM $recharge_table_name LEFT JOIN $table_name ON $table_name.user_id = $recharge_table_name.user_id
                                                                AND $table_name.id = $recharge_table_name.user_info_id WHERE DATE_FORMAT($recharge_table_name.date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['report_from_date'] . "' AND '" . $_POST['report_to_date'] . "' GROUP BY $table_name.vehicle_type");

                if (!empty($recharge_history_result)) {
                    ?>
                    <table class="wp-list-table widefat striped" id="example1" border="1">
                        <thead>
                        <tr>
                            <th>Vehicle Class</th>
                            <th>Subscription Qty</th>
                            <th>Subscription Revenue</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($recharge_history_result as $thisResult) {
                            $tariff_result = $wpdb->get_row("SELECT * FROM $tariff_table_name WHERE vehicle_type='" . $thisResult->vehicle_type . "'");
                            echo '
              <tr>
                <td>' . $thisResult->vehicle_type . '</td>
                <td>' . $thisResult->total_trip . '</td>
                <td>' . ($thisResult->total_trip * $tariff_result->toll_rates) . '</td>
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
    </div>
    <?php
}


