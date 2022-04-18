<head>
<script language="javascript">
<!--
function charge_image() {
	
  var params_url = location.search.substring(1);
  var img = params_url.split("&");
  document.write("<img src='" + img[1] + "' border='0' height='200'>");
}
// -->
</script>
</head>
<body onLoad="charge_image()" OnBlur="self.close()">

