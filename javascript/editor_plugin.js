(function () {
    if (typeof tinymce !== 'undefined') {

        tinymce.PluginManager.add('shortcodable', function (editor) {
            var me = this;
            const shortcodePlaceholderTemplate = '<span class="shortcodable-placeholder">%shortcode%</span>'

            function insertShortcodeAtCursor(shortcode) {
                let shortcodePlaceholder = shortcodePlaceholderTemplate
                    .replace('%shortcode%', shortcode);

                let node = editor.selection.getNode();
                if (node.nodeName === 'SPAN' && editor.dom.hasClass(node, 'shortcodable-placeholder')) {
                    editor.dom.replace(editor.dom.create('span', {
                        'class': 'shortcodable-placeholder',
                        'shortcode': shortcode
                    }, shortcode), node);
                } else {
                    editor.insertContent(shortcodePlaceholder);
                }
            }

            // replace shortcode strings with placeholder images
            function insertPlaceholders(content) {
                return content.replace(/(\[[A-z0-9_]+( [A-z0-9_]+="[^"]+")*\])/g, function (match, shortcode) {
                    console.log(shortcode);
                    return shortcodePlaceholderTemplate
                        .replace('%shortcode%', shortcode);
                });
            }

            // replace placeholder tags with shortcodes
            function stripPlaceholders(content) {
                return content.replace(/<span class="shortcodable-placeholder( shortcodable-placeholder--removing)?">([^<]+)<\/span>/g, function (match, removing, shortcode) {
                    if (shortcode.match(/\[[A-z0-9_]+( [A-z0-9_]+="[^"]+")*\]/))
                        return shortcode;
                    else
                        return '';
                });
            }

            editor.ui.registry.addButton('shortcodable', {
                icon: 'code-sample',
                tooltip: 'Insert Shortcode',
                onAction: function () {
                    jQuery('#' + editor.id).entwine('ss').openShortcodeDialog(null);
                }
            });

            editor.on('LoadContent', function () {
                // Get the HTML content from the editor
                var content = editor.getContent();
                editor.setContent(insertPlaceholders(content));
            });

            editor.on('DblClick', function (e) {
                var node = e.target;
                if (node.nodeName === 'SPAN' && editor.dom.hasClass(node, 'shortcodable-placeholder'))
                    jQuery('#' + editor.id).entwine('ss').openShortcodeDialog(jQuery(node).text())
            });

            // When the editor has changed, check if the shortcode is being removed
            editor.on('keyup', function (e) {
                if (e.keyCode === 8 || e.keyCode === 46) {
                    var node = editor.selection.getNode();
                    if (node.nodeName === 'SPAN' && editor.dom.hasClass(node, 'shortcodable-placeholder')) {
                        if (node.textContent.match(/\[[A-z0-9_]+( [A-z0-9_]+="[^"]+")*\]/))
                            editor.dom.removeClass(node, 'shortcodable-placeholder--removing');
                        else
                            editor.dom.addClass(node, 'shortcodable-placeholder--removing');
                    }
                }
            });

            return {
                getMetadata: function () {
                    return {
                        longname: 'Shortcodable - Shortcode UI plugin for SilverStripe',
                        author: 'Shea Dawson',
                        authorurl: 'http://www.livesource.co.nz/',
                        infourl: 'http://www.livesource.co.nz/',
                        version: "1.0"
                    };
                },

                insertShortcodeAtCursor: insertShortcodeAtCursor,
                stripPlaceholders: stripPlaceholders,
                insertPlaceholders: insertPlaceholders,
            }
        });
    }
})();
