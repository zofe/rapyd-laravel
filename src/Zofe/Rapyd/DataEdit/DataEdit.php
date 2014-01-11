<?php

namespace Zofe\Rapyd\DataEdit;

use Zofe\Rapyd\DataForm\DataForm;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

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
        } elseif ($this->url->value('modify' . $this->cid )) {
            $this->status = "modify";
            
            $this->process_url = $this->url->replace('modify' . $this->cid, 'update' . $this->cid)->get();
            if (!$this->find($this->url->value('modify' . $this->cid))) {
                $this->status = "unknow_record";
            }
            ///// create /////
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
        } elseif ($this->url->value('inset' . $this->cid . '|update' . $this->cid . '|do_delete' . $this->cid)) {
            //status is idle.. action is executed
        } else {
            $this->status = "unknow_record";
        }
    }

    protected function find($id) {
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
    
    
	protected function process() {
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

				}
                
			break;
			case "insert":
                
				if ($this->on("error")) {
					$this->status = "create";
				}
				if ($this->on("success")) {
                    $this->status = "show";
				}
			break;
			case "delete":
				if ($this->on("error")) {
					
                    //$this->build_buttons();
					//return $this->build_message('!errore durante la cancellazione');
				}
				if ($this->on("success")) {
					/*$this->build_buttons();
					if ($this->back_delete) {
                        header("Location: ".$this->back_url);
						die();
					} else {
						return $this->build_message('record cancellato');
					}*/
				}
			break;
		}
	}
    
    protected function buildButtons()
    {
        //show
        if ($this->status == "show") {

            $this->link($this->url->replace('show' . $this->cid, 'modify' . $this->cid)->get(), "modify",  "TR");
            $this->link($this->url->replace('show' . $this->cid, 'delete' . $this->cid)->get(), "delete",  "TR");
            //$this->submit('save');
        }
        //modify
        if ($this->status == "modify") {

            $this->link($this->url->replace('modify' . $this->cid, 'show' . $this->cid)
                                  ->replace('update' . $this->cid, 'show' . $this->cid)->get(), "undo",  "TR");
            //$this->link($this->url->replace('show' . $this->cid, 'delete' . $this->cid), "delete",  "TR");
            $this->submit('save');
        }
        //modify
        if ($this->status == "create") {

            //$this->link($this->url->replace('create' . $this->cid, 'show' . $this->cid)
            //                      ->replace('insert' . $this->cid, 'show' . $this->cid)->get(), "undo",  "TR");
            //$this->link($this->url->replace('show' . $this->cid, 'delete' . $this->cid), "delete",  "TR");
            $this->submit('save');
        }
    }
    
    public function getEdit($view = '')
    {
       
        return $this->getForm($view);
    }
    
  /*  
	protected function modify_button($config = null) {
		$caption = (isset($config['caption'])) ? $config['caption'] : rpd::lang('btn.modify');
		if ($this->status_is("show") && rpd_url_helper::value('show' . $this->cid)) {
			$modify_url = rpd_url_helper::replace('show' . $this->cid, 'modify' . $this->cid);
			$action = "javascript:window.location='" . $modify_url . "'";
			$this->button("btn_modify", $caption, $action, "TR");
		}
	}

	protected function delete_button($config = null) {
		$caption = (isset($config['caption'])) ? $config['caption'] : rpd::lang('btn.delete');
		if ($this->status_is("show") && rpd_url_helper::value('show' . $this->cid)) {
			$delete_url = rpd_url_helper::replace('show' . $this->cid, 'delete' . $this->cid);
			$action = "javascript:window.location='" . $delete_url . "'";
			$this->button("btn_delete", $caption, $action, "TR");
		} elseif ($this->status_is("delete")) {
			$action = "javascript:window.location='" . $this->process_url . "'";
			$this->button("btn_delete", $caption, $action, "BL");
		}
	}
	// --------------------------------------------------------------------
	public function save_button($config = null) {
		$caption = (isset($config['caption'])) ? $config['caption'] : rpd::lang('btn.save');
		if ($this->status_is(array("create", "modify"))) {
			$this->submit("btn_submit", $caption, "BL");
		}
	}
	// --------------------------------------------------------------------
	protected function undo_button($config = null) {
		$caption = (isset($config['caption'])) ? $config['caption'] : rpd::lang('btn.undo');
		if ($this->status_is("create")) {
			$action = "javascript:window.location='{$this->back_url}'";
			$this->button("btn_undo", $caption, $action, "TR");
		} elseif ($this->status_is("modify")) {
			if (($this->back_cancel_save === FALSE) || ($this->back_cancel === FALSE)) {
				//is modify
				if (rpd_url_helper::value('modify' . $this->cid)) {
					$undo_url = rpd_url_helper::replace('modify' . $this->cid, 'show' . $this->cid);
				}
				//is modify on error
				elseif (rpd_url_helper::value('update' . $this->cid)) {
					$undo_url = rpd_url_helper::replace('update' . $this->cid, 'show' . $this->cid);
				}
				$action = "javascript:window.location='" . $undo_url . "'";
			} else {
				$action = "javascript:window.location='{$this->back_url}'";
			}
			$this->button("btn_undo", $caption, $action, "TR");
		} elseif ($this->status_is("delete")) {
			if (($this->back_cancel_delete === FALSE) || ($this->back_cancel === FALSE)) {
				$action = "javascript:window.location='{$this->undo_url}'";
			} else {
				$action = "javascript:window.location='{$this->back_url}'";
			}
			$this->button("btn_undo", $caption, $action, "TR");
		}
	}

	protected function back_button($config = null) {

		$caption = (isset($config['caption'])) ? $config['caption'] : rpd::lang('btn.back');
		if ($this->status_is(array("show", "unknow_record")) || $this->action_is("delete")) {
			$action = "javascript:window.location='{$this->back_url}'";
			$this->button("btn_back", $caption, $action, "BL");
		}
	}
*/
    
    
    
    
   /* public function build($view = '')
    {
        $this->sniffStatus();
        $this->buildFields();
        $this->sniffAction();
        $this->process();
        return $this->buildForm($view);
    }*/
    
    /*

    function process()
    {
        switch ($this->action) {
            case "update":
                if ($this->on("error")) {
                    $this->set_status("modify");
                    $this->process_url = $this->url->get_url();
                    return $this->build_form();
                }
                if ($this->on("success")) {
                    $qs = (count($this->model->pk) < 2) ? current($this->model->pk) : $this->model->pk;
                    $this->postprocess_url = $this->url->append('show' . $this->cid, $qs, $this->postprocess_url);
                    if ($this->back_save) {
                        header("Location: " . $this->back_url);
                        exit();
                    } else {
                        header("Location: " . $this->postprocess_url);
                        exit();
                    }
                }
                break;
            case "insert":
                if ($this->on("error")) {
                    $this->set_status("create");
                    $this->process_url = $this->url->get_url();
                    $this->build_buttons();
                    $this->build_fields(); //rebuild fields to update new status (strictly needed?)
                    return $this->build_form();
                }
                if ($this->on("success")) {
                    $qs = (count($this->model->pk) < 2) ? reset($this->model->pk) : $this->model->pk;
                    $this->postprocess_url = $this->url->append('show' . $this->cid, $qs, $this->postprocess_url);
                    if ($this->back_save) {

                        header("Location: " . $this->back_url);
                        exit();
                    } else {
                        header("Location: " . $this->postprocess_url);
                        exit();
                    }
                }
                break;
            case "delete":
                if ($this->on("error")) {
                    $this->build_buttons();
                    return $this->build_message('!errore durante la cancellazione');
                }
                if ($this->on("success")) {
                    $this->build_buttons();
                    if ($this->back_delete) {
                        header("Location: " . $this->back_url);
                        exit();
                    } else {
                        return $this->build_message('record cancellato');
                    }
                }
                break;
                
        }
        switch ($this->status) {
            case "show":
            case "modify":
            case "create":
                $this->build_buttons();
                return $this->build_form();
                break;
            case "delete":
                $this->build_buttons();
                return $this->build_message('confermi la cancellazione?');
                break;
            case "unknow_record":
                $this->build_buttons();
                return $this->build_message('record sconosciuto');
                break;
        }
        
    }
*/

    
 
}
