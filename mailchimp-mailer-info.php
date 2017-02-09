<?php
/*
 * MailChimp Mailer Info v1.5
 * Designed by Singh
 * 2017
 *
 * Instructions:
 * For a batch command append desired email address to the url (?email=example@ex.com),
 *   like so site.com/cron/mailchimp-mailer-info.php?email=example@ex.com.
*/

$login='XXXXXX'; // Your login name eg jujhar
$key='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // Your apikey found on MailChimp eg ed3b50ee87551696e765790a4743
$server = 'us14.'; // Your MailChimp server followed by a dot this is found in the URL after logging into MailChimp eg
                   // if URL https://us14.admin.mailchimp.com/ then server should be us14.


// Additional options
$display_activity_open_multiple = 1; // If disabled it will create new Opens column displaying number of opens rather
                                     // than display each on its own with its open date. It will also display only the
                                     // first open date.
$enable_db_save = 0; // Please change the database name and table name in the install.sql file and then import it before
                     // enabling
$db_servername = "localhost";
$db_user = "root";
$db_password = "mysql";
$db_name = "abc";
$db_table = "data";

//$debug = 1;
error_reporting(E_ERROR | E_PARSE);



// Please enter info above
// - - - - - - - - - - - - - - - - - - - - -


// Check email is inputted
if (!isset($_GET['email'])) {
    echo "Please add an email to view.";
    die();
}
$email = md5(strtolower($_GET['email']));

// Email details page
if (isset($_GET['campaign_id'])) {
    echo '<a style="padding:5px;margin-top:7px;" href="' . $_SERVER["HTTP_REFERER"] . '">back</a><br /><br />';
    echo '<b style="padding:5px;color:#2c9aa7;">Displaying content for email sent by campaign id ' . $_GET['campaign_id'] . '</b><br />';
    $auth = base64_encode( 'user:'. $key );
    $action = "campaigns/". $_GET['campaign_id'] ."/content";

    // Get email content
    $url = 'https://' . $server . 'api.mailchimp.com/3.0/' . $action;
    $json_data = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
        'Authorization: Basic ' . $auth));
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    $result = curl_exec($ch);
    if ($debug) {
        var_dump($result);
    }

    $json = json_decode($result);
    if ((string)$json->{'status'} == '404') {
        echo 'not found';
    }

    else {
        echo $json->{'html'};
    }

    die();
}
// End Email details page

// Get current url function
function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }

    else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }

    $pageURL = strtok($pageURL, '?');
    return $pageURL;
}
?>

<style>
    .link {
        color: gray;
        text-decoration: none;
        border-bottom: black 1px dashed;
    }
</style>

<?php
echo '<a href="index.html">back</a><br />';

echo "starting...";
$data['user'] = $email;
$action = "reports?count=99999";

$userid = md5($email);
$auth = base64_encode( 'user:'. $key );
$data = array(
    'list_id'        => $listid,
    'subscriber_hash' => md5($email)
);

$url = 'https://'.$server.'api.mailchimp.com/3.0/' . $action;
echo 'URL:'.$url."<br />";

$json_data = json_encode($data);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$url");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
    'Authorization: Basic '. $auth));
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
$result = curl_exec($ch);
if ($debug) {
    var_dump($result);
}

$json = json_decode($result);
echo $json->{'status'};
$campaigns_arr = [];
$campaign_data = [];
//echo $result;

$n_found = 0;
foreach ($json->{'reports'} as $val){
    $campaign_data[$n_found]=['title' =>$val->campaign_title, 'sent' => $val->send_time, 'subject' => $val->subject_line];
    array_push($campaigns_arr, $val->id);
    $n_found++;
}

echo '<span style="color:#2c9aa7" />found ' . $n_found . ' campaigns associated with account</span> <br />';
if ($n_found == 0) {
    echo 'No campaigns associated with account';
}

$data_output = [];
$no_results = 1;
$inserted_count = 0;
$last_updated_at = date('Y-m-d', strtotime('-200 years'));
$i = 0;

foreach ($campaigns_arr as $campaign_id) {
    $action = "reports/$campaign_id/email-activity/$email";

    // Search each campaign for email
    $url = 'https://' . $server . 'api.mailchimp.com/3.0/' . $action;

    $json_data = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
        'Authorization: Basic ' . $auth));
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    $result = curl_exec($ch);
    if ($debug) {
        var_dump($result);
    }

    $json = json_decode($result);
    if ((string)$json->{'status'} == '404') {
        $data_output[$i] = '';
    }

    else {
        $data_output[$i] = [
            'campaign_id' => $campaign_id,
            'title' => $campaign_data[$i]['title'],
            'sent' => $campaign_data[$i]['sent'],
            'subject' => $campaign_data[$i]['subject'],
            'activity' => $json->{'activity'}
        ];
        $no_results = 0;
    }

    $i++;
}

