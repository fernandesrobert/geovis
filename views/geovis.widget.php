<?php

use Zabbix\Tags\CTag;
use Zabbix\Tags\CScriptTag;

// Adiciona o container do mapa
echo (new CTag('div', true, [
    'id' => 'geovis-map-container-'.$this->data['widget']['widgetid'],
    'class' => 'geovis-map-container'
]));

// Passa as configurações do widget e globais para o JavaScript
(new CScriptTag())->addScript(
    '$(function() {
        var widgetConfig = {
            containerId: "geovis-map-container-'.$this->data['widget']['widgetid'].'",
            center_lat: "'.($this->data['widget']['fields']['center_lat']['value'] ?? $this->data['config']['center_lat']['value']).'",
            center_lon: "'.($this->data['widget']['fields']['center_lon']['value'] ?? $this->data['config']['center_lon']['value']).'",
            zoom_level: "'.($this->data['widget']['fields']['zoom_level']['value'] ?? $this->data['config']['zoom_level']['value']).'",
            color_active: "'.$this->data['config']['color_active']['value'].'",
            color_problem: "'.$this->data['config']['color_problem']['value'].'"
        };
        GeoVis.initMap(widgetConfig);
    });'
);