(function (blocks, element) {
    var el = element.createElement;

    blocks.registerBlockType('oc/odds-comparator', {
        title: 'Odds Comparator',
        icon: 'chart-bar',
        category: 'widgets',

        edit: function () {
            return el("div", {},
                el("p", {}, "Odds Comparator block is active.")
            );
        },

        save: function () {
            return null; // PHP will render the front-end via dynamic block
        }
    });
})(
    window.wp.blocks,
    window.wp.element
);
