ms3.window.OrderProduct = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('ms3_menu_update'),
        width: 600,
        baseParams: {
            action: config.action || 'ModxPro\\MiniShop3\\Processors\\Order\\Product\\Update',
        },
        modal: true,
    });
    ms3.window.OrderProduct.superclass.constructor.call(this, config);
};
Ext.extend(ms3.window.OrderProduct, ms3.window.Default, {

    getFields: function (config) {
        const options = this.getOptionsFields(config);
        return [
            {xtype: 'hidden', name: 'id'},
            {xtype: 'hidden', name: 'order_id'},
            {
                layout: 'column',
                border: false,
                anchor: '100%',
                items: [{
                    columnWidth: .3,
                    layout: 'form',
                    defaults: {msgTarget: 'under'},
                    border: false,
                    items: [{
                        xtype: 'numberfield',
                        fieldLabel: _('ms3_product_count'),
                        name: 'count',
                        anchor: '100%',
                        allowNegative: false,
                        allowBlank: false
                    }]
                }, {
                    columnWidth: .7,
                    layout: 'form',
                    defaults: {msgTarget: 'under'},
                    border: false,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: _('ms3_name'),
                        name: 'name',
                        anchor: '100%'
                    }]
                }]
            }, {
                layout: 'column',
                border: false,
                anchor: '100%',
                items: [{
                    columnWidth: .5,
                    layout: 'form',
                    defaults: {msgTarget: 'under'},
                    border: false,
                    items: [{
                        xtype: 'numberfield',
                        decimalPrecision: 2,
                        fieldLabel: _('ms3_product_price'),
                        name: 'price',
                        anchor: '100%'
                    }]
                }, {
                    columnWidth: .5,
                    layout: 'form',
                    defaults: {msgTarget: 'under'},
                    border: false,
                    items: [{
                        xtype: 'numberfield',
                        decimalPrecision: 3,
                        fieldLabel: _('ms3_product_weight'),
                        name: 'weight',
                        anchor: '100%'
                    }]
                },]
            },
            {
                xtype: 'textarea',
                fieldLabel: _('ms3_product_options'),
                name: 'options',
                height: 100,
                anchor: '100%'
            },

            options
        ];
    },

    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: function () {
                this.submit()
            }, scope: this
        }];
    },

    getOptionsFields: function (config) {
        const items = [];
        if (config.record.options) {
            const options = JSON.parse(config.record.options);
            let i = 0;
            for (let key in options) {
                if (!Array.isArray(options[key]) && ms3.config['order_product_options_fields'].includes(key)) {
                    items.push(
                        {
                            columnWidth: 0.5,
                            layout: 'form',
                            defaults: {msgTarget: 'under'},
                            border: false,
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: _('ms3_frontend_' + key) || key,
                                name: 'option-' + key,
                                anchor: '100%',
                            }]
                        }
                    );

                    i++;
                }
            }
        }

        return {
            xtype: 'fieldset',
            title: _('ms3_product_options'),
            layout: 'column',
            style: 'padding:15px 5px;text-align:center;',
            defaults: {msgTarget: 'under', border: false},
            items: [{
                columnWidth: 1,
                layout: 'form',
                items: items
            }]
        };
    },
});
Ext.reg('ms3-window-orderproduct-update', ms3.window.OrderProduct);
