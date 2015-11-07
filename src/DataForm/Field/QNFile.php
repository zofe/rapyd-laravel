<?php namespace Zofe\Rapyd\DataForm\Field;

use Barryvdh\Debugbar\JavascriptRenderer;
use Zofe\Rapyd\Helpers\HTML;
use Zofe\Rapyd\Rapyd;
use Illuminate\Html\FormFacade as Form;

class QNFile extends Field
{
    private $parts = [];
    public $type = "text";
    private $fileType = 'image';
    private $fileMode = 'private';

    public function part($name, $isRequred = false)
    {
        $this->parts [$name] = ['required' => $isRequred];

        return $this;
    }

    public function fileType($type)
    {
        $this->fileType = $type;

        return $this;
    }

    public function fileMode($mode = 'private')
    {
        $this->fileMode = $mode;

        return $this;
    }

    private function getFileExt()
    {
        return array_get([
            'image' => 'jpeg,jpg,gif,png',
            'document' => 'pdf,xls,xlsx,doc,docx,txt',
        ], $this->fileType);
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
        foreach (json_decode($this->value ?: '{}', true) as $name => $value) {
            $value = (isset($value) && is_array($value)) ? $value : [];
            foreach ($value as $key) {
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
                } else {
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

        $title = ($this->fileType == 'image') ? '图片' : '文档';
        $domain = config("services.qiniu.bucket.{$this->fileMode}.domain");
        $upUrl = route('rapyd.qn.up-token') . "/{$this->fileType}/{$this->fileMode}";
        if ($this->fileType == 'image' && $this->fileMode == 'private') {
            $downUrl = '/admin/image-download-url?width=100&height=100';
        } else {
            $downUrl = '';
        }

        // 需要上传的项目
        $parts = '';
        foreach ($this->parts as $part => $partAttr) {
            $required = array_get($partAttr, 'required') ? 'true' : 'false';

            $parts .= <<<HTML
                <span class="qn-upload-part" data-name="{$part}"></span>
                <script>
                $(document).ready(function () {
                    $('.qn-upload-part[data-name={$part}]').qnUploader({
                        name: '{$part}',
                        type: '{$this->fileType}',
                        typeTitle: '{$title}',
                        ext: '{$this->getFileExt()}',
                        required: {$required},
                        status: '{$this->status}',
                        inputName: '{$this->db_name}',
                        mode: '{$this->fileMode}',
                        domain: '{$domain}',
                        upUrl: '{$upUrl}',
                        downUrl: '{$downUrl}'
                    });
                })
                </script>
HTML;

        }

        $links = json_encode($links);
        $output .= <<<HTML
        <div class="qn-upload hide" data-name="{$this->db_name}">
            {$parts}
            <span class="qn-upload-links">{$links}</span>
        </div>
HTML;
        $this->output = "\n" . $output . "\n" . $this->extra_output . "\n";
    }
}
