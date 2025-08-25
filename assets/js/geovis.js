/**
 * Zabbix GeoVis Module
 * Main JavaScript file for map visualization using Leaflet.js and OpenStreetMap.
 */

var GeoVis = GeoVis || {};

(function () {
    'use strict';

    var map, config;

    // Definição dos nossos ícones SVG personalizados
    var icons = {
        active: L.icon({
            iconUrl: 'modules/geovis/assets/img/marker-site-active.svg',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        }),
        problem: L.icon({
            iconUrl: 'modules/geovis/assets/img/marker-site-problem.svg',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        }),
        maintenance: L.icon({
            iconUrl: 'modules/geovis/assets/img/marker-site-maintence.svg',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        }),
        inactive: L.icon({
            iconUrl: 'modules/geovis/assets/img/marker-site-inative.svg',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        })
    };

    /**
     * Busca os dados da API e plota no mapa.
     */
    GeoVis.loadData = function () {
        console.log('GeoVis: Loading data from Zabbix API...');

        fetch('module.php?action=api.geovis.data')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('GeoVis: Data received.', data);

                // Limpa camadas antigas do mapa para evitar duplicatas
                map.eachLayer(function (layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                        map.removeLayer(layer);
                    }
                });

                // Processa os hosts
                data.hosts.forEach(host => {
                    let icon = icons[host.status] || icons.inactive;

                    L.marker([host.lat, host.lon], { icon: icon })
                        .addTo(map)
                        .bindPopup('<b>' + host.name + '</b><br>Status: ' + host.status);
                });

                // Processa os links
                data.links.forEach(link => {
                    let source = data.hosts.find(h => h.hostid === link.source_hostid);
                    let dest = data.hosts.find(h => h.hostid === link.dest_hostid);

                    if (source && dest) {
                        GeoVis.drawRoute(source, dest, link.trigger_status);
                    }
                });
            })
            .catch(error => {
                console.error('GeoVis: There was a problem fetching data:', error);
            });
    };

    /**
     * Desenha a rota entre dois pontos usando a API OSRM.
     * @param {object} source - Objeto do host de origem.
     * @param {object} dest - Objeto do host de destino.
     * @param {string} status - Status do link ('active' ou 'problem').
     */
    GeoVis.drawRoute = function (source, dest, status) {
        var url = `https://router.project-osrm.org/route/v1/driving/${source.lon},${source.lat};${dest.lon},${dest.lat}?overview=full&geometries=geojson`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.routes && data.routes.length > 0) {
                    var routeCoordinates = data.routes[0].geometry.coordinates;
                    var latLngs = routeCoordinates.map(coord => [coord[1], coord[0]]);

                    // Usa as cores da configuração
                    var color = status === 'problem' ? config.color_problem : config.color_active;

                    L.polyline(latLngs, {
                        color: color,
                        weight: 5,
                        opacity: 0.8
                    }).addTo(map);
                }
            })
            .catch(error => {
                console.error('GeoVis: Error fetching route:', error);
                // Fallback para linha reta se a rota falhar
                var color = status === 'problem' ? config.color_problem : config.color_active;
                L.polyline([[source.lat, source.lon], [dest.lat, dest.lon]], { color: color }).addTo(map);
            });
    };

    /**
     * Função principal de inicialização do mapa.
     * @param {object} widgetConfig - Configurações do widget, se aplicável.
     */
    GeoVis.initMap = function (widgetConfig) {
        // Armazena a configuração
        config = widgetConfig;
        
        console.log('GeoVis: Initializing Leaflet map with config:', config);
        
        // Evita reinicializar o mapa se ele já existir
        if (map) {
            map.remove();
        }

        // Converte as strings de lat/lon/zoom para números
        var lat = parseFloat(config.center_lat);
        var lon = parseFloat(config.center_lon);
        var zoom = parseInt(config.zoom_level);

        // Cria o mapa no elemento com o ID fornecido
        map = L.map(config.containerId).setView(
            [lat, lon],
            zoom
        );

        // Adiciona a camada de tiles do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        GeoVis.loadData();
        
        // Configura a atualização automática a cada 60 segundos
        setInterval(GeoVis.loadData, 60000);
    };

    // A chamada para GeoVis.initMap() é feita na view PHP (geovis.view.php ou geovis.widget.php)
    // para que a configuração correta seja passada.

})();