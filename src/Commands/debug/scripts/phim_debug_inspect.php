<?php
function phim_debug_inspect($variable, $isSpecificVar = false, $depth, $variableName = '')
{
    $output = "";
    if (strlen($variableName) > 0) {
        $output .= "Inspecting variable: $variableName\n";
    }
    if (!$isSpecificVar) {
        $variable = array_diff_key($variable, array_flip(
            array('GLOBALS', '_FILES', '_COOKIE', '_POST', '_GET', '_SERVER', '_ENV', '_REQUEST', 'argc', 'argv')
        ));
        $output .= phim_var_debug($variable, $depth);
    } else {
        $output .= phim_var_debug($variable, $depth);
    }

    $output .= "\n\n";

    $output .= "Call Trace:\n";

    $e = new \Exception();
    $trace = explode("\n", $e->getTraceAsString());
    $trace = array_reverse($trace);
    array_shift($trace);
    $length = count($trace);
    $result = array();

    for ($i = 0; $i < $length; $i++)
    {
        array_push($result, ($i + 1)  . '.' . substr($trace[$i], strpos($trace[$i], ' ')));
    }

    $output .= implode("\n", $result);
    $output .= "\n\n";
    echo $output;
}

function phim_var_debug($variable,$depth,$strlen=300,$width=200,$i=0,&$objects = array())
{
    $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
    $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');

    $string = '';

    switch(gettype($variable)) {
    case 'boolean':      $string.= $variable?'true':'false'; break;
    case 'integer':      $string.= $variable;                break;
    case 'double':       $string.= $variable;                break;
    case 'resource':     $string.= '[resource]';             break;
    case 'NULL':         $string.= "null";                   break;
    case 'unknown type': $string.= '???';                    break;
    case 'string':
        $len = strlen($variable);
        if ($len<$strlen) $string.= $variable;
        else $string.= $variable;
        break;
    case 'array':
        $len = count($variable);
        if ($i==$depth) $string.= 'array('.$len.') {...}';
        elseif(!$len) $string.= 'array(0) {}';
        else {
            $keys = array_keys($variable);
            $spaces = str_repeat(' ',$i*2);
            $string.= "array($len)\n".$spaces.'{';
            $count=0;
            foreach($keys as $key) {
                if ($count==$width) {
                    $string.= "\n".$spaces."  ...";
                    break;
                }
                $string.= "\n".$spaces."  [$key] => ";
                $string.= phim_var_debug($variable[$key],$strlen,$width,$depth,$i+1,$objects);
                $count++;
            }
            $string.="\n".$spaces.'}';
        }
        break;
    case 'object':
        $id = array_search($variable,$objects,true);
        if ($id!==false)
            $string.=get_class($variable).' {...}';
        else if($i==$depth)
            $string.=get_class($variable).' {...}';
        else {
            $id = array_push($objects,$variable);
            $array = (array)$variable;
            $spaces = str_repeat(' ',$i*2);
            $string.= get_class($variable)."\n".$spaces.'{';
            $properties = array_keys($array);
            foreach($properties as $property) {
                $name = str_replace("\0",':',trim($property));
                $string.= "\n".$spaces."  [$name] => ";
                $string.= phim_var_debug($array[$property],$strlen,$width,$depth,$i+1,$objects);
            }
            $string.= "\n".$spaces.'}';
        }
        break;
    }

    if ($i>0) return $string;

    return $string;
}