if ($no_results == 1) {
    echo $json->{'status'} . "<br />";
    echo "<b>No results found for email</b><br /><br />";
    die();
}
// End of campaign search
?>

    <style>
        th, td {
            text-align: center;
            font-family: 'Calibri', 'Calibri Light';
            border-bottom: 1px solid #ddd;
            min-width:70px;
            padding:3px;
            margin:3px;
        }
        tr:hover {background-color: #f5f5f5}
    </style>

    <br />
<?php
//print_r($data_output);

echo "<div style='overflow-x:auto;'>";
echo "<table>";
echo "<tr>
        <th>Campaign</th>
        <th>Date Sent</th>
        <th>Subject</th>
        <th>Action</th>
        <th>Action date</th>";
        if ($display_activity_open_multiple == 0) {
            echo '<th>Opens</th>';
        }
echo "</tr>";

foreach ($data_output as $row) {
    $x = 0;
    $display_activity_open_count = 0;
    if (isset($row['title'])) {

        // Format date sent
        $sentat = date( "h:mA m/d/Y", strtotime($row['sent']));

        // Get number of opens
        foreach ($row['activity'] as $activity) {
            if ($activity->action == 'open') {
                $display_activity_open_count++;
            }
        }

        $i = 0; // Used to display at least one open
        foreach ($row['activity'] as $activity) {

            // Ignore extra opens
            if ($display_activity_open_multiple == 0 && $activity->action == 'open' && $display_activity_open_count > 1 && $i!=0) {
            }

            elseif ($display_activity_open_multiple == 0 && ($i==0 || $activity->action != 'open')) {

                // Format time
                $time = date( "h:mA m/d/Y", strtotime($activity->timestamp));

                echo "<tr>";
                echo "<td>" . $row['title'] . "</td>
                      <td>" . $sentat . "</td>
                      <td>
                        <a target='_blank' class='link' href='" . curPageURL() ."?email=". $_GET["email"] . "&campaign_id=".$row['campaign_id']."'>" .
                            $row['subject'] . "
                        </a>
                      </td>

                      <td>" . $activity->action . "</td>
                      <td>" . $time . "</td>";
                if ($display_activity_open_multiple == 0 && $activity->action == 'open') {
                    echo '<td>' . $display_activity_open_count . '</td>';
                }

                elseif ($display_activity_open_multiple == 0 && ($activity->action != 'open')) {
                    echo '<td></td>';
                }

                echo "</tr>";
                $x++; $i++;
            }

            if ($display_activity_open_multiple != 0) {

                // Format time
                $time = date( "h:mA m/d/Y", strtotime($activity->timestamp));

                echo "<tr>";
                echo "<td>" . $row['title'] . "</td>
                      <td>" . $sentat . "</td>
                      <td>" . $row['subject'] . "</td>
                      <td>" . $activity->action . "</td>
                      <td>" . $time . "</td>";
                echo "</tr>";
                $x++;
            }

            // Save to database
            if ($enable_db_save == 1) {

                // Create connection
                $conn = new mysqli($db_servername, $db_user, $db_password, $db_name);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Check if data is new
                if ($inserted_count==0) {
                    $date_chk_query = "SELECT database_last_updated FROM " . $db_table . "
                      WHERE email = '" . $_GET['email'] . "' ORDER BY id DESC LIMIT 1";

                    $date_chk = $conn->query($date_chk_query);

                    if ($date_chk->num_rows > 0) {
                        $a = $date_chk->fetch_assoc();
                        $last_updated_at = $a["database_last_updated"];
                    }
                }

                // Only update if newer than $last_updated_at date
                if($last_updated_at < date( "Y-m-d H:i:s", strtotime($activity->timestamp))) {
                    $insert_query = "INSERT INTO " . $db_table . " (email,
                                                       database_last_updated,
                                                       campaign_id,
                                                       campaign_name,
                                                       date_sent,
                                                       subject,
                                                       action,
                                                       action_date)
                        VALUES ('".$_GET['email']."',
                                NOW(),
                                '".$row['campaign_id']."',
                                '".$row['title']."',
                                '".date('Y-m-d H:i:s', strtotime($row['sent']))."',
                                '".$row['subject']."',
                                '".$activity->action."',
                                '".date( "Y-m-d H:i:s", strtotime($activity->timestamp))."'
                                )";

                    if ($conn->query($insert_query) === TRUE) {$inserted_count++;}

                    else {
                        echo "Error: " . $insert_query . "<br>" . $conn->error;
                    }
                }

                $conn->close();
            }
            // End Database save

        }

    }

}
echo "</table>";
echo "</div>";
if ($enable_db_save == 1) {
    echo "<br /><br />$inserted_count new records added";
}

echo "<br /><br />finished.";


