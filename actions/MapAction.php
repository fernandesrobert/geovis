<?php

namespace Zabbix\Modules\GeoVis\Actions;

use Zabbix\Frontend\CAction;

class MapAction extends CAction {

    protected function init(): void {
        $this->checkAuthentication();
    }

    protected function checkPermissions(): void {
        // Sem permissões específicas por enquanto.
    }

    protected function doAction(): void {
        $this->process();
    }
}