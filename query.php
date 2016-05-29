<?php
/*
 * Sample script to query coordinates from ulyxes monitoring
 * (httpreader)
 *
 * query parameters:
 *    table: coo/poi/pro/typ/ids coordinates/points/projections/types/point names
 *    pid:   point id 
 *    pname: point name SQL pattern (optional)
 *    ptype: point type SQL pattern (optional)
 *    from:  starting date or datetime (optional)
 *    to:    end date or datetime (optional)
 *
 */

	//error_log(http_build_query($_REQUEST));
	// ANSI DATE
	$date_regexp = '/^[12][0-9]{3}([-\.][0-9]{1,2}){2}\.?( [0-9]{1,2}(:[0-9]{1,2}){2})?$/';
	// US DATE
	$date_regexp1 = '/^([0-9][0-9]?\/){2}[12][0-9]{3}$/';

	include_once("config.php");
	
	if (isset($_REQUEST['table'])) {
		switch  ($_REQUEST['table']) {
			case "coo":
				$tables = "$coo_table, $poi_table, $pro_table, $typ_table";
				$cols = "$poi_table.name, $coo_table.easting, $coo_table.northing, $coo_table.elevation, $pro_table.name, $coo_table.date";
				$where = "$coo_table.point_id = $poi_table.id and $coo_table.epsg = $pro_table.epsg and $poi_table.type_id = $typ_table.id";
				$order = "$poi_table.name, $coo_table.date";
				if (isset($_REQUEST['pname']) && strlen(trim($_REQUEST['pname']))){
					$where .= " and $poi_table.name like '" . $_REQUEST['pname']  . "'";
				}
				if (isset($_REQUEST['ptype']) && strlen(trim($_REQUEST['ptype']))){
					$where .= " and $typ_table.type like '" . $_REQUEST['ptype']  . "'";
				}
				if (isset($_REQUEST['from']) && strlen(trim($_REQUEST['from']))) {
					if (preg_match($date_regexp, $_REQUEST['from']) ||
						preg_match($date_regexp1, $_REQUEST['from'])) {
						$from_d = $_REQUEST['from'];
						$where .= " and $coo_table.date >= '$from_d'";
					} else {
						echo -3;	// date format error
						exit();
					}
				}
				if (isset($_REQUEST['to']) && strlen(trim($_REQUEST['to']))) {
					if (preg_match($date_regexp, $_REQUEST['to']) ||
						preg_match($date_regexp1, $_REQUEST['to'])) {
						$to_d = $_REQUEST['to'];
						$where .= " $coo_table.date <= '$to_d'";
					} else {
						echo -4;	// date format error
						exit();
					}
				}
				break;
			case "poi":
				$tables = "$poi_table, $typ_table";
				$cols = "$poi_table.id, $poi_table.name, $typ_table.type, $poi_table.remark";
				$where = "$poi_table.type_id = $typ_table.id";
				$order = "$poi_table.name";
				if (isset($_REQUEST['pname']) && strlen(trim($_REQUEST['pname']))){
					$where .= " and $poi_table.name like '" . $_REQUEST['pname']  . "'";
				}
				if (isset($_REQUEST['ptype']) && strlen(trim($_REQUEST['ptype']))){
					$where .= " and $typ_table.type like '" . $_REQUEST['ptype']  . "'";
				}
				if (isset($_REQUEST['pid']) && strlen(trim($_REQUEST['pid']))) {
					$where .= " and $poi_table.id=" . $_REQUEST['pid'];
				}
				break;
			case "pro":
				$tables = "$pro_table";
				$cols = "$pro_table.name";
				$order = "$pro_table.name";
				break;
			case "typ":
				$tables = "$typ_table";
				$cols = "$typ_table.type";
				$order = "$typ_table.type";
				break;
			case "ids":
				$tables = "$poi_table";
				$cols = "$poi_table.name";
				$order = "$poi_table.name";
				break;
			default:
				echo -5;	// table invalid
				exit();
		}
	} else {
		echo -6;	// no table
		exit();
	}

	$dbh = new PDO($conn_str);
	if (! $dbh) {
		echo -2;	// connection error
		exit();
	}
	// build query
	$sql = "SELECT $cols FROM $tables";
	if (isset($where)) {
		$sql .= " WHERE $where";
	}
	if (isset($order)) {
		$sql .= " ORDER BY $order";
	}
//echo $sql . "<br>";
	echo "[";
	$sep = " ";
	foreach ($dbh->query($sql, PDO::FETCH_ASSOC) as $row) {
		echo $sep . json_encode($row);
		$sep = ", ";
	}
	echo " ]";
?>
