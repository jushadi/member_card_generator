<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Member card print */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB_DIR.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SENAYAN_BASE_DIR.'admin/default/session.inc.php';
require SENAYAN_BASE_DIR.'admin/default/session_check.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');

if (!$can_read) {
    die('<div class="errorBox">You dont have enough privileges to view this section</div>');
}

// local settings
$max_print = 10;

// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
    // update print queue count object
    echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
    utility::jsAlert(__('Print queue cleared!'));
    unset($_SESSION['card']);
    exit();
}

if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!$can_read) {
        die();
    }
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($_POST['itemID']);
    }
    // loop array
    if (isset($_SESSION['card'])) {
        $print_count = count($_SESSION['card']);
    } else {
        $print_count = 0;
    }
    // card size
    $size = 2;
    // create AJAX request
    echo '<script type="text/javascript" src="'.JS_WEB_ROOT_DIR.'jquery.js"></script>';
    echo '<script type="text/javascript">';
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        if ($print_count == $max_print) {
            $limit_reach = true;
            break;
        }
        if (isset($_SESSION['card'][$itemID])) {
            continue;
        }
        if (!empty($itemID)) {
            $card_text = trim($itemID);
            echo '$.ajax({url: \''.SENAYAN_WEB_ROOT_DIR.'lib/phpbarcode/barcode.php?code='.$card_text.'&encoding='.$sysconf['barcode_encoding'].'&scale='.$size.'&mode=png\', type: \'GET\', error: function() { alert(\'Error creating member card!\'); } });'."\n";
            // add to sessions
            $_SESSION['card'][$itemID] = $itemID;
            $print_count++;
        }
    }
    echo '</script>';
    if (isset($limit_reach)) {
        $msg = str_replace('{max_print}', $max_print, __('Selected items NOT ADDED to print queue. Only {max_print} can be printed at once')); //mfc
        utility::jsAlert($msg);
    } else {
        // update print queue count object
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\''.$print_count.'\');</script>';
        utility::jsAlert(__('Selected items added to print queue'));
    }
    exit();
}

