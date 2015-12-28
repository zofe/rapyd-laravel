<?php

namespace Zofe\Rapyd\DataForm\Field;

use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

class Iframe extends Field
{
    public $type = "iframe";
    public $src = '';
    public $height = "200";
    public $scrolling = "auto";
    public $frameborder = "0";
    
    
    public function src($src)
    {
        $this->src = $src;
        return $this;
    }
    
    protected function iframe()
    {
        $this->src = $this->parseString($this->src);
        return  sprintf(
            '<IFRAME src="%s" width="100%%" height="%s" scrolling="%s" frameborder="%s" id="%s" onLoad="iframeAutoResize(\'%s\');">
            iframe not supported
            </IFRAME>', $this->src, $this->height, $this->scrolling, $this->frameborder, $this->name, $this->name);
        
        
    }
    
    
    public function build()
    {
        $output = "";
        
        if (parent::build() === false) return;

        switch ($this->status) {
            case "disabled":
            case "show":
            case "create":
            case "modify":
                    $output = $this->iframe();
                    \Rapyd::script("
                        if(typeof iframeAutoResize != 'function'){
                            window.iframeAutoResize = function(id){
                                var newheight;
                                var newwidth;
                
                                if(document.getElementById){
                                    newheight = document.getElementById(id).contentWindow.document .body.scrollHeight;
                                    newwidth = document.getElementById(id).contentWindow.document .body.scrollWidth;
                                }
                
                                document.getElementById(id).height = (newheight) + 'px';
                                document.getElementById(id).width = (newwidth) + 'px';
                            };
                
                        };
                    ");

                    
                break;
            case "hidden":
                $output = "";
                break;

            default:;
        }
        $this->output = "\n".$output."\n". $this->extra_output."\n";
    }
}
