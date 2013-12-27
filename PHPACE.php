<?php
/**
 * PHP interface for ACE (Answer Constraint Engine)
 * http://sweaglesw.org/linguistics/ace/
 * 
 * Useful in obtaining MRS (Minimal Recursion Semantics)
 * 
 * @link https://github.com/agentile/PHP-ACE
 * @version 0.1.0
 * @author Anthony Gentile <asgentile@gmail.com>
 */
class PHPACE {
    
    /**
     * ACE path
     * 
     * relative/absolute path to ACE
     * e.g. /path/to/ace
     */
    protected $ace_path;
    
    /**
     * ERG path
     * 
     * relative/absolute path to ERG .dat file
     * e.g. /path/to/erg.dat
     */
    protected $erg_path;
    
    /**
     * Command line options to use with ACE
     */
    protected $options;
    
    /**
     * Output from ACE
     */
    protected $output = null;
    
    /**
     * Errors from ACE
     */
    protected $errors = null;
    
    /**
     * Constructor!
     * 
     * @param $ace_path string path to ace binary file
     * @param $erg_path string path erg dat file
     * @param $options mixed command line arguments to pass
     * 
     * @return null
     */
    public function __construct($ace_path, $erg_path, $options = array())
    {
        $this->setACE($ace_path);
        $this->setERG($erg_path);
        $this->setOptions($options);
    }
    
    /**
     * Parse a sentence
     * 
     * @param $sentence string
     * 
     * @return mixed
     */
    public function parseSentence($sentence)
    {
        $ret = $this->parseSentences(array($sentence));
        return isset($ret[0]) ? $ret[0] : array();
    }
    
    /**
     * Parse array of sentences
     * 
     * @param $sentences array of sentences
     * 
     * @return mixed
     */
    public function parseSentences($sentences)
    {
        // Reset errors and output
        $this->setErrors(null);
        $this->setOutput(null);
        
        // Make temp file to store sentences.
        $tmpfname = tempnam(DIRECTORY_SEPARATOR . 'tmp', 'phpace');
        chmod($tmpfname, 0644);
        $handle = fopen($tmpfname, "w");

        $str = rtrim(implode("\n", $sentences), "\n");
        
        fwrite($handle, $str);
        fclose($handle);
        
        // Create process to run stanford ner.
        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin 
           1 => array("pipe", "w"),  // stdout 
           2 => array("pipe", "w")   // stderr 
        );
        
        $cmd = escapeshellcmd($this->getACE() . " -g " . $this->getERG() . " -1T " . $tmpfname);
            
        $process = proc_open($cmd, $descriptorspec, $pipes, dirname($this->getACE()));
        
        $output = null;
        $errors = null;
        if (is_resource($process)) {
            // We aren't working with stdin
            fclose($pipes[0]);
            
            // Get output
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            
            // Get any errors
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
        
            // close pipe before calling proc_close in order to avoid a deadlock
            $return_value = proc_close($process);
            if ($return_value == -1) {
                throw new Exception("ACE process returned with an error (proc_close).");
            }
        }
        
        unlink($tmpfname);
        
        if ($errors) {
            $this->setErrors($errors);
        }
        
        if ($output) {
            $this->setOutput(trim($output));
        }
        
