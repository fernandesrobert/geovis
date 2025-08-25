<?php

namespace Zabbix\Modules\GeoVis\Actions;

use Zabbix\Frontend\CAction;
use Zabbix\Database\DB;

class DashboardWidgetAction extends CAction {

    protected function init(): void {
        $this->checkAuthentication();
    }

    protected function doAction(): void {
        $view = $this->getInput('view', 'geovis.widget');
        
        $data = [];

        if ($view === 'geovis.widget') {
            // Se estiver exibindo o widget, passe as configurações do widget para a view.
            $data['widget'] = $this->data['widget'];

            // Também busque as configurações globais (cores)
            $data['config'] = DB::get_assoc('SELECT name, value FROM geovis_config', 'name');
        } else {
            // Se estiver editando o widget, passe os campos do formulário para a view.
            $data['fields'] = $this->data['fields'];
        }

        $this->process($data);
    }
}