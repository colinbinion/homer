<?php
/*
 * HOMER Web Interface
 * Homer's REST API (Json) v0.1.2
 *
 * Copyright (C) 2011-2012 Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Copyright (C) 2011-2012 Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * The Initial Developers of the Original Code are
 *
 * Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
*/

/* MAIN CLASS modules */
define(_HOMEREXEC, 1);

/* NO AUTH for local calls */
if($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"]) define(SKIPAUTH, 1);
/* END */ 

set_include_path('../');

include_once("../class/index.php");

date_default_timezone_set(CFLOW_TIMEZONE);

$task=($_GET['task']);

switch ($task) {

        case 'session':
                getSession();
                break;

        case 'msg':
                getMsg();
                break;

        case 'last':
                getLast();
                break;

        case 'search':
                getSearch();
                break;

	case 'debug':
		getVars();
		break;

        case 'statsua':
                getStatsUA();
                break;

        case 'statscount':
                getStatsCount();
                break;

	case '';
		echo 'NULL';
		break;

}

function getVars() {
	// debug-only
	print_r($_GET);
}

function getSession() {

	// minimal query
	if(isset($_GET['cid'])) {
 
	//Set our variables
	$cid = intval($_GET['cid']);
	$cid2 = intval($_GET['cid2']);
	$limit = ($_GET['limit']);
	
	if(!isset($limit)) {
                $limit = 100;
        }

	$setdate=setDate();
	
	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {

                $query = "SELECT * "
                        ."\n FROM ".HOMER_TABLE
                        ."\n WHERE callid=".$cid
			 ."\n AND ".$setdate
			."\n ORDER BY id DESC"
			." limit ".$limit;

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('session' => $rows));
	 
	// Output the result
	echo $output;
 
  	}

}

function getMsg() {

	// minimal query
	if(isset($_GET['id'])) {
 
	//Set our variables
	$id = intval($_GET['id']);
	
	$setdate=setDate();
	
	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {

                $query = "SELECT * "
                        ."\n FROM ".HOMER_TABLE
                        ."\n WHERE id=".$id
			 ." AND ".$setdate
			." limit 1";

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('msg' => $rows));
	 
	// Output the result
	echo $output;
 
  	}

}

function getLast() {

	// minimal query
	if(isset($_GET['limit'])) {
 
	//Set our variables
	$limit = ($_GET['limit']);
	$method = ($_GET['method']);
	
	if(!isset($limit)) {
                $limit = 10;
        }

	$setdate=setDate();
	$where .= $setdate;

	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {

                $query = "SELECT * "
                        ."\n FROM ".HOMER_TABLE
                        ."\n WHERE ".$where
			."\n ORDER BY id DESC"
			." limit ".$limit;
			//." limit 1";

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('last' => $rows));
	 
	// Output the result
	echo $output;
 
  	}

}

function getSearch() {

	// minimal query
	if(isset($_GET['field'])) {
 
	//Set our variables
	$field = ($_GET['field']);
	$value = ($_GET['value']);
	$limit = ($_GET['limit']);
	
	if(!isset($limit)) {
                $limit = 10;
        }

	$setdate=setDate();
	$where .= $setdate;

	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {

                $query = "SELECT * "
                        ."\n FROM ".HOMER_TABLE
                        ."\n WHERE ".$field." = '".$value."' "
			."\n ORDER BY id DESC"
			." limit ".$limit;

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('session' => $rows));
	 
	// Output the result
	echo $output;
 
  	}

}



function setDate() {

	// Set Date & Time (!!WORK IN PROGRESS!!)

	$qfd = ($_GET['fd']); $qtd = ($_GET['td']);
	$qft = ($_GET['ft']); $qtt = ($_GET['tt']);

	// If no date/time, default to today
        if(!isset($qfd)) {
                $qfd =  date("Y-m-d");
        }
        $fd = date("Y-m-d", strtotime($qfd));
        if(isset($qtd)) {
        $td = date("Y-m-d", strtotime($qtd));
        } else {
        $td = date("Y-m-d", strtotime($qfd));
        }

        //$setdate = "(`date` >= '$fd' AND `date` <= '$td')";
        $setdate = "`date` >= '$fd'";
	return $setdate;

}


function getStatsUA() {
         
	//Set our variables
	$method = ($_GET['method']);
	$hours = ($_GET['hours']);
	$limit = ($_GET['limit']);
	if(!isset($method)) {
                $method =  "INVITE";
        }
	if(!isset($hours)) {
                $hours =  24;
        }
	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {

	$query = "SELECT useragent, sum(total) as count from stats_useragent "
		   ."where `from_date` > UNIX_TIMESTAMP(CURDATE() - INTERVAL ".$hours." HOUR) "
		   ."AND method='".$method."' group by useragent order by count DESC";
	if($limit) {$query .= " limit ".$limit; }

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('ua' => $rows));
	 
	// Output the result
	echo $output;


}

function getStatsCount() {
         
	//Set our variables
	$method = ($_GET['method']);
	$hours = ($_GET['hours']);
	$measure = ($_GET['measure']);
	if(!isset($method)||$method!="INVITE" && $method!="REGISTER" && $method!="CURRENT") {
                $method =  "ALL";
        }
	if(!isset($hours)) {
                $hours =  24;
        }

	// Proceed with Query
        global $mynodeshost, $db;
        $option = array(); //prevent problems
        if($db->dbconnect_homer(HOMER_HOST)) {
	// Methods & According Response Formats/Vars

	if ($method == "INVITE") {
		if(!isset($measure)) {
		$query = "SELECT from_date,total,asr,ner from stats_method "
		   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
		   ."AND method='".$method."' AND total !=0 order by id";
		} else {
		$query = "SELECT from_date,sum(total),avg(asr),avg(ner),sum(completed),sum(uncompleted) from stats_method "
                   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                   ."AND method='".$method."' AND total !=0 order by id";
		}

	} else if ($method == "REGISTER") {

		 if(!isset($measure)) {
		$query = "SELECT from_date,total,auth,completed,uncompleted from stats_method "
                   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                   ."AND method='".$method."' AND total !=0 order by id";
		} else {
		$query = "SELECT from_date,avg(total),avg(auth),sum(completed),sum(uncompleted) from stats_method "
                   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                   ."AND method='".$method."' AND total !=0 order by id";
		}

	} else if ($method == "CURRENT") {
        $query = "SELECT from_date,total from stats_method "
                   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                   ."AND method='".$method."' AND total !=0 order by id";

	 } else if ($method == "ALL") {
	 if(!isset($measure)) {
        	   $query = "SELECT from_date,total from stats_method "
                	   ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                	   ."AND method='".$method."' AND total !=0 order by id DESC limit 1";
		} else {
		   $query = "SELECT min(from_date),max(to_date),avg(asr),avg(ner),avg(total),avg(completed) from stats_method "
                           ."where `from_date` > DATE_SUB(NOW(), INTERVAL ".$hours." HOUR) "
                           ."AND method='INVITE' AND total !=0 order by id DESC";
		}
	}

                $rows = $db->loadObjectList($query);
        }

	// Prepare JSON reply
	$output = json_encode(array('stats' => $rows));
	 
	// Output the result
	echo $output;


}


?>
