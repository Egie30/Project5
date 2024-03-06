<?php
function pagination($query, $recordPerPage = 10, $pagingBefore = 3, $pagingAfter = 3) {
	
	$page = 1;
	$uriQueries = $_GET;

	if (isset($_GET['page'])) {
		$page = $_GET['page'];

		unset($uriQueries['page']);
	};

	if (isset($_GET['LIMIT'])) {
		$recordPerPage = $_GET['LIMIT'];
	};

	$result = mysql_query($query);
	$totalRecords = mysql_num_rows($result);
	$pagination = array();

	if ($recordPerPage < 0) {
		$offset = 0;
		$totalPages = 1;
	} else {
		$offset = ($page - 1) * $recordPerPage;
		$query .= sprintf(" LIMIT %d, %d", $offset, $recordPerPage);
		$totalPages = ceil($totalRecords / $recordPerPage);

		for ($i = 0; $i < $pagingBefore; $i++) {
			$currentPage = $page - $pagingBefore + $i;

			if ($currentPage > 0) {
				$pagination[] = $currentPage;
			}
		};

		$pagination[] = $page;

		for ($i = 0; $i < $pagingAfter; $i++) {
			$currentPage = $page + $i + 1;

			if ($currentPage < $totalPages) {
				$pagination[] = $currentPage;
			}
		};
	}
	
	$uriQueries['LIMIT'] = $recordPerPage;

	return array(
		'query' => $query,
		'totalRecords' => $totalRecords,
		'offset' => $offset,
		'page' => $page,
		'recordPerPage' => $recordPerPage,
		'totalPages' => $totalPages,
		'pagination' => $pagination,
		'uriQueries' => $uriQueries,
	);
}

function buildComboboxLimit($limits = null, $default = 1000) {
	$limitsDefault = array(100, 250, 500, 1000, 2000, 5000, 10000);

	if (!is_array($limits) || empty($limits)) {
		$limits = $limitsDefault;
	}

	if (!$default) {
		$default = 1000;
	}

	if (!$_GET['LIMIT']) {
		$_GET['LIMIT'] = $default;
	}
	?>
	<label>Jumlah Tampil</label><span>&nbsp;</span>
	<select id="LIMIT" name="LIMIT" class="chosen-select" style="width: 75px">
		<?php foreach ($limits as $value) { ?>
			<option value="<?php echo $value;?>" <?php if ($_GET['LIMIT'] == $value) {echo "selected";}?>><?php echo $value;?></option>
		<?php } ?>
	</select>
	<?php
}

function buildPagination($paginations, $baseUrl, $enableOnclick = true) {
	$paginations = (array) $paginations;
	$uriQueries = (array) $paginations["uriQueries"];
	$onclick = "";

	if ($enableOnclick) {
		$onclick = "paginationLoadPage(this);return false;";
	}

	echo "<div id='pagination-container' class='pagination-container clearfix' data-page='" . $paginations['page'] . "'>";

	if ($paginations['totalPages'] > 1) {
		echo "<ul class='pagination'>";
		
		if ($paginations['page'] > 1) {
			$uriQueries['page'] = 1;

			echo sprintf("<li class='pagination-first'><a href='?%s' data-base-url='%s' onclick='%s' title='Lihat halaman pertama'>|<</a></li>",
				http_build_query($uriQueries), $baseUrl, $onclick
			);

			$uriQueries['page'] = $paginations['page'] - 1;

			echo sprintf("<li class='pagination-before'><a href='?%s' data-base-url='%s' onclick='%s' title='Lihat halaman sebelumnya'><</a></li>",
				http_build_query($uriQueries), $baseUrl, $onclick
			);
		}

		foreach ($paginations['pagination'] as $value) {
			if ($paginations['page'] == $value) {
				echo sprintf("<li class='active'><span>%d</span></li>", $value);
			} else {
				$uriQueries['page'] = $value;

				echo sprintf("<li class='pagination-index'><a href='?%s' data-base-url='%s' onclick='%s' title='Lihat halaman %d'>%d</a></li>",
					http_build_query($uriQueries), $baseUrl, $onclick, $value, $value
				);
			}
		}

		if ($paginations['page'] < $paginations['totalPages']) {
			$uriQueries['page'] = $paginations['page'] + 1;

			echo sprintf("<li class='pagination-next'><a href='?%s' data-base-url='%s' onclick='%s' title='Lihat halaman selanjutnya'>></a></li>",
				http_build_query($uriQueries), $baseUrl, $onclick
			);
			
			$uriQueries['page'] = $paginations['totalPages'];

			echo sprintf("<li class='pagination-last'><a href='?%s' data-base-url='%s' onclick='%s' title='Lihat halaman terakhir'>>|</a></li>",
				http_build_query($uriQueries), $baseUrl, $onclick
			);
		}

		echo "</ul>";
	}
	
	echo sprintf("<span class='pagination-description'>Halaman <b>%s</b> dari <b>%s</b> halaman.</span>",
		number_format($paginations['page'], 0, ",", "."), number_format($paginations['totalPages'], 0, ",", ".")
	);
	echo sprintf("<span class='pagination-description'>Total data <b>%s</b>.</span>",
		number_format($paginations['totalRecords'],0,',','.')
	);
	echo "</div>";
}