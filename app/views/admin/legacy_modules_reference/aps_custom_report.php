<?php

/**
 * Custom Reports Page
 * Displays custom reports for sites, gata, and plots
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();
$page_title = "Custom Reports";
$include_datatables = true;

include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row">
				<div class="col-sm-12">
					<h3 class="page-title"><?php echo h($page_title); ?></h3>
					<ul class="breadcrumb">
						<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
						<li class="breadcrumb-item active">Custom Reports</li>
					</ul>
				</div>
			</div>
		</div>

		<script>
			function check() {
				var site_name = document.getElementById("site_name").value;

				$.ajax({

					method: "POST",
					url: "fetch_gata.php",
					data: {
						id: site_name,
						csrf_token: $("input[name='csrf_token']").val()
					},
					datatype: "html",
					success: function(data) {
						$("#gata_a").html(data);
						$("#plot_no").html('<option value="">Select Plot</option>');


					}
				});
				$.ajax({
					method: "POST",
					url: "fetch_farmer.php",
					data: {
						site_id: site_name,
						csrf_token: $("input[name='csrf_token']").val()
					},
					datatype: "html",
					success: function(data) {

						$("#farmer").html(data);

					}

				});
				$.ajax({
					method: "POST",
					url: "fetch_plot.php",
					data: {
						site_id: site_name,
						csrf_token: $("input[name='csrf_token']").val()
					},
					datatype: "html",
					success: function(data) {

						$("#plot_no").html(data);

					}

				});


				$("#gata_a").on('change', function() {
					var gata_id = $(this).val();
					//alert(gata_id);
					$.ajax({
						method: "POST",
						url: "fetch_gata.php",
						data: {
							sid: gata_id,
							csrf_token: $("input[name='csrf_token']").val()
						},
						datatype: "html",
						success: function(data) {
							$("#plot_no").html(data);


						}

					});
					$.ajax({
						method: "POST",
						url: "fetch_farmer.php",
						data: {
							sid: gata_id,
							csrf_token: $("input[name='csrf_token']").val()
						},
						datatype: "html",
						success: function(data) {

							$("#farmer").html(data);

						}

					});


				});
			}
		</script>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-body">
						<form action="#" method="post">
							<?php echo SecurityUtility::getCsrfField(); ?>
							<div class="row">
								<div class="form-group col-md-3">
									<label for="site_name">Site Name</label>
									<select class="form-select" id="site_name" name="site_name" onchange="check()">
										<option value="">Select Site</option>
										<?php
										$sites = $db->fetchAll("SELECT site_id, site_name FROM site_master ORDER BY site_name");
										foreach ($sites as $row) {
										?>
											<option value="<?php echo h($row['site_id']); ?>"><?php echo h($row['site_name']); ?></option>
										<?php
										}
										?>
									</select>
								</div>

								<div class="form-group col-md-3">
									<label for="gata_a">Gata No</label>
									<select class="form-control" id="gata_a" name="gata_a">
										<option value="">Select Gata</option>
									</select>
								</div>
								<div class="form-group col-md-3">
									<label for="plot_no">Plot</label>
									<select class="form-control" id="plot_no" name="plot_no">
										<option value="">Select Plot</option>
									</select>
								</div>
								<div class="form-group col-md-3">
									<label for="farmer">Select Farmer</label>
									<select class="form-select" id="farmer" name="farmer">
										<option value="">Select Farmer</option>
									</select>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-md-12 text-center">
									<button class="btn btn-primary" type="submit" name="sub">Generate Report</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<style>
			.table-wrapper {
				width: 100%;
				/* max-width: 500px; */
				overflow-x: auto;
			}
		</style>
		<?php
		if (isset($_POST["sub"])) {
			if (!SecurityUtility::validateCsrfToken($_POST['csrf_token'] ?? '')) {
				die('Invalid CSRF token.');
			}

			$site_name = SecurityUtility::sanitizeInput($_POST['site_name'] ?? '', 'int');
			$gata_a = SecurityUtility::sanitizeInput($_POST['gata_a'] ?? '', 'int');
			$plot_no = SecurityUtility::sanitizeInput($_POST['plot_no'] ?? '', 'int');
			$farmer = SecurityUtility::sanitizeInput($_POST['farmer'] ?? '', 'int');
			$order = $_POST['order'] ?? '';
			$limit = $_POST['limit'] ?? '';
			$cate = '';



			if ($site_name == '' && $gata_a == '' && $plot_no == '' && $farmer == '') {
		?>
				<script>
					alert("Kindly Select atleast one Parameter");
				</script>
				<?php
			}

			if ($site_name) {
				if (!$gata_a && !$plot_no && !$farmer) {
					$sql_chk = $db->fetchAll("SELECT * from site_master where site_id = :site_id", ['site_id' => $site_name]);

					if (count($sql_chk) > 0) {

						foreach ($sql_chk as $row_fetch_site) {

				?>



							<main class="cd__main" style="background-color: Tomato;color: white">
								<div class="table-wrapper">
									<!-- Start DEMO HTML (Use the following code into your project)-->
									<table id="example" class="table table-striped">

										<thead>

											<tr>
												<th>Site Name</th>
												<th>Site District</th>

												<th>Gata No</th>
												<!--<th>Total Gata Area</th>
					  <th>Gata Area left for Plot Allocation</th>-->
												<th>Kissan Under the Gata</th>
												<th>Plot NO</th>
												<th>Plot Area in Gata</th>
												<th>Plot Dimension</th>
												<th>Plot Facing</th>
												<th>Plot Rate/Sq.ft</th>
												<th>Plot Status</th>

											</tr>
										</thead>
										<tbody>


											<?php
											$result_gata = $db->fetchAll("select * from gata_master where site_id = :site_id", ['site_id' => $site_name]);

											foreach ($result_gata as $row_fetch_gata) {
												$gata_id = $row_fetch_gata['gata_id'];
												$result_kissan = $db->fetchAll("select * from kissan_master where gata_a = :gata_a or gata_b = :gata_b or gata_c = :gata_c or gata_d = :gata_d", [
													'gata_a' => $gata_id,
													'gata_b' => $gata_id,
													'gata_c' => $gata_id,
													'gata_d' => $gata_id
												]);


											?>
												<tr>
													<td scope="row"><?php echo h($row_fetch_site['site_name']); ?></td>
													<td><?php echo h($row_fetch_site['district']); ?></td>

													<td><?php echo h($row_fetch_gata['gata_no']); ?></td>
													<!--<td><?php //echo h($row_fetch_gata['area'])."Sqft";
															?></td>
										<td><?php //echo h($row_fetch_gata['available_area'])." Sqft";
											?></td>-->
													<td><?php foreach ($result_kissan as $row_fetch_kissan) {
															$kissan_name = $row_fetch_kissan['k_name'];
															$area_kissan = $row_fetch_kissan['area_gata_a'];
															if ($area_kissan == 0) {
																$area_kissan = $row_fetch_kissan['area_gata_b'];
																if ($area_kissan == 0) {
																	$area_kissan = $row_fetch_kissan['area_gata_c'];
																	if ($area_kissan == 0) {
																		$area_kissan = $row_fetch_kissan['area_gata_d'];
																	}
																}
															}
															echo h($kissan_name) . "(" . h($area_kissan) . "Sqft),";
														}
														?></td>
													<td><?php
														$result_plot = $db->fetchAll("select * from plot_master where gata_a = :gata_a or gata_b = :gata_b or gata_c = :gata_c or gata_d = :gata_d", [
															'gata_a' => $gata_id,
															'gata_b' => $gata_id,
															'gata_c' => $gata_id,
															'gata_d' => $gata_id
														]);
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['plot_no']) . ",";
														}
														?></td>
													<td><?php
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['area_gata_a']) . " Sqft,";
														}
														?></td>
													<td><?php
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['plot_dimension']) . ",";
														}
														?></td>
													<td><?php
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['plot_facing']) . ",";
														}
														?></td>
													<td><?php
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['plot_price']) . ",";
														}
														?></td>
													<td><?php
														foreach ($result_plot as $row_fetch_plot) {
															echo h($row_fetch_plot['plot_status']) . ",";
														}
														?></td>
												</tr>
											<?php } ?>
										<?php } ?>
									<?php } ?>
										</tbody>
									</table>
								</div>
							</main>


							<?php
						} else if ($gata_a != '') {


							$sql_chk = $db->fetchAll("SELECT * from gata_master where site_id = :site_id and gata_id = :gata_id", [
								'site_id' => $site_name,
								'gata_id' => $gata_a
							]);

							if (count($sql_chk) > 0) {



							?>

								<main class="cd__main" style="background-color: Tomato;color: white">
									<div class="table-wrapper">
										<!-- Start DEMO HTML (Use the following code into your project)-->
										<table id="example" class="table table-striped" style="width:100%">

											<thead>

												<tr>
													<th scope="col">Site Name</th>
													<th scope="col">Site District</th>

													<th scope="col">Gata No</th>
													<!--<th scope="col">Tota Gata Area</th>
					  <th scope="col">Gata Area left for Plot Allocation</th>-->
													<th scope="col">Kissan Under the Gata</th>
													<th scope="col">Plot NO</th>
													<th scope="col">Plot Area in Gata</th>
													<th>Plot Dimension</th>
													<th>Plot Facing</th>
													<th>Plot Rate/Sq.ft</th>
													<th>Plot Status</th>

												</tr>
											</thead>
											<tbody>


												<?php

												foreach ($sql_chk as $row_fetch_gata) {
													$site_id = $row_fetch_gata['site_id'];
													$row_fetch_site = $db->fetch("select * from site_master where site_id = :site_id", ['site_id' => $site_id]);
													$gata_id = $row_fetch_gata['gata_id'];
													$result_kissan = $db->fetchAll("select * from kissan_master where gata_a = :gata_a or gata_b = :gata_b or gata_c = :gata_c or gata_d = :gata_d", [
														'gata_a' => $gata_id,
														'gata_b' => $gata_id,
														'gata_c' => $gata_id,
														'gata_d' => $gata_id
													]);


												?>
													<tr>
														<td scope="row"><?php echo h($row_fetch_site['site_name']); ?></td>
														<td><?php echo h($row_fetch_site['district']); ?></td>

														<td><?php echo h($row_fetch_gata['gata_no']); ?></td>
														<!--<td><?php //echo h($row_fetch_gata['area'])."Sqft";
																?></td>
										<td><?php //echo h($row_fetch_gata['available_area'])." Sqft";
											?></td>-->
														<td><?php foreach ($result_kissan as $row_fetch_kissan) {
																$kissan_name = $row_fetch_kissan['k_name'];
																$area_kissan = $row_fetch_kissan['area_gata_a'];
																if ($area_kissan == 0) {
																	$area_kissan = $row_fetch_kissan['area_gata_b'];
																	if ($area_kissan == 0) {
																		$area_kissan = $row_fetch_kissan['area_gata_c'];
																		if ($area_kissan == 0) {
																			$area_kissan = $row_fetch_kissan['area_gata_d'];
																		}
																	}
																}

																echo h($kissan_name) . "(" . h($area_kissan) . "Sqft),";
															}

															?>
														</td>
														<td><?php
															$result_plot = $db->fetchAll("select * from plot_master where gata_a = :gata_a or gata_b = :gata_b or gata_c = :gata_c or gata_d = :gata_d", [
																'gata_a' => $gata_id,
																'gata_b' => $gata_id,
																'gata_c' => $gata_id,
																'gata_d' => $gata_id
															]);
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['plot_no']) . ",";
															}
															?></td>
														<td><?php
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['area_gata_a']) . " Sqft,";
															}
															?></td>
														<td><?php
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['plot_dimension']) . ",";
															}
															?></td>
														<td><?php
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['plot_facing']) . ",";
															}
															?></td>
														<td><?php
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['plot_price']) . ",";
															}
															?></td>
														<td><?php
															foreach ($result_plot as $row_fetch_plot) {
																echo h($row_fetch_plot['plot_status']) . ",";
															}
															?></td>


													</tr>
											<?php
												}
											}
											?>
											</tbody>
										</table>
										<div class="table-wrapper">
								</main>
								<?php

							} else if ($gata_a != '' && $plot_no != '') {
								$sql_chk = $db->fetchAll("SELECT * from plot_master where site_id = :site_id  and plot_id = :plot_id", [
									'site_id' => $site_name,
									'plot_id' => $plot_no
								]);

								if (count($sql_chk) > 0) {



								?>

									<main class="cd__main" style="background-color: Tomato;color: white">
										<div class="table-wrapper">
											<!-- Start DEMO HTML (Use the following code into your project)-->
											<table id="example" class="table table-striped" style="width:100%">

												<thead>

													<tr>
														<th scope="col">Site Name</th>
														<th scope="col">Site District</th>

														<th scope="col">Gata No</th>
														<!--<th scope="col">Tota Gata Area</th>
					  <th scope="col">Gata Area left for Plot Allocation</th>-->
														<th scope="col">Kissan Under the Gata</th>
														<th scope="col">Plot NO</th>
														<th scope="col">Plot Area in Gata</th>
														<th>Plot Dimension</th>
														<th>Plot Facing</th>
														<th>Plot Rate/Sq.ft</th>
														<th>Plot Status</th>

													</tr>
												</thead>
												<tbody>


													<?php

													foreach ($sql_chk as $row_fetch_plot) {
														$site_id = $row_fetch_plot['site_id'];
														$row_fetch_site = $db->fetch("select * from site_master where site_id = :site_id", ['site_id' => $site_id]);
														$gata_id = $row_fetch_plot['gata_a'];
														$row_fetch_gata = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_id]);
														$gata_no = $row_fetch_gata['gata_no'] ?? '';
														$result_kissan = $db->fetchAll("select * from kissan_master where gata_a = :gata_a or gata_b = :gata_b or gata_c = :gata_c or gata_d = :gata_d", [
															'gata_a' => $gata_id,
															'gata_b' => $gata_id,
															'gata_c' => $gata_id,
															'gata_d' => $gata_id
														]);


													?>
														<tr>
															<td scope="row"><?php echo h($row_fetch_site['site_name']); ?></td>
															<td><?php echo h($row_fetch_site['district']); ?></td>

															<td><?php echo h($gata_no); ?></td>
															<!--<td><?php //echo h($row_fetch_gata['area'])." Sqft";
																	?></td>
										<td><?php //echo h($row_fetch_gata['available_area'])." Sqft";
											?></td>-->
															<td><?php foreach ($result_kissan as $row_fetch_kissan) {
																	$kissan_name = $row_fetch_kissan['k_name'];
																	$area_kissan = $row_fetch_kissan['area_gata_a'];
																	if ($area_kissan == 0) {
																		$area_kissan = $row_fetch_kissan['area_gata_b'];
																		if ($area_kissan == 0) {
																			$area_kissan = $row_fetch_kissan['area_gata_c'];
																			if ($area_kissan == 0) {
																				$area_kissan = $row_fetch_kissan['area_gata_d'];
																			}
																		}
																	}

																	echo h($kissan_name) . "(" . h($area_kissan) . "Sqft),";
																}

																?>
															</td>
															<td><?php echo h($row_fetch_plot['plot_no']); ?></td>
															<td><?php echo h($row_fetch_plot['area_gata_a']); ?> Sqft</td>
															<td><?php echo h($row_fetch_plot['plot_dimension']); ?></td>
															<td><?php echo h($row_fetch_plot['plot_facing']); ?></td>
															<td><?php echo h($row_fetch_plot['plot_price']); ?></td>
															<td><?php echo h($row_fetch_plot['plot_status']); ?></td>


														</tr>
													<?php


													}
													?>
												<?php
											}
												?>
												</tbody>
											</table>
											<div class="table-wrapper">
									</main>
									<?php

								} else if ($gata_a != '' && $plot_no != '') {

									$sql_chk = $db->fetchAll("SELECT * from plot_master where site_id = :site_id  and plot_id = :plot_id", [
										'site_id' => $site_name,
										'plot_id' => $plot_no
									]);

									if (count($sql_chk) > 0) {



									?>
										<main class="cd__main" style="background-color: Tomato;color: white">
											<div class="table-wrapper">
												<!-- Start DEMO HTML (Use the following code into your project)-->
												<table id="example" class="table table-striped" style="width:100%">
													<thead>


														<tr>
															<th scope="col">Site Name</th>
															<th scope="col">Site District</th>

															<th scope="col">Plot No</th>
															<th scope="col">Plot Area</th>
															<th scope="col">Plot Dimension</th>
															<th scope="col">Plot Facing</th>
															<th scope="col">Plot Rate/Sq.ft</th>
															<th scope="col">Plot Status</th>
															<th scope="col">Gata A</th>
															<th scope="col">Plot Area in Gata A</th>
															<th scope="col">Gata B</th>
															<th scope="col">Plot Area in Gata B</th>
															<th scope="col">Gata C</th>
															<th scope="col">Plot Area in Gata C</th>
															<th scope="col">Gata D</th>
															<th scope="col">Plot Area in Gata D</th>



														</tr>
													</thead>
													<tbody>


														<?php

														foreach ($sql_chk as $row_fetch_plot) {
															$site_id = $row_fetch_plot['site_id'];
															$plot_no_val = $row_fetch_plot['plot_no'];
															$plot_area = $row_fetch_plot['area'];

															$row_fetch_site = $db->fetch("select * from site_master where site_id = :site_id", ['site_id' => $site_id]);
															$site_name_val = $row_fetch_site['site_name'];
															$site_district_val = $row_fetch_site['district'];

															$gata_a_id = $row_fetch_plot['gata_a'];
															$gata_b_id = $row_fetch_plot['gata_b'];
															$gata_c_id = $row_fetch_plot['gata_c'];
															$gata_d_id = $row_fetch_plot['gata_d'];

															$row_fetch_gata_a = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_a_id]);
															$gata_no_a = $row_fetch_gata_a['gata_no'] ?? '';

															$row_fetch_gata_b = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_b_id]);
															$gata_no_b = $row_fetch_gata_b['gata_no'] ?? '';

															$row_fetch_gata_c = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_c_id]);
															$gata_no_c = $row_fetch_gata_c['gata_no'] ?? '';

															$row_fetch_gata_d = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_d_id]);
															$gata_no_d = $row_fetch_gata_d['gata_no'] ?? '';


														?>
															<tr>
																<td><?php echo h($site_name_val); ?></td>
																<td><?php echo h($site_district_val); ?></td>

																<td><?php echo h($plot_no_val); ?></td>
																<td><?php echo h($plot_area) . " Sqft"; ?></td>
																<td><?php echo h($row_fetch_plot['plot_dimension']); ?></td>
																<td><?php echo h($row_fetch_plot['plot_facing']); ?></td>
																<td><?php echo h($row_fetch_plot['plot_price']); ?></td>
																<td><?php echo h($row_fetch_plot['plot_status']); ?></td>
																<td><?php echo h($gata_no_a); ?></td>
																<td><?php echo h($row_fetch_plot['area_gata_a']) . " Sqft"; ?></td>
																<td><?php echo h($gata_no_b); ?></td>
																<td><?php echo h($row_fetch_plot['area_gata_b']) . " Sqft"; ?></td>
																<td><?php echo h($row_fetch_plot['area_gata_c']) . " Sqft"; ?></td>
																<td><?php echo h($gata_no_c); ?></td>
																<td><?php echo h($row_fetch_plot['area_gata_d']) . " Sqft"; ?></td>
																<td><?php echo h($gata_no_d); ?></td>



															</tr>
													<?php
														}
													}
													?>
													</tbody>
												</table>
												<div class="table-wrapper">
										</main>
										<?php

									} else if ($gata_a == '' && $plot_no != '') {

										$sql_chk = $db->fetchAll("SELECT * from plot_master where site_id = :site_id  and plot_id = :plot_id", [
											'site_id' => $site_name,
											'plot_id' => $plot_no
										]);
										if (count($sql_chk) > 0) {



										?>
											<main class="cd__main" style="background-color: Tomato;color: white">
												<div class="table-wrapper">
													<!-- Start DEMO HTML (Use the following code into your project)-->
													<table id="example" class="table table-striped" style="width:100%">
														<thead>


															<tr>
																<th scope="col">Site Name</th>
																<th scope="col">Site District</th>
																<th scope="col">Plot No</th>
																<th scope="col">Plot Area</th>
																<th scope="col">Plot Dimension</th>
																<th scope="col">Plot Facing</th>
																<th scope="col">Plot Rate/Sq.ft</th>
																<th scope="col">Plot Status</th>
																<th scope="col">Gata A</th>
																<th scope="col">Plot Area in Gata A</th>
																<th scope="col">Gata B</th>
																<th scope="col">Plot Area in Gata B</th>
																<th scope="col">Gata C</th>
																<th scope="col">Plot Area in Gata C</th>
																<th scope="col">Gata D</th>
																<th scope="col">Plot Area in Gata D</th>



															</tr>
														</thead>
														<tbody>


															<?php

															foreach ($sql_chk as $row_fetch_plot) {
																$site_id = $row_fetch_plot['site_id'];
																$plot_no_val = $row_fetch_plot['plot_no'];
																$plot_area = $row_fetch_plot['area'];

																$row_fetch_site = $db->fetch("select * from site_master where site_id = :site_id", ['site_id' => $site_id]);
																$site_name_val = $row_fetch_site['site_name'];
																$site_district_val = $row_fetch_site['district'];

																$gata_a_id = $row_fetch_plot['gata_a'];
																$gata_b_id = $row_fetch_plot['gata_b'];
																$gata_c_id = $row_fetch_plot['gata_c'];
																$gata_d_id = $row_fetch_plot['gata_d'];

																$row_fetch_gata_a = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_a_id]);
																$gata_no_a = $row_fetch_gata_a['gata_no'] ?? '';

																$row_fetch_gata_b = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_b_id]);
																$gata_no_b = $row_fetch_gata_b['gata_no'] ?? '';

																$row_fetch_gata_c = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_c_id]);
																$gata_no_c = $row_fetch_gata_c['gata_no'] ?? '';

																$row_fetch_gata_d = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_d_id]);
																$gata_no_d = $row_fetch_gata_d['gata_no'] ?? '';


															?>
																<tr style="background-color: #008080;color:white">
																	<td><?php echo h($site_name); ?></td>
																	<td><?php echo h($site_district); ?></td>

																	<td><?php echo h($plot_no); ?></td>
																	<td><?php echo h($plot_area) . " Sqft"; ?></td>
																	<td><?php echo h($row_fetch_plot['plot_dimension']); ?></td>
																	<td><?php echo h($row_fetch_plot['plot_facing']); ?></td>
																	<td><?php echo h($row_fetch_plot['plot_price']); ?></td>

																	<?php
																	$plot_status = $row_fetch_plot['plot_status'];
																	if ($plot_status == "Available") { ?>
																		<td style="background-color: Green;color:white"><?php echo h($plot_status); ?></td>
																	<?php
																	} else {
																	?><td><?php echo h($plot_status); ?></td>

																	<?php } ?>
																	<td><?php echo h($gata_no_a); ?></td>
																	<td><?php echo h($row_fetch_plot['area_gata_a']) . " Sqft"; ?></td>
																	<td><?php echo h($gata_no_b); ?></td>
																	<td><?php echo h($row_fetch_plot['area_gata_b']) . " Sqft"; ?></td>
																	<td><?php echo h($gata_no_c); ?></td>
																	<td><?php echo h($row_fetch_plot['area_gata_c']) . " Sqft"; ?></td>
																	<td><?php echo h($gata_no_d); ?></td>
																	<td><?php echo h($row_fetch_plot['area_gata_d']) . " Sqft"; ?></td>



																</tr>


														<?php
															}
														}
														?>
														</tbody>
													</table>
													<div class="table-wrapper">
											</main>
											<?php

										} else if ($gata_a == '' && $plot_no == '' && $farmer != '') {

											$sql_chk = $db->fetchAll("SELECT * from kissan_master where site_id = :site_id  and kissan_id = :kissan_id", [
												'site_id' => $site_name,
												'kissan_id' => $farmer
											]);
											if (count($sql_chk) > 0) {



											?>
												<main class="cd__main" style="background-color: Tomato;color: white">
													<div class="table-wrapper">
														<!-- Start DEMO HTML (Use the following code into your project)-->
														<table id="example" class="table table-striped" style="width:100%">
															<thead>


																<tr>
																	<th scope="col">Site Name</th>
																	<th scope="col">Site District</th>
																	<th scope="col">Site Tehsil</th>
																	<th scope="col"> Site Gram</th>
																	<th scope="col">Total Site Area</th>
																	<th scope="col">Area for Gata Allocation</th>
																	<th scope="col">Kissan Name</th>
																	<th scope="col">Kissan total area</th>
																	<th scope="col">Gata A</th>
																	<th scope="col">Area in Gata A</th>
																	<th scope="col">Gata B</th>
																	<th scope="col">Area in Gata B</th>
																	<th scope="col">Gata C</th>
																	<th scope="col">Area in Gata C</th>
																	<th scope="col">Gata D</th>
																	<th scope="col">Area in Gata D</th>



																</tr>
															</thead>
															<tbody>


																<?php

																foreach ($sql_chk as $row_fetch_kissan) {
																	$site_id = $row_fetch_kissan['site_id'];
																	$kissan_name = $row_fetch_kissan['k_name'];
																	$kissan_area = $row_fetch_kissan['area'];


																	$row_fetch_site = $db->fetch("select * from site_master where site_id = :site_id", ['site_id' => $site_id]);
																	$site_name_val = $row_fetch_site['site_name'];
																	$site_district_val = $row_fetch_site['district'];
																	$site_tehsil_val = $row_fetch_site['tehsil'];
																	$site_gram_val = $row_fetch_site['gram'];
																	$site_area_val = $row_fetch_site['area'];
																	$site_avail_area_val = $row_fetch_site['available_area'];

																	$gata_a_id = $row_fetch_kissan['gata_a'];
																	$gata_b_id = $row_fetch_kissan['gata_b'];
																	$gata_c_id = $row_fetch_kissan['gata_c'];
																	$gata_d_id = $row_fetch_kissan['gata_d'];

																	$row_fetch_gata_a = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_a_id]);
																	$gata_no_a = $row_fetch_gata_a['gata_no'] ?? '';

																	$row_fetch_gata_b = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_b_id]);
																	$gata_no_b = $row_fetch_gata_b['gata_no'] ?? '';

																	$row_fetch_gata_c = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_c_id]);
																	$gata_no_c = $row_fetch_gata_c['gata_no'] ?? '';

																	$row_fetch_gata_d = $db->fetch("select * from gata_master where gata_id = :gata_id", ['gata_id' => $gata_d_id]);
																	$gata_no_d = $row_fetch_gata_d['gata_no'] ?? '';




																?>
																	<tr>
																		<td><?php echo h($site_name_val); ?></td>
																		<td><?php echo h($site_district_val); ?></td>
																		<td><?php echo h($site_tehsil_val); ?></td>
																		<td><?php echo h($site_gram_val); ?></td>
																		<td><?php echo h($site_area_val) . " Sqft"; ?></td>
																		<td><?php echo h($site_avail_area_val) . " Sqft"; ?></td>
																		<td><?php echo h($kissan_name); ?></td>
																		<td><?php echo h($kissan_area) . " Sqft"; ?></td>
																		<td><?php echo h($gata_no_a); ?></td>
																		<td><?php echo h($row_fetch_kissan['area_gata_a']) . " Sqft"; ?></td>
																		<td><?php echo h($gata_no_b); ?></td>
																		<td><?php echo h($row_fetch_kissan['area_gata_b']) . " Sqft"; ?></td>
																		<td><?php echo h($row_fetch_kissan['area_gata_c']) . " Sqft"; ?></td>
																		<td><?php echo h($gata_no_c); ?></td>
																		<td><?php echo h($row_fetch_kissan['area_gata_d']) . " Sqft"; ?></td>
																		<td><?php echo h($gata_no_d); ?></td>
																	</tr>


															<?php
																}
															}
															?>
															</tbody>
														</table>
														<div class="table-wrapper">
												</main>
									<?php

										}
									}
								}


									?>

	</div>

	<?php include 'admin_footer.php'; ?>