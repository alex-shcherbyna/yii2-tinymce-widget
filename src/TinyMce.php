<?php

/**
 *
 * TinyMCE Widget for Yii2 renders a tinyMCE js plugin for WYSIWYG editing.
 *
 * @author Alex Shcherbyna <a@shcherbyna.dp.ua>
 * @link https://shcherbyna.dp.ua/
 */

namespace ashch\tinymce;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use ashch\tinymce\assets\TinyMceAsset;
use ashch\tinymce\models\Template;

class TinyMce extends InputWidget
{

    /** @var array TinyMce Widget User configuration */
    public $clientOptions = [];
    //public $options = [];

    /** @var string Must be set to force editor language, if empty - app language OR self::DEFAULT_LANGUAGE is used */
    public $language;

    /** @var array TinyMce Widget default configuration */
    private static $defaultOptions = [
        'language' => 'en',
        'height' => 500,
        'plugins' => [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'template paste textcolor'
        ],
        //'toolbar' => 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | code',
        'toolbar' => 'undo redo | styleselect | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl | code',
        'toolbar_sticky' => true,
        'toolbar_items_size' => 'small',
        'image_advtab' => true,
        'relative_urls' => false,
    ];

    /** @var array Widget settings  - combines defaultOptions and clientOptions */
    private $_options = [];
    private $extendedValidElements = 'div[*],span[*],svg[*],path[*],g[*],img[href|src|name|title|onclick|align|alt|title|width|height|vspace|hspace],font[face|size|color|style],iframe[id|class|width|size|noshade|src|height|frameborder|border|marginwidth|marginheight|target|scrolling|allowtransparency]';

    const DEFAULT_LANGUAGE = 'en';

    /** @var array TinyMce Widget Supported languages */
    private static $languages = [
        'en_US',
        'ru',
        'ru_RU',
        'uk',
        'uk_UA',
    ];

    /** @var array FileManager configuration. */
    public $fileManager; // TODO delet this property use $loadFileManager
    public $loadFileManager;
    public $loadTemplates;
    
    public $triggerSaveOnBeforeValidateForm = true;

    public function init()
    {
        parent::init();

        $this->_options = array_merge(self::$defaultOptions, $this->clientOptions);
        $this->checkAjustLanguage();
        $this->checkAjustContentCss();

    }

    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->_options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->_options);
        }
        $this->registerClientScript();
    }

    protected function registerClientScript()
    {
        $view = $this->getView();

        TinyMceAsset::register($view);

        $id = (!empty($this->options['id']) ? $this->options['id'] : $this->getId());
        $this->_options['selector'] = "#$id";
        if ($this->loadFileManager === true) {
            $this->registerFileManager();
        }
        if ($this->loadTemplates === true) {
            $this->registerTemplates();
        }
        $this->registerExtendedValidElements();
        $options = Json::encode($this->_options);

        $js[] = "tinymce.remove('#$id');tinymce.init($options);";
        if ($this->triggerSaveOnBeforeValidateForm) {
            $js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";
        }

        $view->registerJs(implode("\n", $js));
    }

    protected function registerExtendedValidElements($param = '')
    {
        if (!empty($param)) {
            $extendedValidElements = $param;
        } elseif (isset(\Yii::$app->params['extendedValidElements'])) {
            $extendedValidElements = \Yii::$app->params['extendedValidElements'];
        } else {
            $extendedValidElements = [$this->extendedValidElements];
        }

        if (empty($this->_options['extended_valid_elements'])) {
            $this->_options['extended_valid_elements'] = $extendedValidElements;
        } else {
            $this->_options['extended_valid_elements'] = array_merge($this->_options['extended_valid_elements'], $extendedValidElements);
        }
    }

    protected function registerFileManager()
    {
        $this->_options = array_merge($this->_options, [
            'external_filemanager_path' => \Yii::getAlias('@web') . '/plugins/responsivefilemanager/filemanager/',
            'filemanager_title' => 'Responsive Filemanager',
                ]
        );
        $fileManagerPlugins = [
            //Иконка/кнопка загрузки файла в диалоге вставки изображения.
            'filemanager' => \Yii::getAlias('@web') . '/plugins/responsivefilemanager/filemanager/plugin.min.js',
            //Иконка/кнопка загрузки файла в панеле иснструментов.
            'responsivefilemanager' => \Yii::getAlias('@web') . '/plugins/responsivefilemanager/tinymce/plugins/responsivefilemanager/plugin.min.js',
        ];
        if (empty($this->_options['external_plugins'])) {
            $this->_options['external_plugins'] = $fileManagerPlugins;
        } else {
            $this->_options['external_plugins'] = array_merge($this->_options['external_plugins'], $fileManagerPlugins);
        }
    }
    
    protected function registerTemplates()
    {
        $this->_options['templates'] = Template::getTinyMCETemplates();
    }

    protected function checkAjustLanguage()
    {
        if (empty($this->language)) {
            $this->_options['language'] = Yii::$app->language;
        } else {
            $this->_options['language'] = $this->language;
        }
        if (!in_array($this->_options['language'], self::$languages)) {
            $lang = false;
            foreach (self::$languages as $lng) {
                if (strpos($this->_options['language'], $lng)) {
                    $lang = $lng;
                }
            }
            if ($lang !== false) {
                $this->_options['language'] = $lang;
            } else {
                $this->_options['language'] = self::DEFAULT_LANGUAGE;
            }
        }
        
    }

    protected function checkAjustContentCss()
    {
        if (empty($this->_options['content_css'])) {
            if (isset(\Yii::$app->params['contentCss'])) {
                $this->_options['content_css'] = Yii::$app->params['contentCss'];
            }
        }
    }

}