        return $this->parseOutput();
    }
    
    /**
     * Build text output from ACE into array structure
     * 
     * @return array
     */
    public function parseOutput()
    {
        $output = $this->getOutput();
        if (!$output) {
            return array();
        }
        
        $arr = array();
        $lines = explode("\n", $output);
        foreach ($lines as $i => $line) {
            if (strpos($line, 'SENT:') === 0) {
                $arr[] = $this->parseMRS($lines[$i+1]);
            }
        }
        
        return $arr;
    }
    
    /**
     * Lets take a ACE MRS string and turn it into an array
     * that we can work with. Nothing fancy here just some substring matching
     */
    public function parseMRS($string)
    {
        $LTOP_POS = strpos($string, 'LTOP:');
        $INDEX_POS = strpos($string, 'INDEX:');
        $RELS_POS = strpos($string, 'RELS:');
        $HCONS_POS = strpos($string, 'HCONS:');
        $LTOP = trim(substr($string, $LTOP_POS + 5, $INDEX_POS - ($LTOP_POS + 5)));
        $INDEX = $this->parseIndex(trim(substr($string, $INDEX_POS + 6, $RELS_POS - ($INDEX_POS + 6))));
        $RELS = $this->parseRels(trim(substr($string, $RELS_POS + 5, $HCONS_POS - ($RELS_POS + 5))));
        $HCONS = explode(' ', trim(trim(trim(substr($string, $HCONS_POS + 6, strrpos($string, ']') - ($HCONS_POS + 6))), '<>')));
    
        // TODO: for INDEX and RELS, parse and break down the nested [] arguments 
        // so that we can work with those easily.
        return array(
            'LTOP'  => $LTOP, 
            'INDEX' => $INDEX, 
            'RELS'  => $RELS, 
            'HCONS' => $HCONS
        );
    }
    
    /**
     * Find the position of a matching closing bracket for a string opening bracket
     */
    public function getMatchingBracket($string, $start_pos, $disregard_context_character = false)
    {
        $length = strlen($string);
        $bracket = 1;
        $disregard = 0;
        foreach (range($start_pos + 1, $length) as $i) { 
            if ($disregard_context_character != false && $string[$i] == $disregard_context_character) {
                if ($disregard == 0) {
                    $disregard = 1;
                } else {
                    $disregard = 0;
                }
            }
            if ($string[$i] == '[' && $disregard == 0) {
                $bracket += 1;
            } else if ($string[$i] == ']' && $disregard == 0) {
                $bracket -= 1;
            }
            if ($bracket == 0) {
                return $i;
            }
        }
    }
    
    /**
     * String manipulation to determine RELS arguments
     */
    public function parseRels($string)
    {
        $args = array();
        $eps = array();
        $start = 0;
        $bracket_start = strpos($string, '[');

        while ($bracket_start !== false) {
            $bracket_end = $this->getMatchingBracket($string, $bracket_start, '"');
            
            $e = trim(substr($string, $bracket_start + 1, $bracket_end - ($bracket_start + 1)));
    
            $arg = array();
            
            if (strpos($e, '<') !== false) {
                $arg['label'] = trim(substr($e, 0, strrpos($e, '<'))); // this probably should be renamed, what is the proper name for this?
                $arg['offset_start'] = (int) substr($e, strpos($e, '<') + 1, strpos($e, ':', strpos($e, '<')) - (strpos($e, '<') + 1));
                $arg['offset_end'] = (int) substr($e, strpos($e, ':', strpos($e, '<')) + 1, strpos($e, '>', strpos($e, ':', strpos($e, '<'))) - (strpos($e, ':', strpos($e, '<')) + 1));
            }
                
            if (strpos($e, 'LBL: ') !== false) {
                $arg['LBL'] = substr($e, strpos($e, 'LBL: ') + 5, strpos($e, ' ', strpos($e, 'LBL: ') + 5) - (strpos($e, 'LBL: ') + 5));
            }
            
            if (strpos($e, 'CARG: ') !== false) {
                $arg['CARG'] = substr($e, strpos($e, 'CARG: ') + 6, strpos($e, ' ', strpos($e, 'CARG: ') + 6) - (strpos($e, 'CARG: ') + 6));
            }
            
            if (strpos($e, 'RSTR: ') !== false) {
                $arg['RSTR'] = substr($e, strpos($e, 'RSTR: ') + 6, strpos($e, ' ', strpos($e, 'RSTR: ') + 6) - (strpos($e, 'RSTR: ') + 6));
            }
                
            if (strpos($e, 'BODY: ') !== false) {
                $arg['BODY'] = substr($e, strpos($e, 'BODY: ') + 6, strpos($e, ' ', strpos($e, 'BODY: ') + 6) - (strpos($e, 'BODY: ') + 6));
            }
                
            if (strpos($e, 'L-INDEX: ') !== false) {
                $arg['L-INDEX'] = $this->parseIndex($this->getArgValue($e, 'L-INDEX'));
            }
            
            if (strpos($e, 'R-INDEX: ') !== false) {
                $arg['R-INDEX'] = $this->parseIndex($this->getArgValue($e, 'R-INDEX'));
            }
                
            if (strpos($e, 'L-HNDL: ') !== false) {
                $arg['L-HNDL'] = $this->parseIndex($this->getArgValue($e, 'L-HNDL'));
            }
            
            if (strpos($e, 'R-HNDL: ') !== false) {
                $arg['R-HNDL'] = $this->parseIndex($this->getArgValue($e, 'R-HNDL'));
            }
    
            // Handle ARG
            preg_match_all('/ARG:/', $e, $matches);
            if (is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
                foreach ($matches as $m) {
                    $arg['ARG'] = $this->parseIndex($this->getArgValue($e, substr($m[0], 0, -1)));
                }
            }
                
            // Handle ARGN
            $arg['ARGN'] = array();
            preg_match_all('/ARG[0-9]:/', $e, $matches);
            if (is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
                foreach ($matches as $m) {
                    $arg['ARGN'][substr($m[0], 0, -1)] = $this->parseIndex($this->getArgValue($e, substr($m[0], 0, -1)));
                }
            }
            
            $eps[] = $arg;
            
            // find next eps
            $bracket_start = strpos($string, '[', $bracket_end + 1);
        }
            
        return $eps;
    }
    
    /**
     * Given ARG: x4 [ x PERS: 3 NUM: sg ] or ARG: h4 return x4 [ x PERS: 3 NUM: sg ] or h4 respectively
     * Making sure not to collide with other args/nested brackets
     */
    public function getArgValue($e, $key)
    {
        $s = strpos($e, $key . ': ');
        $s_bracket_pos = strpos($e, ' ', $s + (strlen($key) + 2)) + 1;
        $s_bracket = isset($e[$s_bracket_pos]) ? $e[$s_bracket_pos] : null;

        if ($s_bracket == '[') {
            $e_bracket = $this->getMatchingBracket($e,$s_bracket_pos);
            return substr($e, $s + (strlen($key) + 2), ($e_bracket + 1) - ($s + (strlen($key) + 2)));
        } else {
            preg_match_all("/[A-Z0-9-]+:/", substr($e, $s + (strlen($key) + 2)), $matches);
            if (is_array($matches) && isset($matches[0]) && isset($matches[0][0])) {
                return substr($e, $s + (strlen($key) + 2), strpos($e, $matches[0][0], strpos($e, ($s + (strlen($key) + 2) - 1))));
            } else {
                // assume it is the last arg in the string and there is none after it
                return substr($e, strpos($e, $key . ': ') + (strlen($key) + 2));
            }
        }
    }
    
    /**
     * Parse Index segment of MRS rep
     * 
     * I don't think e2 is called root or the e is child, 
     * but I don't know what they are called properly
     */
    public function parseIndex($string)
    {
        $arr = array();
    
        if (strpos($string, ' ') !== false) {
            $arr['root'] = substr($string, 0, strpos($string, ' '));
        } else {
            $arr['root'] = $string;
        }
        
        if (strpos($string, '[') !== false) {
            $arr['child'] = substr($string, strpos($string, '[') + 2, strpos($string, ' ', strpos($string, '[') + 2) - (strpos($string, '[') + 2));
        }
        
        if (strpos($string, 'SF: ') !== false) {
            $arr['SF'] = substr($string, strpos($string, 'SF: ') + 4, strpos($string, ' ', strpos($string, 'SF: ') + 4) - (strpos($string, 'SF: ') + 4));
        }
        
        if (strpos($string, 'TENSE: ') !== false) {
            $arr['TENSE'] = substr($string, strpos($string, 'TENSE: ') + 7, strpos($string, ' ', strpos($string, 'TENSE: ') + 7) - (strpos($string, 'TENSE: ') + 7));
        }
        
        if (strpos($string, 'MOOD: ') !== false) {
            $arr['MOOD'] = substr($string, strpos($string, 'MOOD: ') + 6, strpos($string, ' ', strpos($string, 'MOOD: ') + 6) - (strpos($string, 'MOOD: ') + 6));
        }

        if (strpos($string, 'PROG: ') !== false) {
            $arr['PROG'] = substr($string, strpos($string, 'PROG: ') + 6, strpos($string, ' ', strpos($string, 'PROG: ') + 6) - (strpos($string, 'PROG: ') + 6));
        }

        if (strpos($string, 'PERF: ') !== false) {
            $arr['PERF'] = substr($string, strpos($string, 'PERF: ') + 6, strpos($string, ' ', strpos($string, 'PERF: ') + 6) - (strpos($string, 'PERF: ') + 6));
        }
        
        if (strpos($string, 'PERS: ') !== false) {
            $arr['PERS'] = substr($string, strpos($string, 'PERS: ') + 6, strpos($string, ' ', strpos($string, 'PERS: ') + 6) - (strpos($string, 'PERS: ') + 6));
        }

        if (strpos($string, 'GEND: ') !== false) {
            $arr['GEND'] = substr($string, strpos($string, 'GEND: ') + 6, strpos($string, ' ', strpos($string, 'GEND: ') + 6) - (strpos($string, 'GEND: ') + 6));
        }
        
        if (strpos($string, 'NUM: ') !== false) {
            $arr['NUM'] = substr($string, strpos($string, 'NUM: ') + 5, strpos($string, ' ', strpos($string, 'NUM: ') + 5) - (strpos($string, 'NUM: ') + 5));
        }
        
        if (strpos($string, 'IND: ') !== false) {
            $arr['IND'] = substr($string, strpos($string, 'IND: ') + 5, strpos($string, ' ', strpos($string, 'IND: ') + 5) - (strpos($string, 'IND: ') + 5));
        }

        return $arr;
    }
    
    /**
     * Options setter
     *
     * @param $options mixed options
     *
     * @return null
     */
    public function setOptions($options)
    {
        $this->options = (array) $options;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    /**
     * ACE path setter
     *
     * @param $ace_path string
     *
     * @return null
     */
    public function setACE($ace_path)
    {
        if (file_exists($ace_path)) {
            $this->ace_path = $ace_path;
        } else {
            throw new Exception("ACE file path does not exist.");
        }
    }
    
    /**
     * ACE path getter
     *
     * @return mixed
     */
    public function getACE()
    {
        return $this->ace_path;
    }
    
    /**
     * ERG path setter
     *
     * @param $erg_path string
     *
     * @return null
     */
    public function setERG($erg_path)
    {
        if (file_exists($erg_path)) {
            $this->erg_path = $erg_path;
        } else {
            throw new Exception("ERG file path does not exist.");
        }
    }
    
    /**
     * ERG path getter
     *
     * @return mixed
     */
    public function getERG()
    {
        return $this->erg_path;
    }
    
    /**
     * Output setter
     *
     * @param $output
     *
     * @return null
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
    
    /**
     * Output getter
     *
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Errors setter
     *
     * @param $errors 
     *
     * @return null
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
    
    /**
     * Errors getter
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
