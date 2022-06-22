<?php

namespace budyaga\cropper;

use budyaga\cropper\assets\CropperAsset;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use Yii;

class Widget extends InputWidget
{
    public $uploadParameter = 'file';
    public $width;
    public $height;
    public $label = '';
    public $uploadUrl;
    public $noPhotoImage = '';
    public $maxSize = 2097152;
    public $thumbnailWidth = 300;
    public $thumbnailHeight = 300;
    public $cropAreaWidth = 300;
    public $cropAreaHeight = 300;
    public $extensions = 'jpeg, jpg, png, gif';
    public $onCompleteJcrop;
    public $pluginOptions = [];
    public $aspectRatio = null;
    public $asModal = false;
    public $modalOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::registerTranslations();

        if ($this->uploadUrl === null) {
            throw new InvalidConfigException(Yii::t('cropper', 'MISSING_ATTRIBUTE', ['attribute' => 'uploadUrl']));
        } else {
            $this->uploadUrl = rtrim(Yii::getAlias($this->uploadUrl), '/');
        }

        if ($this->label == '') {
            $this->label = Yii::t('cropper', 'DEFAULT_LABEL');
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientAssets();

        $view = 'widget';
        if ($this->asModal) {
            $view = 'modal';
        }

        return $this->render($view, [
            'model' => $this->model,
            'widget' => $this
        ]);
    }

    /**
     * Register widget asset.
     */
    public function registerClientAssets()
    {
        $view = $this->getView();
        $assets = CropperAsset::register($view);

        if ($this->noPhotoImage == '') {
            $this->noPhotoImage = $assets->baseUrl . '/img/nophoto.png';
        }

        $settings = array_merge([
            'url' => $this->uploadUrl,
            'name' => $this->uploadParameter,
            'maxSize' => $this->maxSize / 1024,
            'allowedExtensions' => explode(', ', $this->extensions),
            'size_error_text' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
            'ext_error_text' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions]),
            'accept' => 'image/*',
        ], $this->pluginOptions);

        if(is_numeric($this->aspectRatio)) {
                $settings['aspectRatio'] = $this->aspectRatio;
        }

        if ($this->onCompleteJcrop)
            $settings['onCompleteJcrop'] = $this->onCompleteJcrop;


        $selector_width = $this->width ? $this->width : 0;
        $selector_height = $this->height ? $this->height : 0;
        if (!$selector_width || !$selector_height) {
            $selector_width = 0;
            $selector_height = 0;
        }


        $view->registerJs(
            'jQuery("#' . $this->options['id'] . '").parent().find(".new-photo-area").cropper(' . Json::encode($settings) . ', ' . $selector_width . ', ' . $selector_height . ');',
            $view::POS_READY
        );
    }

    /**
     * Register widget translations.
     */
    public static function registerTranslations()
    {
        if (!isset(Yii::$app->i18n->translations['cropper']) && !isset(Yii::$app->i18n->translations['cropper/*'])) {
            Yii::$app->i18n->translations['cropper'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@budyaga/cropper/messages',
                'forceTranslation' => true,
                'fileMap' => [
                    'cropper' => 'cropper.php'
                ]
            ];
        }
    }
}
