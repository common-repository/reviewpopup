<?php
/*
 * @author: Arjun Jain ( http://www.arjunjain.info ) 
 * @license: GNU GENERAL PUBLIC LICENSE Version 3
 *
 */

class ManageReviewBox{
	
	private $_dbobject;
	private $_table1;
	private $_table2;
	
	function __construct(){
		global $wpdb;
		$this->_dbobject=$wpdb;
		$this->_table1=$this->_dbobject->prefix."reviewboxsettings";
		$this->_table2=$this->_dbobject->prefix."reviewboxdata";		
	}
	
	/**
	 * Check whether review order free or not
	 * @param int $order
	 * @param int $id
	 * @since 1.0
	 */
	private function CheckReviewOrder($order,$id){
		$query="SELECT Id FROM $this->_table2 WHERE ReviewOrder=$order and ReviewBoxId=$id";
		try {
			$val=$this->_dbobject->get_var($query);
			if($val!=""){
				return true;
			}
			else{
				return false;
			}
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	/**
	 * Check whether review box added or not
	 * @return boolean
	 * @since 1.0
	 */
	public function CheckBoxActivate(){
		$query="SELECT count(*) FROM $this->_table1";
		$count=$this->_dbobject->get_var($query);
		if($count==0){
			return false;
		}
		else{
			return true;
		}
	}
	
	
	public function CheckAnyReview(){
		$query="SELECT count(*) FROM $this->_table2";
		$count=$this->_dbobject->get_var($query);
		if($count==0)
			return false;
		else
			return true;
	}
	/**
	 * Current Version Support only one review box
	 * @return array
	 * @since 1.0
	 */
	public function GetFirstReviewBoxSettings(){
		$query="SELECT * FROM $this->_table1 LIMIT 0,1";
		$result=$this->_dbobject->get_row($query);
		return $result;
	}
	
	/**
	 * Get the review data using  review id
	 * @param int $id
	 * @since 1.0
	 */
	public function GetReviewById($id){
		$query="SELECT * FROM $this->_table2 WHERE Id=$id";
		try{
			return $this->_dbobject->get_row($this->_dbobject->prepare($query));
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	/** 
	 * Used to activate and deactivate review
	 * @param int $action
	 * @param int $rid
	 */
	public function ReviewAction($action,$rid){
		$query="UPDATE $this->_table2 SET IsActive=$action WHERE Id=$rid";
		try{
			return $this->_dbobject->query($this->_dbobject->prepare($query));
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	/**
	 * Delete review using review id
	 * @param int $rid
	 * @since 1.0
	 */
	public function DeleteReview($rid){
		$query="DELETE FROM $this->_table2 WHERE Id=$rid";
		try{
			return $this->_dbobject->query($this->_dbobject->prepare($query));
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}

	/**
	 * Get all review based on active and limit flag
	 * @param int $isActive
	 * @param int $limit
	 * @since 1.0
	 */
	public function GetAllReview($isActive=0,$limit=0){
		$query="SELECT * FROM $this->_table2";
		if($isActive)
			$query .= " WHERE IsActive=1";
		try{
			$query .= " order by ReviewOrder asc";
			if($limit!=0)
				$query .= " LIMIT 0,$limit";
			return $this->_dbobject->get_results($query);
		}
		
		catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	/**
	 * Insert reviews  into database
	 * @param array $postdata
	 * @since 1.0
	 */
	public function InsertReview($postdata){
		if($postdata['reviewid']==0)
			$query='INSERT INTO '.$this->_table2.'(ReviewBoxId,ReviewText,ReviewerName,ReviewOrder,IsActive,SubmitDate) VALUES("'.$postdata['boxid'].'","'.$postdata['reviewtext'].'","'.$postdata['reviewer'].'","'.$postdata['revieworder'].'",1,now())';
		else
			$query='UPDATE '.$this->_table2.' SET ReviewText="'.$postdata['reviewtext'].'",ReviewerName="'.$postdata['reviewer'].'",ReviewOrder="'.$postdata['revieworder'].'" WHERE Id='.$postdata['reviewid'].' and ReviewBoxId='.$postdata['boxid'];
		try{
			$this->_dbobject->query($this->_dbobject->prepare($query));
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	
	/**
	 * Validate Review box settings
	 * @param array $postdata
	 * @return string
	 * @since 1.0
	 */	
	public function ValidateReviewBoxData($postdata){
		if(strlen($postdata['boxtitle'])==0 || strlen($postdata['boxnextlink'])==0 || strlen($postdata['boxnextlinkanchor'])==0 || strlen($postdata['nor'])==0){
			return "Please enter the required field";
		}
		else if(!filter_var($postdata['boxnextlink'],FILTER_VALIDATE_URL,FILTER_FLAG_HOST_REQUIRED)){
			return "Please enter valid next URL";
		}
		else if(strlen($postdata['boxtitle'])>500)
			return "Please enter title within 500 character limit";
		else if(strlen($postdata['boxnextlink'])>1000)
			return "Please enter next url within 1000 character limit";
		else if(strlen($postdata['boxnextlinkanchor'])>500)
			return "Please enter anchor label within 500 character limit";
		else if(strlen($postdata['stylesheeturl'])>1000)
			return "Please enter stylesheet url within 1000 character limit";
		else if(preg_match ("/[^0-9]/",$postdata['nor']))
			return "Please enter valid number of reviews";
		else if(preg_match ("/[^0-9]/",$postdata['nvr']))
			return "Please enter valid number of visible reviews";
		else if($postdata['nvr']>$postdata['nor'])
			return "Number of visible review should be less the total reviews";
		else if(preg_match ("/[^0-9]/",$postdata['rbw']))
			return "Please enter valid width";
		else if(preg_match ("/[^0-9]/",$postdata['rlength']))
			return "Please enter valid length";
		else if((!filter_var($postdata['stylesheeturl'],FILTER_VALIDATE_URL,FILTER_FLAG_HOST_REQUIRED)) || (substr($postdata['stylesheeturl'],"-3")!="css"))
			return "Please enter valid stylesheet url";
		else 
			return "valid";
	}
	
	public function GetReviewFirstId(){
		$query="SELECT Id from $this->_table1 LIMIT 0,1";
		return $this->_dbobject->get_var($query);
	}
	/**
	 * Insert review box data
	 * @since 1.0
	 */
	public function InsertReviewBox($postdata){
		if($postdata['boxid']==0){
			$query='INSERT INTO '.$this->_table1.'(BoxTitle,BoxNextLink,BoxNextLinkAnchor,NumberofReviews,ReviewBoxWidth,MaxReviewLength,Stylesheeturl,NumberofVisibleReviews,Rememberaction,ReviewHTML,IsActive,SubmitDate) VALUES("'.$postdata['boxtitle'].'","'.$postdata['boxnextlink'].'","'.$postdata['boxnextlinkanchor'].'","'.$postdata['nor'].'","'.$postdata['rbw'].'","'.$postdata['rlength'].'","'.$postdata['stylesheeturl'].'","'.$postdata['nvr'].'","'.$postdata['rememberaction'].'","'.$postdata['reviewhtml'].'",1,now())';
		}
		else{
			$query='UPDATE '.$this->_table1.' SET BoxTitle="'.$postdata['boxtitle'].'",BoxNextLink="'.$postdata['boxnextlink'].'",BoxNextLinkAnchor="'.$postdata['boxnextlinkanchor'].'",NumberofReviews="'.$postdata['nor'].'",ReviewBoxWidth="'.$postdata['rbw'].'",MaxReviewLength="'.$postdata['rlength'].'",Stylesheeturl="'.$postdata['stylesheeturl'].'",NumberofVisibleReviews="'.$postdata['nvr'].'",Rememberaction="'.$postdata['rememberaction'].'",ReviewHTML="'.$postdata['reviewhtml'].'",SubmitDate=now() WHERE Id='.$postdata['boxid'];
		}
		try{
			$this->_dbobject->query($this->_dbobject->prepare($query));
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	
	/**
	 * Show list of available order
	 * @param int $id
	 * @param int $isedit
	 * @param int $revieworder
	 * @since 1.0
	 */
	public function GetSelectOrder($id,$isedit,$revieworder){
		$query="SELECT NumberofReviews FROM $this->_table1 WHERE Id={$id}";
		$count=$this->_dbobject->get_var($query);
		$data="";
		for($i=1;$i<=$count;$i++){
			if($isedit && $revieworder==$i)
				$data .="<option value='$i' selected='selected'>$i</option>";
			else if(!$this->CheckReviewOrder($i,$id))
				$data.="<option value='$i'> $i</option>";
		}
		return $data;
	}
	
	
	/**
	 * Create table when plugin activate
	 * @since 1.0
	 */
	public function CreateTable(){
		$sql="";
		if($this->_dbobject->get_var("SHOW TABLES LIKE '{$this->_table1}'") != $this->_table1){
			$sql .="CREATE TABLE $this->_table1 ("
			."Id INT NOT NULL AUTO_INCREMENT,"
			."BoxTitle VARCHAR(500) NOT NULL,"
			."BoxNextLink VARCHAR(1000) NOT NULL,"
			."BoxNextLinkAnchor VARCHAR(500) NOT NULL,"
			."NumberofReviews INT NOT NULL,"
			."ReviewBoxWidth INT NOT NULL,"
			."MaxReviewLength INT NOT NULL," 
			."Stylesheeturl varchar(1000) NOT NULL,"
			."NumberofVisibleReviews INT NOT NULL,"
			."Rememberaction TINYINT NOT NULL,"
			."ReviewHTML TEXT NOT NULL,"
			."IsActive TINYINT(2) NOT NULL,"
			."SubmitDate datetime NOT NULL,"
			."PRIMARY KEY (Id))ENGINE=INNODB;";
		}
		if($this->_dbobject->get_var("SHOW TABLES LIKE '{$this->_table2}'") != $this->_table2){
			$sql .="CREATE TABLE $this->_table2 ("
			."Id INT NOT NULL AUTO_INCREMENT,"
			."ReviewBoxId INT NOT NULL,"
			."ReviewText text NOT NULL,"
			."ReviewerName varchar(100) NOT NULL,"
			."ReviewOrder int NOT NULL,"
			."IsActive TINYINT(2) NOT NULL,"
			."SubmitDate datetime NOT NULL,"
			."FOREIGN KEY (ReviewBoxId) REFERENCES {$this->_table1}(Id) ON UPDATE CASCADE ON DELETE CASCADE,"
			."PRIMARY KEY (Id))ENGINE=INNODB;";
		}
		if ($sql != ""){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}	
	}
}