<?php

use Zabbix\Widgets\CWidget;
use Zabbix\Tags\CTag;

$widget = (new CWidget())
    ->setTitle('Mapa Geográfico de Sites');

// Adicione um container para o mapa dentro do widget
$widget->addItem(
    (new CTag('div', true, ['id' => 'geovis-map-container', 'class' => 'geovis-map-container']))
);

$widget->show();