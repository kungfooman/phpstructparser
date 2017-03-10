<?php

	function tokenize($input) {
				
		$split = explode(" ", $input);
		
		$n = strlen($input);
		$curstr = ""; // current string
		
		
		$tokens = [];
		
		for ($i=0; $i<$n; $i++) {
			$cc = $input{$i};
			//echo "check: \"$cc\"\n";
			
			if (in_array($cc, [";", "*", ",", "{", "}", "[", "]", "(", ")"]))
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
	
	
	class Tokenizer {
		var $tokens;
		function Tokenizer($source) {
			$this->tokens = tokenize($source);
		}
	}
	

	
	/*
	class Variable {
		var $is_const = false;
		var $isPointer = false;
		var $type = "";
		var $name = "noname";
		var $is_array = 0;
		var $dimension0 = 0;
		var $dimension1 = 0;
	};
	*/
	
	class Variable {
		var $type = "";
		var $name = "";
		var $is_function = false;
		var $args = [];
		var $is_const = false;
		var $is_pointer = 0; // 3 means pointer-on-pointer-on-pointer e.g.
		
		function implodeNames() {
			$ret = array_reduce( $this->args, function( $prev, $curr ) {
				if ($prev)
					return $prev .= ", {$curr->name}";
				return $prev .= $curr->name;
				
			});			
			return $ret;
		}
	};	
	
	function advance($token) {
		global $tokens;
		global $i;
		global $curtok;
		$curtok = $tokens[$i];
		if ($curtok != $token)
			die(sprintf("expected %s, but got %s", $token, $curtok));
		nexttoken();
	}
	
	function nexttoken() {
		global $tokens;
		global $i;
		global $curtok;
		$i++;
		$curtok = $tokens[$i];
	}
	
	function parse_funcarg() {
		global $tokens;
		global $i;
		global $curtok;
		$curtok = $tokens[$i];
		if ($curtok == ")") {
			
			return false;
		}
		
		if ($curtok == ",")
			advance(",");
		
		
		$tmparg = new Variable;
		
		//$tmparg->type = $curtok = $tokens[$i];
		//
		
		if ($curtok == "const") {
			$tmparg->is_const = true;
			nexttoken();
			
			//printf("curtok after nexttoken is: %s\n", $curtok);
		}
		
			
		
		$tmparg->type = $curtok;
		//printf("got type: %s\n", $tmparg->type);
		nexttoken();
		
		
		if ($curtok == "*") {
			$tmparg->is_pointer++;
			nexttoken();
		}
		if ($curtok == "*") {
			$tmparg->is_pointer++;
			nexttoken();
		}
		
		$tmparg->name = $curtok;
		//printf("got name: %s\n", $tmparg->name);
		nexttoken();
		
		if ($tokens[$i]!="," && $tokens[$i]!=")") {
			printf("current i=%d\n", $i);
			printf("i - 2=%s\n", $tokens[$i - 2]);
			printf("i - 1=%s\n", $tokens[$i - 1]);
			printf("i    =%s\n", $tokens[$i]);
			printf("i + 1=%s\n", $tokens[$i + 1]);
			printf("i + 2=%s\n", $tokens[$i + 2]);
			
			die(sprintf("expected either , or ) but got %s", $tokens[$i]));
		}
		
		return $tmparg;

		// jump over "("
		advance(",");
		
		
		if ($curtok != ",")
			die("expected a ,, but got ".$curtok."");
		
	}
	function parse_func() {
		global $tokens;
		global $i;
		global $curtok;
		global $funcs;
		$func = new Variable;
		$func->is_function = true;
		
		$curtok = $tokens[$i];
		
		
		if ($curtok == "const") {
			$func->is_const = true;
			nexttoken();
		}
		
		$func->type = $curtok;
		//printf("func->type: %s\n", $func->type);
		nexttoken();
		
		if ($curtok == "*") {
			$func->is_pointer++;
			nexttoken();
		}		
		
		$func->name = $curtok;
		//printf("func->name: %s\n", $func->name);
		nexttoken();
		
		advance("(");
		
		if ($tokens[$i] == "void" && $tokens[$i+1]==")")
		{
			goto end;
		}
		
		while (1) {
			$ret = parse_funcarg();
			if ( ! $ret) {
				break;
			}
			
			$func->args[] = $ret;
		}
		
		end:
		while ($tokens[$i]!=";" && $i < count($tokens))
			$i++;
		return $func;
	}
	
	function parse_functions_in_headerfile($source) {
		global $tokens, $i;
		$tokens = tokenize($source);
		//for ($i=1340; $i<1340+20; $i++)
		//	printf("tokens[%d] = %s\n", $i, $tokens[$i]);		
		$funcs = [];
		for ($i=0; $i<count($tokens); $i++) {	
			$func = parse_func();
			$funcs[] = $func;
			if (0) printf("func: type=%s name=%s\n",
				$func->type,
				$func->name
			);
		}
		return $funcs;
	}
	
	function arg2string($arg) {
		$ret = "";
		if ($arg->is_const)
			$ret .= "const ";
		$ret .= $arg->type . " ";
		for ($i=0; $i<$arg->is_pointer; $i++)
			$ret .= "*";
		$ret .= $arg->name;
		return $ret;
	}