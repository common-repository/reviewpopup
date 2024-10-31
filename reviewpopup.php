<?php
/*
Plugin Name: reviewpopup
Plugin URI:  
Description: Display review pop up box on home page 
Author: Arjun Jain
Author URI: http://www.arjunjain.info
Version: 1.0
*/

global $reviewpopup_db_version;
$reviewpopup_db_version="1.0";

add_action('admin_menu','reviewpopup');

function reviewpopup(){
	add_menu_page("ReviewPopUp- Display review box to user","ReviewPopUp",'administrator','reviewpopup','ReviewPopUpAdmin');
	add_submenu_page('reviewpopup','Add New Review','Add New Review','administrator','add-new-review','ReviewPopUpAdminAddNewReview');
	add_submenu_page('reviewpopup','Main Settings','Settings','administrator','reviewpopup-settings','ReviewPopUpAdminMainSettings');
}

function ReviewPopUpAdminAddNewReview(){
	require_once 'includes/ManageReviewBox.php';
	$mrbObj=new ManageReviewBox();
	$errormsg='';
	if($mrbObj->CheckBoxActivate()){
		$rid=0;
		$boxid=0;
		$reviewtext="";
		$reviewer="";
		$revieworder="";
		$isedit=0;
		$boxsetting=$mrbObj->GetFirstReviewBoxSettings();
		$buttontext="Add Review";
		if(isset($_GET['edit']) && isset($_GET['rid'])){
			$rid=$_GET['rid'];
			$result=$mrbObj->GetReviewById($rid);
			if(sizeof($result)>0){
				$rid=$result->Id;
				$reviewtext=$result->ReviewText;
				$revieworder=$result->ReviewOrder;
				$reviewer=$result->ReviewerName;
			}
			$isedit=1;
			$buttontext="Update Review";
		}
		if(isset($_POST['issubmit'])){
			$postdata['reviewid']=$_POST['reviewid'];
			$postdata['boxid']=$_POST['boxid'];
			$postdata['reviewtext']=trim($_POST['txtreview']);
			$postdata['revieworder']=trim($_POST['revieworder']);
			$postdata['reviewer']=trim($_POST['reviewer']);
			if($postdata['reviewtext']==""){
				$errormsg='<div class="error"><p>Please enter Review</p></div>';
			}
			else if(strlen($postdata['reviewtext'])>$boxsetting->MaxReviewLength)
			{
				$errormsg="<div class='error'><p>Review length should be less than {$boxsetting->MaxReviewLength}</p></div>";
				$reviewtext=stripcslashes($postdata['reviewtext']);
				$reviewer=stripcslashes($postdata['reviewer']);
			}
			else if($postdata['revieworder']==0){
				$errormsg='<div class="error"><p>Please select correct order</div>';
				$reviewtext=stripslashes($postdata['reviewtext']);
				$reviewer=stripcslashes($postdata['reviewer']);
			}
			else if(strlen($postdata['reviewer'])>100){
				$errormsg='<div class="error"><p>The length of reviewer name should be less than 100 characters</p></div>';
				$reviewtext=stripslashes($postdata['reviewtext']);
				$reviewer=stripcslashes($postdata['reviewer']);
			}
			else{
				$mrbObj->InsertReview($postdata);
				if($postdata['reviewid']==0)
				$errormsg='<div class="updated"><p>Review Added</p></div>';
				else
				$errormsg='<div class="updated"><p>Review Updated</p></div>';
			}
			
		}
	$js="<script type='text/javascript'>
	function limitText(em, limitNum) {
	if (em.value.length > limitNum) {
		em.value = em.value.substring(0, limitNum);
	} else {
		document.getElementById('char_left').innerHTML = (limitNum - em.value.length) + ' characters left';
	}
}		
	</script>";	
	$data='<div class="wrap">
	   	  <h2>Add New Review</h2>'.$errormsg.'
		  <form action="'.$_SERVER['PHP_SELF'].'?page=add-new-review" method="post">
  	<input type="hidden" name="reviewid" value="'.$rid.'" />
  	<input type="hidden" name="boxid" value="'.$boxsetting->Id.'" />
		  		<table class="form-table" style="width:70%;">
		  			<tbody>
		  				<tr valign="top">
		  					<th scope="row" style="width:50%"><label for="txtreview" >Review* (Maximum '.$boxsetting->MaxReviewLength.' characters allowed)</label></th>
		  					<th><textarea id="txtreview" class="large-text code" cols="50"  rows="10" name="txtreview" onkeyup="limitText(this,'.$boxsetting->MaxReviewLength.');">'.$reviewtext.'</textarea>
		  					<span class="description" id="char_left"></span>
		  					</th>
		  				</tr>
		  				<tr>
		  					<th scope="row" style="width:20%"><label for="reviewer" >Reviewer Name</label></th>
		  					<th><input type="text" id="reviewer" name="reviewer" value="'.$reviewer.'" class="regular-text" /></th>
		  				</tr>
		  				<tr>
		  					<th scope="row"><label for="revieworder">Display Order*</label></th>
							<th>
								<select name="revieworder"><option value=0>--Select--</option>';
	$data .=$mrbObj->GetSelectOrder($boxsetting->Id,$isedit,$revieworder);
	$data .='					</select>
							</th>
		  				</tr>
		  			</tbody>
		  		</table>
		  		<p class="submit"><input id="submit" class="button-primary" type="submit" value="'.$buttontext.'" name="issubmit"></p>
		  </form>
	</div>';
	}
	else{
		$data='<div class="wrap">
		<h2>ReviewPopUp</h2>
		<p>Please go to <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=reviewpopup-settings">Settings</a> to create new popup review box</p>
		</div>';
	}
	echo $js.$data;
}


