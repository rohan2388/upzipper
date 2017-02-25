<?php
require '_upzipper/upzipper.php'; 
$upzipper = new upzipper\upzipper(); 
$base_url = str_replace( '/upzipper.php', '', '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
if(isset($_POST) && isset($_POST['action']) && $_POST['action'] == 'do'){
	if( $data = $upzipper->build( $base_url ) ) {
		$data['result'] = true;
		echo json_encode($data);
	}else{
		echo json_encode(array('result' => false));
	}
	exit();
}else{
	$data = $upzipper->last_build( $base_url );
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>UpZipper</title>
</head>
<body>
<style>
	html,body{
		padding: 0;
		margin: 0;
		background: #f5f5f5;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	
	.wrapper {
		max-width: 600px;
		margin: 0 auto;
		min-height: 100vh;
		position: relative;
	}
	.wrapper-inner {
		background: #fff;
		border-radius: 4px;
		padding: 25px;
		box-sizing: border-box;
		position: relative;
		top: 100px;
	}
	header h1 {
		text-align: center;
		font-family: Arial, Helvetica, sans-serif;
		font-weight: 300;
		font-size: 44px;
		color: #333;
		margin: 0;
		line-height: 1;
		margin-top: 40px;
	}

	main {
		text-align: center;
		padding: 100px 0;
	}
	
	button {
		background: #ffdd57;
		border: #ffdd57;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 14px;
		padding: 15px 50px;
		text-transform: uppercase;
		cursor: pointer;
		border-radius: 2px;
		box-shadow: none;
		outline: none;
		display: inline-block;
		min-width: 200px;
		height: 50px;
		box-sizing: border-box;
		transition: .25s;
	}
	button:hover{
		background: #f9d549;
	}

	button span {
		width: 15px;
		display: inline-block;
		text-align: left;
	}

	#button-download {
		background: #f5f5f5;
		border: #f5f5f5;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 14px;
		padding: 15px 50px;
		text-transform: uppercase;
		cursor: pointer;
		border-radius: 2px;
		box-shadow: none;
		outline: none;
		display: inline-block;
		min-width: 200px;
		height: 50px;
		box-sizing: border-box;
		transition: .25s;
		margin-top: 50px;
		text-decoration: none;
		color: #9e9e9e;
		display: none;
	}

	footer {
		text-align: center;
		font-family: Arial, Helvetica, sans-serif;
		color: #ccc;
		font-size: 12px;
	}

	footer a {
		color: inherit;
	}

</style>
<div class="wrapper">
	<div class="wrapper-inner">
		<header>
			<h1>UpZipper</h1>
		</header>
		<main>
			<button id="button">Create Zip</button>
			<div>
				<a href="<?php echo $data['url'] ?>" id="button-download"><?php echo $data['name']; ?></a>
			</div>
		</main>
		<footer>
			 Rohan Das. <a href="https://github.com/rohan2388" target="_blank">Github</a>
		</footer>
	</div>
</div>

<script>
	var _ = function (id) {
		return document.getElementById(id);
	}

	var texts = [
		'Creating<span>.</span>',
		'Creating<span>..</span>',
		'Creating<span>...</span>'
	]
	var text = 'Create Zip'

	var download = _('button-download');
	var button = _('button');
	var url =  '<?php echo '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>';
	var xhr = new XMLHttpRequest();
	var intval = 0


	xhr.onreadystatechange = function () {		
		var DONE = 4; 
		var OK = 200;
		if (xhr.readyState === DONE) {
			if (xhr.status === OK) {
				clearInterval(intval)
				button.innerHTML = text;
				var data = JSON.parse(xhr.responseText)
				if (data.result) {
					download.href = data.url
					download.innerHTML = data.name
				}
				download.style.display = 'inline-block';
			}else{
				console.log('Failed');
			}
		}
	}

	var data = new FormData();
	data.append('action', 'do');

	button.addEventListener('click', function(e) {
		e.preventDefault();
		xhr.open('POST', url);
		xhr.send(data);

		var i = 1;
		button.innerHTML = texts[0]
		intval = setInterval(function(){
			i = ( i > (texts.length -1) ) ? 0 : i;
			button.innerHTML = texts[i]
			i++;
		}, 400)
	});


</script>

</body>
</html>