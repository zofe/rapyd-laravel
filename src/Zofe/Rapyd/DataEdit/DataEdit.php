<?php

namespace Zofe\Rapyd\DataEdit;

use Zofe\Rapyd\DataForm\DataForm;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class DataEdit extends DataForm
{

    //flow
    protected $postprocess_url = "";
    protected $undo_url = "";
    public $back_url = "";
    public $back_save = false;
    public $back_delete = true;
    public $back_cancel = false;
    public $buttons = array();
    public $back_cancel_save = false;
    public $back_cancel_delete = false;

    public function __construct()
    {
        parent::__construct();
        $this->process_url = '';
    }

    protected function sniffStatus()
    {
        $this->status = "idle";
        ///// show /////
        if ($this->url->value('show' . $this->cid)) {
            $this->status = "show";
            $this->process_url = "";
            if (!$this->find($this->url->value('show' . $this->cid))) {
                $this->status = "unknow_record";
            }
            ///// modify /////
        } elseif ($this->url->value('modify' . $this->cid)) {
            $this->status = "modify";

            $this->process_url = $this->url->replace('modify' . $this->cid, 'update' . $this->cid)->get();
            if (!$this->find($this->url->value('modify' . $this->cid))) {
                $this->status = "unknow_record";
            }
            ///// create /////
        } elseif ($this->url->value('show' . $this->cid . "|modify" . $this->cid . "|create" . $this->cid . "|delete" . $this->cid) === false) {
            $this->status = "create";
            $this->process_url = $this->url->append('insert' . $this->cid, 1)->get();
        } elseif ($this->url->value('create' . $this->cid)) {
            $this->status = "create";
            $this->process_url = $this->url->replace('create' . $this->cid, 'insert' . $this->cid)->get();
            ///// delete /////
        } elseif ($this->url->value('delete' . $this->cid)) {
            $this->status = "delete";
            $this->process_url = $this->url->replace('delete' . $this->cid, 'do_delete' . $this->cid)->get();
            $this->undo_url = $this->url->replace('delete' . $this->cid, 'show' . $this->cid);
            if (!$this->find($this->url->value('delete' . $this->cid))) {
                $this->status = "unknow_record";
            }
        } else {
            $this->status = "unknow_record";
        }
    }

    protected function find($id)
    {
        $model = $this->model;
        $this->model = $model::find($id);
        return $this->model->exists;
    }

    protected function sniffAction()
    {
  
        ///// insert /////
        if ($this->url->value('insert' . $this->cid)) {
            $this->action = "insert";
            ///// update /////
        } elseif ($this->url->value('update' . $this->cid)) {
            $this->action = "update";
            $this->process_url = $this->url->append('update', $this->url->value('update' . $this->cid))->get();
            if (!$this->find($this->url->value('update' . $this->cid))) {
                $this->status = "unknow_record";
            }
            ///// delete /////
        } elseif ($this->url->value("do_delete" . $this->cid)) {
            $this->action = "delete";
            if (!$this->find($this->url->value("do_delete" . $this->cid))) {
                $this->status = "unknow_record";
            }
        }
    }


    protected function process()
    {
        $result = parent::process();
        switch ($this->action) {
            case "update":

                if ($this->on("error")) {
                    $this->status = "modify";
                    //$this->process_url = rpd_url_helper::get_url();
                }
                if ($this->on("success")) {

                    //settare messaggio in sessione o in variabile
                    $this->status = "modify";
                    $this->redirect = $this->url->replace('update' . $this->cid, 'show' . $this->cid)->get();

                }

                break;
            case "insert":

                if ($this->on("error")) {
                    $this->status = "create";
                }
                if ($this->on("success")) {
                    $this->status = "show";
                    $this->redirect = $this->url->remove('insert' . $this->cid)->append('show' . $this->cid, $this->model->getKey())->get();
                }
                break;
            case "delete":
                if ($this->on("error")) {

                }
                if ($this->on("success")) {
                    $this->message("record deleted");
                }
                break;
        }
    }

    protected function buildButtons()
    {
        //show
        if ($this->status == "show") {

            $this->link($this->url->replace('show' . $this->cid, 'modify' . $this->cid)->get(), trans('rapyd::rapyd.modify'), "TR");
            //$this->link($this->url->replace('show' . $this->cid, 'delete' . $this->cid)->get(), "delete",  "TR");
        }
        //modify
        if ($this->status == "modify") {

            $this->link($this->url->replace('modify' . $this->cid, 'show' . $this->cid)
                ->replace('update' . $this->cid, 'show' . $this->cid)->get(), trans('rapyd::rapyd.undo'), "TR");
            $this->submit(trans('rapyd::rapyd.save'), 'actions');
        }
        //modify
        if ($this->status == "create") {
            $this->submit(trans('rapyd::rapyd.save'), 'actions');
        }
    }

    public function getEdit($view = '')
    {

        return $this->getForm($view);
    }

}