function ReviewPopUpAdminMainSettings(){
	require_once 'includes/ManageReviewBox.php';
	$mrbObj=new ManageReviewBox();	
	$reviewboxdata="";
	$errormsg="";
	$postdata['boxid']=0;
	$postdata['rememberaction']=1;
	$reviewhtml="<div class='reviewtext'>{#reviewtext}<div class='reviewername'>{#reviewer}</div></div>";
	$postdata['reviewhtml']=$reviewhtml;	
	if(isset($_POST['issubmit'])){
		$postdata['boxid']=$_POST['reviewboxid'];
		$postdata['boxtitle']=trim($_POST['reviewboxtitle']);
		$postdata['boxnextlink']=trim($_POST['reviewboxnexturl']);
		$postdata['boxnextlinkanchor']=trim($_POST['reviewboxnexturlanchor']);
		$postdata['nor']=trim($_POST['reviewlimit']);
		$postdata['nvr']=trim($_POST['visiblereviewlimit']);
		$postdata['rbw']=trim($_POST['reviewboxwidth']);
		$postdata['reviewhtml']=trim($_POST['reviewhtml']);
		if($postdata['reviewhtml']=="")
			$postdata['reviewhtml']=$reviewhtml;
		if(isset($_POST['rememberaction']))
			$postdata['rememberaction']=$_POST['rememberaction'];
		else
			$postdata['rememberaction']=0;
		$postdata['stylesheeturl']=trim($_POST['stylesheeturl']);
		if($postdata['nvr']=="")
			$postdata['nvr']=0;
		if($postdata['rbw']=="")
			$postdata['rbw']='700';
		$postdata['rlength']=trim($_POST['reviewlength']);
		if($postdata['rlength']=="")
			$postdata['rlength']='300';	
		if($postdata['stylesheeturl']=="")
			$postdata['stylesheeturl']='http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css';
		$error=$mrbObj->ValidateReviewBoxData($postdata);
		if($error=="valid"){
			$mrbObj->InsertReviewBox($postdata);
			if($postdata['boxid']==0){
				$postdata['boxid']=$mrbObj->GetReviewFirstId();				
				$errormsg='<div class="updated"><p>Review box settings added</p></div>';
			}
			else
				$errormsg='<div class="updated"><p>Review box settings updated</p></div>';
		}
		else{
			$errormsg='<div class="error"><p>'.$error.'</p></div>';
		}
		$buttontext="Update Review Box Settings";
	}
	else if($mrbObj->CheckBoxActivate()){
		$buttontext="Update Review Box Settings";
		$reviewboxdata=$mrbObj->GetFirstReviewBoxSettings();
		$postdata['boxid']=$reviewboxdata->Id;
		$postdata['boxtitle']=$reviewboxdata->BoxTitle;
		$postdata['boxnextlink']=$reviewboxdata->BoxNextLink;
		$postdata['boxnextlinkanchor']=$reviewboxdata->BoxNextLinkAnchor;
		$postdata['nor']=$reviewboxdata->NumberofReviews;
		$postdata['nvr']=$reviewboxdata->NumberofVisibleReviews;
		$postdata['rbw']=$reviewboxdata->ReviewBoxWidth;
		$postdata['rlength']=$reviewboxdata->MaxReviewLength;
		$postdata['stylesheeturl']=$reviewboxdata->Stylesheeturl;
		$postdata['rememberaction']=$reviewboxdata->Rememberaction;
		$postdata['reviewhtml']=$reviewboxdata->ReviewHTML;
		$buttontext="Add Review Box Settings";
		$buttontext="Update Review Box Settings";
	}
	else {
		$buttontext="Add Review Box Settings";
	}
	$checkedtext=($postdata['rememberaction'] ? "checked":"unchecked");
	$data='<div class="wrap">
				<h2>Review PopUp Box Settings</h2>'.$errormsg.'
				<form action="" method="post">
					<input type="hidden" name="reviewboxid" value="'.@$postdata['boxid'].'">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="reviewboxtitle">Review Box Title*</label></th>
								<th><input id="reviewboxtitle" class="regular-text" type="text" value="'.@$postdata['boxtitle'].'" name="reviewboxtitle"></th>
							</tr>
							<tr>
								<th scope="row"><label for="reviewboxnexturl">Next URL*</label></th>
								<th><input id="reviewboxnexturl" class="regular-text" type="text" value="'.@$postdata['boxnextlink'].'" name="reviewboxnexturl"></th>
							</tr>
							<tr>
								<th scope="row"><label for="reviewboxnexturlanchor">Anchor label*</label></th>
								<th><input id="reviewboxnexturlanchor"  class="regular-text" type="text" value="'.@$postdata['boxnextlinkanchor'].'" name="reviewboxnexturlanchor"></th>
							</tr>
							<tr>
								<th scope="row"><label for="reviewlimit">Maximum number of reviews*</label></th>
								<th><input id="reviewlimit" class="small-text"  name="reviewlimit" type="text" value="'.@$postdata['nor'].'" /> 
							</tr>
							<tr>
								<th scope="row"><label for="visiblereviewlimit">Maximum number of reviews visible on Review box</label></th>
								<th><input id="visiblereviewlimit" class="small-text"  name="visiblereviewlimit" type="text" value="'.@$postdata['nvr'].'" /><p class="description">(Set 0 to show all review)</p> 
							</tr>
							<tr>
								<th scope="row"><label for="rememberaction">Remember user action for 1 day</label></th>
								<th><input id="rememberaction" value="1" name="rememberaction" type="checkbox" '.$checkedtext.' /> <small>(uncheck if you want display review popup box every time same user visit home page)</small> 
							</tr>
							<tr>
								<th scope="row"><label for="reviewboxwidth">Review popup box width</label></th>
								<th><input id="reviewboxwidth" class="small-text"  name="reviewboxwidth" type="text" value="'.@$postdata['rbw'].'" />px <p class="description">(Default 700px)</p> 
							</tr>
							<tr>
								<th scope="row"><label for="reviewlength">Max. Review Text length</label></th>
								<th><input id="reviewlength" class="small-text"  name="reviewlength" type="text" value="'.@$postdata['rlength'].'" />characters <p class="description">(Default 300)</p> 
							</tr>
							<tr>
								<th scope="row"><label for="stylesheeturl">Stylesheet URL</label></th>
								<th><input id="stylesheeturl" class="regular-text"  name="stylesheeturl" type="text" value="'.@$postdata['stylesheeturl'].'" /> <p class="description">Ex: http://example.com/theme.css<br />(Default http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/smoothness/jquery-ui.css) <br /> For theme customization please <a href="http://jqueryui.com/themeroller/" target="_blank">click here</a> For theme path <a href="http://blog.jqueryui.com/" target="_blank">click here</a></p> 
							</tr>
							<tr valign="top">
		  						<th scope="row"><label for="reviewhtml" >Review HTML*</label></th>
		  						<th><textarea id="reviewhtml" class="large-text code" cols="20"  rows="6" name="reviewhtml">'.stripslashes(@$postdata['reviewhtml']).'</textarea><p class="description"><a href="#" onclick="return setdefaulthtml();">Set Default</a></p></th>
		  					</tr>
		  				</tbody>
					</table>
					<p class="submit"><input id="submit" class="button-primary" type="submit" value="'.$buttontext.'" name="issubmit"></p>
				</form>
		   </div>';
	$js=' <script type="text/javascript">
			function setdefaulthtml(){
				var enteredText="'.$reviewhtml.'";
				var reviewhtml=enteredText.replace(/\n/g, "<br />");
				document.getElementById("reviewhtml").value=reviewhtml;
				return false;
			}
		  </script> ';
	echo $data.$js;
}


