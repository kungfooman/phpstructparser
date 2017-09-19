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
		printf("extern %s%s %s(APIENTRY *q%s)(%s);\n",
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
		printf("%s%s %s APIENTRY q%s(%s);\n",
			$const,
			$func->type,
			$pointer,
			$func->name,
			$args
		);

		
	}	
	
	
	
	/*
	struct funcname_s {
		char *name;
		void *func;
	};

	struct funcname_s funcnames[] = {
		
		...
		
		{NULL, NULL}
	};

	for (int i=0; ; i++) {
		auto f = funcnames[i];
		if (f.name == NULL)
			break;
		ImGui::Text("%.8x = %s", f.func, f.name);
	}	
	*/
	
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
		printf("\t\t{\"q%s\", (void *)q%s, 0, 0, 0},\n",
			$func->name,
			$func->name
		);

		
	}	
	
	// typedef unsigned int	GLenum;
	// typedef unsigned char	GLboolean;
	// typedef unsigned int	GLbitfield;
	// typedef void		GLvoid;
	// typedef signed char	GLbyte;		/* 1-byte signed */
	// typedef short		GLshort;	/* 2-byte signed */
	// typedef int		GLint;		/* 4-byte signed */
	// typedef unsigned char	GLubyte;	/* 1-byte unsigned */
	// typedef unsigned short	GLushort;	/* 2-byte unsigned */
	// typedef unsigned int	GLuint;		/* 4-byte unsigned */
	// typedef int		GLsizei;	/* 4-byte signed */
	// typedef float		GLfloat;	/* single precision float */
	// typedef float		GLclampf;	/* single precision float in [0,1] */
	// typedef double		GLdouble;	/* double precision float */
	// typedef double		GLclampd;	/* double precision float in [0,1] */	
	
	function printfformat($var) {
		switch ($var->type) {
			case "GLclampf": return "%f";
			case "GLenum": return "%d";
			case "GLint": return "%d";
			case "GLsizei": return "%d";
			case "GLuint": return "%d";
			case "GLchar": return "%d";
			case "GLboolean": return "%d";
			case "GLfloat": return "%f";
			case "GLvoid": return "%d"; // GLvoid *, just dump address
			case "void": return "%d"; // GLvoid *, just dump address
			case "GLbitfield": return "%d";
			case "GLintptr": return "%d";
			case "GLintptr": return "%d";
			case "GLsizeiptr": return "%d";
			case "GLclampd": return "%f";
			case "GLdouble": return "%f";
			case "GLubyte": return "%d";
			
			
		}
		return "%d (add $var->type to printfformat)";
	}
	
	function toTypeString($var) {
		$pointer = "";
		for ($i=0; $i<$var->is_pointer; $i++)
			$pointer .= "*";
		$const = "";
		if ($var->is_const)
			$const = "const ";
		return "$const$var->type $pointer";
		
	}
	
	echo "\n#include \"tr_local.h\"\nvoid *SDL_GL_GetProcAddress(const char *str);\n";
	$funcid = 0;
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
		
		printf("%s%s %s APIENTRY q%s(%s) {\n",
			$const,
			$func->type,
			$pointer,
			$func->name,
			$args
		);
		$funcdef = sprintf("%s%s %s(APIENTRY *funcptr)(%s);",
			$const,
			$func->type,
			$pointer,
			$args
		);
		
		
		
		$formats = [];
		$arglist = [];
		foreach ($func->args as $arg) {
			// $arg->type
			// $arg->name
			
			
			// printf("glColor> ret=%d")
			
			
			$pf = printfformat($arg);
			
			$formats[] = "/*$arg->name*/ $pf";
			$arglist[] = $arg->name;
			
			
			//echo("$pf = $arg->name = $arg->type\n");
			//$args .= arg2string($arg) . ", ";
		}
		
		$args = implode(", ", $arglist);
		$formats_str = implode(", ", $formats);
		echo "\tstatic $funcdef\n";
		echo "\tif ((void *)funcptr == NULL)\n";
		echo "\t\tfuncptr = SDL_GL_GetProcAddress(\"$func->name\");\n";
		
		if ($func->type != "void" && $func->type != "GLvoid") {
			echo "\t" . toTypeString($func) . " ret = funcptr($args);\n";
		} else
			echo "\tfuncptr($args);\n";
		
		
		printf("\tglfunc_t *glfunc = glfuncs + $funcid;\n");
		printf("\tglfunc->calls++;\n");

		if ($func->name != "glGetError") {
			printf("\tconst GLenum err = qglGetError();\n");
			printf("\tif (err != 0) { printf(\"$func->name: error=%%d\\n\", err); glfunc->showprintf = 1; }\n");
			
		}
		
		if (count($arglist))
			if ($func->type != "void" && $func->type != "GLvoid") {
				echo("\tif (glfunc->showprintf) printf(\"$func->name( $formats_str ); // ret=" . printfformat($func) . "\\n\", $args, ret);\n");
				echo "\treturn ret;\n";
			} else
				echo("\tif (glfunc->showprintf) printf(\"$func->name( $formats_str );\\n\", $args);\n");
		else
			if ($func->type != "void" && $func->type != "GLvoid") {
				echo("\tif (glfunc->showprintf) printf(\"$func->name(); // ret=" . printfformat($func) . "\\n\", ret);\n");
				echo "\treturn ret;\n";
			} else
				echo("\tif (glfunc->showprintf) printf(\"$func->name();\\n\");\n");	
		
		
		
		//var_dump($arglist);
		printf("}\n");
		$funcid++;
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
	
	
	