<?php

class DBPager {

	protected $_stmt;

	function __construct($db, $sql, $vars) {
		$this->db = $db;
		$this->sql = $sql;

		$this->records = $this->db->getval("select count(*) from ($this->sql)", $vars);

		$this->rows = 25;
		$this->page = 1;
		$this->pages = ceil($this->records / $this->rows);		

		$this->calc_limits();

		$this->vars = $vars;
	}

	function set_rows($rows) {
		if(is_numeric($rows)) {
			$this->rows = $rows;
			$this->pages = ceil($this->records / $this->rows);		

			$this->calc_limits();
		}
	}

	function calc_limits() {
		$this->lower_limit = ($this->page - 1) * $this->rows;
		$this->upper_limit = $this->page * $this->rows;

		$this->results_string = 
			"Displaying " . ($this->lower_limit + 1) . 
			" - $this->upper_limit out of $this->records results.";

	}

	function set_page($page) {
		if(is_numeric($page)) {
			$this->page = $page;

			$this->calc_limits();
		}
	}

	function get_array() {

		$this->executed_sql = "
			select * from 
			( select a.*, ROWNUM rnum from 
			  ( $this->sql ) a 
			  where ROWNUM <= $this->upper_limit )
			where rnum  > $this->lower_limit
		";

		$time_start = microtime(true);
		$results = $this->db->pquery_array($this->executed_sql, $this->vars);

		$this->query_time = sprintf("%0.2f", microtime(true) - $time_start);

		return $results;

	}

	function get_results_select($var_name='num_results') {
		$select = "
		<select 
			onchange='document.location=\"?$var_name=\"+this.value'
			class='_snow_pager_select'>
		";
		foreach(array(5, 10, 25, 50, 100) as $page_set) {
			$select .= "
			<option value='$page_set'".
				(($page_set == $this->rows)?" selected='selected'":"").
				">$page_set</option>";
		}
		$select .= "</select>";

		return $select;
	}

	function get_page_links($page_var='page', $results_var='num_results') {
		
		$query = $_SERVER['QUERY_STRING'];
		$query = preg_replace('/page=\d+&*/', '', $query);
		$query = preg_replace('/num_results=\d+&*/', '', $query);

		$links = "<div class='_snow_pager_controls'>";

		if($this->page != 1) {
			$links .= "
				<a href='?page=1&num_results=$this->rows&$query' 
				  class='_snow_pager_first'>&lt;&lt;</a> 
				<a href='?page=".($this->page-1)."&num_results=$this->rows&$query' 
				  class='_snow_pager_prev'>&lt;</a>
		 	";
		}

		// compensate for a high number of pages
		if($this->pages < 25) {
			$range = range(1, $this->pages);
		} else {
			if($this->page > 12) {
				if($this->page > $this->pages - 12) {
					$range = range($this->pages - 25, $this->pages);
				} else {
					$range = range($this->page - 12, $this->page + 13);
				}
			} else {
				$range = range(1, 25);
			}
		}

		foreach($range as $num) {
			if ($num == $this->page) {
				$links .= "<strong class='_snow_pager_current'>$num</strong>";
		 	} else { 
				$links .= "
					<a href='?page=$num&num_results=$this->rows&$query' 
					  class='_snow_pager_num'>$num</a> 
				";
		 	}
		} 
		if($this->page < $this->pages) {
			$links .= "
			<a href='?page=".($this->page+1)."&num_results=$this->rows&$query' 
			  class='_snow_pager_next'>&gt;</a> 
			<a href='?page=$this->pages&num_results=$this->rows&$query' 
			  class='_snow_pager_last'>&gt;&gt;</a>
			";
		} 

		$links .= "</div>";

		return $links;
	}

}