function ReviewPopUpAdmin(){
	require_once 'includes/ManageReviewBox.php';
	$mrbObj=new ManageReviewBox();
	if($mrbObj->CheckBoxActivate()){
	if(isset($_GET['edit'])&&isset($_GET['action'])&&isset($_GET['rid'])){
		if($_GET['action']=="deactivate"){
			$mrbObj->ReviewAction(0,$_GET['rid']);
		}
		else if($_GET['action']=="activate"){
			$mrbObj->ReviewAction(1,$_GET['rid']);
		}
		else if($_GET['action']=="delete"){
			$mrbObj->DeleteReview($_GET['rid']);
		}
	}	
		
	$allreview=$mrbObj->GetAllReview();
	$data ='<div class="wrap">
				<h2>ReviewPopUp<a class="add-new-h2" href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=add-new-review">Add New Review</a></h2>';
	$data .=   '<table class="wp-list-table widefat fixed posts" cellspacing="0">
					<thead>
						<tr>
							<th class="manage-column column-tags" scope="col">Review Display Order</th>
							<th class="manage-column column-title" scope="col">Review</th>
							<th class="manage-column column-author" scope="col">Reviewer Name</th>
							<th class="manage-column column-date" scope="col">Date</th>
						</tr>
					</thead><tbody id="the-list">';
	foreach ($allreview as $review){
	$data .=		'<tr class="" valign="top">
							<td class="tags column-tags"><a href="'.$_SERVER['PHP_SELF'].'?page=add-new-review&edit=1&rid='.$review->Id.'">'.$review->ReviewOrder.'</a></td>
							<td class="post-title page-title column-title"><strong>
								<a class="row-title" title="Edit '.substr($review->ReviewText,0,130).'" href="'.$_SERVER['PHP_SELF'].'?page=add-new-review&edit=1&rid='.$review->Id.'">'.substr($review->ReviewText,0,130).'..</a></strong>
								<div class="row-actions">
									<span class="edit"><a href="'.$_SERVER['PHP_SELF'].'?page=add-new-review&edit=1&rid='.$review->Id.'">Edit</a> | </span>';
	if($review->IsActive)
	$data .=						'<span class="activate"><a href="'.$_SERVER['PHP_SELF'].'?page=reviewpopup&edit=1&action=deactivate&rid='.$review->Id.'">Deactivate</a> | </span>';
	else 
	$data .=						'<span class="activate"><a href="'.$_SERVER['PHP_SELF'].'?page=reviewpopup&edit=1&action=activate&rid='.$review->Id.'">Activate</a> | </span>';
	$data .=						'<span class="trash"><a href="'.$_SERVER['PHP_SELF'].'?page=reviewpopup&edit=1&action=delete&rid='.$review->Id.'">Delete</a></span>
								</div>
							</td>
							<td class="author column-author"><a href="'.$_SERVER['PHP_SELF'].'?page=add-new-review&edit=1&rid='.$review->Id.'">'.$review->ReviewerName.'</a></td>
							<td class="date column-date"><abbr title="'.$review->SubmitDate.'">'.date('Y/m/d',strtotime($review->SubmitDate)).'</abbr></td>
						</tr>';					
	}
	$data .=		'</tbody>
					<tfoot>
						<tr>
							<th class="manage-column column-tags" style="" scope="col"></th>
							<th class="manage-column column-title" style="text-align:right !important;" scope="col">Find Bug or suggest new feature please <a target="_blank" href="http://www.arjunjain.info/contact">click here</a></th>
							<th class="manage-column column-author"></th>
							<th class="manage-column column-date" style="" scope="col"></th>
						</tr>
					</tfoot>
				</table>
			</div>';
	
	}
	else{
		$data='<div class="wrap">
					<h2>ReviewPopUp</h2>
					<p>Please go to <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=reviewpopup-settings">Settings</a> to create new popup review box</p>
			   </div>';		
	}
	echo $data;
}


