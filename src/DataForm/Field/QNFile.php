<?php namespace Zofe\Rapyd\DataForm\Field;

use Zofe\Rapyd\Rapyd;
use Illuminate\Html\FormFacade as Form;

class QNFile extends Field
{
    private $parts = [];
    public $type = "text";
    private $fileType = 'image';

    public function part($name)
    {
        $this->parts [] = $name;

        return $this;
    }

    public function fileType($type)
    {
        $this->fileType = $type;

        return $this;
    }

    public function build()
    {
        $output = "";
        Rapyd::js('qn-file/plupload.full.min.js');
        Rapyd::js('qn-file/qiniu.js');
        Rapyd::js('qn-file/upload.js');

        if (parent::build() === false) {
            return;
        }

        $attrs = [
            'class' => 'qn-upload',
            'data-type' => $this->fileType,
            'data-required' => $this->required ?: '',
            'data-status' => $this->status,
        ];

        $this->attributes = array_merge($this->attributes, $attrs);

        $links = [];
        foreach(json_decode($this->value ?: '{}', true) as $name => $value) {
            $value = (isset($value) && is_array($value)) ? $value : [];
            foreach($value as $key) {
                if ($this->fileType == 'doc') {
                    $links[$key] = [
                        'url' => config('rapyd.qn-doc-store')->url($key),
                        'title' => config('rapyd.qn-doc-store')->title($key)
                    ];
                } elseif ($this->fileType == 'image') {
                    $links[$key] = [
                        'url' => config('rapyd.qn-image-store')->url($key),
                        'small' => config('rapyd.qn-small-image-store')->url($key),
                    ];
                }
            }
        }

        switch ($this->status) {
            case "disabled":
            case "show":
                $output .= "<span name=\"{$this->db_name}\" style=\"display: none;\">{$this->value}</span>";
                break;

            case "create":
            case "modify":
                $output .= Form::text($this->name, $this->value);
                break;

            case "hidden":
                $output = Form::hidden($this->name, $this->value);
                break;
            default:
                ;
        }

        // 需要上传的项目
        $output .= "<div class=\"qn-upload\" data-name=\"{$this->db_name}\" data-status=\"{$this->status}\" data-required=\"{$this->required}\" style='display: none;'>";
        foreach ($this->parts as $part) {
            $output .= "<span class=\"qn-upload-part\" data-name=\"{$part}\" data-type=\"{$this->fileType}\"></span>";
        }
        $output .= '<span class="qn-upload-links">' . json_encode($links) . '</span>';
        $output .= '</div>';

        $this->output = "\n" . $output . "\n" . $this->extra_output . "\n";
    }

}
