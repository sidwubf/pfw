<?php

class PFWTemplate {
    //配置变量
    private $base_path = '';
    private $reset_vars = TRUE;

    //变量开始结束符号
    private $ldelim = '{$';
    private $rdelim = '}';

    //开始符号
    private $BAldelim = '<!--';
    private $BArdelim = '-->';

    //结束符号
    private $EAldelim = '<!--/';
    private $EArdelim = '-->';

    //内部变量
    private $scalars = array();
    private $arrays  = array();
    private $carrays = array();
    private $ifs     = array();

    private $contents = "";

    function __construct($base_path = NULL, $reset_vars = TRUE) {
        if ($base_path)
			$this->base_path = $base_path;
        $this->reset_vars = $reset_vars;
    }

	//赋值函数
    function set($tag, $var, $if = NULL) {
        if (is_array($var)) {
            $this->arrays[$tag] = $var;
            if ($if) {
                $result = $var ? TRUE : FALSE;
                $this->ifs[] = $tag;
                $this->scalars[$tag] = $result;
            }
        } else {
            $this->scalars[$tag] = $var;
            if ($if)
				$this->ifs[] = $tag;
        }
    }

    function set_cloop($tag, $array, $cases) {
        $this->carrays[$tag] = array(
            'array' => $array,
            'cases' => $cases);
    }

    function reset_vars($scalars, $arrays, $carrays, $ifs) {
        if($scalars)
			$this->scalars = array();
        if($arrays)
			$this->arrays  = array();
        if($carrays)
			$this->carrays = array();
        if($ifs)
			$this->ifs     = array();
    }

    function reset() {
        $this->reset_vars(TRUE, TRUE, TRUE, TRUE);
    }

    function get_tags($tag, $directive) {
        $tags['b'] = $this->BAldelim . $directive . $tag . $this->BArdelim;
        $tags['e'] = $this->EAldelim . $directive . $tag . $this->EArdelim;
        return $tags;
    }

    function get_tag($tag) {
        return $this->ldelim . $tag . $this->rdelim;
    }

    function get_statement($t, &$contents) {
        $tag_length = strlen($t['b']);
        $fpos = strpos($contents, $t['b']) + $tag_length;
        $lpos = strpos($contents, $t['e']);
        $length = $lpos - $fpos;

        return substr($contents, $fpos, $length);
    }

    function parse($contents) {
        if (!empty($this->ifs)) {
            foreach ($this->ifs as $value) {
                $contents = $this->parse_if($value, $contents);
            }
        }

        foreach ($this->scalars as $key => $value) {
            $contents = str_replace($this->get_tag($key), $value, $contents);
        }

        foreach ($this->arrays as $key => $array) {
            $contents = $this->parse_loop($key, $array, $contents);
        }

        foreach ($this->carrays as $key => $array) {
            $contents = $this->parse_cloop($key, $array, $contents);
        }

        if ($this->reset_vars)
			$this->reset_vars(FALSE, TRUE, TRUE, FALSE);

        return $contents;
    }

    function parse_if($tag, $contents) {
        $t = $this->get_tags($tag, 'if:');

        $entire_statement = $this->get_statement($t, $contents);

        $tags['b'] = NULL;
        $tags['e'] = $this->BAldelim . 'else:' . $tag . $this->BArdelim;

        if (($else = strpos($entire_statement, $tags['e']))) {
            $if = $this->get_statement($tags, $entire_statement);
            $else = substr($entire_statement, $else + strlen($tags['e']));
        } else {
            $else = NULL;
            $if = $entire_statement;
        }

        $this->scalars[$tag] ? $replace = $if : $replace = $else;

        return str_replace($t['b'] . $entire_statement . $t['e'], $replace, $contents);
    }

    function parse_loop($tag, $array, $contents) {
        $t = $this->get_tags($tag, 'loop:');
        $loop = $this->get_statement($t, $contents);
        $parsed = NULL;

        foreach ($array as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $i = $loop;
                foreach ($value as $key2 => $value2) {
                    if (!is_array($value2)) {
                        $i = str_replace($this->get_tag($tag . '[].' . $key2), $value2, $i);
                    } else {
                        $i = $this->parse_loop($tag . '[].' . $key2, $value2, $i);
                    }
                }
            } else if(is_string($key) && !is_array($value)) {
                $contents = str_replace($this->get_tag($tag . '.' . $key), $value, $contents);
            } else if(!is_array($value)) {
                $i = str_replace($this->get_tag($tag . '[]'), $value, $loop);
            }

            if (isset($i))
				$parsed .= rtrim($i);
        }

        return str_replace($t['b'] . $loop . $t['e'], $parsed, $contents);
    }

    function parse_cloop($tag, $array, $contents) {
        $t = $this->get_tags($tag, 'cloop:');
        $loop = $this->get_statement($t, $contents);

        $array['cases'][] = 'default';
        $case_content = array();
        $parsed = NULL;

        foreach ($array['cases'] as $case) {
            $ctags[$case] = $this->get_tags($case, 'case:');
            $case_content[$case] = $this->get_statement($ctags[$case], $loop);
        }

        foreach ($array['array'] as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                if (isset($value['case']))
					$current_case = $value['case'];
                else
					$current_case = 'default';
                unset($value['case']);
                $i = $case_content[$current_case];

                foreach ($value as $key2 => $value2) {
                    $i = str_replace($this->get_tag($tag . '[].' . $key2), $value2, $i);
                }
            }

            $parsed .= rtrim($i);
        }

        return str_replace($t['b'] . $loop . $t['e'], $parsed, $contents);
    }

    //包含文件函数
    function files($name) {
        $filename = $this->base_path . $name . EXT;
        $handle = fopen($filename, 'rb');
        if (!$handle) {
            echo 'no such file';
            exit;
        }
        $template_cache = fread($handle, filesize($filename));
        $this->contents .= $template_cache;
        fclose($handle);
    }

    //输出函数
    function output() {
        $all = $this->parse($this->contents);
        echo $all;
    }

    function get(){
        $x = $this->parse($this->contents);
        $this->reset();
        return $x;
    }

    function join($s){
        $this->contents .= $s;
    }
}


