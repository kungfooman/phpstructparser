<?php
	require("parselib.php");
	echo "<pre>";
	$funcs = parse_functions_in_headerfile( file_get_contents("fakegl.txt") );
	$names = [];
	foreach ($funcs as $func) {
		$names[] = $func->name;
	}
	function sortlength($a, $b) {
		return strlen($b) - strlen($a);
	}
	usort($names,'sortlength');
	//sort($names);
	//var_dump($names);
	//foreach ($names as $name)
	//	echo "$name\n";
	$in = "";
	if (isset($_POST["in"])) {
		$in = $_POST["in"];
		foreach ($names as $name) {
			$in = str_replace($name, "FakeGL::$name", $in);
			$in = str_replace("FakeGL::FakeGL::", "FakeGL::", $in);
		}
	}
?>
<form method="post" action="?">
<textarea name="in"></textarea>
<input type="submit" value="convert">
</textarea>
<?php
	if ($in == $_POST["in"])
		die("input is same as output!");
	echo "<pre>";
	//echo substr($in, 28000);
	echo htmlspecialchars($in);
	file_put_contents("foo.txt", $in);
	//printf(" %s ", $in);
	echo "</pre>";
?>