add_action('wp_enqueue_scripts','forcefullyaddscriptinheader',1);
add_action("wp_head","pageheaderjs");
add_action("wp_footer","pagefooterhtml");
function forcefullyaddscriptinheader(){
	wp_enqueue_script('jquery');
	wp_deregister_script('jquery-ui-core');
	wp_register_script('jquery-ui-core','http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js',array('jquery'));
	wp_enqueue_script('jquery-ui-core');
}

function pagefooterhtml(){
	require_once 'includes/ManageReviewBox.php';
	$mrbObj=new ManageReviewBox();
	
	if($mrbObj->CheckBoxActivate()  && $mrbObj->CheckAnyReview()){
		if(is_front_page() || is_home()){
			$reviewbox=$mrbObj->GetFirstReviewBoxSettings();
			$allreview=$mrbObj->GetAllReview(1,$reviewbox->NumberofVisibleReviews);
			$html ="<style> .reviewtext{ padding:5px 5px;} .reviewername{display:block;text-align:right;} .reviewhigh{background-color:#E6E6E6;} .buttonlink{text-align:center;margin-top:15px;}</style>";
			$html .="<div id='onpageload' style='display:none'>";
			
			$count=0;
			foreach ($allreview as $review){
					$rhtml=$reviewbox->ReviewHTML;
					$count++;
					if(dividebytwo($count,2)){
						$rhtml=str_replace("class='reviewtext'","class='reviewtext reviewhigh'",$rhtml);
					}
					$temstr=str_replace('{#reviewtext}',$review->ReviewText,$rhtml);
					$html .= str_replace('{#reviewer}', $review->ReviewerName,$temstr);
			}
			$onclicktext="";
			if($reviewbox->Rememberaction)
				$onclicktext='onclick=\'setreviewCookie("reviewpop_cookie",1,1);\'';
	
			$html .="<div class='buttonlink'><p><a id='boxnextlink' href='".$reviewbox->BoxNextLink."'  $onclicktext >$reviewbox->BoxNextLinkAnchor</a></p></div></div>";
		}	
		echo $html;
	}
	
}

