<?php

use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CTextField;

(new CWidgetForm($this->data))
    ->addField(
        (new CTextField('center_lat', 'Latitude Central',
            $this->data['fields']['center_lat']['value'] ?? ''
        ))
    )
    ->addField(
        (new CTextField('center_lon', 'Longitude Central',
            $this->data['fields']['center_lon']['value'] ?? ''
        ))
    )
    ->addField(
        (new CTextField('zoom_level', 'Nível de Zoom',
            $this->data['fields']['zoom_level']['value'] ?? ''
        ))
    )
    ->show();