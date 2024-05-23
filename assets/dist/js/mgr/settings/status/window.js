ms3.window.CreateStatus = function (config) {
    config = config || {};
    this.ident = config.ident || 'mecitem' + Ext.id();
    Ext.applyIf(config, {
        title: _('ms3_menu_create'),
        width: 800,
        baseParams: {
            action: 'ModxPro\\MiniShop3\\Processors\\Settings\\Status\\Create',
        },
    });
    ms3.window.CreateStatus.superclass.constructor.call(this, config);
};
Ext.extend(ms3.window.CreateStatus, ms3.window.Default, {

    getFields: function (config) {
        return [
            {xtype: 'hidden', name: 'id', id: config.id + '-id'},
            {xtype: 'hidden', name: 'color', id: config.id + '-color'},
            {
                xtype: 'textfield',
                id: config.id + '-name',
                fieldLabel: _('ms3_name'),
                name: 'name',
                anchor: '99%',
        }, {
            xtype: 'colorpalette', fieldLabel: _('ms3_color'),
            id: config.id + '-color-palette',
            listeners: {
                select: function (palette, color) {
                    Ext.getCmp(config.id + '-color').setValue(color)
                },
                beforerender: function (palette) {
                    if (config.record['color'] != undefined) {
                        palette.value = config.record['color'];
                    }
                }
            },
        }, {
            layout: 'column',
            items: [{
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    id: config.id + '-email-user',
                    boxLabel: _('ms3_email_user'),
                    name: 'email_user',
                    listeners: {
                        check: {
                            fn: function (checkbox) {
                                this.handleStatusFields(checkbox);
                            }, scope: this
                        },
                        afterrender: {
                            fn: function (checkbox) {
                                this.handleStatusFields(checkbox);
                            }, scope: this
                        }
                    },
                }, {
                    xtype: 'textfield',
                    id: config.id + '-subject-user',
                    fieldLabel: _('ms3_subject_user'),
                    name: 'subject_user',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: _('ms3_body_user'),
                    name: 'body_user',
                    id: config.id + '-body-user',
                    anchor: '99%'
                }],
            }, {
                columnWidth: .5,
                layout: 'form',
                items: [{
                    xtype: 'xcheckbox',
                    id: config.id + '-email-manager',
                    boxLabel: _('ms3_email_manager'),
                    name: 'email_manager',
                    listeners: {
                        check: {
                            fn: function (checkbox) {
                                this.handleStatusFields(checkbox);
                            }, scope: this
                        },
                        afterrender: {
                            fn: function (checkbox) {
                                this.handleStatusFields(checkbox);
                            }, scope: this
                        }
                    },
                }, {
                    xtype: 'textfield',
                    id: config.id + '-subject-manager',
                    fieldLabel: _('ms3_subject_manager'),
                    name: 'subject_manager',
                    anchor: '99%'
                }, {
                    xtype: 'textfield',
                    id: config.id + '-body-manager',
                    fieldLabel: _('ms3_body_manager'),
                    name: 'body_manager',
                    anchor: '99%'
                }],
            }]
        }, {
            xtype: 'textarea',
            id: config.id + '-description',
            fieldLabel: _('ms3_description'),
            name: 'description',
            anchor: '99%',
        }, {
            xtype: 'checkboxgroup',
            hideLabel: true,
            columns: 3,
            items: [{
                xtype: 'xcheckbox',
                id: config.id + '-active',
                boxLabel: _('ms3_active'),
                name: 'active',
                checked: parseInt(config.record['active']),
            }, {
                xtype: 'xcheckbox',
                id: config.id + '-final',
                boxLabel: _('ms3_status_final'),
                description: _('ms3_status_final_help'),
                name: 'final',
                checked: parseInt(config.record['final']),
            }, {
                xtype: 'xcheckbox',
                id: config.id + '-fixed',
                boxLabel: _('ms3_status_fixed'),
                description: _('ms3_status_fixed_help'),
                name: 'fixed',
                checked: parseInt(config.record['fixed']),
            }]
        }
        ];
    },

    handleStatusFields: function (checkbox) {
        const type = checkbox.name.replace(/(^.*?_)/, '');

        const subject = Ext.getCmp(this.config.id + '-subject-' + type);
        const body = Ext.getCmp(this.config.id + '-body-' + type);
        if (checkbox.checked) {
            subject.enable().show();
            body.enable().show();
        } else {
            subject.hide().disable();
            body.hide().disable();
        }
    },

});
Ext.reg('ms3-window-status-create', ms3.window.CreateStatus);


ms3.window.UpdateStatus = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('ms3_menu_update'),
        baseParams: {
            action: 'ModxPro\\MiniShop3\\Processors\\Settings\\Status\\Update',
        },
    });
    ms3.window.UpdateStatus.superclass.constructor.call(this, config);
};
Ext.extend(ms3.window.UpdateStatus, ms3.window.CreateStatus);
Ext.reg('ms3-window-status-update', ms3.window.UpdateStatus);
