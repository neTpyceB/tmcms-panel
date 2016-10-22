<?php
if (!isset($_GET['svg_image_path']) || !$_GET['svg_image_path']) {
    dump('Can not show editor, because SVG image not set.');
}
if (!isset($_GET['selector']) || !$_GET['selector']) {
    dump('Input selector not provided');
}
?>

<style> svg polygon {
        stroke: red !important;
    }
    svg polygon:hover ,
    svg polygon.hovered {
        fill: lightgreen !important;
        cursor: pointer;
    }
    svg circle {
        fill: #ccc !important;
        stroke: #888 !important;
    }
    svg circle:hover ,
    svg circle.hovered {
        fill: #333 !important;
        stroke: #333 !important;
        cursor: pointer;
    }
</style>
<div class="svgmap-container">
    <?= file_get_contents(DIR_BASE . $_GET['svg_image_path']) ?>
</div>
<script>
    var svg_imager = {
        svg_element: null,
        input: null,
        init: function () {
            this.input = $('<?= isset($_GET['selector']) ? '#' . $_GET['selector'] : '' ?>');

            this.svg_element = $('.svgmap-container svg').eq(0);

            var $el;
            this.svg_element.find('polygon').each(function(k, v) {
                $el = $(v);
                $el.click(function() {
                    svg_imager.done($el.attr('id'));
                });
            });
        },
        done: function(id) {
            console.log(popup_modal.result_element);
            this.input.val(id);
            popup_modal.close();
        }
    };

    svg_imager.init();
</script>