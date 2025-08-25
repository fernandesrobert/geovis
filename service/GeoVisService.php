<?php

namespace Zabbix\Modules\GeoVis\Service;

use Zabbix\Core\CServices;
use Zabbix\Core\CModule;

class GeoVisService {

    /**
     * Get all hosts that have geographic inventory data.
     *
     * @return array
     */
    public function getMapData(): array {
        $api = CServices::get('api');
        $mapData = [
            'hosts' => [],
            'links' => []
        ];

        // --- 1. BUSCAR HOSTS COM COORDENADAS GEOGRÁFICAS ---

        // Consulta a API do Zabbix para obter hosts com dados de inventário.
        $zbx_hosts = $api->call('host.get', [
            'output' => ['hostid', 'name'],
            'selectInventory' => ['location_lat', 'location_lon'],
            'selectGroups' => ['name'],
            'selectTriggers' => 'extend',
            'withInventory' => true,
            'preservekeys' => true
        ]);

        if (empty($zbx_hosts)) {
            // Nenhum host encontrado, retorna dados vazios.
            return $mapData;
        }

        $host_ids = array_keys($zbx_hosts);
        $hosts_with_coords = [];

        // Filtra e prepara a lista de hosts que possuem coordenadas geográficas válidas.
        foreach ($zbx_hosts as $host) {
            if (
                isset($host['inventory']['location_lat']) && !empty($host['inventory']['location_lat']) &&
                isset($host['inventory']['location_lon']) && !empty($host['inventory']['location_lon'])
            ) {
                // Adiciona o host ao nosso array de dados
                $hosts_with_coords[$host['hostid']] = [
                    'hostid' => (int) $host['hostid'],
                    'name' => $host['name'],
                    'lat' => (float) $host['inventory']['location_lat'],
                    'lon' => (float) $host['inventory']['location_lon'],
                    'status' => 'active', // Assume que o host está OK por padrão
                    'triggers' => $host['triggers']
                ];
            }
        }

        // --- 2. BUSCAR STATUS DAS TRIGGERS PARA OS LINKS ---
        
        $links = [];
        $triggers_to_check = [];

        // Itera sobre os hosts com coordenadas para encontrar triggers que representam links.
        foreach ($hosts_with_coords as $hostid => $host_data) {
            foreach ($host_data['triggers'] as $trigger) {
                // Procura um padrão de nome de trigger que indique um link entre hosts.
                // Exemplo: 'Link between HostA and HostB'
                // Aqui você pode definir sua própria convenção de nomenclatura.
                if (strpos($trigger['description'], 'Link between ') !== false) {
                    $trigger_name = $trigger['description'];
                    $matches = [];
                    // Exemplo de regex para extrair os nomes dos hosts do trigger
                    if (preg_match('/Link between (.+) and (.+)/', $trigger_name, $matches)) {
                        $source_name = $matches[1];
                        $dest_name = $matches[2];
                        
                        // Encontra os hostids correspondentes
                        $source_hostid = null;
                        $dest_hostid = null;
                        foreach($zbx_hosts as $h) {
                            if ($h['name'] === $source_name) {
                                $source_hostid = $h['hostid'];
                            }
                            if ($h['name'] === $dest_name) {
                                $dest_hostid = $h['hostid'];
                            }
                        }

                        // Se ambos os hosts forem encontrados e tiverem coordenadas, cria o link
                        if ($source_hostid && $dest_hostid && isset($hosts_with_coords[$source_hostid]) && isset($hosts_with_coords[$dest_hostid])) {
                            $links[] = [
                                'source_hostid' => (int) $source_hostid,
                                'dest_hostid' => (int) $dest_hostid,
                                'triggerid' => (int) $trigger['triggerid'],
                                'trigger_status' => ($trigger['value'] === '1' ? 'problem' : 'active')
                            ];
                        }
                    }
                }
            }
        }

        // --- 3. ATUALIZAR STATUS DOS HOSTS ---
        
        // Obter os hosts com triggers em estado de problema
        $problem_triggers = $api->call('trigger.get', [
            'output' => ['triggerid', 'description'],
            'only_true' => 1
        ]);
        
        $problem_host_ids = [];
        foreach($problem_triggers as $trigger) {
            $related_hosts = $api->call('host.get', [
                'triggerids' => $trigger['triggerid'],
                'output' => ['hostid']
            ]);
            foreach($related_hosts as $host) {
                $problem_host_ids[] = (int) $host['hostid'];
            }
        }
        
        // Atualiza o status dos hosts que têm triggers em problema
        foreach ($hosts_with_coords as $hostid => &$host_data) {
            if (in_array($hostid, $problem_host_ids)) {
                $host_data['status'] = 'problem';
            }
        }
        
        // Finaliza o array de retorno
        $mapData['hosts'] = array_values($hosts_with_coords);
        $mapData['links'] = $links;

        return $mapData;
    }
}