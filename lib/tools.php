<?php


	/*
	 * Sort array
	 */
	function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
		$sort_col = array();
		foreach ($arr as $key=> $row) {
			$sort_col[$key] = $row[$col];
		}

		array_multisort($sort_col, $dir, $arr);
	}
  
?>