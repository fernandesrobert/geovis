<?php
namespace Zabbix\Modules\GeoVis;

use Zabbix\Core\CModule;

class Module extends CModule {


    /**
     * Installs the module.
     * @return bool
     */
    public function install(): bool {
        $sql = "
            CREATE TABLE geovis_config (
                configid BIGINT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value TEXT NOT NULL
            ) ENGINE=InnoDB;
        ";

        try {
            DB::query($sql);
            // Insere valores padrão na tabela
            DB::insert('geovis_config', [
                ['configid' => 1, 'name' => 'center_lat', 'value' => '-15.7801'],
                ['configid' => 2, 'name' => 'center_lon', 'value' => '-47.9292'],
                ['configid' => 3, 'name' => 'zoom_level', 'value' => '4'],
                ['configid' => 4, 'name' => 'color_problem', 'value' => '#FF5733'],
                ['configid' => 5, 'name' => 'color_active', 'value' => '#33FF57']
            ]);
        } catch (\Exception $e) {
            // Se a tabela já existir, a instalação falhará. Isso é aceitável.
            return false;
        }

        return true;
    }

    /**
     * Uninstalls the module.
     * @return bool
     */
    public function uninstall(): bool {
        try {
            DB::query("DROP TABLE geovis_config");
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
    
    public function init() {
        $this->registerActions();
        $this->registerAssets();
    }

    public function getMenu() {
        return [
            'sections' => [
                'monitoring' => [
                    'pages' => [
                        [
                            'url' => 'module.geovis.view',
                            'label' => 'Mapa Geográfico'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getDashboardWidgets() {
        return [
            'geovis' => [
                'class' => 'Zabbix\\Modules\\GeoVis\\Actions\\DashboardWidgetAction',
                'view' => 'geovis.widget',
                'name' => 'Mapa Geográfico'
            ]
        ];
    }
}