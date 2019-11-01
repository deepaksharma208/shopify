	<!DOCTYPEhtml>
<head>
<style>
.main {
    border: 3px solid #44df55;
    margin: auto;
	margin-top: 80px;
    width: 500px;
    background-color: white;
    padding: 40px;}

.title{
	border: 3px solid white;
    opacity: 0.7;
    color: blue;
	width: 265px;
	margin: auto;
	text-align: center;
}
.text{
	font-family: sans-serif;
    color: purple;
    text-align: center;
}
.bgcolo{
	background-color: white;
	
}
	}
</style>
</head>
<body class="main">
<div class="bgcolo">
	<a href= <?php echo urldecode($_GET['redirect_url']);?> style="text-decoration: none;">
	<div class="title">
		<img src="shiprocket-logo.png">
	</div></a><br>
	<div>
		<h3 class="text" style="color:royalblue;">Thank You for installing ShipRocket App!</h3><br>
		<p class="text">
		<?php
			if(strpos(urldecode($_GET['message']), 'Error') !== false)
				{echo "Due to an error, your store could not be itegrated with ShipRocket\n Please Email us at 'support@shiprocket.com'";die;}
			if(urldecode($_GET['message']) == 'Channel Integrated after registering the user')
				echo "Your Shopify Store is successfully integrated with ShipRocket ";
			?>
		</p>
		<p class="text">
		<?php 
			if(urldecode($_GET['message']) == 'Channel Already Integrated')
				echo "Your Shopify Store is already integrated with ShipRocket";
			?>
		</p>
		<p class="text">
		<?php 
			if(urldecode($_GET['message']) == 'Channel Already Integrated' || urldecode($_GET['message']) == 'Channel Integrated after registering the user' || urldecode($_GET['message']) == 'Channel Integrated')	
				echo "To ship your orders click "; ?>
			<a href = <?php echo urldecode($_GET['redirect_url']);?> style="text-decoration: none;">here</a>
	</div>
</div>
</body>
</html>