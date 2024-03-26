<?php

use SilverStripe\Admin\CMSMenu;
use SilverStripe\CMS\Controllers\CMSMain;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\HtmlEditorConfig;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use Violet88\Shortcodable\Controllers\ShortcodableController;
use Violet88\Shortcodable\Shortcodable;

CMSMenu::remove_menu_class(ShortcodableController::class);

// enable shortcodable buttons and add to HtmlEditorConfig
$htmlEditorNames = Config::inst()->get(Shortcodable::class, 'htmleditor_names');
if (is_array($htmlEditorNames)) {
    foreach ($htmlEditorNames as $htmlEditorName) {
        $editorConfig = HtmlEditorConfig::get($htmlEditorName);
        // Transform the editor to a TinyMCEConfig if it is one
        if ($editorConfig instanceof TinyMCEConfig) {
            $editorConfig = TinyMCEConfig::get($htmlEditorName);
            $editorConfig->enablePlugins([
                'shortcodable' => ModuleLoader::inst()->getModule('violet88/silverstripe-shortcodable')
                    ->getResource('javascript/editor_plugin.js'),
            ]);

            $contentCss = $editorConfig->getContentCSS();
            $contentCss[] = ModuleLoader::inst()->getModule('violet88/silverstripe-shortcodable')
                ->getResource('css/editor.css');

            $editorConfig->setContentCSS($contentCss);

            $editorConfig->addButtonsToLine(1, 'shortcodable');

            $editorConfig->setOption('extended_valid_elements', $editorConfig->getOption('extended_valid_elements') . ',sc-marker');
        }
    }
}

// register classes added via yml config
$classes = Config::inst()->get(Shortcodable::class, 'shortcodable_classes');
Shortcodable::register_classes($classes);
