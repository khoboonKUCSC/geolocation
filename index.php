<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geolocation</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #mapid {
        height: 480px;
        width: 320px;
        }

        .info { 
        padding: 6px 8px; 
        font: 14px/16px Arial, Helvetica, sans-serif; 
        background: white; 
        background: rgba(255,255,255,0.8); 
        box-shadow: 0 0 15px rgba(0,0,0,0.2); 
        border-radius: 5px; 
        } 

        .info h4 { margin: 0 0 5px; color: #777; }

        .legend { 
        text-align: left; 
        line-height: 18px; 
        color: #555; 
        } 

        .legend i { 
        width: 18px; 
        height: 18px; 
        float: left; 
        margin-right:8px; 
        opacity: 0.7; 
        }
    </style>
</head>
<body>
    <div id="mapid"></div>    



</body>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <script>
        var geojson_data;
        $.ajax({
            url: "./7days.json",
            method: "GET",
            async: false,
            success : function(data){
                geojson_data = JSON.parse(data);
            }
        });

        var map = L.map('mapid').setView([13, 101.5], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // control that shows state info on hover
        var info = L.control();

        info.onAdd = function (map) {
        this._div = L.DomUtil.create('div', 'info');
        this.update();
        return this._div;
        };

        info.update = function (props) {
        this._div.innerHTML = '<h4>Thai Population Density</h4>' +  
            (props ? '<b>' + props.name + '</b><br />' + props.p + ' people / km<sup>2</sup>' : 'Hover over a state');
        };

        info.addTo(map);

        function getColor(d) {
        return  d > 1000 ? '#800026' :
        d > 500  ? '#BD0026' :
        d > 200  ? '#E31A1C' :
        d > 100  ? '#FC4E2A' :
        d > 50   ? '#FD8D3C' :
        d > 20   ? '#FEB24C' :
        d > 10   ? '#FED976' :
        '#FFEDA0';
        }

        function style(feature) {
        return {
            fillColor: getColor(feature.properties.p),
            weight: 1,
            opacity: 1,
            color: 'white',
            dashArray: '3',
            fillOpacity: 0.7
        };
        }

        function highlightFeature(e) {
        var layer = e.target;
        layer.setStyle({
            weight: 5,
            color: '#666',
            dashArray: '',
            fillOpacity: 0.7
        });
        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
            layer.bringToFront();
        }
        info.update(layer.feature.properties);
        }

        var geojson;

        function resetHighlight(e) {
        geojson.resetStyle(e.target);
        info.update();
        }

        function zoomToFeature(e) {
        map.fitBounds(e.target.getBounds());
        }

        function onEachFeature(feature, layer) {
        layer.on({
            mouseover: highlightFeature,
            mouseout: resetHighlight,
            click: zoomToFeature
        });
        }

        geojson = L.geoJson(geojson_data, {
        style: style,
        onEachFeature: onEachFeature
        }).addTo(map);

        var legend = L.control({position: 'bottomright'});

        legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend'),
            grades = [0, 10, 20, 50, 100, 200, 500, 1000],
            labels = [],
            from, to;
        for (var i = 0; i < grades.length; i++) {
            from = grades[i];
            to = grades[i + 1];
            labels.push(
            '<i style="background:' + getColor(from + 1) + '"></i> ' +
            from + (to ? '&ndash;' + to : '+'));
        }
        div.innerHTML = labels.join('<br>');
        return div;
        };

        legend.addTo(map);
    </script>
</html>