Ext.onReady(function(){
    MODx.Summary.init();
});

MODx.Summary = function() {
    return {
        init: function() {
            Ext.get('install').on('submit',function() {
                Ext.get('modx-next').setVisi e(false);
                Ext.get('modx-back').setVisible(false);
            });
        }
    }
}();