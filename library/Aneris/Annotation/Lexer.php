<?php
namespace Aneris\Annotation;

class Lexer
{
	protected $tokens;
    protected $splited;

	public function __construct($docComment,$location)
	{
        $this->location = $location;
        //$c = preg_match('@^/\\*\\*(.*)\\*/$@s', $docComment,$match);
        //if($c) {
        //    $source = "<?php\n" . trim($match[1]);
        //    $tokens = token_get_all($source);
        //} else {
        //    $tokens = array();
        //}
        $docComment = trim($docComment," /*\n\t");
        $lines = explode("\n", $docComment);
        $doc = '';
        foreach($lines as $line) {
            $doc .= ltrim($line," \t*")."\n";
        }
        if(strpos($doc, '@')===false) {
            $this->tokens = array();
        } else {
            $this->tokens = token_get_all("<?php\n".$doc);
        }
	}

    public function get($skipSpace=true)
    {
        while(true) {
            if($this->splited) {
                $token = current($this->splited);
            }
            else
                $token = current($this->tokens);
            if(is_array($token)) {
                $code = $token[0];
                if($code == T_CONSTANT_ENCAPSED_STRING) {
                    if(substr($token[1],0,1) == "'")
                        $text = trim($token[1],"'");
                    else
                        $text = trim($token[1],'"');
                } else {
                    $text = $token[1];
                }
                switch($code) {
                    case T_ABSTRACT:
                    case T_EVAL:
                    case T_ARRAY:
                    case T_AS:
                    case T_CASE:
                    case T_CATCH:
                    case T_CLASS:
                    case T_CLONE:
                    case T_CONTINUE:
                    case T_DECLARE:
                    case T_DEFAULT:
                    case T_DO:
                    case T_ECHO:
                    case T_ELSE:
                    case T_ELSEIF:
                    case T_EMPTY:
                    case T_ENDDECLARE:
                    case T_ENDFOR:
                    case T_ENDFOREACH:
                    case T_ENDIF:
                    case T_ENDSWITCH:
                    case T_ENDWHILE:
                    case T_EVAL:
                    case T_EXIT:
                    case T_EXTENDS:
                    case T_FINAL:
                    case T_FOR:
                    case T_FOREACH:
                    case T_FUNCTION:
                    case T_GLOBAL:
                    case T_GOTO:
                    case T_IF:
                    case T_IMPLEMENTS:
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE:
                    case T_INSTANCEOF:
                    case T_INTERFACE:
                    case T_ISSET:
                    case T_LIST:
                    case T_LOGICAL_AND:
                    case T_LOGICAL_OR:
                    case T_LOGICAL_XOR:
                    case T_NEW:
                    case T_PRINT:
                    case T_PRIVATE:
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                    case T_RETURN:
                    case T_STATIC:
                    case T_SWITCH:
                    case T_THROW:
                    case T_TRY:
                    case T_UNSET:
                    case T_VAR:
                    case T_WHILE:
                        $code=T_STRING;
                        break;
                    case T_ARRAY_CAST:
                    case T_BOOL_CAST:
                    case T_DOUBLE_CAST:
                    case T_INT_CAST:
                    case T_OBJECT_CAST:
                    case T_STRING_CAST:
                    case T_UNSET_CAST:
                        next($this->tokens);
                        $keyword = trim($token[1],'()');
                        $token = array(T_STRING,$keyword,strlen($keyword));
                        if($this->splited==null)
                            $this->splited = array();
                        array_unshift($this->splited,')');
                        array_unshift($this->splited,$token);
                        array_unshift($this->splited,'(');
                        $code = '(';
                        $text = '(';
                        break;
                    default:
                        if(defined("T_CALLABLE") && $code==T_CALLABLE) {
                            $code=T_STRING;
                        }
                        break;
                }
            } else {
                $code = $token;
                $text = $token;
            }
            if($code!=T_WHITESPACE || !$skipSpace)
                break;
            next($this->tokens);
        }
        return array($code,$text);
    }

    public function next()
    {
        if($this->splited) {
            array_shift($this->splited);
            if(count($this->splited)==0)
                $this->splited = null;
            return;
        }
        next($this->tokens);
    }

    public function getLocation()
    {
        return $this->location;
    }
}