// card pdf download
if (isset($_GET['action']) AND $_GET['action'] == 'print') {
    // check if label session array is available
    if (!isset($_SESSION['card'])) {
        utility::jsAlert(__('There is no data to print!'));
        die();
    }
    if (count($_SESSION['card']) < 1) {
        utility::jsAlert(__('There is no data to print!'));
        die();
    }
    // concat all ID together
    $member_ids = '';
    foreach ($_SESSION['card'] as $id) {
        $member_ids .= '\''.$id.'\',';
    }
    // strip the last comma
    $member_ids = substr_replace($member_ids, '', -1);
    // send query to database
    /*$member_q = $dbs->query('SELECT m.member_name, m.member_id, m.member_image, mt.member_type_name FROM member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE m.member_id IN('.$member_ids.')'); */
	/*
	member_id 	member_name 	member_image member_type_id 	member_address 	member_mail_address 	member_email 	postal_code 	inst_name 	 	 	member_phone 	member_since_date 	register_date 	expire_date 	input_date 	

	*/
	
	$member_q = $dbs->query('SELECT m.member_name, m.member_id, m.member_image, m.member_address, m.member_email, m.inst_name, m.postal_code, m.pin, m.member_phone, m.expire_date, m.register_date, mt.member_type_name FROM member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE m.member_id IN('.$member_ids.')');
    $member_datas = array();
    while ($member_d = $member_q->fetch_assoc()) {
        if ($member_d['member_id']) {
            $member_datas[] = $member_d;
        }
    }

    // include printed settings configuration file
    include SENAYAN_BASE_DIR.'admin'.DIRECTORY_SEPARATOR.'admin_template'.DIRECTORY_SEPARATOR.'printed_settings.inc.php';
    // check for custom template settings
    $custom_settings = SENAYAN_BASE_DIR.'admin'.DIRECTORY_SEPARATOR.$sysconf['admin_template']['dir'].DIRECTORY_SEPARATOR.$sysconf['template']['theme'].DIRECTORY_SEPARATOR.'printed_settings.inc.php';
    if (file_exists($custom_settings)) {
        include $custom_settings;
    }
    // chunk cards array
    $chunked_card_arrays = array_chunk($member_datas, $card_items_per_row);
    // create html ouput
    $html_str = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
    $html_str .= '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Member Card by Jushadi Arman Saz</title>'."\n";
    $html_str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $html_str .= '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" /><meta http-equiv="Expires" content="Fry, 02 Oct 2012 12:00:00 GMT" />';
$html_str .= '<style type="text/css">'."\n";
$html_str .= '*{font:'.$card_bio_font_size.'px Arial, Helvetica, sans-serif;}'."\n";
$html_str .= 'p, li{position: relative;}'."\n";
$html_str .= 'p{margin-bottom: 0px;margin-top: 0px;font-weight: bold;}'."\n";
$html_str .= 'li{margin-bottom: 0px; margin-top: 0px;list-style-type: disc;font-size: '.$card_rules_font_size.'px;}'."\n";
$html_str .= 'ul{margin: 0px;padding-left: 10px;}'."\n";
$html_str .= 'h1{margin: 0px;font-weight: bold;text-align: center;font-size:'.$card_front_header1_font_size.'px;}'."\n";
$html_str .= 'h2{margin: 0px;font-weight: bold;text-align: center;padding-bottom:3px;font-size:'.$card_front_header2_font_size.'px;}'."\n";
$html_str .= 'h3{margin: 0px;font-weight: bold;text-align: center;padding-bottom:3px;font-size:'.$card_back_header2_font_size.'px;}'."\n";
$html_str .= 'hr{margin: 0px;border: 1px solid '.$card_header_color.';position: relative;}'."\n";
$html_str .= '#kontainer_div{z-index:1;position: relative; width:'.($card_box_width*$card_factor).'px; height:'.($card_box_height*$card_factor).'px;margin-bottom:'.($card_items_margin*$card_factor).'px;;border:#CCCCCC solid 1px;-moz-border-radius: 8px;border-radius: 8px;}'."\n";
$html_str .= '#header1_div{z-index:2;position: absolute;left: 61px;top: 4px;width:245px;height: 45px;color:'.$card_header_color.';}'."\n";
$html_str .= '#header2_div{z-index:3;position: absolute;left: 10px;top: 4px;width:300px;height: 43px;color:'.$card_header_color.';}'."\n";
$html_str .= '#rules_div{z-index:4;position: absolute;left: 12px;top: 58px;width:300px;height: 142px;text-align: justify;}'."\n";
$html_str .= '#address_div{z-index:4;position: absolute;left: 9px;top: 175px;width:300px;height: 20px;font-size:'.$card_address_font_size.'px;}'."\n";
$html_str .= '#logo_div{z-index:5;position: absolute;left: 10px;top: 4px;width: 35px;height:35px;}'."\n";
$html_str .= '#photo_blank_div{z-index:5;position: absolute;left: 10px;top:130px;font-size: 7px;text-align: center;border:#cccccc solid 1px;width:'.($card_photo_width*$card_factor).'px; height:'.($card_photo_height*$card_factor).'px;}'."\n";
$html_str .= '#photo_div{z-index:6;position: absolute;left: 10px;top:130px;border:#cccccc solid 1px;width:'.($card_photo_width*$card_factor).'px; height:'.($card_photo_height*$card_factor).'px;}'."\n";
$html_str .= '.bio_div{z-index:7;position: absolute;left: 0px;top:48px;height: 110px;margin: 0px;text-align: justify;}'."\n";
$html_str .= '.bio_alamat{z-index:8;top: 0px;}'."\n";
$html_str .= '.bio_label{ z-index:9;float: left;width: 100px;text-align:left;padding-left: 10px;}'."\n";
$html_str .= '.label_alamat{z-index:10;float: left; width: 200px;margin-bottom:0px;margin-left:3px;}'."\n";
$html_str .= '.stempel_div{z-index:11;position: absolute;left: 100px;top:140px;margin-bottom: 34px;width: 118px;}'."\n";
$html_str .= '.stempel{z-index:12;text-align: left;margin: 0px;}'."\n";
$html_str .= '.lokasi{z-index:13;font-size:8px;margin: 0px;}'."\n";
$html_str .= '.jabatan{z-index:14;font-size:8px;margin: 0px;}'."\n";
$html_str .= '.pejabat{z-index:15;top: 0px;font-size: 8px;margin: 0px;}'."\n";
$html_str .= '.gambar_ttd_div{z-index:16;position: absolute;left: -10px;top: 10px;width:107px;height: 25px;}'."\n";
$html_str .= '.gambar_stempel_div{z-index:17;position: absolute;left:-20px;top: 5px;width: 40px;height: 40px;}'."\n";
$html_str .= '.exp_div{z-index:18;position: absolute;left: 200px;top: 142px;width:110px;height: 12px;font-size: 8px;text-align: right;}'."\n";
$html_str .= '.barcode_div{z-index:19;position: absolute;left: 200px;top: 154px;width:112px;height: 42px;}'."\n";
$html_str .= '</style>'."\n";
    $html_str .= '</head>'."\n";
    $html_str .= '<body>'."\n";
    $html_str .= '<a href="#" onclick="window.print()">Print Again</a><br /><br />'."\n";
    $html_str .= '<table style="margin: 0; padding: 0;" cellspacing="0" cellpadding="0">'."\n";
    // loop the chunked arrays to row
    foreach ($chunked_card_arrays as $card_rows) {
        $html_str .= '<tr>'."\n";
        foreach ($card_rows as $card) {
            $html_str .= '<td valign="top">';
$html_str .= '<div id="kontainer_div">';
$html_str .= '<div><img width="'.($card_box_width*$card_factor).'px" height="'.($card_box_height*$card_factor).'px" src="card1.png" style="border-radius: 8px; -moz-border-radius: 8px;-khtml-border-radius: 8px;-webkit-border-radius: 8px;"></img></div>';
$html_str .= '<div id="logo_div"><img height="40px" width="40px" src="'.$card_logo.'"></img></div>';
$html_str .= '<div id="header1_div">';
$html_str .= '<h1>'.$card_front_header1_text.'</h1>';
$html_str .= '<h2>'.$card_front_header2_text.'</h2></div>';
$html_str .= '<div class="bio_div">';
$html_str .= ''.( $card_include_id_label?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Member ID').'</label><span>: </span>'.$card['member_id'].'</p>'.( $card_include_id_label?'':'-->').'';
$html_str .= ''.( $card_include_name_label?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Member Name').'</label><span>: </span>'.$card['member_name'].'</p>'.( $card_include_name_label?'':'-->').'';
$html_str .= ''.( $card_include_pin_label?'':'<!--').'<p class="bio"><label class="bio_label">'.__('Personal ID Number').'</label><span>: </span>'.$card['pin'].'</p>'.( $card_include_pin_label?'':'-->').'';
$html_str .= ''.( $card_include_inst_label?'':'<!--').'<p class="bio_alamat"><label class="bio_label">'.__('Institution').'</label><span style="float:left">: </span>'.( $card_include_inst_label?'':'-->').'';
$html_str .= ''.( $card_include_inst_label?'':'<!--').'<span class="label_alamat">'.$card['inst_name'].'</span></p>'.( $card_include_inst_label?'':'-->').'';
$html_str .= ''.( $card_include_email_label?'':'<!--').'<p class="bio"><label class="bio_label">'.__('E-mail').'</label><span>: </span>'.$card['member_email'].'</p>'.( $card_include_email_label?'':'-->').'';
$html_str .= ''.( $card_include_address_label?'':'<!--').'<p class="bio_alamat"><label class="bio_label">'.__('Address').' / '.__('Phone Number').'</label><span style="float:left">: </span>'.( $card_include_address_label?'':'-->').'';
$html_str .= ''.( $card_include_address_label?'':'<!--').'<span class="label_alamat">'.$card['member_address'].' / '.$card['member_phone'].'</span></p>'.( $card_include_address_label?'':'-->').'';
$html_str .= '</div>';
$html_str .= '<div id="photo_blank_div"><br /><br />Foto Ukuran:<br />'.$card_photo_width.' X '.$card_photo_height.' cm</div>';
$html_str .= '<div id="photo_div"><img width="'.($card_photo_width*$card_factor).'px" height="'.($card_photo_height*$card_factor).'px" src="'.SENAYAN_WEB_ROOT_DIR.IMAGES_DIR.'/persons/'.$card['member_image'].'"/></img></div>';
$html_str .= ''.( $card_include_expired_label?'':'<!--').'<div class="exp_div">'.__('Expiry Date').' : '.$card['expire_date'].'</div>'.( $card_include_expired_label?'':'-->').'';
$html_str .= ''.( $card_include_barcode_label?'':'<!--').'<div class="barcode_div">';
$html_str .= '<img  width="175px" height="40px" src="'.SENAYAN_WEB_ROOT_DIR.IMAGES_DIR.'/barcodes/'.str_replace(array(' '), '_', $card['member_id']).'.png" style="width: '.$card_barcode_scale.'%; border="0px" /></img></div>'.( $card_include_barcode_label?'':'-->').'';
$html_str .= '<div class="stempel_div">';
$html_str .= '<div class="gambar_stempel_div"><img class="" height="35px" width="35px" src="'.$card_gambar_stempel.'"></img></div>';
$html_str .= '<div class="gambar_ttd_div"><img class="" height="30px" width="100px" src="'.$card_gambar_ttd_stempel.'"></img></div>';
$html_str .= '<p class="stempel lokasi">'.$card_lokasi_stempel.', '.$card['register_date'].'</p><p class="stempel jabatan">'.$card_jabatan_stempel.'</p><br>';
$html_str .= '<p class="stempel pejabat">'.$card_pejabat_stempel.'<br />'.$card_nip_pejabat_stempel.'</p></div></div></td>';
$html_str .= '<td valign="top">';
$html_str .= '<div id="kontainer_div">';
$html_str .= '<div><img height="'.($card_box_height*$card_factor).'px" width="'.($card_box_width*$card_factor).'px" src="card2.png" style="border-radius: 8px; -moz-border-radius: 8px;-khtml-border-radius: 8px;-webkit-border-radius: 8px;"></img></div>';
$html_str .= '<div id="logo_div"><img height="35px" width="35px" src="'.$card_logo.'"></img></div>';
$html_str .= '<div id="header2_div">';
$html_str .= '<h1>'.$card_back_header1_text.'</h1>';
$html_str .= '<h3>'.$card_back_header2_text.'</h3><hr></div>';
$html_str .= '<div id="rules_div">'.$card_rules.'</div>';
$html_str .= '<div id="address_div">'.$card_address.'</div></div>';
$html_str .= '</td>';
        }
        $html_str .= '<tr>'."\n";
    }
    $html_str .= '</table>'."\n";
    $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
    $html_str .= '</body></html>'."\n";
    // unset the session
    unset($_SESSION['card']);
    // write to file
    $print_file_name = 'member_card_gen_print_result_'.strtolower(str_replace(' ', '_', $_SESSION['uname'])).'.html';
    $file_write = @file_put_contents(FILES_UPLOAD_DIR.$print_file_name, $html_str);
    if ($file_write) {
        // update print queue count object
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
        // open result in window
        echo '<script type="text/javascript">top.openHTMLpop(\''.SENAYAN_WEB_ROOT_DIR.FILES_DIR.'/'.$print_file_name.'\', 800, 500, \''.__('Member Card Printing').'\')</script>';
    } else { utility::jsAlert('ERROR! Cards failed to generate, possibly because '.SENAYAN_BASE_DIR.FILES_DIR.' directory is not writable'); }
    exit();
}

?>
<fieldset class="menuBox">
<div class="menuBoxInner printIcon">
	<div class="per_title">
    	<h2><?php echo __('Member Card Printing'); ?></h2>
    </div>
	<div class="sub_section">
		<div class="action_button">
		<a target="blindSubmit" href="<?php echo MODULES_WEB_ROOT_DIR; ?>membership/member_card_generator.php?action=clear" class="notAJAX headerText2" style="color: #f00;"><?php echo __('Clear Print Queue'); ?></a>
		<a target="blindSubmit" href="<?php echo MODULES_WEB_ROOT_DIR; ?>membership/member_card_generator.php?action=print" class="notAJAX headerText2"><?php echo __('Print Member Cards for Selected Data'); ?></a>
		</div>
	    <form name="search" action="<?php echo MODULES_WEB_ROOT_DIR; ?>membership/member_card_generator.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?>:
	    <input type="text" name="keywords" size="30" />
	    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="button" />
	    </form>
    </div>
    <div class="infoBox">
    <?php
    echo __('Maximum').' <font style="color: #f00">'.$max_print.'</font> '.__('records can be printed at once. Currently there is').' '; //mfc
    if (isset($_SESSION['card'])) {
        echo '<font id="queueCount" style="color: #f00">'.count($_SESSION['card']).'</font>';
    } else { echo '<font id="queueCount" style="color: #f00">0</font>'; }
    echo ' '.__('in queue waiting to be printed.'); //mfc
    ?>
    </div>
</div>
</fieldset>
<?php
/* search form end */
/* ITEM LIST */
// table spec
$table_spec = 'member AS m
    LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';
// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('m.member_id',
    'm.member_id AS \''.__('Member ID').'\'',
    'm.member_name AS \''.__('Member Name').'\'',
    'mt.member_type_name AS \''.__('Membership Type').'\'');
$datagrid->setSQLorder('m.last_update DESC');
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keyword = $dbs->escape_string(trim($_GET['keywords']));
    $words = explode(' ', $keyword);
    if (count($words) > 1) {
        $concat_sql = ' (';
        foreach ($words as $word) {
            $concat_sql .= " (m.member_id LIKE '%$word%' OR m.member_name LIKE '%$word%'";
        }
        // remove the last AND
        $concat_sql = substr_replace($concat_sql, '', -3);
        $concat_sql .= ') ';
        $datagrid->setSQLCriteria($concat_sql);
    } else {
        $datagrid->setSQLCriteria("m.member_id LIKE '%$keyword%' OR m.member_name LIKE '%$keyword%'");
    }
}
// set table and table header attributes
$datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Add'));
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg = __('Add to print queue?');
$datagrid->column_width = array('10%', '70%', '15%');
// set checkbox action URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    echo '<div class="infoBox">'.__('Found').' '.$datagrid->num_rows.' '.__('from your search with keyword').': "'.$_GET['keywords'].'"</div>'; //mfc
}
echo $datagrid_result;
/* main content end */
?>
