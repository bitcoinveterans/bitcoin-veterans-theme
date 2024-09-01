(function() {
    tinymce.create('tinymce.plugins.my_custom_button', {
        init : function(ed, url) {
            ed.addButton('my_custom_button', {
                title : 'Insert Custom Button',
                icon : 'icon dashicons-admin-post',
                onclick : function() {
                    // Open a form to select options for the button
                    ed.windowManager.open({
                        title: 'Insert Custom Button',
                        body: [
                            {
                                type: 'listbox',
                                name: 'buttonType',
                                label: 'Button Type',
                                values: [
                                    { text: 'Primary', value: 'primary' },
                                    { text: 'Secondary', value: 'secondary' }
                                    // Add more button types as needed
                                ]
                            },
                            {
                                type: 'listbox',
                                name: 'buttonColor',
                                label: 'Button Color',
                                values: [
                                    { text: 'Red', value: 'red' },
                                    { text: 'Blue', value: 'blue' }
                                    // Add more colors as needed
                                ]
                            },
                            {
                                type: 'textbox',
                                name: 'buttonLabel',
                                label: 'Button Label',
                                value: ''
                            },
                            {
                                type: 'textbox',
                                name: 'buttonLink',
                                label: 'Button Link',
                                value: ''
                            },
                            {
                                type: 'listbox',
                                name: 'buttonIconGroup',
                                label: 'Button Icon Group',
                                values: [
                                    { text: 'None', value: '' },
                                    { text: 'Icon 1', value: 'icon-1' },
                                    { text: 'Icon 2', value: 'icon-2' }
                                    // Add more icons as needed
                                ]
                            }
                        ],
                        onsubmit: function(e) {
                            // Insert shortcode with selected options
                            ed.insertContent('[acf_button type="' + e.data.buttonType + '" color="' + e.data.buttonColor + '" link="' + e.data.buttonLink + '" label="' + e.data.buttonLabel + '" icon_group="' + e.data.buttonIconGroup + '"]');
                        }
                    });
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('my_custom_button', tinymce.plugins.my_custom_button);
})();
