<?php
///// IN MODEL //////////////////

	public function get_test_pager($page, $num_results) {

		// instantiate DBPager object by passing your query
		$pager = $this->db->get_pager(
			"select * from tasks where status < 3 order by suspense_date"
		);

		// override defaults
		$pager->set_rows($num_results);
		$pager->set_page($page);

		return $pager;
	}

///// IN CONTROLLER //////////////////

	public function index() {

		// fetch GET parameters, passing in a default if they don't exist
		$page = $this->request->param('page', 1);
		$rows = $this->request->param('num_results', 100);

		// call MODEL function that returns the DBPager object
		$pager = $this->model('issues')->get_test_pager($page, $rows);

		// pass DBPager object to VIEW
		$this->view->display("test_pager.php", array('pager' => $pager));
	}

///// IN VIEW ////////////////// ?>

<p><?=$pager->results_string?></p>
<p>Results per page: <?=$pager->get_results_select()?></p>
<p><?=$pager->get_page_links()?></p>

<table>
	<tr>
		<th>Task ID</th>
		<th>Origin</th>
		<th>Task Agent</th>
		<th>Subject</th>
	</tr>
	<!-- DBPager::get_array() executes the query and returns the results
	     in an associative array -->
	<? foreach($pager->get_array() as $row): ?>
	<tr>
		<td><?=$row['task_id']?></td>
		<td><?=$row['originator']?></td>
		<td><?=$row['task_agent']?></td>
		<td><?=$row['subject']?></td>
	</tr>
	<? endforeach ?>
</table>

<!-- must be checked after $pager->get_array() -->
<p> Query execute time: <?=$pager->query_time?> sec.</p>
