<?php
/*
Plugin Name: USER-TRIP
Plugin URI: http://mmhf.com.bd
Description: A user trip operations plugin.
Version: 1.0.0
Author: Mohammad Ayub Sarwar
Author URI: http://ayubsarwar.com/
License: GPL2
*/

add_action('admin_menu', 'addUSERTRIPAdminPageContent');
function addUSERTRIPAdminPageContent() {
    add_menu_page('USER TRIP', 'USER TRIP', 'manage_categories' ,__FILE__, 'userTripAdminPage', 'dashicons-wordpress');
}
function userTripAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_set_trip';
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

    <script>
        $(document).ready(function () {
            $('#example').DataTable({
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                dom: 'Bflrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
    
    <div class="wrap">
        <h2>Customer Balance Report</h2>
        <br>
<?php
        $query = "SELECT * FROM $table_name";
        $trip_result = $wpdb->get_results($query);
        //echo "SELECT * FROM $recharge_table_name WHERE DATE_FORMAT(date_inserted, '%Y-%m-%d') BETWEEN '" . $_POST['report_from_date'] . "' AND '" . $_POST['report_to_date'] . "' GROUP BY pgw_transaction_id";
        //exit;
        if (!empty($trip_result)) {


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
                                <th>Balance</th>
                            </tr>
                            </thead>
                            <tbody>
<?php
        foreach($trip_result as $thisTrip){
                echo '
                <tr>
                <td>' . $thisTrip->name . '</td>
                <td>' . $thisTrip->mobile . '</td>
                <td>' . $thisTrip->vehicle_type . '</td>
                <td>' . $thisTrip->vehicle_reg_no . '</td>
                <td>' . $thisTrip->gea_customer_id . '</td>
                <td>' . $thisTrip->gea_subscription_id . '</td>
                <td>' . $thisTrip->balance . '</td>
                </tr>
                ';
        }

        echo '
                        </tbody>
                    </table>';
    }

    echo '
            </div>
            ';
        

}

