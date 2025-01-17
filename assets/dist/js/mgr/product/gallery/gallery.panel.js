ms3.panel.Gallery = function (config) {
    config = config || {};

    Ext.apply(config, {
        border: false,
        id: 'ms3-gallery-page',
        baseCls: 'x-panel',
        items: [{
            border: false,
            style: {padding: '10px 5px'},
            xtype: 'ms3-gallery-page-toolbar',
            id: 'ms3-gallery-page-toolbar',
            record: config.record,
        }, {
            border: false,
            style: {padding: '5px'},
            layout: 'anchor',
            items: [{
                border: false,
                xtype: 'ms3-gallery-images-panel',
                id: 'ms3-gallery-images-panel',
                cls: 'modx-pb-view-ct',
                product_id: config.record.id,
                pageSize: config.pageSize
            }]
        }]
    });
    ms3.panel.Gallery.superclass.constructor.call(this, config);

    this.on('afterrender', function () {
        const gallery = this;
        window.setTimeout(function () {
            gallery.initialize();
        }, 100);
    });
};
Ext.extend(ms3.panel.Gallery, MODx.Panel, {
    errors: '',
    progress: null,

    initialize: function () {
        if (this.initialized) {
            return;
        }
        this._initUploader();

        const el = document.getElementById(this.id);
        el.addEventListener('dragenter', function () {
            if (!this.className.match(/drag-over/)) {
                this.className += ' drag-over';
            }
        }, false);
        el.addEventListener('dragleave', function () {
            this.className = this.className.replace(' drag-over', '');
        }, false);
        el.addEventListener('drop', function () {
            this.className = this.className.replace(' drag-over', '');
        }, false);

        this.initialized = true;
    },

    _initUploader: function () {
        const params = {
            action: 'ModxPro\\MiniShop3\\Processors\\Gallery\\Upload',
            id: this.record.id,
            source: this.record.source,
            ctx: 'mgr',
            HTTP_MODAUTH: MODx.siteId
        };

        this.uploader = new plupload.Uploader({
            url: ms3.config.connector_url + '?' + Ext.urlEncode(params),
            browse_button: 'ms3-resource-upload-btn',
            container: this.id,
            drop_element: this.id,
            multipart: true,
            max_file_size: ms3.config.media_source.maxUploadSize || MODx.config.upload_maxsize || 10485760,
            filters: [{
                title: "Image files",
                extensions: ms3.config.media_source.allowedFileTypes || MODx.config.upload_images || 'jpg,jpeg,png,gif,webp'
            }],
            resize: {
                width: ms3.config.media_source.maxUploadWidth || 1920,
                height: ms3.config.media_source.maxUploadHeight || 1080
            }
        });

        const uploaderEvents = ['FilesAdded', 'FileUploaded', 'QueueChanged', /*'UploadFile',*/ 'UploadProgress', 'UploadComplete', 'Error'];
        Ext.each(uploaderEvents, function (v) {
            const fn = 'on' + v;
            this.uploader.bind(v, this[fn], this);
        }, this);
        this.uploader.init();
    },

    onFilesAdded: function () {
        this.updateList = true;
    },

    removeFile: function (id) {
        this.updateList = true;
        const f = this.uploader.getFile(id);
        this.uploader.removeFile(f);
    },

    onQueueChanged: function (up) {
        if (this.updateList) {
            if (this.uploader.files.length > 0) {
                this.progress = Ext.MessageBox.progress(_('please_wait'));
                this.uploader.start();
            } else if (this.progress) {
                this.progress.hide();
            }
            up.refresh();
        }
    },

    /*
    onUploadFile: function (uploader, file) {
    this.updateFile(file);
    },
    */

    onUploadProgress: function (uploader, file) {
        if (this.progress) {
            this.progress.updateText(file.name);
            this.progress.updateProgress(file.percent / 100);
        }
    },

    onUploadComplete: function () {
        if (this.progress) {
            this.progress.hide();
        }
        if (this.errors.length > 0) {
            this.fireAlert();
        }
        this.resetUploader();

        const panel = Ext.getCmp('ms3-gallery-images-panel');
        if (panel) {
            panel.view.getStore().reload();
            // Update thumbnail
            MODx.Ajax.request({
                url: ms3.config.connector_url,
                params: {
                    action: 'ModxPro\\MiniShop3\\Processors\\Product\\Get',
                    id: this.record.id
                },
                listeners: {
                    success: {
                        fn: function (r) {
                            panel.view.updateThumb(r.object['thumb']);
                        }
                    }
                }
            });
        }
    },

    onFileUploaded: function (uploader, file, xhr) {
        const r = Ext.util.JSON.decode(xhr.response);
        if (!r.success) {
            this.addError(file.name, r.message);
        }
    },

    onError: function(uploader, error ) {
        MODx.msg.alert(_('error'), error.message)
    },

    resetUploader: function () {
        this.uploader.files = {};
        this.uploader.destroy();
        this.errors = '';
        this._initUploader();
    },

    addError: function (file, message) {
        this.errors += file + ': ' + message + '<br/>';
    },

    fireAlert: function () {
        MODx.msg.alert(_('ms2_errors'), this.errors);
    },

    /*
    updateFile: function(file) {
    this.uploadGrid.updateFile(file);
    },
    */

});
Ext.reg('ms3-gallery-page', ms3.panel.Gallery);
