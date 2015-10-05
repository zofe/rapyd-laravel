<?php

namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Html\FormFacade as Form;

//TODO google map (rethink extending container)

class Map extends Field
{

    public $type = "map";
    
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
                    //immagine statica della mappa su lat e lon  su api google
                   
                }
                $output = "<div class='help-block'>" . $output . "</div>";
                break;

            case "create":
            case "modify":
                $output  = Form::text($this->lat, $this->attributes);
                $output .= Form::text($this->lon, $this->attributes);
                $output .= '<div id="map" style="width:500px; height:500px"></div>';
            $output .= '<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>';
                
            Rapyd::script("
        
            function initialize()
            {
                var latitude = document.getElementById('latitude');
                var longitude = document.getElementById('longitude');
                var zoom = 7;
        
                var LatLng = new google.maps.LatLng(latitude, longitude);
        
                var mapOptions = {
                    zoom: zoom,
                    center: LatLng,
                    panControl: false,
                    zoomControl: false,
                    scaleControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
        
                var map = new google.maps.Map(document.getElementById('map'),mapOptions);
        
                var marker = new google.maps.Marker({
                    position: LatLng,
                    map: map,
                    title: 'Drag Me!',
                    draggable: true
                });
        
                google.maps.event.addListener(marker, 'dragend', function (marker) {
                    var latLng = marker.latLng;
                    latitude.value = LatLng.lat();
                    longitude.value = LatLng.lng();
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
