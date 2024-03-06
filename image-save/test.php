<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		//header('Content-Type: image/jpeg');
		//echo $_POST['namafile'];
		$file = $_POST['namafile'];
	//	echo "<img src='$file'>";
		//file_get_contents('image-save', $file);
		$images = '<img src="data:image/jpeg;base64,'.base64_encode($file).'"/>';
		//move_uploaded_file($images,"image-save\\hehe.jpg");
		move_uploaded_file($_FILES['PICTURE']['.$images.'], "image-save\\test.png"); 

		//$preview = file_get_contents($_FILES['preview']['tmp_name']);
		//$preview = file_get_contents('/image-save', $_POST['namafile']);
		//move_uploaded_file($_POST['namafile'], $_SERVER['DOCUMENT_ROOT'] . "/image-save/test.png"); 
		//file_put_contents('/image-save', $preview);
		//$image = base64_encode($preview);
		//$previews = $_FILES["preview"]["name"]['name'];
		//echo "1 ".$preview."<br>";
		//echo "2 ".$image."<br>";
		//echo "<img src='data:image/jpg;base64,+ 'base64_encode($image)'>";
	}
?>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>HTML DOM - Paste an image from the clipboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="/css/demo.css" />
	<link rel="preconnect" href="https://fonts.gstatic.com" />
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&family=Source+Code+Pro&display=swap"/>
	<style>
		.container {
			/* Center the content */
			align-items: center;
			display: flex;
			justify-content: center;

			/* Misc */
			height: 32rem;
			padding: 1rem 0;
		}
		.key {
			background-color: #f7fafc;
			border: 1px solid #cbd5e0;
			border-radius: 0.25rem;
			padding: 0.25rem;
		}
		.preview {
			align-items: center;
			border: 1px solid #cbd5e0;
			display: flex;
			justify-content: center;

			margin-top: 1rem;
			max-height: 16rem;
			max-width: 42rem;
		}
	</style>
</head>
<body>
	
	<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
		<div class="container">
			<div>
				<div><kbd class="key">Ctrl</kbd> + <kbd class="key">V</kbd> in this window.</div>
				<img class="preview" name="preview" id="preview" />
			</div>
		</div>
		<input type="text" id="namafile" name="namafile" />
		<input class="process" type="submit" value="Simpan"/>
	</form>
	
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.addEventListener('paste', function (evt) {
				const clipboardItems = evt.clipboardData.items;
				const items = [].slice.call(clipboardItems).filter(function (item) {
					// Filter the image items only
					return item.type.indexOf('image') !== -1;
				});
				if (items.length === 0) {
					return;
				}

				const item = items[0];
				const blob = item.getAsFile();

				const imageEle = document.getElementById('preview');
				imageEle.src = URL.createObjectURL(blob);
				document.getElementById("namafile").value = imageEle.src;
				console.log(imageEle.src);
			});
		});
	</script>
</body>
</html>