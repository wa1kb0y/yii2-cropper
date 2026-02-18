<?php
namespace budyaga\cropper\actions;

use creocoder\flysystem\AwsS3Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Visibility;
use Yii;
use yii\helpers\ArrayHelper;

class UploadS3Action extends UploadAction
{
    /**
     * S3 storage component
     * @var string \bilberrry\spaces\Service
     */
    public string $fsComponent = 'fs';

    /**
     * Path for files on S3
     */
    public string $remotePath = '';

    /**
     * Keep a local file of file after uploading to S3
     */
    public bool $keepLocal = false;

    public function run(): array
    {
        $result = parent::run();

        $localPath = $this->path . $this->model->{$this->uploadParam}->name;
        $remotePath = $this->remotePath . DIRECTORY_SEPARATOR . $this->model->{$this->uploadParam}->name;
        if (file_exists($localPath)) {
            try {
                /** @var AwsS3Filesystem $fs */
                $fs = Yii::$app->get($this->fsComponent);
                $fs->write($remotePath, file_get_contents($localPath), ['visibility' => Visibility::PUBLIC]);
                $fileRemoteUrl = $fs->getFilesystem()->publicUrl($remotePath);
                $result = [
                    'filelink' => $fileRemoteUrl,
                ];
            } catch (FilesystemException $e) {
                $result = [];
            }
            if (!$this->keepLocal) {
                unlink($localPath);
            }
        }

        return $result;
    }
}
