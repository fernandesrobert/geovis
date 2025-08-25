<?php

namespace Zabbix\Modules\GeoVis\Actions;

use Zabbix\Frontend\CAction;
use Zabbix\Database\DB;

class ConfigSaveAction extends CAction {

    protected function doAction(): void {
        $request = $this->getRequest();

        // Valida e salva os dados
        if (isset($request['config'])) {
            $db_values = [];
            foreach ($request['config'] as $name => $value) {
                $db_values[] = [
                    'name' => $name,
                    'value' => $value
                ];
            }

            // Limpa a tabela e insere os novos dados
            DB::delete('geovis_config');
            DB::insert('geovis_config', $db_values);
        }

        $this->setResponse(['result' => 'ok'], 'json');
    }
}