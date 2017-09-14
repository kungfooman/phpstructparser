<?php
	require("parselib.php");
	echo "<pre>";
	
	
	
	$funcs = parse_functions_in_headerfile( file_get_contents("fakegl.txt") );
	
	// easy to test stuff in Chrome JS shell
	echo "<script>";
	echo "funcs = " . json_encode($funcs) . ";\n";
	echo "</script>";
	
	

	foreach ($funcs as $func) {
		$pointer = "";
		for ($i=0; $i<$func->is_pointer; $i++)
			$pointer .= "*";
		
		$args = "";
		foreach ($func->args as $arg) {
			$args .= arg2string($arg) . ", ";
		}
		$args = rtrim($args, ", "); // remove last ", "
		
		$const = "";
		if ($func->is_const)
			$const = "const ";
		//printf("\tstatic %s%s %sFakeGL::%s(%s) {\n",
		printf("%s%s %s(__stdcall *q%s)(%s);\n",
			$const,
			$func->type,
			$pointer,
			$func->name,
			$args
		);

		
	}	
	
	echo "\n\n";
	
	foreach ($funcs as $func) {
		$pointer = "";
		for ($i=0; $i<$func->is_pointer; $i++)
			$pointer .= "*";
		
		$args = "";
		foreach ($func->args as $arg) {
			$args .= arg2string($arg) . ", ";
		}
		$args = rtrim($args, ", "); // remove last ", "
		
		$const = "";
		if ($func->is_const)
			$const = "const ";
		//printf("\tstatic %s%s %sFakeGL::%s(%s) {\n",
		printf("q%s = SDL_GL_GetProcAddress(\"%s\");\n",
			$func->name,
			$func->name
		);

		
	}
	
	echo "\n\n";
	
	printf("static class WrapGL {\n");
	printf("public:\n");
	
	foreach ($funcs as $func) {
		$pointer = "";
		for ($i=0; $i<$func->is_pointer; $i++)
			$pointer .= "*";
		
		$args = "";
		foreach ($func->args as $arg) {
			$args .= arg2string($arg) . ", ";
		}
		$args = rtrim($args, ", "); // remove last ", "
		
		$const = "";
		if ($func->is_const)
			$const = "const ";
		//printf("\tstatic %s%s %sFakeGL::%s(%s) {\n",
		printf("\tstatic %s%s %s%s(%s) {\n",
			$const,
			$func->type,
			$pointer,
			$func->name,
			$args
		);
		
		$args = "";
		$arglist = $func->implodeNames(", ");
		//foreach ($func->args as $arg)
		//	$args .= $arg;
		$printret = "return ";
		if ($func->is_pointer==0 && $func->type=="void")
			$printret = ""; // dont return anything if void function
		printf("\t\t%s::%s(%s);\n", $printret, $func->name, $arglist);
		
		printf("\t}\n");
		
	}
	
	
	printf("};\n");
	
	echo "</pre>";
	
	
	