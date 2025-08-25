<?php

namespace Zabbix\Modules\GeoVis\Actions;

use Zabbix\Frontend\CAction;
use Zabbix\Database\DB;

class ConfigAction extends CAction {

    protected function init(): void {
        $this->checkAuthentication();
        $this->checkPermissions();
    }

    protected function checkPermissions(): void {
        // Apenas administradores podem acessar esta página
        $this->checkUserType(USER_TYPE_SUPER_ADMIN);
    }

    protected function doAction(): void {
        // Busca as configurações do banco de dados
        $config = DB::get_assoc('SELECT name, value FROM geovis_config', 'name');
        $data = [
            'config' => $config
        ];

        $this->process($data);
    }
}