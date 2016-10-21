<?php
ob_start();
?><style>
    #map {
        width: 100%;
        height: 100%;
    }
</style>
<div id="map"></div>
<input type="text" readonly id="cms_map_coordinates" name="cms_map_coordinates" class="form-control" onkeyup="update_coords();">
<input class="btn btn-primary" type="button" onclick="done()" value="Set cms_map_coordinates">
<script>
    var $coords = $('#cms_map_coordinates');

    function done() {
        popup_modal.result_element.val($coords.val());
        popup_modal.result_element.focus();
        popup_modal.close();
        return true;
    }

    resize_wysiwyg = function () {
        var $block = $('#map');
        var $win = $('#modal-popup_inner');

        $block.height($win.height() - 72);
    };

    setTimeout(function () {
        resize_wysiwyg();
    }, 1000);
    $(window).resize(function () {
        resize_wysiwyg();
    });

    var update_coords;

    function initMap() {
        var initialLocation = false;
        var existing_value = false;

        // Current value from input
        var $el = $('<?= isset($_GET['selector']) ? '#' . $_GET['selector'] : '' ?>');
        var value = $el.val();

        if (value) {
            $coords.val(value);
            value = value.split(',');
            existing_value = true;
            initialLocation = new google.maps.LatLng(value[0], value[1]);
        }

        // Default start position
        if (!existing_value) {
            initialLocation = new google.maps.LatLng(56.946579, 24.104830);
        }

        var mapDiv = document.getElementById('map');
        var map = new google.maps.Map(mapDiv, {
            zoom: 6
        });

        // Try to set current location
        if (!existing_value && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

            }, function() {
                // Error - no support or did not allow
            });
        }

        // Set center of the map
        map.setCenter(initialLocation);

        // Put marker to drag on map
        var marker = new google.maps.Marker({
            position: initialLocation,
            map: map,
            draggable:true,
        });

        // function when changing value in input
        update_coords = function() {
            value = $coords.val();
            value = value.split(',');

            // New location
            initialLocation = new google.maps.LatLng(value[0], value[1]);

            // Move map
            map.setCenter(initialLocation);
            // Move marker
            marker.setPosition(initialLocation);
        };

        // function when changing value in marker
        var update_map = function() {
            // New location
            initialLocation = marker.getPosition();

            // Move map
            map.setCenter(initialLocation);

            // Set value to input
            $coords.val((initialLocation.lat()) + ',' + (initialLocation.lng()));
        };

        // Set input update for current location on page load
        $coords.val((initialLocation.lat()) + ',' + (initialLocation.lng()));

        // Change input value when dragging marker
        marker.addListener('dragend', update_map);
    }

    $(function() {
        initMap();
    });
</script><?php
echo ob_get_clean();
die;