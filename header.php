<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="./assets/main.css">
    <title>Report redmine spend time</title>
  </head>
  <body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark white-color">
		<div class="container">
		  	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
		  	</button>
		  	<a class="navbar-brand" href="#">Report spend time</a>
		  	<div class="collapse navbar-collapse" id="navbarTogglerDemo03">
			    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
			      	<!-- <li class="nav-item active">
			        	<a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
			      	</li>--></ul>
			    <form method="post" class="form-inline my-2 my-lg-0">
					<div class="dropdown mr-2" style="max-width:273px">
					  <button class="btn btn-outline-light btn-sm my-2 my-sm-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button">Select multiple projects</button>
					  <div class="dropdown-menu" style="width:530px;max-width:530px;max-height:300px;overflow-y:auto" aria-labelledby="dropdownMenuButton">
					  	<?php foreach($projects as $k=>$v):?>
					    <div class="custom-control custom-checkbox" style="padding-left:2rem;">
						  <input type="checkbox" id="pr_<?=$k?>" value="<?=$k?>" name="projects[]" class="custom-control-input" <?= in_array($k,$selected)?'checked':null?>>
						  <label class="custom-control-label" style="justify-content: left" for="pr_<?=$k?>"><?=$v?></label>
						</div>
					    <?php endforeach;?>
					  </div>
					</div>
			      	<input class="form-control mr-sm-2 form-control-sm form_datetime start-form" name="startDate" type="text" placeholder="from date" value="<?=isset($_POST['startDate'])?$_POST['startDate']:null?>">
			      	<input class="form-control mr-sm-2 form-control-sm form_datetime end-form" name="endDate" type="text" placeholder="to date" value="<?=isset($_POST['endDate'])?$_POST['endDate']:null?>">
			      	<button class="btn btn-caching btn-outline-light btn-sm my-2 mr-2 my-sm-0" type="button">Caching</button>
			      	<button class="btn btn-outline-light btn-sm my-2 mr-2 my-sm-0" type="submit">Report</button>
			      	<button class="btn btn-export btn-outline-light btn-sm my-2 my-sm-0" type="button">Export</button>
			    </form>
		  	</div>
		</div>
	</nav>
    <!-- Optional JavaScript -->