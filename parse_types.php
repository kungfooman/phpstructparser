<?php
	$types = file_get_contents("qertypes.h");
	
	echo "<pre>\n";
	echo $types . "\n";
	
	
	function tokenize($input) {
				
		$split = explode(" ", $input);
		
		$n = strlen($input);
		$curstr = ""; // current string
		
		
		$tokens = [];
		
		for ($i=0; $i<$n; $i++) {
			$cc = $input{$i};
			//echo "check: \"$cc\"\n";
			
			if (in_array($cc, [";", "*", ",", "{", "}", "[", "]"]))
			{
				// if we have a curstr, add it, before we add the special chars
				if (strlen($curstr)) {
					$tokens[] = $curstr;
					$curstr = "";
				}
				
				$tokens[] = $cc;
				continue;
			}
			if ($cc == "/") {
				if ($input{$i+1} == "/") {
					for ($j=$i; $j<$n; $j++) {
						if ($input{$j} == "\n")
							{
								//echo "found end of comment: $j\n";
								$i = $j;
								break;
							}
					}
				}
				
			}
			
			if (ctype_alpha($cc) || $cc == "_" || ctype_digit($cc)) {
					$curstr .= $cc;
				
			} else {
				if (trim($curstr) == "")
					continue;
				//echo "new string: \"$curstr\"\n";
				$tokens[] = $curstr;
				$curstr = "";
			}
			
			
			
		}
		
		
		
		return $tokens;
	}
	
	$tokens = tokenize($types);
	
	echo "</pre><h1>tokens</h1><pre>";
	foreach ($tokens as $token) {
		echo "$token\n";
		
	}
	
	echo "</pre><h1>lets analyze the tokens</h1><pre>";
	
	class Variable {
		var $name = "no name";
		var $isPointer = false;
		var $type = "";
		var $is_array = 0;
		var $dimension0 = 0;
		var $dimension1 = 0;
	};
	
	$in = []; // meh a "stack"
	
	function stack_last() {
		global $in;
		return $in[count($in) - 1];
	}
	function stack_pop() {
		global $in;
		return array_pop($in);
	}
	
	
	class Structure {
		var $members = [];
		var $type = "";
		function addVariable($var) {
			$this->members[] = $var;
		}
	}
	$structures = [];
	$structure = new Structure();
	$curvar = new Variable();
	$typedef_structname = "";
	$expect_a_type = 0; // after ";" in a struct the next thing there is must be a type or ending "}"
	for ($i=0; $i<count($tokens); $i++) {
		$curtok = $tokens[$i];
		
		$is_a_type = in_array($curtok, ["int", "vec3_t", "qboolean", "float", "patchMesh_t", "void", "epair_t", "bool", "face_t"]);
		//printf("is a type: %d\n", $is_a_type);
		
		// set this to 2, so:
		// next iteration 2 - 1 == 1
		// next iteration 1 - 1 == 0
		// its only true for the next iteration
		if ($expect_a_type > 0)
			$expect_a_type--;
		
		if ($expect_a_type && $curtok == "}") {
			$expect_a_type = 0;
		}
		
		if ($is_a_type || $expect_a_type) {
			//echo "is_a_type> stack_last(): " . stack_last() . "\n";
			//echo "is_a_type> stack_last(): " . stack_last() . "\n";
			//echo "is_a_type> stack_last(): " . stack_last() . "\n";
			//echo "is_a_type> stack_last(): " . stack_last() . "\n";
			$in[] = "newvar";
			$curvar->type = $curtok;
			if ($curtok == "struct") {
				$curvar->type .= " " . $tokens[$i + 1];
				$i++;
			}
			
			continue;
		}
		
		switch ($curtok) {
			case "typedef":
				// 
			
				$in[] = "typedef";
				
				echo "k type\n";
				//echo "last element: " . stack_last() . "\n";
				
				
				break;
			case "struct":
			{
				if (stack_last() == "typedef")
				{
					$name = $tokens[$i + 1];
					echo "name of struct for type: $name\n";
					$typedef_structname = "struct $name";
					$i++;
					
				} else {
					$in[] = "newvar";
					
					//array_push($in, "newvar");
					//echo "NEW new var: $curtok\n";
					
					$curvar->type = "struct " . $tokens[$i + 1];
					printf("NEW STRUCT TYPE: %s\n", $curvar->type);
					$i++;

				}
				break;
				
			}
			
			case "{":
				$in[] = "struct";
				break;
			case "}":
				echo "last element: " . stack_last() . "\n";
				
				if (stack_last() == "struct") {
					echo "new struct created $typedef_structname\n";
					
					// set type of struct (could be done earlier as well prolly)
					$structure->type = $typedef_structname;
					$structures[] = $structure;
					$structure = new Structure();
					
					stack_pop();
				}
				break;
				
			case "[":
				$curvar->is_array = 1;
				$curvar->dimension0 = $tokens[$i + 1];
				//$i + 2 == "]"
				// do we have a second dimension aswell?
				if ($tokens[$i + 3] == "[") {
					$curvar->dimension1 = $tokens[$i + 4];
					echo "dimension 1: " . $tokens[$i + 4] . "\n";
				}
				// $i + 5 should be ] now
				$i += 5;
				continue;
				printf("+1 %s \n", $tokens[$i + 1]);
				printf("+2 %s \n", $tokens[$i + 2]);
				printf("+3 %s \n", $tokens[$i + 3]);
				echo "dimension: $curtok\n";
				echo "dimension: $curtok\n";
				break;
				
			case ";":
				if (stack_last() == "newvar") {
					
					printf("var finished! name=%s type=%s isPointer=%d\n", $curvar->name, $curvar->type, $curvar->isPointer);
					
					$structure->addVariable($curvar);
					$curvar = new Variable();
					$expect_a_type = 2; // we decrease it until 0 at beginning, so this is true for next iteration, but not the very next one anymore
					stack_pop();
					
				}
				
				
				//echo "expect new type: " . stack_last() . "\n";
				//echo "expect new type: " . stack_last() . "\n";
				//echo "expect new type: " . stack_last() . "\n";
				//echo "expect new type: " . stack_last() . "\n";
				
				
				break;
			
			case "*":
				$curvar->isPointer = true;
				//echo "is pointer\n";
				break;
				
			case ",":
				printf("var finished! name=%s type=%s isPointer=%d\n", $curvar->name, $curvar->type, $curvar->isPointer);
				$structure->addVariable($curvar);
				$type = $curvar->type;
				$curvar = new Variable();
				$curvar->type = $type;
				
				
				break;
		
			default:
				if (stack_last() == "newvar") {
					//echo "add to var: $curtok\n";
					$curvar->name = $curtok;
					break;
				}
			
				echo "default: $curtok stack_last=" .stack_last(). "\n";
		}
		
	}
	/*
	
struct reflection_data b[] = {
		{"prev", "brush", offsetof(struct brush_s, prev), 1},
		{"next", "brush", offsetof(struct brush_s, next), 1},
		{"oprev", "brush", offsetof(struct brush_s, oprev), 1},
		{"onext", "brush", offsetof(struct brush_s, onext), 1},
		{"owner", "entity", offsetof(struct brush_s, owner), 1},
		{"mins", "vec3", offsetof(struct brush_s, mins), 0},
		{"maxs", "vec3", offsetof(struct brush_s, maxs), 0},
		{"brush_faces", "face", offsetof(struct brush_s, brush_faces), 1},
		NULL

	};	
	*/
	
	foreach ($structures as $structure) {
		$structtype = $structure->type;
		$typestr = str_replace(" ", "_", $structtype);
		echo "struct reflection_data reflection_{$typestr}[] = {\n";
		printf("\t// name, is_pointer, type, is_array, dim1, dim2, offset, size\n");
		foreach ($structure->members as $var) {
			
			printf("\t{ %-20s, %d, %-25s, %d, %d, %d, offsetof(%-20s, %-20s), sizeof( ((%-20s *)0)->%-20s) },\n",
				'"'.$var->name.'"',
				$var->isPointer,
				'"'.$var->type.'"',
				$var->is_array,
				$var->dimension0,
				$var->dimension1,
				$structtype,
				$var->name,
				$structtype,
				$var->name
			);
		}
		echo "\tNULL\n";
		echo "};\n";
		
	}
	
	printf("struct str_to_func { char *str; struct reflection_data *data; };\n");
	printf("struct str_to_func all[] = {\n");
	foreach ($structures as $structure) {
		$structtype = $structure->type;
		$typestr = "reflection_" . str_replace(" ", "_", $structtype);
		printf("\t{ %-30s, %-30s },\n",
			'"' . $structtype . '"',
			$typestr
		);
		
	}
	printf("\tNULL\n");
	printf("};\n");
	echo "</pre>\n";