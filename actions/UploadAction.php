<?php

namespace budyaga\cropper\actions;

use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use budyaga\cropper\Widget;
use yii\imagine\Image;
use Imagine\Image\Box;
use Yii;

class UploadAction extends Action
{
    public $path;
    public $url;
    public $uploadParam = 'file';
    public $maxSize = 2097152;
    public $extensions = 'jpeg, jpg, png, gif';
    public $jpegQuality = 100;
    public $pngCompressionLevel = 1;
    public $filenamePrefix = '';

    protected $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Widget::registerTranslations();
        if ($this->url === null) {
            throw new InvalidConfigException(Yii::t('cropper', 'MISSING_ATTRIBUTE', ['attribute' => 'url']));
        } else {
            $this->url = rtrim($this->url, '/') . '/';
        }
        if ($this->path === null) {
            throw new InvalidConfigException(Yii::t('cropper', 'MISSING_ATTRIBUTE', ['attribute' => 'path']));
        } else {
            $this->path = rtrim(Yii::getAlias($this->path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName($this->uploadParam);
            $this->model = new DynamicModel(compact($this->uploadParam));
            $this->model->addRule($this->uploadParam, 'image', [
                'maxSize' => $this->maxSize,
                'tooBig' => Yii::t('cropper', 'TOO_BIG_ERROR', ['size' => $this->maxSize / (1024 * 1024)]),
                'extensions' => explode(', ', $this->extensions),
                'wrongExtension' => Yii::t('cropper', 'EXTENSION_ERROR', ['formats' => $this->extensions])
            ])->validate();

            if ($this->model->hasErrors()) {
                $result = [
                    'error' => $this->model->getFirstError($this->uploadParam)
                ];
            } else {
                $this->model->{$this->uploadParam}->name = uniqid($this->filenamePrefix) . '.' . $this->model->{$this->uploadParam}->extension;
                $request = Yii::$app->request;

                $width = (int)$request->post('width');
                $height = (int)$request->post('height');
                $crop_x = (int)$request->post('x');
                $crop_y = (int)$request->post('y');
                $crop_w = (int)abs($request->post('w'));
                $crop_h = (int)abs($request->post('h'));

                if ($crop_x < 0) { $crop_x = 0; }
                if ($crop_y < 0) { $crop_y = 0; }

                $image = Image::crop(
                    $file->tempName . $request->post('filename'),
                    intval($crop_w),
                    intval($crop_h),
                    [$crop_x, $crop_y]
                );

                if (!$width) {
                    $width = null;
                }
                if (!$height) {
                    $height = null;
                }

                // both edges can't be null
                if (!$width && !$height) {
                    $width = $crop_w;
                    $height = $crop_h;
                }

                $image = Image::resize($image, $width, $height);

                if (!file_exists($this->path) || !is_dir($this->path)) {
                    $result = [
                        'error' => Yii::t('cropper', 'ERROR_NO_SAVE_DIR')]
                    ;
                } else {
                    $saveOptions = ['jpeg_quality' => $this->jpegQuality, 'png_compression_level' => $this->pngCompressionLevel];
                    if ($image->save($this->path . $this->model->{$this->uploadParam}->name, $saveOptions)) {
                        $result = [
                            'filelink' => $this->url . $this->model->{$this->uploadParam}->name
                        ];
                    } else {
                        $result = [
                            'error' => Yii::t('cropper', 'ERROR_CAN_NOT_UPLOAD_FILE')
                        ];
                    }
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $result;
        } else {
            throw new BadRequestHttpException(Yii::t('cropper', 'ONLY_POST_REQUEST'));
        }
    }
}
