(function () {
    if (typeof tinymce !== 'undefined') {

        tinymce.PluginManager.add('shortcodable', function (editor) {
            const shortcodePlaceholderTemplate = '<sc-marker>%shortcode%</sc-marker>'

            function insertShortcodeAtCursor(shortcode) {
                let shortcodePlaceholder = shortcodePlaceholderTemplate
                    .replace('%shortcode%', shortcode);

                let node = editor.selection.getNode();
                if (node.nodeName === 'SC-MARKER') {
                    editor.dom.replace(editor.dom.create('sc-marker', {
                        'shortcode': shortcode
                    }, shortcode), node);
                } else {
                    editor.insertContent(shortcodePlaceholder);
                }
            }

            function insertPlaceholders(content) {
                return content.replace(/(\[[A-z0-9_]+( [A-z0-9_]+="[^"]+")*\])/g, function (match, shortcode) {
                    return shortcodePlaceholderTemplate
                        .replace('%shortcode%', shortcode);
                });
            }

            function stripPlaceholders(content) {
                return content.replace(/<sc-marker>([^<]+)<\/sc-marker>/g, function (match, shortcode) {
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

            editor.on('Click', function (e) {
                var node = e.target;
                if (node.nodeName === 'SC-MARKER') {
                    jQuery('#' + editor.id).entwine('ss').openShortcodeDialog(jQuery(node).text())
                    editor.selection.select(node);
                }
            });

            // When the editor has changed, check if the shortcode is being removed
            editor.on('keyup', function (e) {
                if (e.keyCode === 8 || e.keyCode === 46) {
                    var node = editor.selection.getNode();
                    if (node.nodeName === 'SC-MARKER') {
                        if (node.textContent.match(/\[[A-z0-9_]+( [A-z0-9_]+="[^"]+")*\]/))
                            editor.dom.replace(editor.dom.create('sc-marker', {
                                'shortcode': node.textContent
                            }, node.textContent), node);
                        else
                            editor.dom.remove(node);
                    }
                }
            });

            return {
                getMetadata: function () {
                    return {
                        longname: 'Shortcodable - Shortcode UI plugin for SilverStripe',
                        author: 'Roël Couwenberg',
                        authorurl: 'https://violet88.nl',
                        infourl: 'https://github.com/Violet88github/silverstripe-shortcodable/',
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