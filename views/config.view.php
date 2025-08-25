<?php

use Zabbix\Widgets\CWidget;
use Zabbix\Widgets\CForm;
use Zabbix\Widgets\CFormList;
use Zabbix\Tags\CInput;

$widget = (new CWidget())
    ->setTitle('Configurações do Mapa Geográfico');

$form = (new CForm('config_form', 'module.php?action=api.geovis.config.save'))
    ->setAttribute('id', 'config_form');

$formList = (new CFormList());

// Obtenha as configurações passadas pela ação
$config = $this->data['config'];

$formList->addRow(_('Mapa'),
    (new CInput('text', 'config[center_lat]', $config['center_lat']['value']))
        ->setAttribute('placeholder', 'Latitude')
    . (new CInput('text', 'config[center_lon]', $config['center_lon']['value']))
        ->setAttribute('placeholder', 'Longitude')
    . (new CInput('text', 'config[zoom_level]', $config['zoom_level']['value']))
        ->setAttribute('placeholder', 'Nível de Zoom')
);

$formList->addRow(_('Paleta de Cores'),
    _('Ativo: ') . (new CInput('color', 'config[color_active]', $config['color_active']['value']))
    . _(' Problema: ') . (new CInput('color', 'config[color_problem]', $config['color_problem']['value']))
);

$form->addItem($formList);

// Adiciona o botão de salvar
$form->addButton((new CButton('Salvar'))->setCssClasses('button button-primary'));

$widget->addItem($form);

$widget->show();

// Adiciona um script para enviar o formulário via AJAX
(new CScriptTag())->addScript(
    '$(function() {
        $("#config_form").on("submit", function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "module.php?action=api.geovis.config.save",
                type: "POST",
                data: formData,
                success: function(response) {
                    alert("Configurações salvas com sucesso!");
                },
                error: function() {
                    alert("Erro ao salvar as configurações.");
                }
            });
        });
    });'
);