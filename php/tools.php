<?php
function print_var_name($var) {
	foreach($GLOBALS as $var_name => $value) {
		if ($value === $var) {
			return $var_name;
		}
	}
	return false;
}

function print_exception($name, $message) {
	print_r("[".$name."] : ".$message);
	die();
}
?>