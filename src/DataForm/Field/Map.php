<?php

namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Html\FormFacade as Form;

//TODO google map (rethink extending container)

class Map extends Field
{

    public $type = "map";
    public $lat = "lat";
    public $lon = "lon";
    public $zoom = 12;

    public function latlon($lat, $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        return $this;
    }

    public function zoom($zoom)
    {
        $this->zoom = $zoom;
        return $this;
    }
    
    public function getValue()
    {
        if (isset($this->model)) 
        {
            $this->value['lat'] = $this->model->getAttribute($this->lat);
            $this->value['lon'] = $this->model->getAttribute($this->lon);
            $this->description =  implode(',', array_values($this->value));
        }
    }

    public function getNewValue()
    {
        $process = (\Input::get('search') || \Input::get('save')) ? true : false;
        if ($process && \Input::exists($this->lat)) {
            $this->new_value['lat'] = \Input::get($this->lat);
            $this->new_value['lon'] = \Input::get($this->lon);
        
        }
    }
    
    public function autoUpdate($save = false)
    {
        if (isset($this->model))
        {
            $this->getValue();
            $this->getNewValue();
            $this->model->setAttribute($this->lat, $this->new_value['lat']);
            $this->model->setAttribute($this->lon, $this->new_value['lon']);
            if ($save) {
                return $this->model->save();
            }
        }
        return true;
    }
    
    public function build()
    {
        $output = "";
        $this->attributes["class"] = "form-control";
        if (parent::build() === false)
            return;

        switch ($this->status) {
            case "disabled":
            case "show":

                if ($this->type == 'hidden' || $this->value == "") {
                    $output = "";
                } elseif ((!isset($this->value))) {
                    $output = $this->layout['null_label'];
                } else {
                    $output = "<img border=\"0\" src=\"//maps.googleapis.com/maps/api/staticmap?center={$this->value['lat']},{$this->value['lon']}&zoom={$this->zoom}&size=500x500\">";
                   
                }
                $output = "<div class='help-block'>" . $output . "</div>";
                break;

            case "create":
            case "modify":
                $output  = Form::hidden($this->lat, $this->value['lat'], ['id'=>$this->lat]);
                $output .= Form::hidden($this->lon, $this->value['lon'], ['id'=>$this->lon]);
                $output .= '<div id="map_'.$this->name.'" style="width:500px; height:500px"></div>';
                $output .= '<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>';
                
            \Rapyd::script("
        
            function initialize()
            {
                var latitude = document.getElementById('{$this->lat}');
                var longitude = document.getElementById('{$this->lon}');
                var zoom = {$this->zoom};
        
                var LatLng = new google.maps.LatLng(latitude.value, longitude.value);
        
                var mapOptions = {
                    zoom: zoom,
                    center: LatLng,
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
        
                var map = new google.maps.Map(document.getElementById('map_{$this->name}'),mapOptions);
        
                var marker = new google.maps.Marker({
                    position: LatLng,
                    map: map,
                    title: 'Drag Me!',
                    draggable: true
                });
        
                google.maps.event.addListener(marker, 'dragend', function (event) {
                    latitude.value = event.latLng.lat();
                    longitude.value = event.latLng.lng();
                });
        
            }
            initialize();
        ");
                
                break;

            case "hidden":
                $output = '';//Form::hidden($this->db_name, $this->value);
                break;

            default:;
        }
        $this->output = "\n" . $output . "\n" . $this->extra_output . "\n";
    }

}
