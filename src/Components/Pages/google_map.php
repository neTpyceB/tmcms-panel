<?php
if (!isset($_GET['selector']) || !$_GET['selector']) {
    dump('Input selector not provided');
}
?>
<style>
    #map {
        width: 100%;
        height: 100%;
    }
</style>
<div id="map"></div>
<input type="text" readonly id="cms_map_coordinates" name="cms_map_coordinates" class="form-control" onkeyup="update_coords();"/>
<div class="input-group">
    <input type="text" id="cms_map_address" name="cms_map_address" class="form-control" onblur="update_address();" placeholder="Address"/>
    <span class="input-group-btn">
        <button class="btn btn-default" type="button" id="cms_map_refresh" onclick="update_address();"><span class="fa fa-refresh"></span></button>
    </span>
</div><!-- /input-group -->
<input class="btn btn-primary" type="button" onclick="done()" value="Set cms_map_coordinates">
<script>
    var $coords = $('#cms_map_coordinates');
    var $address = $('#cms_map_address');

    function done() {
        var input = $('<?= isset($_GET['selector']) ? '#' . $_GET['selector'] : '' ?>');
        input.val($coords.val());
        input.focus();
        popup_modal.close();
        return true;
    }

    resize_wysiwyg = function () {
        var $block = $('#map');
        var $win = $('#modal-popup_inner');

        $block.height($win.height() - 106);
    };

    setTimeout(function () {
        resize_wysiwyg();
    }, 1000);
    $(window).resize(function () {
        resize_wysiwyg();
    });

    var update_coords;
    var update_address;

    function initMap() {
        var initialLocation = false;
        var existing_value = false;

        // Current value from input
        var $el = $('<?= isset($_GET['selector']) ? '#' . $_GET['selector'] : '' ?>');
        var value = $el.val();
        var address = <?= isset($_GET['address_source']) ? "$('#" . $_GET['address_source'] . "').val()" : "''" ?>;
        console.log("<?= $_GET['address_source'] ?>");

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
            zoom: 11
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

        update_address = function(){
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ 'address': $("#cms_map_address").val() }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $coords.val(results[0].geometry.location.lat() + ',' + results[0].geometry.location.lng());
                    update_coords();
                }
            });
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
        $address.val(address);
        if(!existing_value && address) {
            update_address();
        }else{
            // Set input update for current location on page load
            $coords.val((initialLocation.lat()) + ',' + (initialLocation.lng()));
        }

        // Change input value when dragging marker
        marker.addListener('dragend', update_map);
    }

    $(function() {
        initMap();
    });
</script>
<?php
die;