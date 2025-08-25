<?php

namespace Zabbix\Modules\GeoVis\Actions;

use Zabbix\Frontend\CAction;
use Zabbix\Modules\GeoVis\Service\GeoVisService;

class ApiDataAction extends CAction {

    protected function init(): void {
        $this->checkAuthentication();
    }

    protected function doAction(): void {
        // Criar uma instância do nosso serviço
        $service = new GeoVisService();
        
        // Obter os dados do mapa a partir do serviço
        $data = $service->getMapData();

        // Enviar os dados como resposta JSON
        $this->setResponse($data, 'json');
    }
}