function dividebytwo($num1,$num2){
	if($num1 % $num2==0)
		return true;
	else 
		return false;
}
function pageheaderjs(){
	require_once 'includes/ManageReviewBox.php';
	$mrbObj=new ManageReviewBox();
	if($mrbObj->CheckBoxActivate() && $mrbObj->CheckAnyReview()){
		if(is_front_page() || is_home()){
			$reviewbox=$mrbObj->GetFirstReviewBoxSettings();
				$script='<link rel="stylesheet" href="'.$reviewbox->Stylesheeturl.'"/>
						<script type="text/javascript">
							function setreviewCookie(c_name,value,exdays)
							{
								var exdate=new Date();
								exdate.setDate(exdate.getDate() + exdays);
								var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
								document.cookie=c_name + "=" + c_value;
							}
							function getCookie(c_name)
							{
								var i,x,y,ARRcookies=document.cookie.split(";");
								for (i=0;i<ARRcookies.length;i++)
								{
  									x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
 									y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  									x=x.replace(/^\s+|\s+$/g,"");
  									if (x==c_name)
    								{
   	 									return unescape(y);
    								}
  								}
							}
							if(getCookie("reviewpop_cookie")!=1){
								jQuery(document).ready(function(){
									jQuery("#boxnextlink").button();
									jQuery("#onpageload").dialog({ 
										modal:true,
										closeOnEscape: false,
										draggable: false,
										width:'.$reviewbox->ReviewBoxWidth.',
										zIndex:10000,
										title:"'.$reviewbox->BoxTitle.'"
									});	
								});
							}
					</script>';
			echo $script;
		}
	}
}

/**
 * For wordpress multisite setup
 */

register_activation_hook( __FILE__, "reviewpopup_activate" );
function reviewpopup_activate(){
	global $wpdb;
	global $reviewpopup_db_version;
	require_once 'includes/ManageReviewBox.php';
	$reviewObj=new ManageReviewBox();
	if (function_exists('is_multisite') && is_multisite()) {
		if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				$reviewObj->CreateTable();
			}
			switch_to_blog($old_blog);
			return;
		}
		else
			$reviewObj->CreateTable();
	}
	else
		$reviewObj->CreateTable();
	add_option("reviewpopup_db_version", $reviewpopup_db_version);
}

/**
 * 
 * Add Settings tab with plugin action
 */
add_filter("plugin_action_links",'reviewpopupsettingslink','administrator',2);
function reviewpopupsettingslink($link,$file){
	static $this_plugin;
	if (!$this_plugin) {
	   $this_plugin = plugin_basename(__FILE__);
	}
	if ($file == $this_plugin) {
	   $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=reviewpopup-settings">Settings</a>';
	   array_unshift($link, $settings_link);
	}
	return $link;
}
